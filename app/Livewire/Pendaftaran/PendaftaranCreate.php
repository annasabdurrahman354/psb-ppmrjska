<?php

namespace App\Livewire\Pendaftaran;

use App\Enums\BahasaMakna;
use App\Enums\GolonganDarah;
use App\Enums\HubunganWali;
use App\Enums\JenisKelamin;
use App\Enums\JenisSIM;
use App\Enums\MulaiMengaji;
use App\Enums\Negara;
use App\Enums\PendidikanTerakhir;
use App\Enums\StatusKuliah;
use App\Enums\StatusKuliahCalonSantri;
use App\Enums\StatusOrangTua;
use App\Enums\StatusPernikahan;
use App\Enums\StatusTinggal;
use App\Enums\UkuranBaju;
use App\Models\CalonSantri;
use App\Models\Daerah;
use App\Models\DokumenPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Kota;
use App\Models\Provinsi;
use Carbon\Carbon;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Component;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Wizard; // Import Wizard
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;
use Livewire\WithFileUploads;

class PendaftaranCreate extends Component implements HasForms
{
    use InteractsWithForms, WithFileUploads;

    public ?array $data = []; // Holds the form data

    public ?GelombangPendaftaran $activeGelombang = null;
    public bool $pendaftaranDibuka = false;
    public array $requiredDokumenList = []; // To store required document info for repeater default

