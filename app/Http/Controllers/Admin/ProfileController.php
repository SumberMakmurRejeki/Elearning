<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function show(): View
    {
        $user = request()->user();

        return view('admin.profile.index', [
            'user' => $user,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
                    'name' => ['required', 'string', 'max:100'],
                    'username' => [
                        'required',
                        'string',
                        'max:50',
                        'unique:users,username,'.$user->id,
                    ],
                ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
                    'current_password' => ['required', 'string'],
                    'new_password' => ['required', 'string', 'min:8', 'confirmed'],
                ]);

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.'])->withInput();
        }

        $user->update(['password' => $validated['new_password']]);

        return back()->with('success', 'Password berhasil diubah.');
    }
}
