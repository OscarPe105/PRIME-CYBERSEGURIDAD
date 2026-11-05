<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class EvidenceController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function evidence(Request $request, int $id): array
    {
        abort_unless(DB::table('findings')->find($id), 404);
        $request->validate(['file' => 'required|file|max:2048|mimes:txt,png,pdf,json']);
        $file = $request->file('file');
        $path = $file->store('evidence', 'local');
        $evidenceId = DB::table('evidence_files')->insertGetId([
            'finding_id' => $id,
            'uploaded_by' => Auth::id(),
            'path' => $path,
            'name' => mb_substr(basename($file->getClientOriginalName()), 0, 200),
            'sha256' => hash_file('sha256', $file->getRealPath()),
            'size' => $file->getSize(),
            'created_at' => now(),
        ]);
        $this->audit->record('evidence_uploaded', ['finding' => $id, 'evidence' => $evidenceId]);

        return ['id' => $evidenceId];
    }

    public function download(int $id): StreamedResponse
    {
        $evidence = DB::table('evidence_files')->find($id);
        abort_unless($evidence, 404);

        return Storage::disk('local')->download($evidence->path, $evidence->name, ['X-Content-Type-Options' => 'nosniff']);
    }
}
