<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CsvImporter;
use App\Services\CtiClient;
use App\Services\Totp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class PrimeFlowTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role = 'analista'): User
    {
        $u = User::factory()->create(['password' => Hash::make('Testing-password-123')]);
        DB::table('users')->where('id', $u->id)->update(['role' => $role, 'mfa_secret' => Crypt::encryptString((new Totp)->secret())]);

        return $u->fresh();
    }

    private function asRole(string $role = 'analista'): User
    {
        $u = $this->user($role);
        $this->actingAs($u)->withSession(['mfa_verified' => true]);

        return $u;
    }

    private function csv(string $id = 'T01', string $asset = 'HOST-TEST'): string
    {
        $s = fopen('php://temp', 'r+');
        fputcsv($s, CsvImporter::HEADERS, ',', '"', '');
        fputcsv($s, [$id, '', $asset, 'Dev', 'Servicio de prueba', '1.0', '4.0', 'CVSS:4.0/AV:N/AC:L/AT:N/PR:N/UI:N/VC:H/VI:H/VA:H/SC:N/SI:N/SA:N', 9.3, 4, 4, 4, 4, 4, 'Caso sintético para prueba automatizada.'], ',', '"', '');
        rewind($s);

        return stream_get_contents($s);
    }

    private function import(string $content)
    {
        return $this->postJson('/api/imports', ['file' => UploadedFile::fake()->createWithContent('input.csv', $content)]);
    }

    public function test_pending_session_cannot_read_data_then_totp_enrollment_works(): void
    {
        $u = $this->user();
        $this->postJson('/api/auth/login', ['email' => $u->email, 'password' => 'Testing-password-123'])->assertOk()->assertJson(['enrollment' => true]);
        $this->getJson('/api/findings')->assertUnauthorized();
        $code = (new Totp)->code(Crypt::decryptString($u->mfa_secret), intdiv(time(), 30));
        $this->postJson('/api/auth/mfa', ['code' => $code])->assertOk()->assertJsonCount(8, 'recovery_codes');
        $this->getJson('/api/findings')->assertOk();
    }

    public function test_rbac_denies_auditor_import_and_analyst_audit(): void
    {
        $this->asRole('auditor');
        $this->import($this->csv())->assertForbidden();
        $this->getJson('/api/audit')->assertOk();
        $this->asRole('analista');
        $this->getJson('/api/audit')->assertForbidden();
    }

    public function test_import_persists_dread_dimensions_and_rejects_duplicates_and_formula(): void
    {
        Storage::fake('local');
        $this->asRole();
        $this->import($this->csv())->assertOk()->assertJson(['count' => 1]);
        $this->assertDatabaseCount('findings', 1);
        $this->assertDatabaseCount('triage_revisions', 1);
        $this->import($this->csv())->assertUnprocessable();
        $this->import($this->csv('T02', '=1+1'))->assertUnprocessable();
        $this->assertDatabaseCount('findings', 1);
    }

    public function test_clearing_the_queue_archives_without_deleting_and_allows_reimport(): void
    {
        Storage::fake('local');
        $user = $this->asRole();
        $this->import($this->csv())->assertOk();
        $this->postJson('/api/queue/clear')->assertOk()->assertJson(['count' => 1]);

        $this->assertDatabaseCount('findings', 1);
        $this->assertDatabaseCount('triage_revisions', 1);
        $this->getJson('/api/findings')->assertOk()->assertJsonCount(0, 'findings');
        $this->postJson('/api/queue/clear')->assertUnprocessable();

        $archives = $this->getJson('/api/queue/archives')->assertOk()->json('archives');
        $this->assertCount(1, $archives);
        $this->assertSame($user->email, $archives[0]['user_email']);
        $detail = $this->getJson('/api/queue/archives/'.$archives[0]['id'])->assertOk();
        $this->assertSame('T01', $detail->json('findings.0.reference'));
        $this->assertCount(1, $detail->json('batches'));
        $this->assertDatabaseHas('audit_events', ['event' => 'queue_cleared']);

        $this->import($this->csv())->assertOk();
        $this->getJson('/api/findings')->assertOk()->assertJsonCount(1, 'findings');
    }

    public function test_auditor_can_read_archives_but_not_clear_the_queue(): void
    {
        $this->asRole('auditor');
        $this->postJson('/api/queue/clear')->assertForbidden();
        $this->getJson('/api/queue/archives')->assertOk();
    }

    public function test_old_revision_survives_recalculation(): void
    {
        Storage::fake('local');
        $this->asRole();
        $this->import($this->csv())->assertOk();
        $before = DB::table('triage_revisions')->first()->snapshot;
        $id = DB::table('findings')->first()->id;
        $this->postJson("/api/findings/$id/cti")->assertOk();
        $this->assertDatabaseCount('triage_revisions', 2);
        $this->assertSame($before, DB::table('triage_revisions')->orderBy('id')->first()->snapshot);
    }

    public function test_treatment_requires_proposal_evidence_and_another_reviewer(): void
    {
        Storage::fake('local');
        $analyst = $this->asRole();
        $this->import($this->csv())->assertOk();
        $id = DB::table('findings')->first()->id;
        $this->postJson("/api/findings/$id/propose", ['treatment' => 'Propuesta sintética; verificar conectividad.'])->assertOk();
        $admin = $this->asRole('administrador');
        $this->postJson("/api/findings/$id/review", ['note' => 'Revisión de archivo de prueba.'])->assertUnprocessable();
        $this->postJson("/api/findings/$id/evidence", ['file' => UploadedFile::fake()->createWithContent('resultado.txt', 'Prueba sintética, sin inferencia de laboratorio.')])->assertOk();
        $this->postJson("/api/findings/$id/review", ['note' => 'Revisión de evidencia sintética de prueba.'])->assertOk();
        $this->assertDatabaseHas('findings', ['id' => $id, 'status' => 'mitigado']);
    }

    public function test_cti_refresh_uses_prime_1_and_preserves_the_legacy_calculation(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([
            'api.first.org/*' => Http::response(['data' => [
                ['cve' => 'CVE-2020-0001', 'epss' => '0.8', 'percentile' => '0.9', 'date' => '2026-11-02'],
            ]]),
            'www.cisa.gov/*' => Http::response([
                'catalogVersion' => 'test-1', 'dateReleased' => '2026-11-02', 'vulnerabilities' => [],
            ]),
        ]);
        $this->asRole();
        $this->import(str_replace('T01,,', 'T01,CVE-2020-0001,', $this->csv()))->assertOk();
        $revision = DB::table('triage_revisions')->first();
        $legacy = json_decode($revision->calculation, true);
        $legacy['version'] = 'PRIME-experimental-0.1';
        $legacy['score'] = 10;
        $legacy['display_score'] = '10.00';
        $legacy['severity'] = 'Crítico';
        $legacy['priority'] = 'Crítico';
        $legacy['epss_applied'] = true;
        $legacyJson = json_encode($legacy);
        DB::table('triage_revisions')->where('id', $revision->id)->update([
            'policy_version' => 'PRIME-experimental-0.1', 'calculation' => $legacyJson,
            'score' => 10, 'priority' => 'Crítico',
        ]);

        $this->postJson("/api/findings/{$revision->finding_id}/cti")->assertOk();
        $this->getJson('/api/findings')->assertOk()
            ->assertJsonPath('policy', 'PRIME-1.0')
            ->assertJsonPath('findings.0.calculation.version', 'PRIME-1.0')
            ->assertJsonPath('findings.0.calculation.epss_applied', false)
            ->assertJsonPath('findings.0.cti.epss.value', 0.8);
        $latest = DB::table('triage_revisions')->latest('id')->first();
        $this->assertEqualsWithDelta(7.18, $latest->score, .00001);
        $original = DB::table('triage_revisions')->find($revision->id);
        $this->assertSame($legacyJson, $original->calculation);
        $this->assertSame($revision->snapshot, $original->snapshot);
        $this->assertSame('PRIME-experimental-0.1', $original->policy_version);
        $export = $this->get('/api/export')->assertOk()->streamedContent();
        $this->assertStringContainsString('Versión PRIME', $export);
        $this->assertStringContainsString('PRIME-1.0', $export);
    }

    public function test_cti_timeout_missing_cve_and_snapshot_are_distinct(): void
    {
        Http::fake(['api.first.org/*' => Http::sequence()->push(['data' => []])->push('failure', 503), 'www.cisa.gov/*' => Http::response(['catalogVersion' => 'test-1', 'dateReleased' => '2026-10-03', 'vulnerabilities' => []])]);
        $x = (new CtiClient)->fetch('CVE-2020-0001');
        $this->assertSame('missing', $x['epss']['status']);
        $this->assertSame(false, $x['kev']['value']);
        $this->assertArrayHasKey('raw', $x['epss']);
        $x = (new CtiClient)->fetch('CVE-2020-0001');
        $this->assertSame('error', $x['epss']['status']);
        $this->assertNull($x['epss']['value']);
    }

    public function test_bad_encoding_empty_and_wrong_headers_are_rejected(): void
    {
        $this->asRole();
        foreach (["id,cve\nT01,\n", implode(',', CsvImporter::HEADERS)."\n", "\xff\xfe".$this->csv()] as $csv) {
            $this->import($csv)->assertUnprocessable();
        }
    }

    public function test_malicious_text_is_stored_as_data_and_exported_as_quoted_csv(): void
    {
        Storage::fake('local');
        $this->asRole();
        $payload = "<img src=x onerror=alert(1)> ' OR '1'='1";
        $this->import($this->csv('T03', $payload))->assertOk();
        $this->assertDatabaseHas('findings', ['asset' => $payload]);
        $this->getJson('/api/findings')->assertOk()->assertJsonCount(1, 'findings');
        $this->get('/api/export')->assertOk();
    }

    public function test_every_protected_endpoint_requires_verified_mfa(): void
    {
        $endpoints = [
            ['GET', '/api/findings'],
            ['GET', '/api/findings/1/history'],
            ['GET', '/api/evidence/1'],
            ['GET', '/api/export'],
            ['GET', '/api/audit'],
            ['POST', '/api/auth/logout'],
            ['POST', '/api/imports'],
            ['POST', '/api/queue/clear'],
            ['GET', '/api/queue/archives'],
            ['GET', '/api/queue/archives/1'],
            ['POST', '/api/findings/1/cti'],
            ['POST', '/api/findings/1/propose'],
            ['POST', '/api/findings/1/evidence'],
            ['POST', '/api/findings/1/review'],
        ];
        foreach ($endpoints as [$method, $url]) {
            $this->json($method, $url)->assertUnauthorized();
        }
        $this->actingAs($this->user('administrador'))->withSession(['mfa_verified' => false]);
        foreach ($endpoints as [$method, $url]) {
            $this->json($method, $url)->assertUnauthorized();
        }
    }

    public function test_read_only_role_cannot_mutate_findings_and_analyst_cannot_review(): void
    {
        $this->asRole('auditor');
        foreach (['cti', 'propose', 'evidence', 'review'] as $action) {
            $this->postJson("/api/findings/1/$action")->assertForbidden();
        }
        $this->asRole('analista');
        $this->postJson('/api/findings/1/review')->assertForbidden();
    }

    public function test_session_shape_and_logout_invalidate_access(): void
    {
        $user = $this->asRole();
        $this->getJson('/api/session')->assertOk()->assertJson([
            'user' => ['name' => $user->name, 'email' => $user->email, 'role' => 'analista'],
        ])->assertJsonMissingPath('user.password')->assertJsonMissingPath('user.mfa_secret');
        $this->postJson('/api/auth/logout')->assertOk()->assertJsonStructure(['csrf']);
        $this->getJson('/api/session')->assertOk()->assertJson(['user' => null]);
        $this->getJson('/api/findings')->assertUnauthorized();
    }

    public function test_recovery_code_is_consumed_once_without_generating_new_codes(): void
    {
        $user = $this->user();
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'Testing-password-123'])->assertOk();
        $code = (new Totp)->code(Crypt::decryptString($user->mfa_secret), intdiv(time(), 30));
        $recovery = $this->postJson('/api/auth/mfa', ['code' => $code])->assertOk()->json('recovery_codes');
        $this->assertCount(8, $recovery);
        $this->postJson('/api/auth/logout')->assertOk();
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'Testing-password-123'])
            ->assertOk()->assertJson(['enrollment' => false])->assertJsonMissingPath('secret');
        $this->postJson('/api/auth/mfa', ['code' => $recovery[0]])->assertOk()->assertJsonCount(0, 'recovery_codes');
        $this->assertCount(7, json_decode(DB::table('users')->find($user->id)->recovery_hashes, true));
        $this->postJson('/api/auth/logout')->assertOk();
        $this->travel(61)->seconds();
        $this->postJson('/api/auth/login', ['email' => $user->email, 'password' => 'Testing-password-123'])->assertOk();
        $this->postJson('/api/auth/mfa', ['code' => $recovery[0]])->assertUnprocessable();
        $this->getJson('/api/findings')->assertUnauthorized();
    }

    public function test_expired_mfa_challenge_cannot_authenticate(): void
    {
        $user = $this->user();
        $this->withSession(['pending_user' => $user->id, 'pending_until' => time() - 1]);
        $code = (new Totp)->code(Crypt::decryptString($user->mfa_secret), intdiv(time(), 30));
        $this->postJson('/api/auth/mfa', ['code' => $code])->assertUnauthorized();
        $this->assertDatabaseHas('users', ['id' => $user->id, 'mfa_confirmed' => false]);
    }

    public function test_proposer_cannot_review_own_treatment_even_with_evidence(): void
    {
        Storage::fake('local');
        $this->asRole('administrador');
        $this->import($this->csv())->assertOk();
        $id = DB::table('findings')->first()->id;
        $this->postJson("/api/findings/$id/propose", ['treatment' => 'Control de prueba.'])->assertOk();
        $this->postJson("/api/findings/$id/evidence", [
            'file' => UploadedFile::fake()->createWithContent('evidence.txt', 'Evidencia sintética.'),
        ])->assertOk();
        $this->postJson("/api/findings/$id/review", ['note' => 'Revisado por la misma persona.'])->assertForbidden();
        $this->assertDatabaseHas('findings', ['id' => $id, 'status' => 'propuesto']);
    }

    public function test_evidence_download_and_history_preserve_file_metadata(): void
    {
        Storage::fake('local');
        $this->asRole();
        $this->import($this->csv())->assertOk();
        $id = DB::table('findings')->first()->id;
        $content = 'Resultado de prueba sintética.';
        $evidenceId = $this->postJson("/api/findings/$id/evidence", [
            'file' => UploadedFile::fake()->createWithContent('resultado.txt', $content),
        ])->assertOk()->json('id');
        $this->getJson("/api/findings/$id/history")->assertOk()
            ->assertJsonCount(1, 'revisions')->assertJsonCount(1, 'evidence')
            ->assertJsonPath('evidence.0.sha256', hash('sha256', $content))
            ->assertJsonMissingPath('evidence.0.path');
        $download = $this->get("/api/evidence/$evidenceId")->assertOk()->assertDownload('resultado.txt');
        $this->assertSame($content, $download->streamedContent());
    }

    public function test_cti_list_omits_raw_responses_but_history_keeps_them(): void
    {
        Storage::fake('local');
        Http::preventStrayRequests();
        Http::fake([
            'api.first.org/*' => Http::response(['data' => [
                ['cve' => 'CVE-2020-0001', 'epss' => '0.12', 'percentile' => '0.8', 'date' => '2026-10-03'],
            ]]),
            'www.cisa.gov/*' => Http::response([
                'catalogVersion' => 'test-1', 'dateReleased' => '2026-10-03',
                'vulnerabilities' => [['cveID' => 'CVE-2020-0001']],
            ]),
        ]);
        $this->asRole();
        $this->import(str_replace('T01,,', 'T01,CVE-2020-0001,', $this->csv()))->assertOk();
        $id = DB::table('findings')->first()->id;
        $this->postJson("/api/findings/$id/cti")->assertOk();
        $this->getJson('/api/findings')->assertOk()
            ->assertJsonPath('findings.0.calculation.kev_urgent', true)
            ->assertJsonMissingPath('findings.0.cti.epss.raw')
            ->assertJsonMissingPath('findings.0.cti.kev.raw');
        $this->getJson("/api/findings/$id/history")->assertOk()->assertJsonCount(2, 'revisions')
            ->assertJsonStructure(['revisions' => [1 => ['snapshot' => ['epss' => ['raw'], 'kev' => ['raw']]]]]);
        $export = $this->get('/api/export')->assertOk()->assertDownload('prime-triaje.csv')->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $export);
        $this->assertStringContainsString('CVE-2020-0001', $export);
        $this->assertStringContainsString('Urgente KEV', $export);
    }
}
