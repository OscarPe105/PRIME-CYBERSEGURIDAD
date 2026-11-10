<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\FindingService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/** Limpieza de la cola de tratamiento: archiva sin borrar y deja registro consultable. */
final class QueueController extends Controller
{
    public function __construct(private readonly AuditLogger $audit, private readonly FindingService $findings) {}

    public function clear(): array
    {
        $archiveId = DB::transaction(function () {
            $active = DB::table('findings')->whereNull('archive_id')->count();
            if ($active === 0) {
                throw ValidationException::withMessages(['queue' => 'La cola de tratamiento ya está vacía.']);
            }
            $id = DB::table('queue_archives')->insertGetId([
                'archived_by' => Auth::id(),
                'count' => $active,
                'created_at' => now(),
            ]);
            DB::table('findings')->whereNull('archive_id')->update(['archive_id' => $id, 'archive_key' => $id]);
            $this->audit->record('queue_cleared', ['archive' => $id, 'count' => $active]);

            return $id;
        });

        return ['archive_id' => $archiveId, 'count' => DB::table('queue_archives')->where('id', $archiveId)->value('count')];
    }

    public function index(): array
    {
        $archives = DB::table('queue_archives')
            ->join('users', 'users.id', '=', 'queue_archives.archived_by')
            ->orderByDesc('queue_archives.id')
            ->get(['queue_archives.id', 'queue_archives.count', 'queue_archives.created_at', 'users.name as user_name', 'users.email as user_email']);

        return ['archives' => $archives];
    }

    public function show(int $id): array
    {
        $archive = DB::table('queue_archives')
            ->join('users', 'users.id', '=', 'queue_archives.archived_by')
            ->where('queue_archives.id', $id)
            ->first(['queue_archives.id', 'queue_archives.count', 'queue_archives.created_at', 'users.name as user_name', 'users.email as user_email']);
        abort_unless($archive, 404);

        $findings = $this->findings->archived($id);
        $batchIds = array_values(array_unique(array_column($findings, 'batch_id')));
        $batches = DB::table('import_batches')
            ->join('users', 'users.id', '=', 'import_batches.uploaded_by')
            ->whereIn('import_batches.id', $batchIds)
            ->get(['import_batches.id', 'import_batches.count', 'import_batches.created_at', 'users.name as user_name', 'users.email as user_email']);

        return ['archive' => $archive, 'batches' => $batches, 'findings' => $findings];
    }
}
