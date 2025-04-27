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

        Schema::create('gelombang_pendaftaran', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('pendaftaran_id')->nullable()->references('id')->on('pendaftaran')->cascadeOnUpdate()->nullOnDelete();
            $table->unsignedTinyInteger('nomor_gelombang');
            $table->dateTime('awal_pendaftaran');
            $table->dateTime('akhir_pendaftaran');
            $table->json('timeline');
            $table->string('link_grup')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('gelombang_pendaftaran');
    }
};
