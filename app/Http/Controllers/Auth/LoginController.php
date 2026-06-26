<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function show(): View|RedirectResponse
    {
        if (! Auth::check()) {
            return view('auth.login');
        }

        return $this->redirectForRole(Auth::user()->role, request());
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $user = User::query()->where('username', $credentials['username'])->first();

        if ($user?->is_active === false) {
            return back()
                ->withErrors(['auth' => 'Akun Anda sedang nonaktif. Hubungi admin.'])
                ->withInput($request->only('username'));
        }

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['auth' => 'Username atau password salah.'])
                ->withInput($request->only('username'));
        }

        $request->session()->regenerate();

        $authenticatedUser = $request->user();

        if ($authenticatedUser instanceof User) {
            $authenticatedUser->forceFill(['last_login_at' => now()])->save();

            return $this->redirectForRole($authenticatedUser->role, $request);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors(['auth' => 'Terjadi kesalahan saat login. Coba lagi.']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectForRole(string $role, Request $request): RedirectResponse
    {
        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'karyawan' => redirect()->route('employee.dashboard'),
            default => $this->logoutAndRedirectInvalidRole($request),
        };
    }

    private function logoutAndRedirectInvalidRole(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->withErrors(['auth' => 'Akun Anda tidak memiliki role yang valid.']);
    }
}
