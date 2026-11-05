<?php

namespace App\Services;

use InvalidArgumentException;

final class PrimeEngine
{
    public const VERSION = 'PRIME-1.0';

    private const WEIGHTS = [
        'cvss' => .60,
        'dread' => .40,
        'damage' => .30,
        'exploitability' => .25,
        'reproducibility' => .20,
        'affected_users' => .15,
        'discoverability' => .10,
    ];

    private const INCOMPLETE_STATUSES = ['pending', 'error', 'missing'];

    public function calculate(array $input, array $cti): array
    {
        $this->validateInput($input);
        $dread = self::WEIGHTS['damage'] * $input['damage']
            + self::WEIGHTS['exploitability'] * $input['exploitability']
            + self::WEIGHTS['reproducibility'] * $input['reproducibility']
            + self::WEIGHTS['affected_users'] * $input['affected_users']
            + self::WEIGHTS['discoverability'] * $input['discoverability'];
        $base = self::WEIGHTS['cvss'] * $input['cvss'] + self::WEIGHTS['dread'] * $dread;

        $epss = $cti['epss']['value'] ?? null;
        if ($epss !== null && (! is_numeric($epss) || ! is_finite((float) $epss) || $epss < 0 || $epss > 1)) {
            throw new InvalidArgumentException('EPSS fuera de rango');
        }
        // PRIME 1.0 keeps threat intelligence separate from the numeric IRC.
        $score = $base;
        $severity = $this->severity($score);
        $kev = $cti['kev']['value'] ?? null;

        return [
            'version' => self::VERSION,
            'weights' => self::WEIGHTS,
            'dread' => $dread,
            'base' => $base,
            'score' => $score,
            'display_score' => number_format($score, 2, '.', ''),
            'severity' => $severity,
            'priority' => $kev === true ? 'Urgente KEV' : $severity,
            'provisional' => $this->isIncomplete($cti['epss'] ?? []) || $this->isIncomplete($cti['kev'] ?? []),
            'epss_applied' => false,
            'kev_urgent' => $kev === true,
        ];
    }

    private function validateInput(array $input): void
    {
        foreach (['cvss', 'damage', 'exploitability', 'reproducibility', 'affected_users', 'discoverability'] as $key) {
            if (! isset($input[$key]) || ! is_numeric($input[$key]) || ! is_finite((float) $input[$key]) || $input[$key] < 0 || $input[$key] > 10) {
                throw new InvalidArgumentException("Valor inválido: $key");
            }
        }
    }

    private function severity(float $score): string
    {
        return match (true) {
            $score >= 9 => 'Crítico',
            $score >= 7 => 'Alto',
            $score >= 4 => 'Medio',
            default => 'Bajo',
        };
    }

    private function isIncomplete(array $source): bool
    {
        return in_array($source['status'] ?? 'pending', self::INCOMPLETE_STATUSES, true);
    }
}