    public function mount(): void
    {
        $now = Carbon::now();
        $currentYear = $now->year;

        $this->activeGelombang = GelombangPendaftaran::with('pendaftaran')
            ->whereHas('pendaftaran', fn ($query) => $query->where('tahun', $currentYear))
            ->whereDate('awal_pendaftaran', '<=', $now)
            ->whereDate('akhir_pendaftaran', '>=', $now)
            ->orderBy('nomor_gelombang') // Prioritize lower number if overlap
            ->first();

        if ($this->activeGelombang) {
            $this->pendaftaranDibuka = true;

            // Prepare required documents for the repeater default
            $requiredDokumen = DokumenPendaftaran::where('pendaftaran_id', $this->activeGelombang->pendaftaran_id)
                ->select('id', 'nama', 'keterangan') // Select needed fields
                ->get();


            $defaultDokumenItems = [];
            foreach ($requiredDokumen as $dokumen) {
                $this->requiredDokumenList[$dokumen->id] = $dokumen; // Store info for later use in form
                $defaultDokumenItems[] = [
                    'dokumen_pendaftaran_id' => $dokumen->id,
                    'media' => null, // Key for Spatie field
                ];
            }

            // Initialize form data with the active gelombang ID and default repeater items
            $this->form->fill([
                'gelombang_pendaftaran_id' => $this->activeGelombang->id,
                'dokumen' => $defaultDokumenItems, // Set default items for repeater
            ]);

        } else {
            $this->pendaftaranDibuka = false;
        }
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pendaftaran')
                    ->hidden()
                    ->schema([
                        // Hidden field to store the active gelombang ID
                        Hidden::make('gelombang_pendaftaran_id')
                            ->required(), // Ensure it's filled from mount()

                        // Placeholder to display the active wave information
                        Placeholder::make('info_gelombang')
                            ->label('Gelombang Pendaftaran Aktif')
                            ->content(fn (): ?string => $this->activeGelombang
                                ? 'Gelombang ' . $this->activeGelombang->nomor_gelombang . ' - Tahun ' . $this->activeGelombang->pendaftaran?->tahun
                                : 'Tidak ada gelombang pendaftaran aktif saat ini.')
                            ->visible($this->pendaftaranDibuka), // Only show if registration is open
                    ]),

                Wizard::make([
                    Wizard\Step::make('Informasi Dasar')
                        ->icon('heroicon-o-user')
                        ->description('Data diri dan alamat calon santri')
                        ->schema([
                            Hidden::make('gelombang_pendaftaran_id')->required(),

                            // --- Data Diri ---
                            Section::make('Data Diri')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('nama')
                                        ->label('Nama Lengkap')
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('nama_panggilan')
                                        ->label('Nama Panggilan')
                                        ->required()
                                        ->maxLength(255),
                                    ToggleButtons::make('jenis_kelamin')
                                        ->label('Jenis Kelamin')
                                        ->options(JenisKelamin::class)
                                        ->required()
                                        ->inline(),
                                    TextInput::make('nomor_telepon')
                                        ->label('Nomor Telepon (Whatsapp)')
                                        ->tel()
                                        ->required()
                                        ->rules([
                                            'regex:/^0[0-9]{8,14}$/',
                                        ])
                                        ->validationMessages([
                                            'regex' => 'Nomor telepon harus diawali dengan angka 0 dan tidak boleh mengandung spasi atau tanda hubung (-).',
                                        ])
                                        ->extraAttributes(['inputmode' => 'numeric', 'pattern' => '[0-9]*']),
                                    TextInput::make('email')
                                        ->label('Email')
                                        ->email()
                                        ->required()
                                        ->maxLength(255),
                                    TextInput::make('tempat_lahir')
                                        ->label('Tempat Lahir')
                                        ->required(),
                                    DatePicker::make('tanggal_lahir')
                                        ->label('Tanggal Lahir')
                                        ->required(),
                                    Select::make('kewarganegaraan')
                                        ->label('Kewarganegaraan')
                                        ->options(Negara::class)
                                        ->default(Negara::INDONESIA->value)
                                        ->searchable()
                                        ->live()
                                        ->required(),
                                    TextInput::make('nomor_induk_kependudukan')
                                        ->label('Nomor Induk Kependudukan')
                                        ->length(16)
                                        ->visible(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value)
                                        ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                    TextInput::make('nomor_kartu_keluarga')
                                        ->label('No. Kartu Keluarga')
                                        ->visible(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value)
                                        ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                    TextInput::make('nomor_passport')
                                        ->label('No. Passport')
                                        ->visible(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== Negara::INDONESIA->value)
                                        ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== Negara::INDONESIA->value),
                                ]),

                            // --- Alamat ---
                            Section::make('Alamat')
                                ->visible(fn (Get $get): bool => $get('kewarganegaraan') != null)
                                ->schema([
                                    // Alamat Indonesia
                                    Textarea::make('alamat')
                                        ->label('Alamat')
                                        ->rows(3)
                                        ->visible(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value)
                                        ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                    Grid::make(4)
                                        ->visible(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value)
                                        ->schema([
                                            TextInput::make('rt')
                                                ->label('RT')
                                                ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                            TextInput::make('rw')
                                                ->label('RW')
                                                ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                            TextInput::make('kode_pos')
                                                ->label('Kode Pos')
                                                ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                        ]),
                                    Grid::make(4) // Dependent Selects for Indonesia
                                    ->visible(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value)
                                        ->schema([
                                            Select::make('provinsi_id')
                                                ->label('Provinsi')
                                                ->options(Provinsi::query()->pluck('nama', 'id'))
                                                ->live()
                                                ->afterStateUpdated(fn (Set $set) => $set('kota_id', null))
                                                ->searchable()
                                                ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                            Select::make('kota_id')
                                                ->label('Kota/Kabupaten')
                                                ->options(fn (Get $get): Collection => Kota::query()
                                                    ->where('provinsi_id', $get('provinsi_id'))
                                                    ->pluck('nama', 'id'))
                                                ->live()
                                                ->afterStateUpdated(fn (Set $set) => $set('kecamatan_id', null))
                                                ->searchable()
                                                ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                            Select::make('kecamatan_id')
                                                ->label('Kecamatan')
                                                ->options(fn (Get $get): Collection => Kecamatan::query()
                                                    ->where('kota_id', $get('kota_id'))
                                                    ->pluck('nama', 'id'))
                                                ->live()
                                                ->afterStateUpdated(fn (Set $set) => $set('kelurahan_id', null))
                                                ->searchable()
                                                ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                            Select::make('kelurahan_id')
                                                ->label('Kelurahan/Desa')
                                                ->options(fn (Get $get): Collection => Kelurahan::query()
                                                    ->where('kecamatan_id', $get('kecamatan_id'))
                                                    ->pluck('nama', 'id'))
                                                ->searchable()
                                                ->live()
                                                ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                        ]),
                                    // Alamat Non-Indonesia
                                    Grid::make(2)
                                        ->visible(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== Negara::INDONESIA->value)
                                        ->schema([
                                            Textarea::make('alamat') // Re-use 'alamat' for non-indo address
                                            ->label('Address')
                                                ->rows(3)
                                                ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== Negara::INDONESIA->value),
                                            TextInput::make('city')
                                                ->label('City')
                                                ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== Negara::INDONESIA->value),
                                            TextInput::make('state_province')
                                                ->label('State/Province')
                                                ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== Negara::INDONESIA->value),
                                            TextInput::make('kode_pos') // Re-use 'kode_pos'
                                            ->label('Postal Code')
                                                ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== Negara::INDONESIA->value),
                                        ]),
                                ]),

                            // --- Informasi Sambung ---
                            Section::make('Informasi Sambung')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('kelompok_sambung')
                                        ->label('Kelompok Sambung')
                                        ->required(),
                                    TextInput::make('desa_sambung')
                                        ->label('Desa Sambung')
                                        ->required(),
                                    Select::make('daerah_sambung_id')
                                        ->label('Daerah Sambung')
                                        ->options(Daerah::query()->pluck('nama', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->required(),
                                ]),
                        ]),
                    Wizard\Step::make('Informasi Lanjutan')
                        ->icon('heroicon-o-information-circle')
                        ->description('Detail pendidikan, dan kesehatan')
                        ->schema([
                            // --- Informasi Pondok & Pendidikan ---
                            Section::make('Informasi Pondok')
                                ->columns(2)
                                ->schema([
                                    Select::make('mulai_mengaji')
                                        ->label('Mulai Mengaji Sejak')
                                        ->options(MulaiMengaji::class)
                                        ->required(),
                                    Select::make('bahasa_makna')
                                        ->label('Bahasa Makna')
                                        ->options(BahasaMakna::class)
                                        ->required(),
                                    Toggle::make('status_mubaligh')
                                        ->label('Status Mubaligh?')
                                        ->live()
                                        ->helperText('Jika dicentang, Pernah Mondok akan otomatis tercentang.')
                                        ->afterStateUpdated(function (Set $set, $state) {
                                            if ($state === true) {
                                                $set('pernah_mondok', true);
                                            }
                                        }),
                                    Toggle::make('pernah_mondok')
                                        ->label('Pernah Mondok?')
                                        ->live()
                                        ->validationMessages([
                                            'accepted' => 'Jika Status Mubaligh dicentang, Pernah Mondok harus dicentang.',
                                        ])
                                        ->rules([
                                            fn (Get $get): \Closure => function (string $attribute, $value, \Closure $fail) use ($get) {
                                                if ($get('status_mubaligh') && !$value) {
                                                    $fail('Jika Status Mubaligh dicentang, Pernah Mondok harus dicentang.');
                                                }
                                            },
                                        ]),
                                    TextInput::make('nama_pondok_sebelumnya')
                                        ->label('Nama Pondok Sebelumnya')
                                        ->visible(fn (Get $get): bool => $get('pernah_mondok') === true)
                                        ->required(fn (Get $get): bool => $get('pernah_mondok') === true),
                                    TextInput::make('lama_mondok_sebelumnya')
                                        ->label('Lama Mondok Sebelumnya (Bulan)')
                                        ->numeric()
                                        ->visible(fn (Get $get): bool => $get('pernah_mondok') === true)
                                        ->required(fn (Get $get): bool => $get('pernah_mondok') === true),
                                ]),

                            Section::make('Informasi Pendidikan')
                                ->columns(2)
                                ->schema([
                                    Select::make('pendidikan_terakhir')
                                        ->label('Pendidikan Terakhir')
                                        ->options(PendidikanTerakhir::class)
                                        ->searchable()
                                        ->required(),
                                    TextInput::make('jurusan')
                                        ->label('Jurusan Pendidikan Terakhir'),
                                    TextInput::make('universitas')
                                        ->label('Universitas')
                                        ->required(), // Make conditional based on pendidikan?
                                    TextInput::make('program_studi')
                                        ->label('Program Studi')
                                        ->required(), // Make conditional based on pendidikan?
                                    TextInput::make('angkatan_kuliah')
                                        ->label('Angkatan Kuliah')
                                        ->numeric()
                                        ->required(), // Make conditional based on pendidikan?
                                    Select::make('status_kuliah')
                                        ->label('Status Kuliah')
                                        ->options(StatusKuliahCalonSantri::class)
                                        ->searchable()
                                        ->required(), // Make conditional based on pendidikan?
                                ]),

                            // --- Informasi Tambahan ---
                            Section::make('Informasi Tambahan')
                                ->columns(2)
                                ->schema([
                                    TagsInput::make('bahasa_harian')
                                        ->label('Bahasa Sehari-hari')
                                        ->required(),
                                    TagsInput::make('keahlian')
                                        ->label('Keahlian'),
                                    TagsInput::make('hobi')
                                        ->label('Hobi'),
                                    Select::make('sim')
                                        ->label('SIM yang Dimiliki')
                                        ->multiple()
                                        ->options(JenisSIM::class),
                                    TextInput::make('tinggi_badan')
                                        ->label('Tinggi Badan (cm)')
                                        ->numeric()
                                        ->required(),
                                    TextInput::make('berat_badan')
                                        ->label('Berat Badan (kg)')
                                        ->numeric()
                                        ->required(),
                                    Textarea::make('riwayat_sakit')
                                        ->label('Riwayat Sakit')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    Textarea::make('alergi')
                                        ->label('Alergi')
                                        ->rows(3)
                                        ->columnSpanFull(),
                                    Select::make('golongan_darah')
                                        ->label('Golongan Darah')
                                        ->options(GolonganDarah::class)
                                        ->required(),
                                    Select::make('ukuran_baju')
                                        ->label('Ukuran Baju')
                                        ->options(UkuranBaju::class)
                                        ->required(),
                                    Select::make('status_pernikahan')
                                        ->label('Status Pernikahan')
                                        ->options(StatusPernikahan::class)
                                        ->required(),
                                    Select::make('status_tinggal')
                                        ->label('Status Tinggal')
                                        ->options(StatusTinggal::class)
                                        ->required(),
                                    TextInput::make('anak_nomor')
                                        ->label('Anak ke-')
                                        ->numeric()
                                        ->required(),
                                    TextInput::make('jumlah_saudara')
                                        ->label('Jumlah Saudara')
                                        ->numeric()
                                        ->required(),
                                ]),
                        ]),
                    Wizard\Step::make('Informasi Keluarga')
                        ->icon('heroicon-o-users')
                        ->description('Data ayah, ibu, dan wali')
                        ->schema([
                            // --- Informasi Ayah ---
                            Section::make('Informasi Ayah')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('nama_ayah')
                                        ->label('Nama Ayah')
                                        ->required(),
                                    Select::make('status_ayah')
                                        ->label('Status Ayah')
                                        ->options(StatusOrangTua::class)
                                        ->live()
                                        ->required(),
                                    TextInput::make('nomor_telepon_ayah')
                                        ->label('Nomor Telepon Ayah (Whatsapp Diutamakan)')
                                        ->tel()
                                        ->visible(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('tempat_lahir_ayah')
                                        ->label('Tempat Lahir Ayah')
                                        ->visible(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    DatePicker::make('tanggal_lahir_ayah')
                                        ->label('Tanggal Lahir Ayah')
                                        ->visible(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('pekerjaan_ayah')
                                        ->label('Pekerjaan Ayah')
                                        ->visible(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('dapukan_ayah')
                                        ->label('Dapukan Ayah')
                                        ->visible(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    Textarea::make('alamat_ayah')
                                        ->label('Alamat Ayah')
                                        ->rows(3)
                                        ->columnSpanFull()
                                        ->visible(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('kelompok_sambung_ayah')
                                        ->label('Kelompok Sambung Ayah')
                                        ->visible(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('desa_sambung_ayah')
                                        ->label('Desa Sambung Ayah')
                                        ->visible(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    Select::make('daerah_sambung_ayah_id')
                                        ->label('Daerah Sambung Ayah')
                                        ->options(Daerah::query()->pluck('nama', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                ]),

                            // --- Informasi Ibu ---
                            Section::make('Informasi Ibu')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('nama_ibu')
                                        ->label('Nama Ibu')
                                        ->required(),
                                    Select::make('status_ibu')
                                        ->label('Status Ibu')
                                        ->options(StatusOrangTua::class)
                                        ->live()
                                        ->required(),
                                    TextInput::make('nomor_telepon_ibu')
                                        ->label('Nomor Telepon Ibu (Whatsapp Diutamakan)')
                                        ->tel()
                                        ->visible(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('tempat_lahir_ibu')
                                        ->label('Tempat Lahir Ibu')
                                        ->visible(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    DatePicker::make('tanggal_lahir_ibu')
                                        ->label('Tanggal Lahir Ibu')
                                        ->visible(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('pekerjaan_ibu')
                                        ->label('Pekerjaan Ibu')
                                        ->visible(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('dapukan_ibu')
                                        ->label('Dapukan Ibu')
                                        ->visible(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    Textarea::make('alamat_ibu')
                                        ->label('Alamat Ibu')
                                        ->rows(3)
                                        ->columnSpanFull()
                                        ->visible(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('kelompok_sambung_ibu')
                                        ->label('Kelompok Sambung Ibu')
                                        ->visible(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('desa_sambung_ibu')
                                        ->label('Desa Sambung Ibu')
                                        ->visible(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    Select::make('daerah_sambung_ibu_id')
                                        ->label('Daerah Sambung Ibu')
                                        ->options(Daerah::query()->pluck('nama', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                ]),

                            // --- Informasi Wali ---
                            Section::make('Informasi Wali')
                                ->columns(2)
                                ->schema([
                                    Select::make('hubungan_wali')
                                        ->label('Hubungan Wali')
                                        ->options(HubunganWali::class)
                                        ->default(HubunganWali::ORANGTUA->value)
                                        ->live()
                                        ->helperText('Jika memilih "Orang Tua", pastikan Status Ayah/Ibu "Hidup". Jika tidak, pilih hubungan lain.')
                                        ->required(),
                                    TextInput::make('nama_wali')
                                        ->label('Nama Wali')
                                        ->visible(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value)
                                        ->required(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    TextInput::make('nomor_telepon_wali')
                                        ->label('Nomor Telepon Wali (Whatsapp Diutamakan)')
                                        ->tel()
                                        ->visible(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value)
                                        ->required(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    TextInput::make('pekerjaan_wali')
                                        ->label('Pekerjaan Wali')
                                        ->visible(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value)
                                        ->required(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    TextInput::make('dapukan_wali')
                                        ->label('Dapukan Wali')
                                        ->visible(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    //->required(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value), // Dapukan wali optional?
                                    Textarea::make('alamat_wali')
                                        ->label('Alamat Wali')
                                        ->rows(3)
                                        ->columnSpanFull()
                                        ->visible(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value)
                                        ->required(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    TextInput::make('kelompok_sambung_wali')
                                        ->label('Kelompok Sambung Wali')
                                        ->visible(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    TextInput::make('desa_sambung_wali')
                                        ->label('Desa Sambung Wali')
                                        ->visible(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    Select::make('daerah_sambung_wali_id')
                                        ->label('Daerah Sambung Wali')
                                        ->options(Daerah::query()->pluck('nama', 'id'))
                                        ->searchable()
                                        ->preload()
                                        ->visible(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                ]),
                        ]),
                    Wizard\Step::make('Upload Dokumen')
                        ->icon('heroicon-o-document-arrow-up')
                        ->description('Unggah dokumen persyaratan')
                        ->schema([
                            Section::make('Upload Dokumen')
                                ->schema([
                                    // Adjust the repeater: Remove relationship(), modify default(), adapt schema()
                                    Repeater::make('dokumen')
                                        ->hiddenLabel()
                                        ->relationship('dokumen') // Remove this for standalone forms
                                        ->addable(false)
                                        ->deletable(false)
                                        ->reorderable(false)
                                        ->columnSpanFull()
                                        ->grid(1)
                                        ->required() // Repeater itself is required
                                        ->helperText('Pastikan semua dokumen yang diperlukan telah diunggah.')
                                        // Use the pre-filled data from mount()
                                        ->default(function(): ?array {
                                            // This is handled by form->fill() in mount now
                                            // If needed for dynamic updates, you might adjust logic here,
                                            // but initial load is via mount().
                                            return null;
                                        })
                                        ->schema([
                                            Hidden::make('dokumen_pendaftaran_id')->required(),

                                            Placeholder::make('nama_dokumen')
                                                ->label(function(Get $get): string {
                                                    $docId = $get('dokumen_pendaftaran_id');
                                                    // Access the list populated in mount()
                                                    return $this->requiredDokumenList[$docId]?->nama ?? 'Dokumen Tidak Dikenal';
                                                })
                                                ->content(function(Get $get): ?string {
                                                    $docId = $get('dokumen_pendaftaran_id');
                                                    // Access the list populated in mount()
                                                    return $this->requiredDokumenList[$docId]?->keterangan;
                                                }),

                                            SpatieMediaLibraryFileUpload::make('media') // State path MUST match repeater item key
                                                ->hiddenLabel()
                                                ->collection('dokumen_calon_santri_berkas') // Collection on DokumenCalonSantri
                                                ->required()
                                                ->maxSize(5120) // 5MB
                                                ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png', 'image/jpg'])
                                                ->openable()
                                                ->downloadable()
                                                ->hintAction(
                                                    Action::make('download_template_repeater')
                                                        ->label('Unduh Template')
                                                        ->icon('heroicon-o-arrow-down-tray')
                                                        ->url(function(Get $get): ?string {
                                                            $docId = $get('dokumen_pendaftaran_id');
                                                            // Need to query the DokumenPendaftaran model again here
                                                            // This requires DokumenPendaftaran model to have media relationship configured
                                                            $dokumen = DokumenPendaftaran::find($docId);
                                                            // Ensure DokumenPendaftaran uses Spatie Media Library and has this collection
                                                            return $dokumen?->getFirstMediaUrl('dokumen_pendaftaran_template');
                                                        })
                                                        ->openUrlInNewTab()
                                                        ->color('gray')
                                                        ->visible(function(Get $get): bool {
                                                            $docId = $get('dokumen_pendaftaran_id');
                                                            $dokumen = DokumenPendaftaran::find($docId);
                                                            // Check if the DokumenPendaftaran model itself has a template file
                                                            return $dokumen && $dokumen->hasMedia('dokumen_pendaftaran_template');
                                                        })
                                                        ->tooltip('Unduh template jika diperlukan')
                                                )
                                                ->columnSpan(1),
                                        ])
                                        ->key('repeater_dokumen'),
                                ]),
                        ]),
                ])
                    ->columnSpanFull()
                    ->submitAction(new HtmlString(Blade::render(<<<BLADE
                        <x-filament::button
                            type="submit"
                            size="sm"
                        >
                            Submit
                        </x-filament::button>
                    BLADE))), // Make wizard take full width
                // --- End Schema Adaptation ---
            ])
            ->statePath('data')
            ->model(CalonSantri::class);
    }


    protected function getFormStatePath(): string
    {
        // Tells Filament where to store the form data in the component
        return 'data';
    }

    public function create(): void
    {
        if (!$this->pendaftaranDibuka || !$this->activeGelombang) {
            Notification::make()
                ->title('Pendaftaran Tidak Dibuka')
                ->body('Saat ini tidak ada gelombang pendaftaran yang aktif.')
                ->danger()
                ->send();
            return;
        }


        DB::beginTransaction();
        try {
            $calonSantri = CalonSantri::create($this->form->getState());
            $this->form->model($calonSantri)->saveRelationships();

            DB::commit();

            Notification::make()
                ->title('Pendaftaran Berhasil')
                ->body('Data calon santri telah berhasil dikirim.')
                ->success()
                ->send();

            $this->redirect('/selesai/'.$calonSantri->id);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating Calon Santri: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            Notification::make()
                ->title('Pendaftaran Gagal')
                ->body('Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()) // Show generic or specific error
                ->danger()
                ->send();
        }
    }


    public function render(): View
    {
        // Pass the flag to the view
        return view('livewire.pendaftaran.create', [
            'pendaftaranDibuka' => $this->pendaftaranDibuka,
        ]);
    }
}
