<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pegawais', function (Blueprint $table) {
            $table->id();
            $table->string('nik', 20)->unique();
            $table->string('nama_pegawai', 100);

            $table->foreignId('jabatan_id')->constrained('jabatans')->restrictOnDelete();
            $table->foreignId('unit_organisasi_id')->constrained('unit_organisasis')->restrictOnDelete();

            $table->string('no_telepon', 20)->nullable();
            $table->string('email', 100)->nullable();

            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->timestamps();

            $table->index(['unit_organisasi_id', 'status']);
            $table->index(['unit_organisasi_id', 'jabatan_id']);
            $table->index('nama_pegawai');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pegawais');
    }
};