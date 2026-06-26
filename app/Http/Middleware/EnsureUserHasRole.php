<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response|RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = $request->user();

        if ($user === null || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'karyawan' => redirect()->route('employee.dashboard'),
            default => abort(403),
        };
    }
}
