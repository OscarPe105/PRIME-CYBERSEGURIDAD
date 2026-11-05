<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class RequireVerifiedRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        abort_unless(Auth::check() && $request->session()->get('mfa_verified'), 401, 'Inicie sesión y valide MFA.');
        $allowedRoles = $roles ?: ['analista', 'administrador', 'auditor'];
        abort_unless(in_array(Auth::user()->role, $allowedRoles, true), 403, 'Operación no permitida para su rol.');

        return $next($request);
    }
}
