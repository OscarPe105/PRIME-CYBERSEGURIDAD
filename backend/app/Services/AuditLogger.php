<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

final class AuditLogger
{
    public function record(string $event, array $details = []): void
    {
        DB::table('audit_events')->insert([
            'user_id' => Auth::id(),
            'event' => $event,
            'details' => json_encode($details),
            'created_at' => now(),
        ]);
    }
}
