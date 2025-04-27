<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create the daerah table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('daerah', function (Blueprint $table) {
            // Define the primary key column 'id' as an integer.
            // Note: We are not using increments() or id() because the model
            // specifies $incrementing = false. If the ID should auto-increment,
            // change this to $table->id(); or $table->increments('id');
            $table->id();

            // Define the 'nama' column as a nullable string with a max length of 100.
            $table->string('nama')->nullable();

            // Define the 'provinsi' column as a nullable string with a max length of 100.
            $table->string('provinsi')->nullable();

            // Define the 'wilayah' column as a nullable string with a max length of 10.
            $table->string('wilayah')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daerah');
    }
};
