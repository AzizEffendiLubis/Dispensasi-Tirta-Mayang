<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();

            $table->enum('role', ['admin_sdm', 'admin_departemen', 'approver']);

            $table->foreignId('jabatan_id')
                  ->nullable()
                  ->constrained('jabatans')
                  ->restrictOnDelete();

            $table->foreignId('unit_organisasi_id')
                  ->nullable()
                  ->constrained('unit_organisasis')
                  ->nullOnDelete();

            $table->boolean('is_plt')->default(false);
            $table->boolean('is_active')->default(true);
            $table->boolean('must_change_password')->default(false);
            $table->timestamps();

            $table->index(['role', 'is_active']);
            $table->index(['jabatan_id', 'unit_organisasi_id']);
            $table->index(['unit_organisasi_id', 'jabatan_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};