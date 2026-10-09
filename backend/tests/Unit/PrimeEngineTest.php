<?php
namespace Tests\Unit;
use App\Services\{PrimeEngine,Totp};
use PHPUnit\Framework\TestCase;
final class PrimeEngineTest extends TestCase {
 private function input(float $cvss=5): array {return ['cvss'=>$cvss,'damage'=>5,'exploitability'=>5,'reproducibility'=>5,'affected_users'=>5,'discoverability'=>5];}
 private function cti(?float $p=null,?bool $kev=null,string $status='not_applicable'): array{return ['epss'=>['value'=>$p,'status'=>$status],'kev'=>['value'=>$kev,'status'=>$status]];}
 public function test_cvss_changes_the_result_while_dread_and_cti_stay_fixed(): void {
  $e=new PrimeEngine();$a=$e->calculate($this->input(2),$this->cti());$b=$e->calculate($this->input(8),$this->cti());
  $this->assertEqualsWithDelta(3.2,$a['score'],.00001);$this->assertEqualsWithDelta(6.8,$b['score'],.00001);
 }
 public function test_kev_is_urgent_without_overwriting_the_numeric_score(): void {
  $r=(new PrimeEngine())->calculate($this->input(),$this->cti(.1,true,'ok'));
  $this->assertEqualsWithDelta(5.5,$r['score'],.00001);$this->assertSame('Urgente KEV',$r['priority']);
 }
 public function test_cti_failure_is_provisional_and_distinct_from_zero(): void {
  $e=new PrimeEngine();$missing=$e->calculate($this->input(),$this->cti(null,null,'error'));$zero=$e->calculate($this->input(),$this->cti(0,false,'ok'));
  $this->assertTrue($missing['provisional']);$this->assertFalse($zero['provisional']);$this->assertFalse($missing['epss_applied']);$this->assertTrue($zero['epss_applied']);
 }
 public function test_score_is_capped_and_thresholds_use_full_precision(): void {
  $r=(new PrimeEngine())->calculate($this->input(10),$this->cti(1,false,'ok'));$this->assertSame(10,$r['score']);$this->assertSame('Crítico',$r['severity']);
  $i=array_fill_keys(array_keys($this->input()),3.999);$r=(new PrimeEngine())->calculate($i,$this->cti());$this->assertSame('Bajo',$r['severity']);$this->assertSame('4.00',$r['display_score']);
 }
 public function test_out_of_range_is_rejected(): void {$this->expectException(\InvalidArgumentException::class);(new PrimeEngine())->calculate($this->input(11),$this->cti());}
 public function test_totp_matches_rfc_6238_sha1_and_rejects_replay(): void {
  $t=new Totp();$secret='GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
  $this->assertSame('287082',$t->code($secret,1));$this->assertSame(1,$t->verify($secret,'287082',-1,59));$this->assertNull($t->verify($secret,'287082',1,59));$this->assertNull($t->verify($secret,'000000',-1,59));
 }
}
