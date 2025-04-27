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
        Schema::disableForeignKeyConstraints();

        Schema::create('presensi', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('santri_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('kelas_id')->references('id')->on('kelas')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal');
            $table->string('sesi');
            $table->string('status_kehadiran')->default('alpa');
            $table->foreignUlid('perekap_id')->nullable()->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
