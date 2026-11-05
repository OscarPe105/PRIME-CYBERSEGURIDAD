<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\CsvImporter;
use App\Services\CtiClient;
use App\Services\FindingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

final class ImportController extends Controller
{
    public function __construct(private readonly AuditLogger $audit, private readonly FindingService $findings) {}

    public function upload(Request $request, CsvImporter $csv, CtiClient $cti): array
    {
        $request->validate(['file' => 'required|file|max:2048']);
        $file = $request->file('file');
        $rows = $csv->parse($file->getRealPath());
        if (DB::table('findings')->whereIn('reference', array_column($rows, 'id'))->exists()) {
            throw ValidationException::withMessages(['file' => 'Existe un ID previamente importado. No se sobrescribieron hallazgos.']);
        }
        $path = $file->store('imports', 'local');
        try {
            $batch = DB::transaction(function () use ($rows, $path, $file, $cti) {
                $batch = DB::table('import_batches')->insertGetId([
                    'uploaded_by' => Auth::id(),
                    'path' => $path,
                    'sha256' => hash_file('sha256', $file->getRealPath()),
                    'count' => count($rows),
                    'created_at' => now(),
                ]);
                foreach ($rows as $row) {
                    $id = DB::table('findings')->insertGetId([
                        'batch_id' => $batch,
                        'reference' => $row['id'],
                        'cve' => $row['cve'],
                        'asset' => $row['asset'],
                        'zone' => $row['zone'],
                        'product' => $row['product'],
                        'product_version' => $row['version'],
                        'input' => json_encode($row),
                        'status' => 'abierto',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    $this->findings->revise($id, $row, $cti->pending($row['cve']));
                }
                $this->audit->record('csv_imported', ['batch' => $batch, 'count' => count($rows)]);

                return $batch;
            });
        } catch (\Throwable $e) {
            Storage::disk('local')->delete($path);
            throw $e;
        }

        return ['batch_id' => $batch, 'count' => count($rows)];
    }
}
