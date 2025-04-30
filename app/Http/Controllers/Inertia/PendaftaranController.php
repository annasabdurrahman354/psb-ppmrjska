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

class PendaftaranController extends Controller
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
        $pendaftaran = Pendaftaran::where('tahun', $currentYear)->first();

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
            'tahunPendaftaran' => $pendaftaran->tahun,
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
            'nomor_induk_kependudukan' => ['required_if:kewarganegaraan,Indonesia', 'digits:16'],
            'nomor_kartu_keluarga' => ['required_if:kewarganegaraan,Indonesia', 'string', 'max:255'], // Adjust validation as needed
            'nomor_passport' => ['required_unless:kewarganegaraan,Indonesia', 'string', 'max:255'],

            // Alamat Indonesia
            'alamat' => ['string'],
            'rt' => ['required_if:kewarganegaraan,Indonesia', 'string', 'max:5'],
            'rw' => ['required_if:kewarganegaraan,Indonesia', 'string', 'max:5'],
            'provinsi_id' => ['required_if:kewarganegaraan,Indonesia', 'integer', 'exists:provinsi,id'],
            'kota_id' => ['required_if:kewarganegaraan,Indonesia', 'integer', Rule::exists('kota', 'id')->where('provinsi_id', $request->input('provinsi_id'))],
            'kecamatan_id' => ['required_if:kewarganegaraan,Indonesia', 'integer', Rule::exists('kecamatan', 'id')->where('kota_id', $request->input('kota_id'))],
            'kelurahan_id' => ['required_if:kewarganegaraan,Indonesia', 'integer', Rule::exists('kelurahan', 'id')->where('kecamatan_id', $request->input('kecamatan_id'))],

            // Alamat Non-Indonesia
            'city' => ['required_unless:kewarganegaraan,Indonesia', 'string', 'max:255'],
            'state_province' => ['required_unless:kewarganegaraan,Indonesia', 'string', 'max:255'],
            'kode_pos' => ['required', 'string', 'max:10'], // Make kode_pos required for both cases

            // Informasi Sambung
            'kelompok_sambung' => ['required', 'string', 'max:255'],
            'desa_sambung' => ['required', 'string', 'max:255'],
            'daerah_sambung_id' => ['required', 'integer', 'exists:daerah,id'],

            // Informasi Pondok & Pendidikan
            'status_mubaligh' => ['required', 'boolean'],
            'pernah_mondok' => ['required', 'boolean'],
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
     * Display the registration index page.
     *
     * @return Response
     */
    public function index(): Response
    {
        $tahunSekarang = Carbon::now()->year;
        $sekarang = Carbon::now();

        // Ambil data pendaftaran untuk tahun ini beserta relasinya
        $pendaftaran = Pendaftaran::with([
            'gelombangPendaftaran' => function ($query) {
                $query->orderBy('nomor_gelombang', 'asc'); // Urutkan gelombang
            },
            'dokumenPendaftaran', // Ensure media relation is loaded if needed for URL
            'indikatorPenilaian'
        ])
            ->where('tahun', $tahunSekarang)
            ->first();

        // Default status pendaftaran
        $statusPendaftaran = 'belum_dibuka'; // belum_dibuka, menunggu_pembukaan, sedang_dibuka, menunggu_gelombang_berikutnya, ditutup
        $gelombangAktifId = null;
        $pesanStatus = "Mohon maaf, pendaftaran calon santri baru untuk tahun {$tahunSekarang} belum dibuka. Silakan cek kembali nanti.";
        $tombolAksiTeks = null;
        $pendaftaranData = null; // Initialize as null

        if ($pendaftaran) {
            $gelombangs = $pendaftaran->gelombangPendaftaran;
            $gelombangAktif = null;
            $adaGelombangMendatang = false;
            $semuaGelombangTerlewat = true;

            foreach ($gelombangs as $gelombang) {
                $awal = Carbon::parse($gelombang->awal_pendaftaran);
                $akhir = Carbon::parse($gelombang->akhir_pendaftaran);

                // Cek apakah ada gelombang yang sedang aktif
                if ($sekarang->isBetween($awal, $akhir)) {
                    $gelombangAktif = $gelombang;
                    break; // Langsung keluar loop jika ada yang aktif
                }

                // Cek apakah ada gelombang di masa depan
                if ($awal->isAfter($sekarang)) {
                    $adaGelombangMendatang = true;
                }

                // Cek apakah semua gelombang sudah terlewat
                if ($akhir->isAfter($sekarang)) {
                    $semuaGelombangTerlewat = false;
                }
            }

            // Logika penentuan status
            if ($gelombangAktif) {
                $statusPendaftaran = 'sedang_dibuka';
                $gelombangAktifId = $gelombangAktif->id;
                $pesanStatus = "Pendaftaran Gelombang {$gelombangAktif->nomor_gelombang} sedang dibuka. Ayo bergabung!";
                $tombolAksiTeks = "Daftar Sekarang!";
            } elseif ($adaGelombangMendatang && !$gelombangAktif) {
                $gelombangPertama = $gelombangs->first();
                if ($gelombangPertama && $sekarang->isBefore(Carbon::parse($gelombangPertama->awal_pendaftaran))) {
                    $statusPendaftaran = 'menunggu_pembukaan';
                    $pesanStatus = "Pendaftaran akan segera dibuka. Persiapkan dirimu untuk menjadi bagian dari keluarga besar PPM Roudlotul Jannah!";
                } else {
                    $statusPendaftaran = 'menunggu_gelombang_berikutnya';
                    $pesanStatus = "Pendaftaran gelombang saat ini telah ditutup. Nantikan pembukaan gelombang berikutnya!";
                }
                $tombolAksiTeks = "Lihat Jadwal";
            } elseif ($semuaGelombangTerlewat) {
                $statusPendaftaran = 'ditutup';
                $pesanStatus = "Mohon maaf, seluruh periode pendaftaran untuk tahun {$tahunSekarang} telah ditutup. Untuk informasi lebih lanjut, silakan hubungi panitia.";
                $tombolAksiTeks = "Hubungi Panitia";
            } elseif ($gelombangs->isEmpty()) {
                $statusPendaftaran = 'belum_dibuka';
                $pesanStatus = "Informasi gelombang pendaftaran untuk tahun {$tahunSekarang} belum tersedia. Silakan cek kembali nanti.";
            }

            // --- MODIFICATION START ---
            // Check if the relation is loaded before attempting to modify it
            if ($pendaftaran->relationLoaded('dokumenPendaftaran')) {
                // Use transform to modify the collection in place
                $pendaftaran->dokumenPendaftaran->transform(function ($dokumen) {
                    // Add the 'url' attribute by calling getFirstMediaUrl
                    // Ensure the DokumenPendaftaran model uses Spatie\MediaLibrary\HasMedia
                    // and has the appropriate media collection defined.
                    $dokumen->url = $dokumen->getFirstMediaUrl('dokumen_pendaftaran_template');
                    return $dokumen; // Return the modified model
                });
            }
            // --- MODIFICATION END ---

            // Convert the modified Pendaftaran object to an array for Inertia
            // This ensures the added 'url' attribute is included in the JSON response
            $pendaftaranData = $pendaftaran->toArray();

        } else {
            // Status remains 'belum_dibuka' as set initially
            $pesanStatus = "Mohon maaf, pendaftaran calon santri baru untuk tahun {$tahunSekarang} belum dibuka atau informasi belum tersedia. Silakan cek kembali nanti.";
        }


        // Daftar Kampus (Hardcoded sesuai permintaan)
        $kampusSekitar = [
            'Kota Surakarta (PTN)' => [
                'Universitas Sebelas Maret Surakarta (UNS)',
                'Institut Seni Indonesia (ISI) Surakarta',
                'Politeknik Kesehatan Kemenkes Surakarta (Poltekkes Solo)',
                'Akademi Komunitas Industri Tekstil dan Produk Tekstil (AK-Tekstil) Solo',
                'Universitas Terbuka (UT) Surakarta',
            ],
            'Kota Surakarta (PTS)' => [
                'Universitas Slamet Riyadi (UNISRI)',
                'Universitas Setia Budi (USB)',
                'Universitas Kristen Surakarta (UKS)',
                'Universitas Islam Batik (UNIBA) Surakarta',
                'Universitas Tunas Pembangunan (UTP) Surakarta',
                'Universitas \'Aisyiyah Surakarta (AISKA)',
                'Universitas Duta Bangsa (UDB) Surakarta',
                'Universitas Sahid (USAHID) Surakarta',
                'Universitas Nahdlatul Ulama (UNU) Surakarta',
                'Politeknik ATMI Surakarta',
                'Politeknik Indonusa Surakarta (Polinus)',
                'Politeknik Pratama Mulia (POLITAMA)',
                'Politeknik Santo Paulus Surakarta',
                'Politeknik Harapan Bangsa Surakarta (Polnas)',
                'Universitas BSI Kampus Surakarta',
                'Akademi Pariwisata Mandala Bhakti Surakarta',
                'Akademi Bahasa Asing R.A. Kartini',
                'Akademi Seni Mangkunegaran Surakarta 1 (ASGA)',
                'Akademi Pelayaran Nasional (APN) Surakarta',
                'Akademi Keperawatan (AKPER) Patria Husada Surakarta',
                'AKBARA SURAKARTA',
                'STMIK Adi Unggul Bhirawa (AUB) Surakarta',
                'Sekolah Tinggi Ilmu Ekonomi (STIE) Surakarta',
                'Sekolah Tinggi Teknologi Warga (STTW) Surakarta',
                'Stikes Mambaul Ulum Surakarta (STIKESMUS)',
                'Institut Islam Mamba\'ul \'Ulum Surakarta',
                'Institut Teknologi Sains dan Kesehatan (ITS) PKU Muhammadiyah Surakarta',
            ],
            'Kabupaten Sukoharjo' => [
                'Universitas Muhammadiyah Surakarta (UMS)',
                'UIN Raden Mas Said Surakarta',
                'Universitas Veteran Bangun Nusantara (UNIVET BANTARA) Sukoharjo',
                'Politeknik Kesehatan Bhakti Mulia',
                'Institut Teknologi Bisnis AAS Indonesia',
                'Sekolah Tinggi Ilmu Ekonomi (STIE) Surakarta', // Disebutkan lagi
                'Sekolah Tinggi Ilmu Manajemen (STIM) Surakarta',
                'Akademi Teknik Warga (ATW) Surakarta',
                'Akademi Pelayaran Nasional (APN) Surakarta Kampus 1',
                'Universitas Sugeng Hartono (USH)',
                'Akademi Seni dan Desain Indonesia (Asdi)',
            ],
            'Kabupaten Karanganyar' => [
                'Universitas Surakarta (UNSA)',
                'Universitas Muhammadiyah Karanganyar (UMUKA)',
                'Institut Teknologi dan Bisnis Kristen Bukit Pengharapan',
                'STIKES Mitra Husada Karanganyar',
                'STIKES Tujuh Belas',
                'Akademi Komunitas Adiyasa Karanganyar',
            ]
        ];

        // Data lain untuk view
        $jargon = "Sarjana yang Mubaligh, Profesional, Religius";
        $mengapaMondok = "Menjadi mahasiswa sekaligus santri membuka pintu kesuksesan dunia dan akhirat. Di PPM Roudlotul Jannah, kamu tidak hanya mengejar gelar sarjana, tapi juga memperdalam ilmu agama, membentuk karakter mulia, dan membangun jaringan persaudaraan yang kuat.";

        return Inertia::render('Pendaftaran/Index', [
            'pendaftaran' => $pendaftaranData, // Pass the modified data (or null)
            'statusPendaftaran' => $statusPendaftaran,
            'pesanStatus' => $pesanStatus,
            'tombolAksiTeks' => $tombolAksiTeks,
            'gelombangAktifId' => $gelombangAktifId,
            'kampusSekitar' => $kampusSekitar,
            'jargon' => $jargon,
            'mengapaMondok' => $mengapaMondok,
            // Use optional chaining or check if $pendaftaranData is null before accessing contact
            'kontakPanitia' => $pendaftaranData['kontak_panitia'] ?? [],
            // Dokumen and Indikator are now included within $pendaftaranData if it's not null
        ]);
    }

    /**
     * Display the success page after registration.
     */
    /**
     * Show the finish page with the group link.
     *
     * Uses Route Model Binding to automatically find the GelombangPendaftaran
     * or throw a 404 error if not found.
     *
     * @param GelombangPendaftaran $gelombangPendaftaran The GelombangPendaftaran instance resolved from the {id} route parameter.
     * @return \Inertia\Response
     */
    public function selesai($id) // <-- Type-hint the model
    {

        // Laravel automatically fetched the GelombangPendaftaran using the {id} from the route.
        // If the ID doesn't exist, Laravel will automatically return a 404 Not Found error.

        $calonSantri = CalonSantri::find($id);
        if (!$calonSantri) abort(404);
        // Get the link_grup attribute from the fetched model
        $pendaftaran = GelombangPendaftaran::find($calonSantri->gelombang_pendaftaran_id);

        // Render the Inertia view and pass the link_grup as a prop
        return Inertia::render('Pendaftaran/Selesai', [
            'link_grup' => $pendaftaran->link_grup ?? null
        ]);
    }
}
