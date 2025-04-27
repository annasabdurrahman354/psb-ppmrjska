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
        Schema::create('dokumen_calon_santri', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('calon_santri_id')->references('id')->on('calon_santri')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('dokumen_pendaftaran_id')->references('id')->on('dokumen_pendaftaran')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_calon_santri');
    }
};
