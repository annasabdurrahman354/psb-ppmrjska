<?php

namespace App\Filament\Pages\Auth;

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
use App\Enums\UserRole;
use App\Enums\UsersStatus;
use App\Models\BiodataSantri;
use App\Models\Daerah;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Kota;
use App\Models\Provinsi;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Http\Responses\Auth\RegistrationResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\Register as BaseRegister;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;

class RegisterSantri extends BaseRegister
{
    protected static string $view = 'filament.pages.auth.register';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    Wizard\Step::make('Akun Pengguna')
                        ->icon('heroicon-o-key')
                        ->description('Informasi untuk login sistem.')
                        ->schema([
                            TextInput::make('nama') // For User model
                                ->label('Nama')
                                ->required()
                                ->maxLength(255),
                            TextInput::make('nama_panggilan') // For User model
                                ->label('Nama Panggilan')
                                ->required()
                                ->maxLength(255),
                            ToggleButtons::make('jenis_kelamin') // For User model
                                ->label('Jenis Kelamin')
                                ->options(JenisKelamin::class)
                                ->required()
                                ->inline(),
                            TextInput::make('nomor_telepon') // For User model
                                ->label('Nomor Telepon (WhatsApp Aktif)')
                                ->tel()
                                ->helperText('Awali dengan 0, contoh: 081234567890')
                                ->required()
                                ->rules(['regex:/^0[0-9]{8,14}$/'])
                                ->unique(table: User::class, column: 'nomor_telepon', ignoreRecord: true)
                                ->validationMessages(['regex' => 'Nomor telepon harus valid (diawali 0, 8-15 digit).']),
                            $this->getEmailFormComponent(),
                            $this->getPasswordFormComponent(),
                            $this->getPasswordConfirmationFormComponent(),
                        ]),

