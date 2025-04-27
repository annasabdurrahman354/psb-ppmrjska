<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::create('detail_penilaian_calon_santri', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('penilaian_calon_santri_id')->references('id')->on('penilaian_calon_santri')->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreignUlid('indikator_penilaian_id')->references('id')->on('indikator_penilaian')->cascadeOnUpdate()->cascadeOnDelete();
            $table->integer('nilai');
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_penilaian_calon_santri');
    }
};
