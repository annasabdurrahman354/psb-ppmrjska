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

        Schema::create('materi_surat', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->unsignedTinyInteger('nomor');
            $table->string('nama');
            $table->unsignedSmallInteger('jumlah_ayat');
            $table->unsignedTinyInteger('jumlah_halaman');
            $table->unsignedSmallInteger('halaman_awal');
            $table->unsignedSmallInteger('halaman_akhir');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi_surat');
    }
};