                    Wizard\Step::make('Data Diri & Alamat')
                        ->icon('heroicon-o-user-circle')
                        ->description('Detail identitas dan alamat santri.')
                        ->schema([
                            Section::make('Data Diri')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('nomor_induk_santri')
                                        ->label('Nomor Induk Santri (NIS)')
                                        ->helperText('Jika belum ada, akan dibuatkan oleh sistem setelah diterima.')
                                        ->numeric()
                                        ->unique(table: BiodataSantri::class, column: 'nomor_induk_santri', ignoreRecord: true),
                                    TextInput::make('tahun_pendaftaran')
                                        ->label('Tahun Pendaftaran')
                                        ->numeric()
                                        ->minValue(2000)
                                        ->maxValue(now()->year + 1)
                                        ->required(),
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
                                        ->label('Nomor Induk Kependudukan (NIK)')
                                        ->helperText('Sesuai KTP atau Kartu Keluarga.')
                                        ->numeric()
                                        ->length(16)
                                        ->unique(table: BiodataSantri::class, column: 'nomor_induk_kependudukan', ignoreRecord: true)
                                        ->visible(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value)
                                        ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                    TextInput::make('nomor_kartu_keluarga')
                                        ->label('No. Kartu Keluarga')
                                        ->numeric()
                                        ->visible(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value)
                                        ->required(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value),
                                    TextInput::make('nomor_passport')
                                        ->label('No. Passport')
                                        ->unique(table: BiodataSantri::class, column: 'nomor_passport', ignoreRecord: true)
                                        ->visible(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== Negara::INDONESIA->value)
                                        ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== Negara::INDONESIA->value),
                                ]),
                            Section::make('Alamat')
                                ->visible(fn (Get $get): bool => $get('kewarganegaraan') != null)
                                ->schema([
                                    Textarea::make('alamat') // Shared field name
                                    ->label(fn (Get $get) => $get('kewarganegaraan') === Negara::INDONESIA->value ? 'Alamat Lengkap' : 'Full Address')
                                        ->rows(3)
                                        ->required(),
                                    Grid::make(2) // For RT/RW and Kode Pos (Indonesia)
                                    ->visible(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value)
                                        ->schema([
                                            TextInput::make('rt')
                                                ->label('RT')->numeric()->maxLength(3)->required(),
                                            TextInput::make('rw')
                                                ->label('RW')->numeric()->maxLength(3)->required(),
                                        ]),
                                    TextInput::make('kode_pos') // Shared field name
                                    ->label(fn (Get $get) => $get('kewarganegaraan') === Negara::INDONESIA->value ? 'Kode Pos' : 'Postal Code')
                                        ->numeric()
                                        ->required(),
                                    Grid::make(2) // For Provinsi/Kota (Indonesia)
                                    ->visible(fn (Get $get): bool => $get('kewarganegaraan') === Negara::INDONESIA->value)
                                        ->schema([
                                            Select::make('provinsi_id')
                                                ->label('Provinsi')
                                                ->options(Provinsi::query()->pluck('nama', 'id'))
                                                ->live()
                                                ->afterStateUpdated(fn (Set $set) => $set('kota_id', null))
                                                ->searchable()
                                                ->required(),
                                            Select::make('kota_id')
                                                ->label('Kota/Kabupaten')
                                                ->options(fn (Get $get): Collection => Kota::query()
                                                    ->where('provinsi_id', $get('provinsi_id'))
                                                    ->pluck('nama', 'id'))
                                                ->live()
                                                ->afterStateUpdated(fn (Set $set) => $set('kecamatan_id', null))
                                                ->searchable()
                                                ->required(),
                                            Select::make('kecamatan_id')
                                                ->label('Kecamatan')
                                                ->options(fn (Get $get): Collection => Kecamatan::query()
                                                    ->where('kota_id', $get('kota_id'))
                                                    ->pluck('nama', 'id'))
                                                ->live()
                                                ->afterStateUpdated(fn (Set $set) => $set('kelurahan_id', null))
                                                ->searchable()
                                                ->required(),
                                            Select::make('kelurahan_id')
                                                ->label('Kelurahan/Desa')
                                                ->options(fn (Get $get): Collection => Kelurahan::query()
                                                    ->where('kecamatan_id', $get('kecamatan_id'))
                                                    ->pluck('nama', 'id'))
                                                ->searchable()
                                                ->live()
                                                ->required(),
                                        ]),
                                    Grid::make(1) // For City, State/Province (Non-Indonesia)
                                    ->visible(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== Negara::INDONESIA->value)
                                        ->schema([
                                            TextInput::make('city')
                                                ->label('City')
                                                ->required(),
                                            TextInput::make('state_province')
                                                ->label('State/Province')
                                                ->required(),
                                        ]),
                                ]),
                            Section::make('Informasi Sambung')
                                ->columns(3)
                                ->schema([
                                    TextInput::make('kelompok_sambung')->label('Kelompok')->required(),
                                    TextInput::make('desa_sambung')->label('Desa')->required(),
                                    Select::make('daerah_sambung_id')
                                        ->label('Daerah')
                                        ->searchable()
                                        ->getSearchResultsUsing(fn (string $search): array => Daerah::where('nama', 'like', "%{$search}%")->limit(2)->pluck('nama', 'id')->toArray())
                                        ->getOptionLabelUsing(fn ($value): ?string => Daerah::find($value)?->nama)
                                        ->required(),
                                ]),
                        ]),

                    Wizard\Step::make('Informasi Tambahan')
                        ->icon('heroicon-o-sparkles')
                        ->description('Pendidikan, keahlian, dan kesehatan.')
                        ->schema([
                            Grid::make(2)->schema([
                                Section::make('Pendidikan & Keagamaan')
                                    ->columnSpan(1)
                                    ->schema([
                                        Select::make('mulai_mengaji')
                                            ->label('Mulai Mengaji')
                                            ->options(MulaiMengaji::class)
                                            ->required(),
                                        Select::make('bahasa_makna')
                                            ->label('Bahasa Makna')
                                            ->options(BahasaMakna::class)
                                            ->required(),
                                        Toggle::make('status_mubaligh')
                                            ->label('Status Mubaligh')
                                            ->default(false)
                                            ->disabled(),
                                        Toggle::make('pernah_mondok')
                                            ->label('Pernah mondok/pesantren?')
                                            ->live(),
                                        TextInput::make('nama_pondok_sebelumnya')
                                            ->label('Nama Pondok/Pesantren Sebelumnya')
                                            ->visible(fn (Get $get) => $get('pernah_mondok') === true)
                                            ->requiredIf('pernah_mondok', true),
                                        TextInput::make('lama_mondok_sebelumnya')
                                            ->label('Lama Mondok (Bulan)')
                                            ->numeric()
                                            ->visible(fn (Get $get) => $get('pernah_mondok') === true)
                                            ->requiredIf('pernah_mondok', true),
                                        Select::make('pendidikan_terakhir')
                                            ->label('Pendidikan Terakhir')
                                            ->options(PendidikanTerakhir::class)
                                            ->required(),
                                        TextInput::make('jurusan')
                                            ->label('Jurusan (Pendidikan Terakhir)'),
                                        TextInput::make('program_studi')
                                            ->label('Program Studi'),
                                        TextInput::make('universitas')
                                            ->label('Universitas'),
                                        TextInput::make('angkatan_kuliah')
                                            ->label('Angkatan Kuliah')
                                            ->numeric(),
                                        Select::make('status_kuliah')
                                            ->label('Status Kuliah')
                                            ->options(StatusKuliah::class)
                                            ->searchable(),
                                        DatePicker::make('tanggal_lulus_kuliah')
                                            ->label('Tanggal Lulus Kuliah (Jika Sudah)'),
                                    ]),
                                Section::make('Keterampilan & Kesehatan')
                                    ->columnSpan(1)
                                    ->schema([
                                        TagsInput::make('bahasa_harian')
                                            ->label('Bahasa Sehari-hari')
                                            ->required(),
                                        TagsInput::make('keahlian')
                                            ->label('Keahlian/Keterampilan'),
                                        TagsInput::make('hobi')
                                            ->label('Hobi'),
                                        Select::make('sim')
                                            ->label('SIM yang Dimiliki')
                                            ->multiple()
                                            ->options(JenisSIM::class),
                                        TextInput::make('tinggi_badan')
                                            ->numeric()
                                            ->suffix('cm')
                                            ->required(),
                                        TextInput::make('berat_badan')
                                            ->numeric()
                                            ->suffix('kg')
                                            ->required(),
                                        Select::make('golongan_darah')
                                            ->options(GolonganDarah::class)
                                            ->required(),
                                        Select::make('ukuran_baju')
                                            ->options(UkuranBaju::class)
                                            ->required(),
                                        Select::make('status_pernikahan')
                                            ->options(StatusPernikahan::class)
                                            ->required(),
                                        Select::make('status_tinggal')
                                            ->options(StatusTinggal::class)
                                            ->required(),
                                        TextInput::make('anak_nomor')
                                            ->label('Anak ke-')
                                            ->numeric()
                                            ->required(),
                                        TextInput::make('jumlah_saudara')
                                            ->label('Dari berapa bersaudara')
                                            ->numeric()
                                            ->required(),
                                        Textarea::make('riwayat_sakit')
                                            ->label('Riwayat Sakit (yang perlu perhatian khusus)')
                                            ->rows(2),
                                        Textarea::make('alergi')
                                            ->label('Alergi (Makanan/Obat)')
                                            ->rows(2),
                                    ]),
                            ]),
                        ]),
                    Wizard\Step::make('Informasi Keluarga')
                        ->icon('heroicon-o-users')
                        ->description('Data orang tua dan wali santri.')
                        ->schema([
                            Section::make('Informasi Ayah')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('nama_ayah')
                                        ->label('Nama')
                                        ->required(),
                                    Select::make('status_ayah')
                                        ->label('Status')
                                        ->options(StatusOrangTua::class)
                                        ->live()
                                        ->required(),
                                    TextInput::make('nomor_telepon_ayah')
                                        ->label('Nomor Telepon')
                                        ->tel()
                                        ->visible(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('tempat_lahir_ayah')
                                        ->label('Tempay Lahir')
                                        ->visible(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    DatePicker::make('tanggal_lahir_ayah')
                                        ->label('Tanggal Lahir')
                                        ->visible(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('pekerjaan_ayah')
                                        ->label('Pekerjaan')
                                        ->visible(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('dapukan_ayah')
                                        ->label('Dapukan Ayah (Jika Ada)')
                                        ->visible(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    Textarea::make('alamat_ayah')
                                        ->label('Alamat')
                                        ->columnSpanFull()
                                        ->visible(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('kelompok_sambung_ayah')
                                        ->label('Kelompok Sambung')
                                        ->visible(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('desa_sambung_ayah')
                                        ->label('Desa Sambung')
                                        ->visible(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                    Select::make('daerah_sambung_ayah_id')
                                        ->label('Daerah Sambung')
                                        ->searchable()
                                        ->getSearchResultsUsing(fn (string $search): array => Daerah::where('nama', 'like', "%{$search}%")->limit(2)->pluck('nama', 'id')->toArray())
                                        ->getOptionLabelUsing(fn ($value): ?string => Daerah::find($value)?->nama)
                                        ->visible(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ayah') === StatusOrangTua::HIDUP->value),
                                ]),
                            Section::make('Informasi Ibu')
                                ->columns(2)
                                ->schema([
                                    TextInput::make('nama_ibu')
                                        ->label('Nama')
                                        ->required(),
                                    Select::make('status_ibu')
                                        ->label('Status')
                                        ->options(StatusOrangTua::class)
                                        ->live()
                                        ->required(),
                                    TextInput::make('nomor_telepon_ibu')
                                        ->label('Nomor Telepon')
                                        ->tel()
                                        ->visible(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('tempat_lahir_ibu')
                                        ->label('Tempat Lahir')
                                        ->visible(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    DatePicker::make('tanggal_lahir_ibu')
                                        ->label('Tanggal Lahir')
                                        ->visible(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('pekerjaan_ibu')
                                        ->label('Pekerjaan')
                                        ->visible(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('dapukan_ibu')
                                        ->label('Dapukan Ibu (Jika Ada)')
                                        ->visible(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    Textarea::make('alamat_ibu')
                                        ->label('Alamat')
                                        ->columnSpanFull()
                                        ->visible(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('kelompok_sambung_ibu')
                                        ->label('Kelompok Sambung')
                                        ->visible(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    TextInput::make('desa_sambung_ibu')
                                        ->label('Desa Sambung')
                                        ->visible(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                    Select::make('daerah_sambung_ibu_id')
                                        ->label('Daerah Sambung')
                                        ->getSearchResultsUsing(fn (string $search): array => Daerah::where('nama', 'like', "%{$search}%")->limit(2)->pluck('nama', 'id')->toArray())
                                        ->getOptionLabelUsing(fn ($value): ?string => Daerah::find($value)?->nama)
                                        ->visible(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value)
                                        ->required(fn (Get $get) => $get('status_ibu') === StatusOrangTua::HIDUP->value),
                                ]),
                            Section::make('Informasi Wali (Jika berbeda dengan Ayah/Ibu)')
                                ->columns(2)
                                ->schema([
                                    Select::make('hubungan_wali')
                                        ->label('Hubungan Wali')
                                        ->options(HubunganWali::class)
                                        ->live()
                                        ->default(HubunganWali::ORANGTUA->value) // Default to Orang Tua
                                        ->helperText('Pilih "Orang Tua" jika wali adalah ayah/ibu yang masih hidup.')
                                        ->required(),
                                    TextInput::make('nama_wali')
                                        ->label('Nama')
                                        ->visible(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value)
                                        ->required(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    TextInput::make('nomor_telepon_wali')
                                        ->label('Nomor Telepon')
                                        ->tel()
                                        ->visible(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value)
                                        ->required(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    TextInput::make('pekerjaan_wali')
                                        ->label('Pekerjaan')
                                        ->visible(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value)
                                        ->required(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    TextInput::make('dapukan_wali')
                                        ->label('Dapukan Wali (Jika Ada)')
                                        ->visible(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    Textarea::make('alamat_wali')
                                        ->label('Alamat')
                                        ->columnSpanFull()
                                        ->visible(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value)
                                        ->required(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    TextInput::make('kelompok_sambung_wali')
                                        ->label('Kelompok Sambung')
                                        ->visible(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    TextInput::make('desa_sambung_wali')
                                        ->label('Desa Sambung')
                                        ->visible(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                    Select::make('daerah_sambung_wali_id')
                                        ->label('Daerah Sambung')
                                        ->getSearchResultsUsing(fn (string $search): array => Daerah::where('nama', 'like', "%{$search}%")->limit(2)->pluck('nama', 'id')->toArray())
                                        ->getOptionLabelUsing(fn ($value): ?string => Daerah::find($value)?->nama)
                                        ->visible(fn (Get $get): bool => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
                                ]),
                        ]),
                ])
                ->columnSpanFull()
                ->submitAction(new HtmlString(Blade::render(<<<BLADE
                    <x-filament::button type="submit" size="sm">
                        Daftarkan Santri
                    </x-filament::button>
                BLADE)))
            ])
            ->statePath('data');
    }

    public function register(): ?\Filament\Http\Responses\Auth\Contracts\RegistrationResponse
    {
        $formData = $this->form->getState();

        DB::beginTransaction();
        try {
            // Create User with user-specific fields
            $user = User::create([
                'name' => $formData['nama'],
                'email' => $formData['email'],
                'password' => Hash::make($formData['password']),
                'role' => UserRole::SANTRI,
                'nama_panggilan' => $formData['nama_panggilan'],
                'jenis_kelamin' => $formData['jenis_kelamin'],
                'nomor_telepon' => $formData['nomor_telepon'],
                'status' => UsersStatus::AKTIF, // Default status for new user
                'email_verified_at' => now(),
            ]);

            // Define user-specific fields to exclude from biodata
            $userFields = [
                'nama', 'email', 'password', 'nama_panggilan',
                'jenis_kelamin', 'nomor_telepon'
            ];

            // Extract biodata fields (everything except user fields)
            $biodataSantriData = collect($formData)
                ->except($userFields)
                ->toArray();

            // Add required biodata fields
            $biodataSantriData['user_id'] = $user->id;
            $biodataSantriData['status_mubaligh'] = $biodataSantriData['status_mubaligh'] ?? false;
            $biodataSantriData['tanggal_lulus'] = null;
            $biodataSantriData['tanggal_keluar'] = null;
            $biodataSantriData['alasan_keluar'] = null;

            // Clean up array fields that might be null from TagsInput
            $arrayFields = ['bahasa_harian', 'keahlian', 'hobi', 'sim'];
            foreach ($arrayFields as $field) {
                if (!isset($biodataSantriData[$field]) || !is_array($biodataSantriData[$field])) {
                    $biodataSantriData[$field] = [];
                }
            }

            BiodataSantri::create($biodataSantriData);

            DB::commit();

            event(new Registered($user));
            Filament::auth()->login($user);

            session()->regenerate();

            return app(RegistrationResponse::class);
        } catch (\Throwable $exception) {
            DB::rollBack();
            Log::error('Santri Registration Error: ' . $exception->getMessage() . "\n" . $exception->getTraceAsString());
            Notification::make()
                ->title('Registrasi Gagal')
                ->body('Terjadi kesalahan saat menyimpan data. Silakan coba lagi. Details: ' . $exception->getMessage())
                ->danger()
                ->send();

            if ($exception instanceof \Illuminate\Database\QueryException && $exception->errorInfo[1] == 1062) { // 1062 is MySQL error code for duplicate entry
                if (str_contains($exception->getMessage(), 'biodata_santri_nomor_induk_santri_unique')) {
                    $this->addError('data.nomor_induk_santri', 'Nomor Induk Santri sudah terdaftar.');
                } else {
                    // Generic duplicate error
                    $this->addError('form', 'Data yang Anda masukkan sudah ada yang terdaftar.');
                }
            }

            return null;
        }
    }
}
