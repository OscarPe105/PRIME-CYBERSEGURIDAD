<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

final class CtiClient
{
    private const EPSS_URL = 'https://api.first.org/data/v1/epss';

    private const KEV_URL = 'https://www.cisa.gov/sites/default/files/feeds/known_exploited_vulnerabilities.json';

    private const KEV_CACHE_KEY = 'prime-kev-catalog';

    public function pending(?string $cve): array
    {
        $status = $cve ? 'pending' : 'not_applicable';

        return [
            'queried_at' => now()->toIso8601String(),
            'epss' => ['status' => $status, 'value' => null, 'percentile' => null],
            'kev' => ['status' => $status, 'value' => null],
        ];
    }

    public function fetch(?string $cve): array
    {
        $snapshot = $this->pending($cve);
        if (! $cve) {
            return $snapshot;
        }

        // Record each source independently: one outage must not discard the other result.
        $snapshot['epss'] = $this->capture(fn () => $this->fetchEpss($cve));
        $snapshot['kev'] = $this->capture(fn () => $this->fetchKev($cve));

        return $snapshot;
    }

    private function capture(callable $query): array
    {
        try {
            return $query();
        } catch (Throwable $error) {
            return ['status' => 'error', 'value' => null, 'error_type' => get_class($error)];
        }
    }

    private function fetchEpss(string $cve): array
    {
        $response = Http::timeout(8)->connectTimeout(4)->get(self::EPSS_URL, ['cve' => $cve])->throw();
        $raw = $response->body();
        $data = $response->json('data');
        if (! is_array($data)) {
            throw new RuntimeException('EPSS inválido');
        }

        $item = collect($data)->firstWhere('cve', $cve);
        if ($item && ! $this->validEpss($item)) {
            throw new RuntimeException('EPSS inválido');
        }

        return [
            'status' => $item ? 'ok' : 'missing',
            'value' => $item ? (float) $item['epss'] : null,
            'percentile' => $item ? (float) $item['percentile'] : null,
            'data_date' => $item['date'] ?? null,
            'source' => self::EPSS_URL,
            'sha256' => hash('sha256', $raw),
            'raw' => $raw,
        ];
    }

    private function validEpss(array $item): bool
    {
        return isset($item['epss'], $item['percentile'], $item['date'])
            && is_numeric($item['epss']) && $item['epss'] >= 0 && $item['epss'] <= 1
            && is_numeric($item['percentile']) && $item['percentile'] >= 0 && $item['percentile'] <= 1;
    }

    private function fetchKev(string $cve): array
    {
        $cached = Cache::get(self::KEV_CACHE_KEY);
        $fromCache = $cached !== null;
        if (! $cached) {
            $cached = $this->downloadKevCatalog();
            Cache::put(self::KEV_CACHE_KEY, $cached, now()->addHours(24));
        }

        $catalog = json_decode($cached['raw'], true, 512, JSON_THROW_ON_ERROR);
        $item = collect($catalog['vulnerabilities'])->firstWhere('cveID', $cve);

        return [
            'status' => 'ok',
            'value' => $item !== null,
            'catalog_version' => $catalog['catalogVersion'],
            'data_date' => $catalog['dateReleased'],
            'source' => self::KEV_URL,
            'retrieved_at' => $cached['retrieved_at'],
            'cached' => $fromCache,
            'sha256' => hash('sha256', $cached['raw']),
            'entry' => $item,
            'raw' => $cached['raw'],
        ];
    }

    private function downloadKevCatalog(): array
    {
        $response = Http::timeout(12)->connectTimeout(4)->get(self::KEV_URL)->throw();
        $catalog = $response->json();
        if (! is_array($catalog['vulnerabilities'] ?? null) || ! isset($catalog['catalogVersion'], $catalog['dateReleased'])) {
            throw new RuntimeException('KEV inválido');
        }

        return ['raw' => $response->body(), 'retrieved_at' => now()->toIso8601String()];
    }
}
