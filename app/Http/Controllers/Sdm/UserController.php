<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\Jabatan;
use App\Models\UnitOrganisasi;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private const ROLE_TERSEDIA = [
        'admin_sdm',
        'admin_departemen',
        'approver',
    ];

    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $jabatanId = $request->input('jabatan_id');
        $unitOrganisasiId = $request->input('unit_organisasi_id');
        $status = $request->input('status'); // 'aktif' | 'nonaktif'

        $query = User::with(['jabatan', 'unitOrganisasi']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($role && in_array($role, self::ROLE_TERSEDIA, true)) {
            $query->where('role', $role);
        }

        if ($jabatanId) {
            $query->where('jabatan_id', $jabatanId);
        }

        if ($unitOrganisasiId) {
            $query->where('unit_organisasi_id', $unitOrganisasiId);
        }

        if ($status === 'aktif') {
            $query->where('is_active', true);
        } elseif ($status === 'nonaktif') {
            $query->where('is_active', false);
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('sdm.pengguna.index', [
            'users'            => $users,
            'jabatans'         => Jabatan::urut()->get(),
            'unitOrganisasis'  => UnitOrganisasi::active()->orderBy('nama')->get(),
            'search'           => $search,
            'role'             => $role,
            'jabatanId'        => $jabatanId,
            'unitOrganisasiId' => $unitOrganisasiId,
            'status'           => $status,
        ]);
    }

    public function create()
    {
        return view('sdm.pengguna.create', [
            'jabatans'        => Jabatan::urut()->get(),
            'unitOrganisasis' => UnitOrganisasi::active()->orderBy('tingkat')->orderBy('nama')->get(),
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_plt'] = $request->boolean('is_plt');
        $data = $this->bersihkanFieldSesuaiRole($data);

        User::create($data);

        return redirect()->route('sdm.pengguna.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $pengguna)
    {
        return view('sdm.pengguna.edit', [
            'user'            => $pengguna,
            'jabatans'        => Jabatan::urut()->get(),
            'unitOrganisasis' => UnitOrganisasi::active()->orderBy('tingkat')->orderBy('nama')->get(),
        ]);
    }

    public function update(StoreUserRequest $request, User $pengguna)
    {
        $data = $request->validated();

        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_plt'] = $request->boolean('is_plt');
        $data = $this->bersihkanFieldSesuaiRole($data);

        $pengguna->update($data);

        return redirect()->route('sdm.pengguna.index')->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $pengguna)
    {
        $pengguna->update(['is_active' => false]);
        return back()->with('success', 'Pengguna berhasil dinonaktifkan.');
    }

    private function bersihkanFieldSesuaiRole(array $data): array
    {
        if ($data['role'] !== 'approver') {
            $data['jabatan_id'] = null;
            $data['is_plt'] = false;
        }

        if ($data['role'] === 'admin_sdm') {
            $data['unit_organisasi_id'] = null;
        }

        return $data;
    }
}