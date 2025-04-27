<?php

namespace App\Models;

use App\Enums\BahasaMakna;
use App\Enums\GolonganDarah;
use App\Enums\HubunganWali;
use App\Enums\MulaiMengaji;
use App\Enums\PendidikanTerakhir;
use App\Enums\StatusKuliah;
use App\Enums\StatusPernikahan;
use App\Enums\StatusTinggal;
use App\Enums\UkuranBaju;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Represents the detailed biodata for a Santri.
 *
 * @property string $id // ULID
 * @property string $user_id // Foreign ULID
 * @property string $nomor_induk_santri
 * @property int $tahun_pendaftaran
 * @property string $kewarganegaraan
 * @property string|null $nomor_identitas_kependudukan
 * @property string|null $nomor_kartu_keluarga
 * @property string|null $nomor_passport
 * @property string|null $tempat_lahir
 * @property \Illuminate\Support\Carbon|null $tanggal_lahir
 * @property PendidikanTerakhir $pendidikan_terakhir // Updated type hint
 * @property string|null $jurusan
 * @property string|null $program_studi
 * @property string|null $universitas
 * @property int|null $angkatan_kuliah
 * @property string|null $status_kuliah
 * @property \Illuminate\Support\Carbon|null $tanggal_lulus_kuliah
 * @property string|null $alamat
 * @property string|null $rt
 * @property string|null $rw
 * @property int|null $provinsi_id
 * @property int|null $kota_id
 * @property int|null $kecamatan_id // bigint in schema
 * @property int|null $kelurahan_id // bigint in schema
 * @property string|null $city
 * @property string|null $state_province
 * @property string|null $kode_pos
 * @property string|null $kelompok_sambung
 * @property string|null $desa_sambung
 * @property int|null $daerah_sambung_id
 * @property string|null $mulai_mengaji
 * @property string|null $bahasa_makna
 * @property array|null $bahasa_harian // Cast from JSON
 * @property array|null $keahlian // Cast from JSON
 * @property array|null $hobi // Cast from JSON
 * @property array|null $sim // Cast from JSON
 * @property int|null $tinggi_badan
 * @property int|null $berat_badan
 * @property string|null $riwayat_sakit
 * @property string|null $alergi
 * @property string|null $golongan_darah
 * @property string|null $ukuran_baju
 * @property string|null $status_pernikahan
 * @property string|null $status_tinggal
 * @property int|null $anak_nomor
 * @property int|null $jumlah_saudara
 * @property string|null $nama_ayah
 * @property string|null $status_ayah
 * @property string|null $nomor_telepon_ayah
 * @property string|null $tempat_lahir_ayah
 * @property \Illuminate\Support\Carbon|null $tanggal_lahir_ayah
 * @property string|null $pekerjaan_ayah
 * @property string|null $dapukan_ayah
 * @property string|null $alamat_ayah
 * @property string|null $kelompok_sambung_ayah
 * @property string|null $desa_sambung_ayah
 * @property int|null $daerah_sambung_ayah_id
 * @property string|null $nama_ibu
 * @property string|null $status_ibu
 * @property string|null $nomor_telepon_ibu
 * @property string|null $tempat_lahir_ibu
 * @property \Illuminate\Support\Carbon|null $tanggal_lahir_ibu
 * @property string|null $pekerjaan_ibu
 * @property string|null $dapukan_ibu
 * @property string|null $alamat_ibu
 * @property string|null $kelompok_sambung_ibu
 * @property string|null $desa_sambung_ibu
 * @property int|null $daerah_sambung_ibu_id
 * @property string|null $hubungan_wali
 * @property string|null $nama_wali
 * @property string|null $nomor_telepon_wali
 * @property string|null $pekerjaan_wali
 * @property string|null $dapukan_wali
 * @property string|null $alamat_wali
 * @property string|null $kelompok_sambung_wali
 * @property string|null $desa_sambung_wali
 * @property int|null $daerah_sambung_wali_id
 * @property bool $status_mubaligh // Cast from boolean
 * @property bool $pernah_mondok // Cast from boolean
 * @property string|null $nama_pondok_sebelumnya
 * @property int|null $lama_mondok_sebelumnya
 * @property \Illuminate\Support\Carbon|null $tanggal_lulus
 * @property \Illuminate\Support\Carbon|null $tanggal_keluar
 * @property string|null $alasan_keluar
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User $user
 * @property-read \App\Models\Provinsi|null $provinsi
 * @property-read \App\Models\Kota|null $kota
 * @property-read \App\Models\Kecamatan|null $kecamatan
 * @property-read \App\Models\Kelurahan|null $kelurahan
 * @property-read \App\Models\Daerah|null $daerahSambung
 * @property-read \App\Models\Daerah|null $daerahSambungAyah
 * @property-read \App\Models\Daerah|null $daerahSambungIbu
 * @property-read \App\Models\Daerah|null $daerahSambungWali
 */
class BiodataSantri extends Model
{
    use HasFactory, HasUlids, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'biodata_santri';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'nomor_induk_santri',
        'tahun_pendaftaran',
        'kewarganegaraan',
        'nomor_identitas_kependudukan',
        'nomor_kartu_keluarga',
        'nomor_passport',
        'tempat_lahir',
        'tanggal_lahir',

