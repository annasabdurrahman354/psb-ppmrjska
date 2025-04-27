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

        Schema::create('materi_tambahan', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('nama');
            $table->unsignedSmallInteger('jumlah_halaman')->nullable();
            $table->unsignedSmallInteger('halaman_awal')->nullable();
            $table->unsignedSmallInteger('halaman_akhir')->nullable();
            $table->text('link_materi')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi_tambahan');
    }
};
