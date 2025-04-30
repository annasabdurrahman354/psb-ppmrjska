<?php

namespace App\Filament\Resources;

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
use App\Filament\Resources\CalonSantriResource\Pages;
use App\Filament\Resources\CalonSantriResource\RelationManagers; // If you have relation managers
use App\Models\CalonSantri;
use App\Models\Daerah;
use App\Models\DokumenPendaftaran;
use App\Models\GelombangPendaftaran;
use App\Models\Pendaftaran;
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
use Filament\Infolists;
use Filament\Infolists\Components\Group as InfolistGroup;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Section as InfolistSection;
use Filament\Infolists\Components\Split as InfolistSplit;
use Filament\Infolists\Components\SpatieMediaLibraryImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Split as TableSplit;
use Filament\Tables\Columns\Layout\Stack as TableStack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TagsInput;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CalonSantriResource extends Resource
{
    protected static ?string $model = CalonSantri::class;
    protected static ?string $slug = 'calon-santri';
    protected static ?string $navigationIcon = 'heroicon-o-user-plus';
    protected static ?string $navigationGroup = 'Pendaftaran';
    protected static ?string $navigationLabel = 'Calon Santri';
    protected static ?string $pluralModelLabel = 'Calon Santri';
    protected static ?string $modelLabel = 'Calon Santri';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pendaftaran')
                ->columns(2)
                ->schema([
                    Select::make('pendaftaran_id') // Field baru untuk memilih Pendaftaran (tahun)
                        ->label('Tahun Pendaftaran')
                        ->options(
                            // Ambil semua pendaftaran, jadikan tahun sebagai label
                            Pendaftaran::query()
                                ->orderBy('tahun', 'desc') // Urutkan dari terbaru
                                ->pluck('tahun', 'id')
                                ->mapWithKeys(fn ($tahun, $id) => [$id => $tahun]) // Format label
                        )
                        ->searchable()
                        ->preload() // Load opsi saat halaman dimuat
                        ->live() // Penting agar field Gelombang bereaksi
                        ->afterStateUpdated(fn (Set $set) => $set('gelombang_pendaftaran_id', null)) // Reset Gelombang jika Pendaftaran berubah
                        ->required(fn (string $operation): bool => $operation === 'create')
                        // Disable di halaman edit/view
                        ->disabled(fn (string $operation): bool => $operation !== 'create')
                        // Jangan simpan field ini ke database CalonSantri
                        ->dehydrated(false)
                        // Saat load (edit/view), default value diambil dari relasi record
                        ->default(function (Get $get, $record) {
                            // Coba ambil pendaftaran_id dari record yang ada (edit/view)
                            if ($record instanceof CalonSantri && $record->gelombangPendaftaran) {
                                return $record->gelombangPendaftaran->pendaftaran_id;
                            }
                            // Jika create atau tidak ada relasi, biarkan kosong
                            return null;
                        }),

                    Select::make('gelombang_pendaftaran_id')
                        ->label('Gelombang Pendaftaran')
                        ->options(function (Get $get): Collection { // Gunakan closure untuk opsi dinamis
                            $pendaftaranId = $get('pendaftaran_id'); // Ambil ID pendaftaran yang dipilih
                            if (!$pendaftaranId) {
                                return collect(); // Kembalikan koleksi kosong jika belum ada pendaftaran dipilih
                            }
                            // Query Gelombang berdasarkan pendaftaran_id yang dipilih
                            return GelombangPendaftaran::query()
                                ->where('pendaftaran_id', $pendaftaranId)
                                ->orderBy('nomor_gelombang') // Urutkan berdasarkan nomor gelombang
                                ->pluck('nomor_gelombang', 'id') // Ambil nomor gelombang dan id
                                ->mapWithKeys(fn ($nomor, $id) => [$id => 'Gelombang ' . $nomor]); // Format label
                        })
                        ->searchable()
                        ->preload()
                        ->live() // Penting agar section dokumen bereaksi
                        ->required()
                        ->disabled(fn (Get $get): bool => !$get('pendaftaran_id'))
                        ->afterStateUpdated(function (Set $set, ?string $state): void {
                            // Hitung ulang default items untuk repeater dokumen
                            $defaultItems = [];
                            if ($state) { // Jika ada gelombang dipilih
                                $gelombang = GelombangPendaftaran::find($state);
                                if ($gelombang && $gelombang->pendaftaran_id) {
                                    $requiredDokumen = DokumenPendaftaran::where('pendaftaran_id', $gelombang->pendaftaran_id)->get();
                                    foreach ($requiredDokumen as $dokumen) {
                                        $defaultItems[] = [
                                            'dokumen_pendaftaran_id' => $dokumen->id,
                                            'media' => null, // Pastikan ada key untuk field Spatie
                                        ];
                                    }
                                }
                            }
                            // Set ulang state repeater 'dokumen' dengan item baru
                            // Ini akan mengganti item yang ada di repeater saat create/edit
                            $set('dokumen', $defaultItems);
                        })
                        ->key('gelombang_select'), // Beri key unik jika diperlukan untuk reset/update

                ]),

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
                            ->required(),
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
                            ->options(Negara::class) // Panggil fungsi global Anda
                            ->default(Negara::INDONESIA->value)
                            ->searchable()
                            ->live() // <-- Penting untuk logika kondisional
                            ->required(),
                        TextInput::make('nomor_induk_kependudukan')
                            ->label('Nomor Induk Kependudukan')
                            ->length(16)
                            ->visible(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia') // Tampil jika Indonesia
                            ->required(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia'), // Wajib jika Indonesia
                        TextInput::make('nomor_kartu_keluarga')
                            ->label('No. Kartu Keluarga')
                            ->visible(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia') // Tampil jika Indonesia
                            ->required(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia'), // Wajib jika Indonesia
                        TextInput::make('nomor_passport')
                            ->label('No. Passport')
                            ->visible(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== 'Indonesia') // Tampil jika bukan Indonesia
                            ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== 'Indonesia'), // Wajib jika bukan Indonesia
                    ]),

                Section::make('Alamat')
                    ->schema([
                        Textarea::make('alamat')
                            ->label('Alamat Lengkap (Indonesia)')
                            ->rows(3)
                            ->visible(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia')
                            ->required(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia'),
                        Grid::make(4)
                            ->visible(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia')
                            ->schema([
                                TextInput::make('rt')
                                    ->label('RT')
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia'),
                                TextInput::make('rw')
                                    ->label('RW')
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia'),
                                TextInput::make('kode_pos')
                                    ->label('Kode Pos')
                                    ->required(),
                            ]),
                        Grid::make(4) // Dependent Selects for Indonesia
                            ->visible(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia')
                            ->schema([
                                Select::make('provinsi_id')
                                    ->label('Provinsi')
                                    ->options(Provinsi::query()->pluck('nama', 'id'))
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('kota_id', null))
                                    ->searchable()
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia'),
                                Select::make('kota_id')
                                    ->label('Kota/Kabupaten')
                                    ->options(fn (Get $get): Collection => Kota::query()
                                        ->where('provinsi_id', $get('provinsi_id'))
                                        ->pluck('nama', 'id'))
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('kecamatan_id', null))
                                    ->searchable()
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia'),
                                Select::make('kecamatan_id')
                                    ->label('Kecamatan')
                                    ->options(fn (Get $get): Collection => Kecamatan::query()
                                        ->where('kota_id', $get('kota_id'))
                                        ->pluck('nama', 'id'))
                                    ->live()
                                    ->afterStateUpdated(fn (Set $set) => $set('kelurahan_id', null))
                                    ->searchable()
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia'),
                                Select::make('kelurahan_id')
                                    ->label('Kelurahan/Desa')
                                    ->options(fn (Get $get): Collection => Kelurahan::query()
                                        ->where('kecamatan_id', $get('kecamatan_id'))
                                        ->pluck('nama', 'id'))
                                    ->searchable()
                                    ->live()
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') === 'Indonesia'),
                            ]),
                        Grid::make(2) // Fields for Non-Indonesia
                            ->visible(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== 'Indonesia')
                            ->schema([
                                Textarea::make('alamat')
                                    ->label('Address')
                                    ->rows(3)
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== 'Indonesia'),
                                TextInput::make('city')
                                    ->label('City')
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== 'Indonesia'),
                                TextInput::make('state_province')
                                    ->label('State/Province')
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== 'Indonesia'),
                                TextInput::make('kode_pos') // Kode Pos bisa relevan untuk non-indo juga
                                    ->label('Postal Code')
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== 'Indonesia'),
                            ]),
                    ]),

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
                            ->options(Daerah::query()->pluck('nama', 'id')) // Atau relasi jika ada
                            ->searchable()
                            ->preload()
                            ->required(),
                    ]),

                Section::make('Informasi Pondok & Pendidikan')
                    ->columns(2)
                    ->schema([
                        Toggle::make('status_mubaligh')
                            ->label('Status Mubaligh?')
                            ->live() // <-- Agar pernah_mondok bisa bereaksi
                            ->helperText('Jika dicentang, Pernah Mondok akan otomatis tercentang.')
                            ->afterStateUpdated(function (Set $set, $state) { // Opsi: Auto-check pernah_mondok
                                 if ($state === true) {
                                     $set('pernah_mondok', true);
                                 }
                            }),
                        Toggle::make('pernah_mondok')
                            ->label('Pernah Mondok?')
                            ->live()
                            ->validationMessages([ // Custom validation message
                                'accepted' => 'Jika Status Mubaligh dicentang, Pernah Mondok harus dicentang.',
                            ])
                            ->rules([ // Rule untuk memastikan pernah_mondok true jika status_mubaligh true
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
                        Select::make('pendidikan_terakhir')
                            ->label('Pendidikan Terakhir')
                            ->options(PendidikanTerakhir::class)
                            ->searchable()
                            ->required(),
                        TextInput::make('jurusan')
                            ->label('Jurusan'),
                        TextInput::make('universitas')
                            ->label('Universitas')
                            ->required(),
                        TextInput::make('program_studi')
                            ->label('Program Studi')
                            ->required(),
                        TextInput::make('angkatan_kuliah')
                            ->label('Angkatan Kuliah')
                            ->numeric()
                            ->required(),
                        Select::make('status_kuliah')
                            ->label('Status Kuliah')
                            ->options(StatusKuliah::class)
                            ->searchable()
                            ->required(),
                    ]),

                Section::make('Informasi Tambahan')
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
                            ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        TextInput::make('tempat_lahir_ayah')
                            ->label('Tempat Lahir Ayah')
                            ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        DatePicker::make('tanggal_lahir_ayah')
                            ->label('Tanggal Lahir Ayah')
                            ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        TextInput::make('pekerjaan_ayah')
                            ->label('Pekerjaan Ayah')
                            ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        TextInput::make('dapukan_ayah')
                            ->label('Dapukan Ayah')
                            ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        Textarea::make('alamat_ayah')
                            ->label('Alamat Ayah')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        TextInput::make('kelompok_sambung_ayah')
                            ->label('Kelompok Sambung Ayah')
                            ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        TextInput::make('desa_sambung_ayah')
                            ->label('Desa Sambung Ayah')
                            ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        Select::make('daerah_sambung_ayah_id')
                            ->label('Daerah Sambung Ayah')
                            ->options(Daerah::query()->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => $get('status_ayah') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                    ]),

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
                            ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        TextInput::make('tempat_lahir_ibu')
                            ->label('Tempat Lahir Ibu')
                            ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        DatePicker::make('tanggal_lahir_ibu')
                            ->label('Tanggal Lahir Ibu')
                            ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        TextInput::make('pekerjaan_ibu')
                            ->label('Pekerjaan Ibu')
                            ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        TextInput::make('dapukan_ibu')
                            ->label('Dapukan Ibu')
                            ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        Textarea::make('alamat_ibu')
                            ->label('Alamat Ibu')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        TextInput::make('kelompok_sambung_ibu')
                            ->label('Kelompok Sambung Ibu')
                            ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        TextInput::make('desa_sambung_ibu')
                            ->label('Desa Sambung Ibu')
                            ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                        Select::make('daerah_sambung_ibu_id')
                            ->label('Daerah Sambung Ibu')
                            ->options(Daerah::query()->pluck('nama', 'id'))
                            ->searchable()
                            ->preload()
                            ->required(fn (Get $get): bool => $get('status_ibu') === StatusOrangTua::HIDUP->value), // Wajib jika hidup
                    ]),

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
                            ->visible(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value)
                            ->required(fn(Get $get) => $get('hubungan_wali') !== HubunganWali::ORANGTUA->value),
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

                Section::make('Upload Dokumen')
                    ->columns(2)
                    ->schema([
                        Repeater::make('dokumen') // Nama relasi HasMany di CalonSantri
                            ->hiddenLabel()
                            ->relationship('dokumen') // Menggunakan relasi 'dokumen()'
                            ->addable(false) // User tidak bisa menambah item
                            ->deletable(false) // User tidak bisa menghapus item
                            ->reorderable(false) // User tidak bisa mengubah urutan
                            ->columnSpanFull()
                            ->grid(1) // Layout 2 kolom untuk setiap item dokumen
                            ->required() // Repeater secara keseluruhan wajib (minimal harus ada item default)
                            ->helperText('Pastikan semua dokumen yang diperlukan telah diunggah.')
                            ->default(function(Get $get, string $operation, ?Model $record): ?array {
                                // Hanya isi default saat CREATE dan belum ada data relasi
                                if ($operation !== 'create' || ($record instanceof CalonSantri && $record->dokumen()->exists())) {
                                    return null; // Biarkan Filament load dari relasi jika Edit/View
                                }

                                $gelombangId = $get('gelombang_pendaftaran_id');
                                if (!$gelombangId) return []; // Perlu gelombang dipilih

                                $gelombang = GelombangPendaftaran::find($gelombangId);
                                if (!$gelombang || !$gelombang->pendaftaran_id) return [];

                                $requiredDokumen = DokumenPendaftaran::where('pendaftaran_id', $gelombang->pendaftaran_id)->get();
                                if($requiredDokumen->isEmpty()) return [];

                                $defaultItems = [];
                                foreach ($requiredDokumen as $dokumen) {
                                    // Siapkan data untuk setiap item repeater (akan menjadi record DokumenCalonSantri baru)
                                    $defaultItems[] = [
                                        // 'calon_santri_id' => ?? -> ini akan diisi otomatis oleh relationship()
                                        'dokumen_pendaftaran_id' => $dokumen->id, // Set ID dokumen yang diperlukan
                                        // 'file_upload' => null // Nama field upload di skema bawah
                                    ];
                                }
                                return $defaultItems;
                            })
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                // Seharusnya ID sudah ada dari ->default(), tapi bisa ditambahkan safety check
                                if (empty($data['dokumen_pendaftaran_id'])) {
                                    // Mungkin perlu logic fallback atau throw error jika ID hilang
                                    Log::error('Missing dokumen_pendaftaran_id during Repeater creation', $data);
                                    // throw new \Exception('Dokumen pendaftaran ID tidak ditemukan.');
                                }
                                // Pastikan hanya field yg ada di fillable DokumenCalonSantri yg dikirim
                                return [
                                    'dokumen_pendaftaran_id' => $data['dokumen_pendaftaran_id'] ?? null,
                                    // 'calon_santri_id' akan dihandle oleh Filament
                                ];
                            })
                            ->schema([
                                // Field tersembunyi untuk menyimpan ID DokumenPendaftaran
                                // Ini penting agar data tersimpan dengan benar ke pivot
                                Hidden::make('dokumen_pendaftaran_id')->required(),

                                Placeholder::make('nama_dokumen')
                                    ->label(function(Get $get): string {
                                        $docId = $get('dokumen_pendaftaran_id');
                                        return DokumenPendaftaran::find($docId)?->nama ?? 'Dokumen Tidak Dikenal';
                                    })
                                    ->content(function(Get $get): ?string {
                                        $docId = $get('dokumen_pendaftaran_id');
                                        return DokumenPendaftaran::find($docId)?->keterangan;
                                    }),

                                // Field upload Spatie, terikat ke DokumenCalonSantri (model relasi)
                                SpatieMediaLibraryFileUpload::make('media') // Nama field ini PENTING! Harus match dengan collection di DokumenCalonSantri jika ingin otomatis
                                    ->hiddenLabel()
                                    ->collection('dokumen_calon_santri_berkas') // Nama collection di DokumenCalonSantri
                                    ->required() // Wajib upload
                                    ->maxSize(5120)
                                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png'])
                                    ->openable()
                                    ->downloadable()
                                    // Aksi download template
                                    ->hintAction(
                                        Action::make('download_template_repeater')
                                            ->label('Unduh Template')
                                            ->icon('heroicon-o-arrow-down-tray')
                                            ->url(function(Get $get): ?string {
                                                $docId = $get('dokumen_pendaftaran_id');
                                                $dokumen = DokumenPendaftaran::with('media')->find($docId);
                                                return $dokumen?->getFirstMediaUrl('dokumen_pendaftaran_template');
                                            })
                                            ->openUrlInNewTab()
                                            ->color('gray')
                                            ->visible(function(Get $get): bool {
                                                $docId = $get('dokumen_pendaftaran_id');
                                                $dokumen = DokumenPendaftaran::find($docId);
                                                // Cek apakah model punya media di collection tsb
                                                return $dokumen && $dokumen->hasMedia('dokumen_pendaftaran_template');
                                            })
                                            ->tooltip('Unduh template jika diperlukan')
                                    )
                                    ->columnSpan(1), // Kolom untuk field upload
                            ])
                            ->key('repeater_dokumen'),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama Lengkap')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gelombangPendaftaran.pendaftaran.tahun')
                    ->label('Tahun Daftar')
                    ->sortable()
                    ->searchable()
                    ->formatStateUsing(fn ($state) => str($state)), // Bisa search tahun
                TextColumn::make('gelombangPendaftaran.nomor_gelombang')
                    ->label('Gelombang')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->badge()
                    ->searchable()
                    ->sortable(),

                IconColumn::make('status_mubaligh') // Contoh jika ingin menampilkan boolean sebagai icon
                    ->label('Mubaligh')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('nomor_telepon')
                    ->label('No. Telepon')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                // --- Kolom Baru ---
                TextColumn::make('tanggal_lahir')
                    ->label('Tanggal Lahir')
                    ->date('d M Y') // Format tanggal Indonesia
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('usia')
                    ->label('Usia')
                    ->state(function (CalonSantri $record): ?string {
                        if ($record->tanggal_lahir) {
                            // Hitung usia menggunakan Carbon
                            return Carbon::parse($record->tanggal_lahir)->age . ' Thn';
                        }
                        return null;
                    })
                    // Usia biasanya tidak di-sort/search langsung dari DB
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('kewarganegaraan')
                    ->label('Kewarganegaraan')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pendidikan_terakhir')
                    ->label('Pendidikan')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('universitas')
                    ->label('Universitas')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('program_studi')
                    ->label('Program Studi')
                    ->searchable(),
                TextColumn::make('angkatan_kuliah')
                    ->label('Angkatan')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('status_kuliah')
                    ->label('Status Kuliah')
                    ->badge()
                    ->searchable()
                    ->sortable(), // Tampilkan default
                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable() // Search alamat mungkin kurang efektif jika panjang
                    ->limit(50) // Batasi tampilan awal
                    ->tooltip(fn (CalonSantri $record): ?string => $record->alamat),
                TextColumn::make('daerahSambung.nama') // Akses nama dari relasi
                ->label('Daerah Sambung')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_ayah')
                    ->label('Nama Ayah')
                    ->searchable(),
                TextColumn::make('status_ayah')
                    ->label('Status Ayah')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('nama_ibu')
                    ->label('Nama Ibu')
                    ->searchable(),
                TextColumn::make('status_ibu')
                    ->label('Status Ibu')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status_tinggal')
                    ->label('Status Tinggal')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status_pernikahan')
                    ->label('Status Nikah')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('hubungan_wali')
                    ->label('Hubungan Wali')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                // --- Akhir Kolom Baru ---

                TextColumn::make('created_at')
                    ->label('Tanggal Input')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('pendaftaran_tahun')
                    ->label('Tahun Pendaftaran')
                    ->options(
                        Pendaftaran::query()
                            ->orderBy('tahun', 'desc')
                            ->pluck('tahun', 'tahun')
                    )
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $tahun): Builder => $query->whereHas('gelombangPendaftaran.pendaftaran', fn(Builder $q) => $q->where('tahun', $tahun))
                        );
                    }),

                SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options(JenisKelamin::class),

                // --- Filter Baru ---
                SelectFilter::make('status_kuliah')
                    ->label('Status Kuliah')
                    ->options(StatusKuliah::class) // Langsung dari Enum
                    ->searchable(), // Buat searchable jika opsinya banyak
                // --- Akhir Filter Baru ---

                Tables\Filters\TrashedFilter::make(), // Jika menggunakan SoftDeletes
            ])
            ->actions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                //BulkActionGroup::make([
                    // DeleteBulkAction::make(),
                    // Tables\Actions\ForceDeleteBulkAction::make(),
                    // Tables\Actions\RestoreBulkAction::make(),
                // ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\DokumenRelationManager::class, // Example
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCalonSantris::route('/'),
            'create' => Pages\CreateCalonSantri::route('/create'), // Ensure this uses your custom page
            'view' => Pages\ViewCalonSantri::route('/{record}'),
            'edit' => Pages\EditCalonSantri::route('/{record}/edit'),
        ];
    }

    /**
     * Eager load relasi untuk performa tabel.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            // Tambahkan relasi yang dibutuhkan untuk kolom & filter
            ->with(['gelombangPendaftaran.pendaftaran', 'daerahSambung'])
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
