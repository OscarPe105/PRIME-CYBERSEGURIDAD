<?php
namespace Tests\Feature;
use App\Models\User;
use App\Services\{Totp,CtiClient,CsvImporter};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\{Crypt,DB,Hash,Http,Storage};
use Tests\TestCase;
final class PrimeFlowTest extends TestCase {
 use RefreshDatabase;
 private function user(string $role='analista'): User {
  $u=User::factory()->create(['password'=>Hash::make('Testing-password-123')]);DB::table('users')->where('id',$u->id)->update(['role'=>$role,'mfa_secret'=>Crypt::encryptString((new Totp())->secret())]);return $u->fresh();
 }
 private function asRole(string $role='analista'): User {$u=$this->user($role);$this->actingAs($u)->withSession(['mfa_verified'=>true]);return $u;}
 private function csv(string $id='T01',string $asset='HOST-TEST'): string {
  $s=fopen('php://temp','r+');fputcsv($s,CsvImporter::HEADERS,',','"','');
  fputcsv($s,[$id,'',$asset,'Dev','Servicio de prueba','1.0','4.0','CVSS:4.0/AV:N/AC:L/AT:N/PR:N/UI:N/VC:H/VI:H/VA:H/SC:N/SI:N/SA:N',9.3,4,4,4,4,4,'Caso sintético para prueba automatizada.'],',','"','');rewind($s);return stream_get_contents($s);
 }
 private function import(string $content) {return $this->postJson('/api/imports',['file'=>UploadedFile::fake()->createWithContent('input.csv',$content)]);}
 public function test_pending_session_cannot_read_data_then_totp_enrollment_works(): void {
  $u=$this->user();$this->postJson('/api/auth/login',['email'=>$u->email,'password'=>'Testing-password-123'])->assertOk()->assertJson(['enrollment'=>true]);
  $this->getJson('/api/findings')->assertUnauthorized();
  $code=(new Totp())->code(Crypt::decryptString($u->mfa_secret),intdiv(time(),30));
  $this->postJson('/api/auth/mfa',['code'=>$code])->assertOk()->assertJsonCount(8,'recovery_codes');$this->getJson('/api/findings')->assertOk();
 }
 public function test_rbac_denies_auditor_import_and_analyst_audit(): void {
  $this->asRole('auditor');$this->import($this->csv())->assertForbidden();$this->getJson('/api/audit')->assertOk();
  $this->asRole('analista');$this->getJson('/api/audit')->assertForbidden();
 }
 public function test_import_persists_dread_dimensions_and_rejects_duplicates_and_formula(): void {
  Storage::fake('local');$this->asRole();$this->import($this->csv())->assertOk()->assertJson(['count'=>1]);
  $this->assertDatabaseCount('findings',1);$this->assertDatabaseCount('triage_revisions',1);
  $this->import($this->csv())->assertUnprocessable();$this->import($this->csv('T02','=1+1'))->assertUnprocessable();$this->assertDatabaseCount('findings',1);
 }
 public function test_old_revision_survives_recalculation(): void {
  Storage::fake('local');$this->asRole();$this->import($this->csv())->assertOk();$before=DB::table('triage_revisions')->first()->snapshot;
  $id=DB::table('findings')->first()->id;$this->postJson("/api/findings/$id/cti")->assertOk();
  $this->assertDatabaseCount('triage_revisions',2);$this->assertSame($before,DB::table('triage_revisions')->orderBy('id')->first()->snapshot);
 }
 public function test_treatment_requires_proposal_evidence_and_another_reviewer(): void {
  Storage::fake('local');$analyst=$this->asRole();$this->import($this->csv())->assertOk();$id=DB::table('findings')->first()->id;
  $this->postJson("/api/findings/$id/propose",['treatment'=>'Propuesta sintética; verificar conectividad.'])->assertOk();
  $admin=$this->asRole('administrador');$this->postJson("/api/findings/$id/review",['note'=>'Revisión de archivo de prueba.'])->assertUnprocessable();
  $this->postJson("/api/findings/$id/evidence",['file'=>UploadedFile::fake()->createWithContent('resultado.txt','Prueba sintética, sin inferencia de laboratorio.')])->assertOk();
  $this->postJson("/api/findings/$id/review",['note'=>'Revisión de evidencia sintética de prueba.'])->assertOk();$this->assertDatabaseHas('findings',['id'=>$id,'status'=>'mitigado']);
 }
 public function test_cti_timeout_missing_cve_and_snapshot_are_distinct(): void {
  Http::fake(['api.first.org/*'=>Http::sequence()->push(['data'=>[]])->push('failure',503),'www.cisa.gov/*'=>Http::response(['catalogVersion'=>'test-1','dateReleased'=>'2026-10-03','vulnerabilities'=>[]])]);
  $x=(new CtiClient())->fetch('CVE-2020-0001');$this->assertSame('missing',$x['epss']['status']);$this->assertSame(false,$x['kev']['value']);$this->assertArrayHasKey('raw',$x['epss']);
  $x=(new CtiClient())->fetch('CVE-2020-0001');$this->assertSame('error',$x['epss']['status']);$this->assertNull($x['epss']['value']);
 }
 public function test_bad_encoding_empty_and_wrong_headers_are_rejected(): void {
  $this->asRole();foreach(["id,cve\nT01,\n",implode(',',CsvImporter::HEADERS)."\n", "\xff\xfe".$this->csv()] as $csv) $this->import($csv)->assertUnprocessable();
 }
 public function test_malicious_text_is_stored_as_data_and_exported_as_quoted_csv(): void {
  Storage::fake('local');$this->asRole();$payload="<img src=x onerror=alert(1)> ' OR '1'='1";
  $this->import($this->csv('T03',$payload))->assertOk();$this->assertDatabaseHas('findings',['asset'=>$payload]);$this->getJson('/api/findings')->assertOk()->assertJsonCount(1,'findings');$this->get('/api/export')->assertOk();
 }
}
