<?php

namespace App\Http\Controllers;

use App\Services\AuditLogger;
use App\Services\MfaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

final class AuthController extends Controller
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function session(Request $request): array
    {
        return ['csrf' => csrf_token(), 'user' => Auth::check() && $request->session()->get('mfa_verified') ? $this->currentUser() : null];
    }

    public function login(Request $request): array
    {
        $validated = $request->validate(['email' => 'required|email', 'password' => 'required|string']);
        $user = DB::table('users')->where('email', $validated['email'])->first();
        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            $this->audit->record('login_denied');
            throw ValidationException::withMessages(['email' => 'Credenciales incorrectas.']);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->put(['pending_user' => $user->id, 'pending_until' => time() + 300]);
        $response = ['requires_mfa' => true, 'enrollment' => ! $user->mfa_confirmed, 'csrf' => csrf_token()];
        if (! $user->mfa_confirmed) {
            $secret = Crypt::decryptString($user->mfa_secret);
            $response['secret'] = $secret;
            $response['otpauth'] = 'otpauth://totp/'.rawurlencode('PRIME:'.$user->email).'?secret='.$secret.'&issuer=PRIME&algorithm=SHA1&digits=6&period=30';
        }

        return $response;
    }

    public function verify(Request $request, MfaService $mfa): array
    {
        $validated = $request->validate(['code' => 'required|string|max:100']);
        abort_unless($request->session()->get('pending_user') && $request->session()->get('pending_until', 0) >= time(), 401, 'Desafío MFA expirado.');
        $id = $request->session()->get('pending_user');
        $recovery = $mfa->verify($id, $validated['code']);
        Auth::loginUsingId($id);
        $request->session()->forget(['pending_user', 'pending_until']);
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        $request->session()->put('mfa_verified', true);
        $this->audit->record('mfa_login');

        return ['csrf' => csrf_token(), 'recovery_codes' => $recovery, 'user' => $this->currentUser()];
    }

    public function logout(Request $request): array
    {
        $this->audit->record('logout');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return ['csrf' => csrf_token()];
    }

    private function currentUser(): array
    {
        $user = Auth::user();

        return ['name' => $user->name, 'email' => $user->email, 'role' => $user->role];
    }
}
