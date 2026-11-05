<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

final class AuditController extends Controller
{
    public function auditLog(): array
    {
        return ['events' => DB::table('audit_events')->latest('id')->limit(200)->get()];
    }
}
