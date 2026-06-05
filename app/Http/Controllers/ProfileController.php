<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'user' => $this->currentUser()->load('posyandu'),
        ]);
    }

    public function update(Request $request)
    {
        $user = $this->currentUser();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['nullable', 'string'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if (! empty($data['password'])) {
            if (empty($data['current_password']) || ! Hash::check($data['current_password'], $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Password saat ini tidak sesuai.',
                ])->withInput($request->except(['password', 'password_confirmation', 'current_password']));
            }

            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        unset($data['current_password']);

        $user->update($data);

        return redirect()->route('profile.edit')->with('status', 'Profil berhasil diperbarui.');
    }
}
