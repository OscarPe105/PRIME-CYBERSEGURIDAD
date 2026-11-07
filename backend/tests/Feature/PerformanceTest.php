<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CsvImporter;
use App\Services\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Evidencias E14 y E15: tiempos de importación y consulta con 25, 100 y 500 filas.
 * Usa la BD de ensayo en memoria (phpunit.xml) y datos sintéticos; sin red.
 * Escribe los tiempos en evidencias-iniciales/nuevas/E14_E15_rendimiento.json.
 */
final class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private function csv(int $rows): string
    {
        $s = fopen('php://temp', 'r+');
        fputcsv($s, CsvImporter::HEADERS, ',', '"', '');
        for ($i = 1; $i <= $rows; $i++) {
            fputcsv($s, [sprintf('P%04d', $i), '', "HOST-$i", 'Dev', 'Servicio sintético', '1.0', '4.0', 'CVSS:4.0/AV:N/AC:L/AT:N/PR:N/UI:N/VC:H/VI:H/VA:H/SC:N/SI:N/SA:N', 9.3, 4, 4, 4, 4, 4, 'Caso sintético de rendimiento.'], ',', '"', '');
        }
        rewind($s);

        return stream_get_contents($s);
    }

    public function test_import_and_list_timings_for_25_100_and_500_rows(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        $results = [];
        foreach ([25, 100, 500] as $n) {
            DB::table('triage_revisions')->delete();
            DB::table('findings')->delete();
            DB::table('import_batches')->delete();
            $u = User::factory()->create(['password' => Hash::make('Testing-password-123')]);
            DB::table('users')->where('id', $u->id)->update(['role' => 'analista', 'mfa_secret' => Crypt::encryptString((new Totp)->secret())]);
            $this->actingAs($u->fresh())->withSession(['mfa_verified' => true]);

            $t = hrtime(true);
            $this->postJson('/api/imports', ['file' => UploadedFile::fake()->createWithContent('input.csv', $this->csv($n))])
                ->assertOk()->assertJson(['count' => $n]);
            $import = (hrtime(true) - $t) / 1e6;

            $t = hrtime(true);
            $this->getJson('/api/findings')->assertOk();
            $list = (hrtime(true) - $t) / 1e6;

            $this->assertDatabaseCount('findings', $n);
            $results[] = ['rows' => $n, 'import_ms' => round($import, 1), 'list_ms' => round($list, 1)];
        }
        $dir = base_path('../evidencias-iniciales/nuevas');
        is_dir($dir) || mkdir($dir, 0775, true);
        file_put_contents($dir.'/E14_E15_rendimiento.json', json_encode(['date' => date('c'), 'php' => PHP_VERSION, 'db' => 'sqlite :memory:', 'results' => $results], JSON_PRETTY_PRINT));
        $this->assertCount(3, $results);
    }
}
