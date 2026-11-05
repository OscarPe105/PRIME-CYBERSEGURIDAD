<?php

namespace App\Services;

final class CsvSafety
{
    public static function hasFormulaPrefix(string $value): bool
    {
        return preg_match('/^[\s\x00-\x1F]*[=+@-]/u', $value) === 1;
    }

    public static function escape(string $value): string
    {
        return self::hasFormulaPrefix($value) ? "'".$value : $value;
    }
}
