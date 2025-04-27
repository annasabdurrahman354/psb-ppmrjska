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

        Schema::create('kurikulum', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('kelas_id')->references('id')->on('kelas')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('materi_type')->nullable();
            $table->unsignedTinyInteger('materi_id')->nullable();
            $table->timestamps();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kurikulum');
    }
};
