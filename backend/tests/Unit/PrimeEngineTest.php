<?php

namespace Tests\Unit;

use App\Services\PrimeEngine;
use App\Services\Totp;
use PHPUnit\Framework\TestCase;

final class PrimeEngineTest extends TestCase
{
    private function input(float $cvss = 5): array
    {
        return ['cvss' => $cvss, 'damage' => 5, 'exploitability' => 5, 'reproducibility' => 5, 'affected_users' => 5, 'discoverability' => 5];
    }

    private function cti(?float $p = null, ?bool $kev = null, string $status = 'not_applicable'): array
    {
        return ['epss' => ['value' => $p, 'status' => $status], 'kev' => ['value' => $kev, 'status' => $status]];
    }

    public function test_cvss_changes_the_result_while_dread_and_cti_stay_fixed(): void
    {
        $e = new PrimeEngine;
        $a = $e->calculate($this->input(2), $this->cti());
        $b = $e->calculate($this->input(8), $this->cti());
        $this->assertEqualsWithDelta(3.2, $a['score'], .00001);
        $this->assertEqualsWithDelta(6.8, $b['score'], .00001);
    }

    public function test_kev_is_urgent_without_overwriting_the_numeric_score(): void
    {
        $r = (new PrimeEngine)->calculate($this->input(), $this->cti(.1, true, 'ok'));
        $this->assertEqualsWithDelta(5.0, $r['score'], .00001);
        $this->assertSame('Urgente KEV', $r['priority']);
        $this->assertSame('Medio', $r['severity']);
    }

    public function test_cti_failure_is_provisional_and_distinct_from_zero(): void
    {
        $e = new PrimeEngine;
        $missing = $e->calculate($this->input(), $this->cti(null, null, 'error'));
        $zero = $e->calculate($this->input(), $this->cti(0, false, 'ok'));
        $this->assertTrue($missing['provisional']);
        $this->assertFalse($zero['provisional']);
        $this->assertFalse($missing['epss_applied']);
        $this->assertFalse($zero['epss_applied']);
        $this->assertSame($missing['score'], $zero['score']);
    }

    public function test_epss_does_not_change_prime_1_numeric_score_or_category(): void
    {
        foreach ([null, 0, .1, 1] as $probability) {
            $r = (new PrimeEngine)->calculate($this->input(), $this->cti($probability, false, $probability === null ? 'missing' : 'ok'));
            $this->assertEqualsWithDelta(5.0, $r['score'], .00001);
            $this->assertSame('Medio', $r['severity']);
            $this->assertSame('PRIME-1.0', $r['version']);
            $this->assertFalse($r['epss_applied']);
        }
    }

    public function test_thresholds_use_full_precision(): void
    {
        $i = array_fill_keys(array_keys($this->input()), 3.999);
        $r = (new PrimeEngine)->calculate($i, $this->cti());
        $this->assertSame('Bajo', $r['severity']);
        $this->assertSame('4.00', $r['display_score']);
    }

    public function test_score_boundaries_and_categories_match_the_documented_policy(): void
    {
        foreach ([[0, 'Bajo'], [4, 'Medio'], [6.999, 'Medio'], [7, 'Alto'], [8.999, 'Alto'], [9, 'Crítico'], [10, 'Crítico']] as [$value, $category]) {
            $input = array_fill_keys(array_keys($this->input()), $value);
            $r = (new PrimeEngine)->calculate($input, $this->cti(1, false, 'ok'));
            $this->assertEqualsWithDelta($value, $r['score'], .00001);
            $this->assertSame($category, $r['severity']);
        }
    }

    public function test_weighted_dread_and_report_examples_are_reproducible(): void
    {
        foreach ([[9.3, 9.6, 9.42], [9.3, 2.5, 6.58], [6.5, 8.4, 7.26], [9.8, 7.4, 8.84], [9.8, 3.8, 7.40]] as [$cvss, $dread, $expected]) {
            $input = array_fill_keys(array_keys($this->input()), $dread);
            $input['cvss'] = $cvss;
            $r = (new PrimeEngine)->calculate($input, $this->cti());
            $this->assertEqualsWithDelta($expected, $r['score'], .00001);
        }
        $input = array_fill_keys(array_keys($this->input()), 0);
        $input['damage'] = 10;
        $r = (new PrimeEngine)->calculate($input, $this->cti());
        $this->assertEqualsWithDelta(3, $r['dread'], .00001);
        $this->assertEqualsWithDelta(1.2, $r['score'], .00001);
    }

    public function test_non_finite_scores_are_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new PrimeEngine)->calculate($this->input(NAN), $this->cti());
    }

    public function test_invalid_epss_is_rejected_even_as_complementary_information(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new PrimeEngine)->calculate($this->input(), $this->cti(INF, false, 'ok'));
    }

    public function test_out_of_range_is_rejected(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        (new PrimeEngine)->calculate($this->input(11), $this->cti());
    }

    public function test_totp_matches_rfc_6238_sha1_and_rejects_replay(): void
    {
        $t = new Totp;
        $secret = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';
        $this->assertSame('287082', $t->code($secret, 1));
        $this->assertSame(1, $t->verify($secret, '287082', -1, 59));
        $this->assertNull($t->verify($secret, '287082', 1, 59));
        $this->assertNull($t->verify($secret, '000000', -1, 59));
    }
}
