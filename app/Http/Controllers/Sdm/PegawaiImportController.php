<?php

namespace App\Http\Controllers\Sdm;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Pegawai;
use App\Models\UnitOrganisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class PegawaiImportController extends Controller
{
    private const TARGET_FIELDS = [
        'nik'           => 'NIK',
        'nama'          => 'Nama Pegawai',
        'jabatan'       => 'Jabatan',
        'departemen'    => 'Departemen',
        'subdepartemen' => 'Subdepartemen',
        'no_telepon'    => 'No. Telepon',
        'email'         => 'Email',
    ];

    public function form()
    {
        return view('sdm.pegawai.import');
    }

    public function preview(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        $path = $request->file('file')->store('temp-imports', 'local');
        $rows = Excel::toArray(null, Storage::disk('local')->path($path))[0] ?? [];

        if (count($rows) < 2) {
            Storage::disk('local')->delete($path);
            return back()->withErrors(['file' => 'File kosong atau tidak ada data setelah header.']);
        }

        $headers = array_map(fn ($h) => trim((string) $h), $rows[0]);

        return view('sdm.pegawai.import-preview', [
            'path' => $path,
            'headers' => $headers,
            'previewRows' => array_slice($rows, 1, 5),
            'totalRows' => count($rows) - 1,
            'targetFields' => self::TARGET_FIELDS,
            'suggestedMapping' => $this->suggestMapping($headers),
        ]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'path' => ['required', 'string'],
            'mapping.nik' => ['required'],
            'mapping.nama' => ['required'],
            'mapping.departemen' => ['required'],
        ]);

        $path = $request->input('path');

        if (! Storage::disk('local')->exists($path)) {
            return back()->withErrors(['file' => 'Sesi import sudah kedaluwarsa, silakan upload ulang filenya.']);
        }

        $mapping = $request->input('mapping');
        $rows = Excel::toArray(null, Storage::disk('local')->path($path))[0] ?? [];
        $dataRows = array_slice($rows, 1);

        $jabatanDefault = Jabatan::where('kode', 'staf')->first();

        $sukses = 0;
        $gagal = [];

        DB::transaction(function () use ($dataRows, $mapping, $jabatanDefault, &$sukses, &$gagal) {
            foreach ($dataRows as $i => $row) {
                $baris = $i + 2;

                $nik = trim((string) ($row[$mapping['nik']] ?? ''));
                $nama = trim((string) ($row[$mapping['nama']] ?? ''));
                $namaDept = $this->ambilKolom($row, $mapping, 'departemen');
                $namaSub = $this->ambilKolom($row, $mapping, 'subdepartemen');
                $jabatanRaw = $this->ambilKolom($row, $mapping, 'jabatan');
                $noTelepon = $this->ambilKolom($row, $mapping, 'no_telepon');
                $email = $this->ambilKolom($row, $mapping, 'email');

                if ($nik === '' || $nama === '') {
                    $gagal[] = "Baris {$baris}: NIK/nama kosong, dilewati.";
                    continue;
                }

                if (Pegawai::where('nik', $nik)->exists()) {
                    $gagal[] = "Baris {$baris}: NIK {$nik} sudah terdaftar, dilewati.";
                    continue;
                }

                $unitDepartemen = $namaDept ? $this->cariUnit($namaDept, 'departemen') : null;
                if (! $unitDepartemen) {
                    $gagal[] = "Baris {$baris}: departemen '{$namaDept}' tidak dikenali sistem atau kosong, baris dilewati.";
                    continue;
                }

                $unitSubdepartemen = null;
                if ($namaSub) {
                    $unitSubdepartemen = $this->cariUnit($namaSub, 'subdepartemen', $unitDepartemen->id);
                    if (! $unitSubdepartemen) {
                        $gagal[] = "Baris {$baris}: subdepartemen '{$namaSub}' tidak dikenali sistem di bawah departemen '{$namaDept}', pegawai ditempatkan langsung di departemen.";
                    }
                }

                $unitTujuan = $unitSubdepartemen ?? $unitDepartemen;

                $jabatan = $jabatanRaw ? $this->cariJabatan($jabatanRaw) : null;
                if (! $jabatan) {
                    $jabatan = $jabatanDefault;
                    $gagal[] = "Baris {$baris}: jabatan '{$jabatanRaw}' tidak dikenali sistem, diset ke 'Staf' — cek & koreksi manual kalau perlu.";
                }

                if (! $jabatan) {
                    $gagal[] = "Baris {$baris}: jabatan default 'Staf' belum ada di master Jabatan, baris dilewati. Tambahkan dulu jabatan 'Staf' lewat menu Jabatan.";
                    continue;
                }

                Pegawai::create([
                    'nik'                 => $nik,
                    'nama_pegawai'        => $nama,
                    'jabatan_id'          => $jabatan->id,
                    'unit_organisasi_id'  => $unitTujuan->id,
                    'no_telepon'          => $noTelepon,
                    'email'               => $email,
                    'status'              => 'aktif',
                ]);

                $sukses++;
            }
        });

        Storage::disk('local')->delete($path);

        return redirect()->route('sdm.pegawai.index')
            ->with('success', "{$sukses} pegawai berhasil diimpor." . (count($gagal) ? ' ' . count($gagal) . ' baris perlu dicek.' : ''))
            ->with('warning', count($gagal) ? implode(' | ', array_slice($gagal, 0, 10)) : null);
    }

    private function ambilKolom(array $row, array $mapping, string $field): ?string
    {
        if (empty($mapping[$field]) && $mapping[$field] !== '0') {
            return null;
        }

        return trim((string) ($row[$mapping[$field]] ?? '')) ?: null;
    }

    private function suggestMapping(array $headers): array
    {
        $keywords = [
            'nik'           => ['nik'],
            'nama'          => ['nama', 'name'],
            'jabatan'       => ['jabatan'],
            'subdepartemen' => ['subdepartemen', 'sub departemen', 'sub-departemen', 'unit'],
            'departemen'    => ['departemen', 'department', 'divisi'],
            'no_telepon'    => ['telepon', 'telp', 'hp', 'phone'],
            'email'         => ['email', 'e-mail'],
        ];

        $suggestion = [];
        $usedHeaders = [];

        // Tahap 1: cocokkan header yang PERSIS SAMA dengan kata kunci dulu.
        foreach ($keywords as $field => $terms) {
            foreach ($headers as $index => $header) {
                if (in_array($index, $usedHeaders)) continue;

                $h = strtolower(trim($header));
                foreach ($terms as $term) {
                    if ($h === $term) {
                        $suggestion[$field] = $index;
                        $usedHeaders[] = $index;
                        continue 3;
                    }
                }
            }
        }

        foreach ($keywords as $field => $terms) {
            if (isset($suggestion[$field])) continue;

            foreach ($headers as $index => $header) {
                if (in_array($index, $usedHeaders)) continue;

                $h = strtolower(trim($header));
                foreach ($terms as $term) {
                    if (str_contains($h, $term)) {
                        if ($field === 'departemen' && str_contains($h, 'sub')) {
                            continue;
                        }
                        $suggestion[$field] = $index;
                        $usedHeaders[] = $index;
                        continue 3;
                    }
                }
            }
        }

        return $suggestion;
    }

    /**
     * Cari unit organisasi berdasarkan nama/kode pada tingkat tertentu,
     * opsional dibatasi di bawah satu parent (mis. subdepartemen harus
     * berada di bawah departemen yang sudah ditemukan).
     */
    private function cariUnit(string $nama, string $tingkat, ?int $parentId = null): ?UnitOrganisasi
    {
        $normal = $this->normalisasiNamaOrganisasi($nama);

        $query = UnitOrganisasi::where('tingkat', $tingkat)
            ->where(function ($q) use ($nama, $normal) {
                $q->whereRaw('LOWER(kode) = ?', [strtolower($nama)])
                  ->orWhereRaw('LOWER(nama) = ?', [strtolower($nama)])
                  ->orWhereRaw('LOWER(nama) LIKE ?', ['%' . $normal . '%']);
            });

        if ($parentId) {
            $query->where('parent_id', $parentId);
        }

        return $query->first();
    }

    private function cariJabatan(string $namaJabatan): ?Jabatan
    {
        $namaLower = strtolower(trim($namaJabatan));

        return Jabatan::whereRaw('LOWER(kode) = ?', [$namaLower])
            ->orWhereRaw('LOWER(nama) = ?', [$namaLower])
            ->first();
    }

    private function normalisasiNamaOrganisasi(string $nama): string
    {
        $nama = strtolower(trim($nama));
        $nama = preg_replace('/^(departemen|dept\.?|divisi|bagian)\s+/', '', $nama);
        $nama = preg_replace('/^(sub\s*-?\s*departemen|subdept\.?|unit)\s+/', '', $nama);
        return trim($nama);
    }
}