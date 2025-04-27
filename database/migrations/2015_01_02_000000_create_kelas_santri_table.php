<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kelas_santri', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('santri_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('kelas_id')->references('id')->on('kelas')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal_masuk');
            $table->date('tanggal_lulus')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kelas_santri');
    }
};
