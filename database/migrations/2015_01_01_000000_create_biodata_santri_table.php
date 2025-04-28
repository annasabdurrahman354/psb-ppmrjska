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
        Schema::create('biodata_santri', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->references('id')->on('users')->cascadeOnUpdate()->cascadeOnDelete();
            $table->string('nomor_induk_santri')->unique();
            $table->unsignedInteger('tahun_pendaftaran');
            $table->string('kewarganegaraan')->default('Indonesia');
            $table->string('nomor_induk_kependudukan')->nullable();
            $table->string('nomor_kartu_keluarga')->nullable();
            $table->string('nomor_passport')->nullable();
            $table->string('tempat_lahir')->nullable();
            $table->date('tanggal_lahir')->nullable();

            $table->string('pendidikan_terakhir')->nullable();
            $table->string('jurusan')->nullable();
            $table->string('program_studi')->nullable();
            $table->string('universitas')->nullable();
            $table->unsignedSmallInteger('angkatan_kuliah')->nullable();
            $table->string('status_kuliah')->nullable();
            $table->date('tanggal_lulus_kuliah')->nullable();

            // Indonesia-specific address fields
            $table->string('alamat')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->unsignedTinyInteger('provinsi_id')->nullable();
            $table->foreign('provinsi_id')->references('id')->on('provinsi')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedSmallInteger('kota_id')->nullable();
            $table->foreign('kota_id')->references('id')->on('kota')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('kecamatan_id')->nullable();
            $table->foreign('kecamatan_id')->references('id')->on('kecamatan')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedBigInteger('kelurahan_id')->nullable();
            $table->foreign('kelurahan_id')->references('id')->on('kelurahan')->nullOnDelete()->cascadeOnUpdate();

            // International address fields
            $table->string('city')->nullable();
            $table->string('state_province')->nullable();

            // Common postal code
            $table->string('kode_pos')->nullable();

            $table->string('kelompok_sambung')->nullable();
            $table->string('desa_sambung')->nullable();
            $table->foreignId('daerah_sambung_id')->nullable()->references('id')->on('daerah')->nullOnDelete()->cascadeOnUpdate();
            $table->string('mulai_mengaji')->nullable();
            $table->string('bahasa_makna')->nullable();
            $table->json('bahasa_harian')->nullable();
            $table->json('keahlian')->nullable();
            $table->json('hobi')->nullable();
            $table->json('sim')->nullable();
            $table->integer('tinggi_badan')->nullable();
            $table->integer('berat_badan')->nullable();
            $table->string('riwayat_sakit')->nullable();
            $table->string('alergi')->nullable();
            $table->string('golongan_darah')->nullable();
            $table->string('ukuran_baju')->nullable();

            $table->string('status_pernikahan')->nullable();
            $table->string('status_tinggal')->nullable();
            $table->unsignedTinyInteger('anak_nomor')->nullable();
            $table->unsignedTinyInteger('jumlah_saudara')->nullable();

            $table->string('nama_ayah')->nullable();
            $table->string('status_ayah')->nullable();
            $table->string('nomor_telepon_ayah', 16)->nullable();
            $table->string('tempat_lahir_ayah')->nullable();
            $table->date('tanggal_lahir_ayah')->nullable();
            $table->string('pekerjaan_ayah')->nullable();
            $table->string('dapukan_ayah')->nullable();
            $table->string('alamat_ayah')->nullable();
            $table->string('kelompok_sambung_ayah')->nullable();
            $table->string('desa_sambung_ayah')->nullable();
            $table->foreignId('daerah_sambung_ayah_id')->nullable()->references('id')->on('daerah')->nullOnDelete()->cascadeOnUpdate();

            $table->string('nama_ibu')->nullable();
            $table->string('status_ibu')->nullable();
            $table->string('nomor_telepon_ibu', 16)->nullable();
            $table->string('tempat_lahir_ibu')->nullable();
            $table->date('tanggal_lahir_ibu')->nullable();
            $table->string('pekerjaan_ibu')->nullable();
            $table->string('dapukan_ibu')->nullable();
            $table->string('alamat_ibu')->nullable();
            $table->string('kelompok_sambung_ibu')->nullable();
            $table->string('desa_sambung_ibu')->nullable();
            $table->foreignId('daerah_sambung_ibu_id')->nullable()->references('id')->on('daerah')->nullOnDelete()->cascadeOnUpdate();

            $table->string('hubungan_wali')->nullable();
            $table->string('nama_wali')->nullable();
            $table->string('nomor_telepon_wali', 16)->nullable();
            $table->string('pekerjaan_wali')->nullable();
            $table->string('dapukan_wali')->nullable();
            $table->string('alamat_wali')->nullable();
            $table->string('kelompok_sambung_wali')->nullable();
            $table->string('desa_sambung_wali')->nullable();
            $table->foreignId('daerah_sambung_wali_id')->nullable()->references('id')->on('daerah')->nullOnDelete()->cascadeOnUpdate();

            $table->boolean('status_mubaligh')->default(false);
            $table->boolean('pernah_mondok')->default(false);
            $table->string('nama_pondok_sebelumnya')->nullable(); // Nama pondok sebelumnya (nullable jika tidak pernah mondok)
            $table->unsignedTinyInteger('lama_mondok_sebelumnya')->nullable();

            $table->date('tanggal_lulus')->nullable();
            $table->date('tanggal_keluar')->nullable();
            $table->string('alasan_keluar')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biodata_santri');
    }
};
