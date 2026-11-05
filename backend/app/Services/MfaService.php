<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class MfaService
{
    public function __construct(
        private readonly Totp $totp,
        private readonly AuditLogger $audit,
    ) {}

    /** Validate a challenge atomically and return recovery codes only on enrollment. */
    public function verify(int $userId, string $code): array
    {
        return DB::transaction(function () use ($userId, $code) {
            $user = DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            abort_unless($user, 401);

            $step = $this->totp->verify(Crypt::decryptString($user->mfa_secret), $code, $user->mfa_last_step);
            $hashes = json_decode($user->recovery_hashes ?? '[]', true);
            $matched = $step === null && $user->mfa_confirmed
                ? $this->matchingRecoveryCode($hashes, $code)
                : null;

            if ($step === null && $matched === null) {
                $this->audit->record('mfa_denied', ['target_user' => $userId]);
                throw ValidationException::withMessages(['code' => 'Código incorrecto, expirado o ya utilizado.']);
            }
            if ($matched !== null) {
                unset($hashes[$matched]);
            }

            $recovery = [];
            if (! $user->mfa_confirmed) {
                for ($index = 0; $index < 8; $index++) {
                    $recovery[] = bin2hex(random_bytes(12));
                }
                $hashes = array_map(fn ($value) => hash('sha256', $value), $recovery);
            }

            DB::table('users')->where('id', $userId)->update([
                'mfa_confirmed' => true,
                'mfa_last_step' => $step ?? $user->mfa_last_step,
                'recovery_hashes' => json_encode(array_values($hashes)),
            ]);

            return $recovery;
        });
    }

    private function matchingRecoveryCode(array $hashes, string $code): ?int
    {
        $candidate = hash('sha256', $code);
        foreach ($hashes as $index => $hash) {
            if (hash_equals($hash, $candidate)) {
                return $index;
            }
        }

        return null;
    }
}
