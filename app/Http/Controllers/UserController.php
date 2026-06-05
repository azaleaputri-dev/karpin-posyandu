<?php

namespace App\Http\Controllers;

use App\Models\Posyandu;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const DEFAULT_RESET_PASSWORD = 'posyandu12345';

    public function index()
    {
        $filters = [
            'search' => request('search'),
            'role' => request('role'),
            'posyandu_id' => request('posyandu_id'),
        ];

        $query = User::with('posyandu')->latest();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        if (! empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (! empty($filters['posyandu_id'])) {
            if ($filters['posyandu_id'] === 'none') {
                $query->whereNull('posyandu_id');
            } else {
                $query->where('posyandu_id', $filters['posyandu_id']);
            }
        }

        return view('users.index', [
            'users' => $query->paginate(10)->withQueryString(),
            'posyandus' => Posyandu::orderBy('name')->get(),
            'filters' => $filters,
        ]);
    }

    public function create()
    {
        return view('users.create', [
            'posyandus' => Posyandu::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in(['admin', 'petugas'])],
            'posyandu_id' => ['nullable', 'exists:posyandus,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        if ($data['role'] === 'petugas' && empty($data['posyandu_id'])) {
            return back()->withErrors([
                'posyandu_id' => 'Petugas harus dihubungkan ke satu posyandu.',
            ])->withInput();
        }

        if ($data['role'] === 'admin') {
            $data['posyandu_id'] = null;
        }

        $data['password'] = Hash::make($data['password']);

        User::create($data);

        return redirect()->route('users.index')->with('status', 'Akun pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('users.edit', [
            'user' => $user,
            'posyandus' => Posyandu::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'role' => ['required', Rule::in(['admin', 'petugas'])],
            'posyandu_id' => ['nullable', 'exists:posyandus,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($data['role'] === 'petugas' && empty($data['posyandu_id'])) {
            return back()->withErrors([
                'posyandu_id' => 'Petugas harus dihubungkan ke satu posyandu.',
            ])->withInput();
        }

        if ($data['role'] === 'admin') {
            $data['posyandu_id'] = null;
        }

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('status', 'Akun pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === $this->currentUser()->id) {
            return redirect()->route('users.index')->with('status', 'Akun yang sedang dipakai tidak bisa dihapus.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Akun pengguna berhasil dihapus.');
    }

    public function resetPassword(User $user)
    {
        if ($user->id === $this->currentUser()->id) {
            return redirect()->route('users.index')->with('status', 'Akun yang sedang dipakai tidak bisa di-reset dari tombol cepat.');
        }

        $user->update([
            'password' => Hash::make(self::DEFAULT_RESET_PASSWORD),
        ]);

        return redirect()->route('users.index', request()->query())->with(
            'status',
            'Password untuk ' . $user->email . ' berhasil di-reset menjadi: ' . self::DEFAULT_RESET_PASSWORD
        );
    }
}
