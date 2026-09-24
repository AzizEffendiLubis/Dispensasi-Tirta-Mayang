<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alur_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('unit_organisasi_id')->constrained('unit_organisasis')->cascadeOnDelete();
            $table->foreignId('jabatan_pengaju_id')->constrained('jabatans')->cascadeOnDelete();
            $table->foreignId('jabatan_approver_id')->constrained('jabatans')->restrictOnDelete();
            $table->unsignedInteger('urutan')->default(1);

            $table->timestamps();

            $table->unique(
                ['unit_organisasi_id', 'jabatan_pengaju_id', 'urutan'],
                'alur_approvals_unit_jabatan_urutan_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alur_approvals');
    }
};