<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispensasis', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_dispensasi', 50)->unique();
            $table->foreignId('pegawai_id')->constrained('pegawais')->restrictOnDelete();

            $table->foreignId('unit_organisasi_id')->constrained('unit_organisasis')->restrictOnDelete();

            $table->foreignId('admin_departemen_id')
                  ->comment('User dengan role admin_departemen yang menginput')
                  ->constrained('users')->restrictOnDelete();

            $table->date('tanggal_pengajuan');
            $table->date('tanggal_dispensasi');
            $table->enum('waktu_dispensasi', ['T', 'TBO', 'TBI', 'CP']);
            $table->text('keterangan');
            $table->string('bukti_pendukung', 255)->nullable();

            $table->enum('status_pengajuan', [
                'menunggu_persetujuan',
                'disetujui',
                'ditolak',
            ])->default('menunggu_persetujuan');

            $table->foreignId('diproses_oleh_id')
                  ->nullable()
                  ->comment('User yang memberi keputusan final atas pengajuan ini')
                  ->constrained('users')->nullOnDelete();

            $table->foreignId('approver_saat_ini_id')
                  ->nullable()
                  ->comment('User yang saat ini harus memutuskan pengajuan ini')
                  ->constrained('users')->nullOnDelete();

            $table->text('catatan_persetujuan')->nullable();
            $table->timestamp('tanggal_keputusan')->nullable();

            $table->string('nomor_surat_dispensasi', 100)->nullable();
            $table->date('tanggal_surat_dispensasi')->nullable();
            $table->foreignId('dicetak_oleh_id')
                  ->nullable()
                  ->comment('Admin Departemen yang menerbitkan surat e-dispensasi')
                  ->constrained('users')->nullOnDelete();
            $table->foreignId('ditujukan_kepada_id')
                  ->nullable()
                  ->comment('Snapshot user Direktur yang dituju saat surat diterbitkan')
                  ->constrained('users')->nullOnDelete();
            $table->uuid('token_verifikasi')->nullable();
            $table->timestamp('dicetak_pada')->nullable();

            $table->timestamps();

            $table->index(['unit_organisasi_id', 'status_pengajuan']);
            $table->index(['pegawai_id', 'status_pengajuan']);
            $table->index(['approver_saat_ini_id', 'status_pengajuan']);
            $table->index('tanggal_dispensasi');
            $table->index('tanggal_pengajuan');
            $table->index('status_pengajuan');
            $table->index('token_verifikasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispensasis');
    }
};