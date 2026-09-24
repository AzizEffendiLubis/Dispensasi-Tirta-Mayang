<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jabatans', function (Blueprint $table) {
            $table->id();
            $table->string('kode', 40)->unique();
            $table->string('nama', 100);
            $table->unsignedInteger('level_urutan')->default(0)
                  ->comment('Hanya untuk urutan tampilan, bukan penentu approval otomatis');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jabatans');
    }
};