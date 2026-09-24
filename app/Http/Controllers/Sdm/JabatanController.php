<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJabatanRequest;
use App\Models\Jabatan;
use Illuminate\Http\Request;

class JabatanController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Jabatan::withCount(['pegawais', 'users']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'LIKE', "%{$search}%")
                  ->orWhere('nama', 'LIKE', "%{$search}%");
            });
        }

        $jabatans = $query->orderBy('level_urutan')->orderBy('nama')->get();

        return view('sdm.jabatan.index', compact('jabatans', 'search'));
    }

    public function create()
    {
        return view('sdm.jabatan.create');
    }

    public function store(StoreJabatanRequest $request)
    {
        Jabatan::create($request->validated());

        return redirect()->route('sdm.jabatan.index')->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function edit(Jabatan $jabatan)
    {
        return view('sdm.jabatan.edit', compact('jabatan'));
    }

    public function update(StoreJabatanRequest $request, Jabatan $jabatan)
    {
        $jabatan->update($request->validated());

        return redirect()->route('sdm.jabatan.index')->with('success', 'Jabatan berhasil diperbarui.');
    }

    public function destroy(Jabatan $jabatan)
    {
        if ($jabatan->pegawais()->exists() || $jabatan->users()->exists()) {
            return back()->with('error', "Tidak bisa menghapus {$jabatan->nama} karena masih dipakai oleh pegawai atau akun pengguna. Pindahkan dulu semua pemegangnya ke jabatan lain.");
        }

        if ($jabatan->alurApprovalsSebagaiPengaju()->exists() || $jabatan->alurApprovalsSebagaiApprover()->exists()) {
            return back()->with('error', "Tidak bisa menghapus {$jabatan->nama} karena masih direferensikan di Alur Approval. Hapus atau ubah dulu aturan yang memakainya.");
        }

        $jabatan->delete();

        return back()->with('success', "Jabatan {$jabatan->nama} berhasil dihapus.");
    }
}