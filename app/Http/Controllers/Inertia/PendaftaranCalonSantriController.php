<?php

namespace App\Http\Controllers\Inertia;

use App\Enums\BahasaMakna;
use App\Enums\GolonganDarah;
use App\Enums\HubunganWali;
use App\Enums\JenisKelamin;
use App\Enums\JenisSIM;
use App\Enums\MulaiMengaji;
use App\Enums\Negara;
use App\Enums\PendidikanTerakhir;
use App\Enums\StatusKuliah;
use App\Enums\StatusOrangTua;
use App\Enums\StatusPernikahan;
use App\Enums\StatusTinggal;
use App\Enums\UkuranBaju;
use App\Http\Controllers\Controller;
use App\Models\CalonSantri;
use App\Models\Daerah;
use App\Models\DokumenPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\Pendaftaran;
use App\Models\Provinsi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;
use Inertia\Inertia;
use Inertia\Response;

class PendaftaranCalonSantriController extends Controller
{
    /**
     * Display the registration form page.
     * Checks for active registration waves and provides necessary data.
     */
    public function create(): Response|\Illuminate\Http\RedirectResponse // Added RedirectResponse type hint
    {
        $now = Carbon::now();
        $currentYear = $now->year;

        // Find the Pendaftaran for the current year
        $pendaftaran = Pendaftaran::where('tahun_pendaftaran', $currentYear)->first();

        if (!$pendaftaran) {
            return Inertia::render('Pendaftaran/TidakDibuka', [
                'message' => 'Pendaftaran untuk tahun ini belum dibuka.'
            ]);
        }

        // Find the active GelombangPendaftaran within the current Pendaftaran
        $gelombangAktif = GelombangPendaftaran::where('pendaftaran_id', $pendaftaran->id)
            ->where('awal_pendaftaran', '<=', $now)
            ->where('akhir_pendaftaran', '>=', $now)
            ->first();

        if (!$gelombangAktif) {
            return Inertia::render('Pendaftaran/TidakDibuka', [
                'message' => 'Saat ini tidak ada gelombang pendaftaran yang sedang dibuka.'
            ]);
        }

        // Fetch required documents for the active registration year
        $requiredDokumen = DokumenPendaftaran::where('pendaftaran_id', $pendaftaran->id)
            ->with('media') // Eager load media for template URLs
            ->get()
            ->map(function ($doc) {
                return [
                    'id' => $doc->id,
                    'nama' => $doc->nama,
                    'keterangan' => $doc->keterangan,
                    'template_url' => $doc->getFirstMediaUrl('dokumen_pendaftaran_template'), // Get template URL
                ];
            });


        // Prepare options for selects
        $options = [
            'negara' => collect(Negara::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'jenis_kelamin' => collect(JenisKelamin::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'pendidikan_terakhir' => collect(PendidikanTerakhir::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'status_kuliah' => collect(StatusKuliah::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'mulai_mengaji' => collect(MulaiMengaji::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'bahasa_makna' => collect(BahasaMakna::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'golongan_darah' => collect(GolonganDarah::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'ukuran_baju' => collect(UkuranBaju::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'status_pernikahan' => collect(StatusPernikahan::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'status_tinggal' => collect(StatusTinggal::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'status_orang_tua' => collect(StatusOrangTua::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'hubungan_wali' => collect(HubunganWali::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'jenis_sim' => collect(JenisSIM::cases())->map(fn($e) => ['value' => $e->value, 'label' => $e->getLabel()])->values(),
            'provinsi' => Provinsi::orderBy('nama')->pluck('nama', 'id'),
            'daerah_sambung' => Daerah::orderBy('nama')->pluck('nama', 'id'),
        ];

        return Inertia::render('Pendaftaran/Create', [
            'gelombangPendaftaranId' => $gelombangAktif->id,
            'tahunPendaftaran' => $pendaftaran->tahun_pendaftaran,
            'requiredDokumen' => $requiredDokumen,
            'options' => $options,
            'statusOrangTuaHidup' => StatusOrangTua::HIDUP->value,
            'hubunganWaliOrangTua' => HubunganWali::ORANGTUA->value,
            'negaraIndonesia' => Negara::INDONESIA->value,
        ]);
    }

    /**
     * Store the newly registered Santri.
     */
    public function store(Request $request)
    {
        // --- Validation ---
        // Define validation rules similar to the Filament form
        $rules = [
            'gelombang_pendaftaran_id' => ['required', 'ulid', 'exists:gelombang_pendaftaran,id'],

            // Data Diri
            'nama' => ['required', 'string', 'max:255'],
            'nama_panggilan' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', new Enum(JenisKelamin::class)],
            'nomor_telepon' => ['required', 'string', 'max:20'], // Add tel validation if needed
            'email' => ['required', 'email', 'max:255'],
            'tempat_lahir' => ['required', 'string', 'max:255'],
            'tanggal_lahir' => ['required', 'date'],
            'kewarganegaraan' => ['required', new Enum(Negara::class)],
            'nomor_induk_kependudukan' => ['required_if:kewarganegaraan,Indonesia', 'nullable', 'digits:16'],
            'nomor_kartu_keluarga' => ['required_if:kewarganegaraan,Indonesia', 'nullable', 'string', 'max:255'], // Adjust validation as needed
            'nomor_passport' => ['required_unless:kewarganegaraan,Indonesia', 'nullable', 'string', 'max:255'],

            // Alamat Indonesia
            'alamat' => ['required_if:kewarganegaraan,Indonesia', 'nullable', 'string'],
            'rt' => ['required_if:kewarganegaraan,Indonesia', 'nullable', 'string', 'max:5'],
            'rw' => ['required_if:kewarganegaraan,Indonesia', 'nullable', 'string', 'max:5'],
            'provinsi_id' => ['required_if:kewarganegaraan,Indonesia', 'nullable', 'integer', 'exists:provinsi,id'],
            'kota_id' => ['required_if:kewarganegaraan,Indonesia', 'nullable', 'integer', Rule::exists('kota', 'id')->where('provinsi_id', $request->input('provinsi_id'))],
            'kecamatan_id' => ['required_if:kewarganegaraan,Indonesia', 'nullable', 'integer', Rule::exists('kecamatan', 'id')->where('kota_id', $request->input('kota_id'))],
            'kelurahan_id' => ['required_if:kewarganegaraan,Indonesia', 'nullable', 'integer', Rule::exists('kelurahan', 'id')->where('kecamatan_id', $request->input('kecamatan_id'))],

            // Alamat Non-Indonesia
            'alamat_non_indo' => ['required_unless:kewarganegaraan,Indonesia', 'nullable', 'string'],
            'city' => ['required_unless:kewarganegaraan,Indonesia', 'nullable', 'string', 'max:255'],
            'state_province' => ['required_unless:kewarganegaraan,Indonesia', 'nullable', 'string', 'max:255'],
            'kode_pos' => ['required', 'string', 'max:10'], // Make kode_pos required for both cases

            // Informasi Sambung
            'kelompok_sambung' => ['required', 'string', 'max:255'],
            'desa_sambung' => ['required', 'string', 'max:255'],
            'daerah_sambung_id' => ['required', 'integer', 'exists:daerah,id'],

            // Informasi Pondok & Pendidikan
            'status_mubaligh' => ['required', 'boolean'],
            'pernah_mondok' => ['required', 'boolean', Rule::acceptedIf($request->input('status_mubaligh') == true)],
            'nama_pondok_sebelumnya' => ['required_if:pernah_mondok,true', 'nullable', 'string', 'max:255'],
            'lama_mondok_sebelumnya' => ['required_if:pernah_mondok,true', 'nullable', 'integer', 'min:1'],
            'pendidikan_terakhir' => ['required', new Enum(PendidikanTerakhir::class)],
            'jurusan' => ['nullable', 'string', 'max:255'],
            'universitas' => ['required', 'string', 'max:255'], // Required based on Filament
            'program_studi' => ['required', 'string', 'max:255'], // Required based on Filament
            'angkatan_kuliah' => ['required', 'integer', 'digits:4'], // Required based on Filament
            'status_kuliah' => ['required', new Enum(StatusKuliah::class)],

            // Informasi Tambahan
            'mulai_mengaji' => ['required', new Enum(MulaiMengaji::class)],
            'bahasa_makna' => ['required', new Enum(BahasaMakna::class)],
            'bahasa_harian' => ['required', 'array'],
            'bahasa_harian.*' => ['string', 'max:100'],
            'keahlian' => ['nullable', 'array'],
            'keahlian.*' => ['string', 'max:100'],
            'hobi' => ['nullable', 'array'],
            'hobi.*' => ['string', 'max:100'],
            'sim' => ['nullable', 'array'],
            'sim.*' => [new Enum(JenisSIM::class)],
            'tinggi_badan' => ['required', 'integer', 'min:50', 'max:250'],
            'berat_badan' => ['required', 'integer', 'min:10', 'max:200'],
            'riwayat_sakit' => ['nullable', 'string'],
            'alergi' => ['nullable', 'string'],
            'golongan_darah' => ['required', new Enum(GolonganDarah::class)],
            'ukuran_baju' => ['required', new Enum(UkuranBaju::class)],
            'status_pernikahan' => ['required', new Enum(StatusPernikahan::class)],
            'status_tinggal' => ['required', new Enum(StatusTinggal::class)],
            'anak_nomor' => ['required', 'integer', 'min:1'],
            'jumlah_saudara' => ['required', 'integer', 'min:0'],

            // Informasi Ayah
            'nama_ayah' => ['required', 'string', 'max:255'],
            'status_ayah' => ['required', new Enum(StatusOrangTua::class)],
            'nomor_telepon_ayah' => ['required_if:status_ayah,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:20'],
            'tempat_lahir_ayah' => ['required_if:status_ayah,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:255'],
            'tanggal_lahir_ayah' => ['required_if:status_ayah,' . StatusOrangTua::HIDUP->value, 'nullable', 'date'],
            'pekerjaan_ayah' => ['required_if:status_ayah,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:255'],
            'dapukan_ayah' => ['required_if:status_ayah,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:255'],
            'alamat_ayah' => ['required_if:status_ayah,' . StatusOrangTua::HIDUP->value, 'nullable', 'string'],
            'kelompok_sambung_ayah' => ['required_if:status_ayah,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:255'],
            'desa_sambung_ayah' => ['required_if:status_ayah,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:255'],
            'daerah_sambung_ayah_id' => ['required_if:status_ayah,' . StatusOrangTua::HIDUP->value, 'nullable', 'integer', 'exists:daerah,id'],

            // Informasi Ibu
            'nama_ibu' => ['required', 'string', 'max:255'],
            'status_ibu' => ['required', new Enum(StatusOrangTua::class)],
            'nomor_telepon_ibu' => ['required_if:status_ibu,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:20'],
            'tempat_lahir_ibu' => ['required_if:status_ibu,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:255'],
            'tanggal_lahir_ibu' => ['required_if:status_ibu,' . StatusOrangTua::HIDUP->value, 'nullable', 'date'],
            'pekerjaan_ibu' => ['required_if:status_ibu,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:255'],
            'dapukan_ibu' => ['required_if:status_ibu,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:255'],
            'alamat_ibu' => ['required_if:status_ibu,' . StatusOrangTua::HIDUP->value, 'nullable', 'string'],
            'kelompok_sambung_ibu' => ['required_if:status_ibu,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:255'],
            'desa_sambung_ibu' => ['required_if:status_ibu,' . StatusOrangTua::HIDUP->value, 'nullable', 'string', 'max:255'],
            'daerah_sambung_ibu_id' => ['required_if:status_ibu,' . StatusOrangTua::HIDUP->value, 'nullable', 'integer', 'exists:daerah,id'],

            // Informasi Wali
            'hubungan_wali' => ['required', new Enum(HubunganWali::class)],
            'nama_wali' => ['required_unless:hubungan_wali,' . HubunganWali::ORANGTUA->value, 'nullable', 'string', 'max:255'],
            'nomor_telepon_wali' => ['required_unless:hubungan_wali,' . HubunganWali::ORANGTUA->value, 'nullable', 'string', 'max:20'],
            'pekerjaan_wali' => ['required_unless:hubungan_wali,' . HubunganWali::ORANGTUA->value, 'nullable', 'string', 'max:255'],
            'dapukan_wali' => ['required_unless:hubungan_wali,' . HubunganWali::ORANGTUA->value, 'nullable', 'string', 'max:255'],
            'alamat_wali' => ['required_unless:hubungan_wali,' . HubunganWali::ORANGTUA->value, 'nullable', 'string'],
            'kelompok_sambung_wali' => ['nullable', 'string', 'max:255'],
            'desa_sambung_wali' => ['nullable', 'string', 'max:255'],
            'daerah_sambung_wali_id' => ['nullable', 'integer', 'exists:daerah,id'],

            // Dokumen (Handle file uploads)
            'dokumen' => ['required', 'array'],
            'dokumen.*.dokumen_pendaftaran_id' => ['required', 'ulid', 'exists:dokumen_pendaftaran,id'],
            'dokumen.*.file' => ['required', 'file', 'max:5120', 'mimes:pdf,jpg,jpeg,png'], // Adjust max size and mimes as needed

        ];

        // --- Validation Messages (Optional) ---
        $messages = [
            'nomor_induk_kependudukan.required_if' => 'NIK wajib diisi untuk WNI.',
            'nomor_kartu_keluarga.required_if' => 'No. KK wajib diisi untuk WNI.',
            'nomor_passport.required_unless' => 'No. Passport wajib diisi jika bukan WNI.',
            'alamat.required_if' => 'Alamat (Indonesia) wajib diisi untuk WNI.',
            'rt.required_if' => 'RT wajib diisi untuk WNI.',
            'rw.required_if' => 'RW wajib diisi untuk WNI.',
            'provinsi_id.required_if' => 'Provinsi wajib diisi untuk WNI.',
            'kota_id.required_if' => 'Kota/Kabupaten wajib diisi untuk WNI.',
            'kecamatan_id.required_if' => 'Kecamatan wajib diisi untuk WNI.',
            'kelurahan_id.required_if' => 'Kelurahan/Desa wajib diisi untuk WNI.',
            'alamat_non_indo.required_unless' => 'Address wajib diisi jika bukan WNI.',
            'city.required_unless' => 'City wajib diisi jika bukan WNI.',
            'state_province.required_unless' => 'State/Province wajib diisi jika bukan WNI.',
            'pernah_mondok.accepted_if' => 'Jika Status Mubaligh dicentang, Pernah Mondok harus dicentang.',
            'nama_pondok_sebelumnya.required_if' => 'Nama Pondok Sebelumnya wajib diisi jika pernah mondok.',
            'lama_mondok_sebelumnya.required_if' => 'Lama Mondok Sebelumnya wajib diisi jika pernah mondok.',
            'nomor_telepon_ayah.required_if' => 'Nomor Telepon Ayah wajib diisi jika status Ayah hidup.',
            // Add more messages for other conditional fields...
            'dokumen.*.file.required' => 'Dokumen :attribute wajib diunggah.',
            'dokumen.*.file.max' => 'Ukuran file :attribute maksimal 5MB.',
            'dokumen.*.file.mimes' => 'Format file :attribute harus PDF, JPG, atau PNG.',
        ];

        // --- Attribute Names for Validation Messages ---
        $attributeNames = [];
        $pendaftaranId = GelombangPendaftaran::find($request->input('gelombang_pendaftaran_id'))?->pendaftaran_id;
        if ($pendaftaranId) {
            $requiredDokumenNames = DokumenPendaftaran::where('pendaftaran_id', $pendaftaranId)->pluck('nama', 'id');
            foreach ($request->input('dokumen', []) as $index => $docData) {
                $docId = $docData['dokumen_pendaftaran_id'] ?? null;
                $docName = $requiredDokumenNames[$docId] ?? "Dokumen #".($index + 1);
                $attributeNames["dokumen.{$index}.file"] = $docName;
            }
        }


        // --- Validate Request ---
        $validatedData = Validator::make($request->all(), $rules, $messages, $attributeNames)->validate();

        // --- Prepare Data ---
        $calonSantriData = collect($validatedData)->except(['dokumen', 'alamat_non_indo'])->toArray();

        // Handle conditional alamat based on kewarganegaraan
        if ($validatedData['kewarganegaraan'] !== Negara::INDONESIA->value) {
            $calonSantriData['alamat'] = $validatedData['alamat_non_indo'] ?? null;
            // Clear Indonesian address fields if not WNI
            $calonSantriData['rt'] = null;
            $calonSantriData['rw'] = null;
            $calonSantriData['provinsi_id'] = null;
            $calonSantriData['kota_id'] = null;
            $calonSantriData['kecamatan_id'] = null;
            $calonSantriData['kelurahan_id'] = null;
        } else {
            // Clear non-Indonesian address fields if WNI
            $calonSantriData['city'] = null;
            $calonSantriData['state_province'] = null;
        }

        // Clear parent/wali fields if not applicable based on status/hubungan
        if ($validatedData['status_ayah'] !== StatusOrangTua::HIDUP->value) {
            foreach (['nomor_telepon_ayah', 'tempat_lahir_ayah', 'tanggal_lahir_ayah', 'pekerjaan_ayah', 'dapukan_ayah', 'alamat_ayah', 'kelompok_sambung_ayah', 'desa_sambung_ayah', 'daerah_sambung_ayah_id'] as $field) {
                $calonSantriData[$field] = null;
            }
        }
        if ($validatedData['status_ibu'] !== StatusOrangTua::HIDUP->value) {
            foreach (['nomor_telepon_ibu', 'tempat_lahir_ibu', 'tanggal_lahir_ibu', 'pekerjaan_ibu', 'dapukan_ibu', 'alamat_ibu', 'kelompok_sambung_ibu', 'desa_sambung_ibu', 'daerah_sambung_ibu_id'] as $field) {
                $calonSantriData[$field] = null;
            }
        }
        if ($validatedData['hubungan_wali'] === HubunganWali::ORANGTUA->value) {
            foreach (['nama_wali', 'nomor_telepon_wali', 'pekerjaan_wali', 'dapukan_wali', 'alamat_wali', 'kelompok_sambung_wali', 'desa_sambung_wali', 'daerah_sambung_wali_id'] as $field) {
                $calonSantriData[$field] = null;
            }
        }


        // --- Create CalonSantri ---
        $calonSantri = CalonSantri::create($calonSantriData);

        // --- Handle Document Uploads ---
        if ($request->has('dokumen')) {
            foreach ($validatedData['dokumen'] as $index => $dokumenData) {
                if ($request->hasFile("dokumen.{$index}.file")) {
                    $file = $request->file("dokumen.{$index}.file");

                    // Create the DokumenCalonSantri record first
                    $dokumenRecord = $calonSantri->dokumen()->create([
                        'dokumen_pendaftaran_id' => $dokumenData['dokumen_pendaftaran_id'],
                    ]);

                    // Add the file to the media library collection
                    $dokumenRecord->addMedia($file)
                        ->toMediaCollection('dokumen_calon_santri_berkas'); // Match collection name in model
                }
            }
        }


        // --- Redirect or Response ---
        // Redirect to a success page or return a success response
        return Redirect::route('pendaftaran.sukses') // Define this route
        ->with('success', 'Pendaftaran berhasil!');
    }

    /**
     * Display the success page after registration.
     */
    public function sukses()
    {
        return Inertia::render('Pendaftaran/Sukses');
    }
}
