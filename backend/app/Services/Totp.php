<?php

namespace App\Services;

use InvalidArgumentException;

final class Totp
{
    private const ALPHABET = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    private const PERIOD_SECONDS = 30;

    public function secret(): string
    {
        $secret = '';
        for ($index = 0; $index < 32; $index++) {
            $secret .= self::ALPHABET[random_int(0, 31)];
        }

        return $secret;
    }

    public function code(string $secret, int $step): string
    {
        $key = $this->decodeSecret($secret);
        $hash = hash_hmac('sha1', pack('N2', 0, $step), $key, true);
        $offset = ord($hash[19]) & 15;
        $number = unpack('N', substr($hash, $offset, 4))[1] & 0x7FFFFFFF;

        return str_pad((string) ($number % 1000000), 6, '0', STR_PAD_LEFT);
    }

    public function verify(string $secret, string $code, int $lastStep, ?int $time = null): ?int
    {
        if (! preg_match('/^\d{6}$/D', $code)) {
            return null;
        }
        $now = intdiv($time ?? time(), self::PERIOD_SECONDS);
        foreach ([$now, $now - 1, $now + 1] as $step) {
            if ($step > $lastStep && hash_equals($this->code($secret, $step), $code)) {
                return $step;
            }
        }

        return null;
    }

    private function decodeSecret(string $secret): string
    {
        $bits = '';
        foreach (str_split($secret) as $character) {
            $position = strpos(self::ALPHABET, $character);
            if ($position === false) {
                throw new InvalidArgumentException('Secreto inválido');
            }
            $bits .= str_pad(decbin($position), 5, '0', STR_PAD_LEFT);
        }

        $key = '';
        for ($index = 0; $index + 8 <= strlen($bits); $index += 8) {
            $key .= chr(bindec(substr($bits, $index, 8)));
        }

        return $key;
    }
}
