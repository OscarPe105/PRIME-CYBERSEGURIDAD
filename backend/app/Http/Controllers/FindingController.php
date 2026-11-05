<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\CsvSafety;
use App\Services\CtiClient;
use App\Services\FindingService;
use App\Services\PrimeEngine;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class FindingController extends Controller
{
    public function __construct(
        private readonly AuditLogger $audit,
        private readonly FindingService $findings,
    ) {}

    public function index(): array
    {
        return ['findings' => $this->findings->all(), 'policy' => PrimeEngine::VERSION];
    }

    public function enrich(int $id, CtiClient $cti): array
    {
        $finding = DB::table('findings')->find($id);
        abort_unless($finding, 404);
        $snapshot = $cti->fetch($finding->cve);
        DB::transaction(function () use ($finding, $snapshot) {
            $this->findings->revise($finding->id, json_decode($finding->input, true), $snapshot);
            $this->audit->record('cti_refreshed', [
                'finding' => $finding->id,
                'epss_status' => $snapshot['epss']['status'],
                'kev_status' => $snapshot['kev']['status'],
            ]);
        });

        return [
            'updated' => true,
            'epss_status' => $snapshot['epss']['status'],
            'kev_status' => $snapshot['kev']['status'],
        ];
    }

    public function history(int $id): array
    {
        abort_unless(DB::table('findings')->find($id), 404);
        $revisions = DB::table('triage_revisions')->where('finding_id', $id)->orderBy('id')->get();
        $revisions->transform(function ($revision) {
            $revision->snapshot = json_decode($revision->snapshot, true);
            $revision->calculation = json_decode($revision->calculation, true);

            return $revision;
        });

        return [
            'revisions' => $revisions,
            'evidence' => DB::table('evidence_files')->where('finding_id', $id)
                ->get(['id', 'name', 'sha256', 'size', 'created_at']),
        ];
    }

    public function export(): StreamedResponse
    {
        $rows = $this->findings->all();

        return response()->streamDownload(function () use ($rows) {
            $output = fopen('php://output', 'w');
            try {
                fwrite($output, "\xEF\xBB\xBF");
                fputcsv($output, ['ID', 'CVE', 'Activo', 'Zona', 'Prioridad', 'IRC', 'CTI provisional', 'Estado', 'Versión PRIME'], ',', '"', '');
                foreach ($rows as $finding) {
                    $row = [
                        $finding['reference'],
                        $finding['cve'] ?? 'No aplica',
                        $finding['asset'],
                        $finding['zone'],
                        $finding['calculation']['priority'],
                        $finding['calculation']['display_score'],
                        $finding['calculation']['provisional'] ? 'Sí' : 'No',
                        $finding['status'],
                        $finding['calculation']['version'],
                    ];
                    fputcsv($output, array_map(CsvSafety::escape(...), $row), ',', '"', '');
                }
            } finally {
                fclose($output);
            }
        }, 'prime-triaje.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
