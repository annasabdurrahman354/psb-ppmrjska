<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Migration to create the ponpes table.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Disable foreign key checks temporarily if needed, especially during initial setup or complex changes.
        Schema::disableForeignKeyConstraints();

        Schema::create('ponpes', function (Blueprint $table) {
            // Define the primary key 'id' as an integer.
            // Assuming 'id' is not auto-incrementing based on the sample data.
            // If it should be auto-incrementing, change to $table->id();
            $table->id();

            // Define the foreign key 'daerah_id'.
            // Assuming 'daerah.id' is an integer. Make it nullable if a ponpes might not have a daerah.
            $table->foreignId('daerah_id')
                ->references('id')
                ->on('daerah')
                ->restrictOnDelete() // Or restrictOnDelete(), cascadeOnDelete() depending on your logic
                ->cascadeOnUpdate();; // Made nullable to match sample data where daerah_id might be missing or optional initially

            // Define the 'nama' column for the ponpes name.
            $table->string('nama'); // Default length is 255, adjust if needed.

            // Define the 'status' column, allowing null values.
            $table->string('status')->nullable(); // Adjust length if needed, e.g., $table->string('status', 50)->nullable();
        });

        // Re-enable foreign key checks.
        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Disable foreign key checks before dropping the table.
        Schema::disableForeignKeyConstraints();

        Schema::dropIfExists('ponpes');

        // Re-enable foreign key checks.
        Schema::enableForeignKeyConstraints();
    }
};