        'pendidikan_terakhir',
        'jurusan',
        'program_studi',
        'universitas',
        'angkatan_kuliah',
        'status_kuliah',
        'tanggal_lulus_kuliah',
        'alamat',
        'rt',
        'rw',
        'provinsi_id',
        'kota_id',
        'kecamatan_id',
        'kelurahan_id',
        'city',
        'state_province',
        'kode_pos',
        'kelompok_sambung',
        'desa_sambung',
        'daerah_sambung_id',
        'mulai_mengaji',
        'bahasa_makna',
        'bahasa_harian',
        'keahlian',
        'hobi',
        'sim',
        'tinggi_badan',
        'berat_badan',
        'riwayat_sakit',
        'alergi',
        'golongan_darah',
        'ukuran_baju',
        'status_pernikahan',
        'status_tinggal',
        'anak_nomor',
        'jumlah_saudara',
        'nama_ayah',
        'status_ayah',
        'nomor_telepon_ayah',
        'tempat_lahir_ayah',
        'tanggal_lahir_ayah',
        'pekerjaan_ayah',
        'dapukan_ayah',
        'alamat_ayah',
        'kelompok_sambung_ayah',
        'desa_sambung_ayah',
        'daerah_sambung_ayah_id',
        'nama_ibu',
        'status_ibu',
        'nomor_telepon_ibu',
        'tempat_lahir_ibu',
        'tanggal_lahir_ibu',
        'pekerjaan_ibu',
        'dapukan_ibu',
        'alamat_ibu',
        'kelompok_sambung_ibu',
        'desa_sambung_ibu',
        'daerah_sambung_ibu_id',
        'hubungan_wali',
        'nama_wali',
        'nomor_telepon_wali',
        'pekerjaan_wali',
        'dapukan_wali',
        'alamat_wali',
        'kelompok_sambung_wali',
        'desa_sambung_wali',
        'daerah_sambung_wali_id',

        'status_mubaligh',
        'pernah_mondok',
        'nama_pondok_sebelumnya',
        'lama_mondok_sebelumnya',

        'tanggal_lulus',
        'tanggal_keluar',
        'alasan_keluar',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tahun_pendaftaran' => 'integer',
            'tanggal_lahir' => 'date',
            'status_mubaligh' => 'boolean',
            'pernah_mondok' => 'boolean',
            'lama_mondok_sebelumnya' => 'integer',

            'angkatan_kuliah' => 'integer',
            'tanggal_lulus_kuliah' => 'date',

            'bahasa_harian' => 'array', // Cast JSON to array
            'keahlian' => 'array',      // Cast JSON to array
            'hobi' => 'array',          // Cast JSON to array
            'sim' => 'array',           // Cast JSON to array

            'tinggi_badan' => 'integer',
            'berat_badan' => 'integer',
            'anak_nomor' => 'integer',
            'jumlah_saudara' => 'integer',

            'tanggal_lahir_ayah' => 'date',
            'tanggal_lahir_ibu' => 'date',

            'pendidikan_terakhir' => PendidikanTerakhir::class,
            'status_kuliah' => StatusKuliah::class,
            'golongan_darah' => GolonganDarah::class,
            'ukuran_baju' => UkuranBaju::class,
            'status_pernikahan' => StatusPernikahan::class,
            'status_tinggal' => StatusTinggal::class,
            'hubungan_wali' => HubunganWali::class,
            'mulai_mengaji' => MulaiMengaji::class,
            'bahasa_makna' => BahasaMakna::class,

            'tanggal_lulus' => 'date',
            'tanggal_keluar' => 'date',

            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * Get the user that owns the biodata.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the province associated with the santri's address.
     */
    public function provinsi(): BelongsTo
    {
        return $this->belongsTo(Provinsi::class, 'provinsi_id');
    }

    /**
     * Get the city/regency associated with the santri's address.
     */
    public function kota(): BelongsTo
    {
        return $this->belongsTo(Kota::class, 'kota_id');
    }

    /**
     * Get the district associated with the santri's address.
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    /**
     * Get the village/sub-district associated with the santri's address.
     */
    public function kelurahan(): BelongsTo
    {
        return $this->belongsTo(Kelurahan::class, 'kelurahan_id');
    }

    /**
     * Get the 'daerah sambung' associated with the santri.
     */
    public function daerahSambung(): BelongsTo
    {
        return $this->belongsTo(Daerah::class, 'daerah_sambung_id');
    }

    /**
     * Get the 'daerah sambung' associated with the santri's father.
     */
    public function daerahSambungAyah(): BelongsTo
    {
        return $this->belongsTo(Daerah::class, 'daerah_sambung_ayah_id');
    }

    /**
     * Get the 'daerah sambung' associated with the santri's mother.
     */
    public function daerahSambungIbu(): BelongsTo
    {
        return $this->belongsTo(Daerah::class, 'daerah_sambung_ibu_id');
    }

    /**
     * Get the 'daerah sambung' associated with the santri's guardian.
     */
    public function daerahSambungWali(): BelongsTo
    {
        return $this->belongsTo(Daerah::class, 'daerah_sambung_wali_id');
    }
}
