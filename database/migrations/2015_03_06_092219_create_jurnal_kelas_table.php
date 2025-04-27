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

        Schema::create('jurnal_kelas', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('kelas_id')->references('id')->on('kelas')->cascadeOnUpdate()->restrictOnDelete();
            $table->date('tanggal');
            $table->string('sesi');
            $table->foreignUlid('guru_id')->nullable()->references('id')->on('users')->cascadeOnUpdate()->nullOnDelete();
            $table->string('materi_awal_type')->nullable();
            $table->unsignedTinyInteger('materi_awal_id')->nullable();
            $table->string('materi_akhir_type')->nullable();
            $table->unsignedTinyInteger('materi_akhir_id')->nullable();
            $table->unsignedSmallInteger('halaman_awal')->nullable();
            $table->unsignedSmallInteger('halaman_akhir')->nullable();
            $table->unsignedSmallInteger('ayat_awal')->nullable();
            $table->unsignedSmallInteger('ayat_akhir')->nullable();
            $table->text('link_rekaman')->nullable();
            $table->string('keterangan')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jurnal_kelas');
    }
};
