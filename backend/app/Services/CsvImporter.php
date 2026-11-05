<?php

namespace App\Services;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

final class CsvImporter
{
    public const HEADERS = [
        'id', 'cve', 'asset', 'zone', 'product', 'version', 'cvss_version', 'cvss_vector',
        'cvss', 'damage', 'exploitability', 'reproducibility', 'affected_users',
        'discoverability', 'justification',
    ];

    private const NUMERIC_FIELDS = [
        'cvss', 'damage', 'exploitability', 'reproducibility', 'affected_users', 'discoverability',
    ];

    private const TEXT_FIELDS = ['id', 'asset', 'zone', 'product', 'version', 'justification'];

    private const MAX_BYTES = 2 * 1024 * 1024;

    private const MAX_ROWS = 500;

    public function parse(string $path): array
    {
        $raw = file_get_contents($path);
        if (strlen($raw) > self::MAX_BYTES || ! mb_check_encoding($raw, 'UTF-8') || str_contains($raw, "\0")) {
            $this->error('Archivo inválido; use UTF-8 y hasta 2 MB.');
        }

        $stream = fopen('php://temp', 'r+');
        try {
            fwrite($stream, preg_replace('/^\xEF\xBB\xBF/', '', $raw));
            rewind($stream);

            return $this->readRows($stream);
        } finally {
            fclose($stream);
        }
    }

    /** @param resource $stream */
    private function readRows($stream): array
    {
        $header = fgetcsv($stream, 0, ',', '"', '');
        if ($header !== self::HEADERS) {
            $this->error('Encabezados incorrectos o fuera de orden.');
        }

        $rows = [];
        $seen = [];
        $line = 1;
        while (($values = fgetcsv($stream, 0, ',', '"', '')) !== false) {
            $line++;
            if ($values === [null]) {
                continue;
            }
            if (count($values) !== count($header)) {
                $this->error("Fila $line: columnas incorrectas.");
            }
            if (count($rows) >= self::MAX_ROWS) {
                $this->error('Máximo 500 registros en este prototipo.');
            }

            $row = array_combine($header, $values);
            $this->validateRow($row, $line);
            if (isset($seen[$row['id']])) {
                $this->error("Fila $line: ID duplicado.");
            }
            $seen[$row['id']] = true;
            $this->rejectFormulaPrefixes($row, $line);

            foreach (self::NUMERIC_FIELDS as $key) {
                $row[$key] = (float) $row[$key];
            }
            $row['cve'] = $row['cve'] ?: null;
            $rows[] = $row;
        }
        if (! $rows) {
            $this->error('CSV sin registros.');
        }

        return $rows;
    }

    private function validateRow(array $row, int $line): void
    {
        $validator = Validator::make($row, [
            'id' => ['required', 'regex:/^[A-Za-z0-9_-]{1,40}$/D'],
            'cve' => ['nullable', 'regex:/^CVE-\d{4}-\d{4,}$/D'],
            'asset' => 'required|string|max:100',
            'zone' => 'required|string|max:60',
            'product' => 'required|string|max:100',
            'version' => 'required|string|max:80',
            'cvss_version' => 'required|in:4.0',
            'cvss_vector' => [
                'required',
                'regex:~^CVSS:4\.0/AV:[NALP]/AC:[LH]/AT:[NP]/PR:[NLH]/UI:[NPA]/VC:[HLN]/VI:[HLN]/VA:[HLN]/SC:[HLN]/SI:[HLN]/SA:[HLN]$~D',
            ],
            'justification' => 'required|string|max:2000',
            ...array_fill_keys(self::NUMERIC_FIELDS, 'required|numeric|between:0,10'),
        ]);
        if ($validator->fails()) {
            $this->error("Fila $line: ".implode(' ', $validator->errors()->all()));
        }
    }

    private function rejectFormulaPrefixes(array $row, int $line): void
    {
        foreach (self::TEXT_FIELDS as $key) {
            if (CsvSafety::hasFormulaPrefix($row[$key])) {
                $this->error("Fila $line: prefijo de fórmula no permitido en $key.");
            }
        }
    }

    private function error(string $message): never
    {
        throw ValidationException::withMessages(['file' => $message]);
    }
}
