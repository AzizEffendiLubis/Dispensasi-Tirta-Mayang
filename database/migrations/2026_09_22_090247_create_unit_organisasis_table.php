<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unit_organisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')
                  ->nullable()
                  ->constrained('unit_organisasis')
                  ->nullOnDelete();
            $table->enum('tingkat', ['divisi', 'departemen', 'subdepartemen']);
            $table->string('kode', 20)->nullable()->unique();
            $table->string('nama', 150);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->index(['parent_id', 'tingkat']);
            $table->index('tingkat');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unit_organisasis');
    }
};