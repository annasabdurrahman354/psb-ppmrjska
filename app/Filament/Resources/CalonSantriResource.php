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
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use App\Models\Kota;
use App\Models\Provinsi;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
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
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Split as TableSplit;
use Filament\Tables\Columns\Layout\Stack as TableStack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TagsInput;
use Illuminate\Support\Collection;

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
                        Select::make('gelombang_pendaftaran_id')
                            ->label('Gelombang Pendaftaran')
                            ->relationship('gelombangPendaftaran', 'nomor_gelombang') // Asumsi relasi ada dan menampilkan ID atau field lain
                            ->required()
                            ->preload()
                            ->searchable(),
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
                            ->label('Nomor Telepon')
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
                        TextInput::make('nomor_identitas_kependudukan')
                            ->label('NIK')
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
                                TextInput::make('city')
                                    ->label('City')
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== 'Indonesia'),
                                TextInput::make('state_province')
                                    ->label('State/Province')
                                    ->required(fn (Get $get): bool => $get('kewarganegaraan') !== null && $get('kewarganegaraan') !== 'Indonesia'),
                                TextInput::make('kode_pos') // Kode Pos bisa relevan untuk non-indo juga
                                ->label('Postal Code'),
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
                            ->label('Nomor Telepon Ayah')
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
                            ->label('Nomor Telepon Ibu')
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
                            ->label('Nomor Telepon Wali')
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
            ]);
    }

    public static function table(Table $table): Table
    {
        // Keep table definition from the previous response
        return $table
            ->columns([
                // --- Simple view for mobile ---
                TableStack::make([
                    TextColumn::make('nama')
                        ->weight(FontWeight::Bold)
                        ->searchable(),
                    TextColumn::make('gelombangPendaftaran.pendaftaran.tahun_pendaftaran')
                        ->label('Periode')
                        ->badge(),
                    TextColumn::make('gelombangPendaftaran.nomor_gelombang')
                        ->label('Gel.')
                        ->formatStateUsing(fn ($state) => "Gel. {$state}") // Format state
                        ->badge()
                        ->color('success'),
                    TextColumn::make('email')
                        ->icon('heroicon-m-envelope')
                        ->iconColor('primary'),
                    TextColumn::make('penilaian.status_penerimaan') // Assuming relationship name is 'penilaian'
                    ->label('Status')
                        ->badge(),
                ])->hiddenFrom('md'), // Hide stack on medium screens and up

                // --- Detailed view for desktop ---
                TableSplit::make([
                    TableStack::make([
                        TextColumn::make('nama')
                            ->weight(FontWeight::Bold)
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('email')
                            ->icon('heroicon-m-envelope')
                            ->searchable()
                            ->copyable()
                            ->copyMessage('Email copied')
                            ->iconColor('primary'),
                        TextColumn::make('nomor_telepon')
                            ->icon('heroicon-m-phone')
                            ->searchable()
                            ->copyable()
                            ->copyMessage('Phone number copied')
                            ->iconColor('primary'),
                    ])->space(1), // Add minimal space in the stack
                    TableStack::make([
                        TextColumn::make('gelombangPendaftaran.pendaftaran.tahun_pendaftaran')
                            ->label('Periode Pendaftaran')
                            ->badge(),
                        TextColumn::make('gelombangPendaftaran.nomor_gelombang')
                            ->label('Gelombang')
                            ->formatStateUsing(fn ($state) => "Gel. {$state}") // Format state
                            ->badge()
                            ->color('success'),
                        TextColumn::make('penilaian.status_penerimaan') // Assuming relationship name is 'penilaian'
                        ->label('Status Penerimaan')
                            ->badge(),
                        TextColumn::make('created_at')
                            ->label('Tgl Daftar')
                            ->dateTime('d M Y H:i')
                            ->sortable(),
                    ])->alignment('end')->space(1), // Align to end on desktop
                ])->visibleFrom('md'), // Show split layout on medium screens and up
            ])
            ->contentGrid([ // Optional: Use grid layout on desktop for better spacing if needed
                'md' => 2,
                'xl' => 3,
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('gelombang_pendaftaran_id')
                    ->label('Gelombang')
                    ->relationship('gelombangPendaftaran', 'nomor_gelombang', fn(Builder $query) =>
                    $query->join('pendaftaran', 'gelombang_pendaftaran.pendaftaran_id', '=', 'pendaftaran.id')
                        ->selectRaw("gelombang_pendaftaran.id, CONCAT(pendaftaran.tahun_pendaftaran, ' - Gel. ', gelombang_pendaftaran.nomor_gelombang) as display_name")
                        ->orderBy('pendaftaran.tahun_pendaftaran', 'desc')
                        ->orderBy('gelombang_pendaftaran.nomor_gelombang', 'asc')
                    )
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => $record->display_name ?? "Gel. {$record->nomor_gelombang}")
                    ->searchable()
                    ->preload(),
                Tables\Filters\SelectFilter::make('penilaian_status_penerimaan') // Ensure unique name if relation used elsewhere
                ->label('Status Penerimaan')
                    ->options(\App\Enums\StatusPenerimaan::class) // Assuming you have the enum
                    ->query(function (Builder $query, array $data): Builder {
                        $status = $data['value'];
                        if (blank($status)) {
                            return $query;
                        }
                        return $query->whereHas('penilaian', function (Builder $subQuery) use ($status) {
                            $subQuery->where('status_penerimaan', $status);
                        });
                    }),


            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                InfolistSplit::make([
                    // Left Column (Grow)
                    InfolistGroup::make([
                        // ... (Keep sections for Pendaftaran, Pribadi, Pendidikan, Sambung, Kesehatan from previous response) ...
                        InfolistSection::make('Informasi Pendaftaran')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('gelombangPendaftaran.pendaftaran.tahun_pendaftaran')->label('Periode'),
                                TextEntry::make('gelombangPendaftaran.nomor_gelombang')->label('Gelombang')->formatStateUsing(fn ($state) => "Gel. {$state}"),
                                TextEntry::make('penilaian.status_penerimaan')->badge()->label('Status Penerimaan'),
                                TextEntry::make('created_at')->dateTime('d M Y H:i')->label('Tanggal Daftar'),
                            ]),
                        InfolistSection::make('Informasi Pribadi')
                            ->columns(3)
                            ->schema([
                                TextEntry::make('nama')->columnSpan(2),
                                TextEntry::make('nama_panggilan'),
                                TextEntry::make('jenis_kelamin')->badge(),
                                TextEntry::make('kewarganegaraan'),
                                TextEntry::make('nomor_identitas_kependudukan')->label('NIK')->copyable(),
                                TextEntry::make('nomor_kartu_keluarga')->label('No. KK')->copyable(),
                                TextEntry::make('nomor_passport')->copyable()->placeholder('N/A'),
                                TextEntry::make('tempat_lahir'),
                                TextEntry::make('tanggal_lahir')->date('d M Y'),
                                IconEntry::make('status_mubaligh')->boolean()->label('Sudah Mubaligh?'),
                                IconEntry::make('pernah_mondok')->boolean()->label('Pernah Mondok?'),
                                TextEntry::make('nama_pondok_sebelumnya')->visible(fn($state) => filled($state)),
                                TextEntry::make('lama_mondok_sebelumnya')->suffix(' tahun')->visible(fn($state) => filled($state)),
                                TextEntry::make('status_pernikahan')->badge(),
                                TextEntry::make('status_tinggal')->badge(),
                                TextEntry::make('anak_nomor')->label('Anak Ke-'),
                                TextEntry::make('jumlah_saudara')->label('Jml. Saudara'),
                            ]),
                        InfolistSection::make('Pendidikan & Keahlian')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('pendidikan_terakhir')->badge(),
                                TextEntry::make('jurusan'),
                                TextEntry::make('program_studi'),
                                TextEntry::make('universitas'),
                                TextEntry::make('angkatan_kuliah'),
                                TextEntry::make('status_kuliah')->badge(),
                                TextEntry::make('mulai_mengaji')->badge(),
                                TextEntry::make('bahasa_makna')->badge(),
                                TextEntry::make('bahasa_harian')->badge()->listWithLineBreaks()->label('Bahasa Harian'),
                                TextEntry::make('keahlian')->badge()->listWithLineBreaks(),
                                TextEntry::make('hobi')->badge()->listWithLineBreaks(),
                                TextEntry::make('sim')->badge()->separator(', ')->label('SIM'),
                            ]),
                        InfolistSection::make('Informasi Sambung')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('kelompok_sambung'),
                                TextEntry::make('desa_sambung'),
                                TextEntry::make('daerahSambung.nama')->label('Daerah Sambung'),
                            ]),
                        InfolistSection::make('Informasi Kesehatan')
                            ->columns(2)
                            ->schema([
                                TextEntry::make('tinggi_badan')->suffix(' cm'),
                                TextEntry::make('berat_badan')->suffix(' kg'),
                                TextEntry::make('golongan_darah')->badge(),
                                TextEntry::make('ukuran_baju')->badge(),
                                TextEntry::make('riwayat_sakit')->columnSpanFull(),
                                TextEntry::make('alergi')->columnSpanFull(),
                            ]),

                        // Section to display uploaded documents using RepeatableEntry
                        InfolistSection::make('Dokumen Terupload')
                            ->schema([
                                RepeatableEntry::make('dokumen') // Target the relationship
                                ->label('') // Hide main label if needed
                                ->schema([
                                    TextEntry::make('dokumenPendaftaran.nama') // Show the required document name
                                    ->label('Jenis Dokumen')
                                        ->weight(FontWeight::Medium),
                                    SpatieMediaLibraryImageEntry::make('dokumen_upload') // Arbitrary name for the media entry
                                    ->label('File Terupload')
                                        ->collection('dokumen_calon_santri_berkas') // The collection name on DokumenCalonSantri
                                        ->checkFileExistence(false) // Optimisation if many files/remote storage
                                        ->hintAction( // Add a download action
                                            Infolists\Components\Actions\Action::make('download')
                                                ->label('Download')
                                                ->icon('heroicon-o-document-arrow-down')
                                                ->url(function (Model $record): ?string {
                                                    $media = $record->getFirstMedia('dokumen_calon_santri_berkas');
                                                    return $media?->getUrl();
                                                })
                                                ->openUrlInNewTab(),
                                        ),

                                ])
                                    ->grid(1) // Display each document info stacked
                                    ->contained(false), // Remove container around each repeatable item if desired
                            ])
                    ])->grow(), // Left column takes available space

                    // Right Column (Fixed Width)
                    InfolistGroup::make([
                        // ... (Keep sections for Alamat, Kontak, Keluarga from previous response) ...
                        InfolistSection::make('Alamat')
                            ->schema([
                                TextEntry::make('alamat')->columnSpanFull(),
                                TextEntry::make('rt'),
                                TextEntry::make('rw'),
                                TextEntry::make('provinsi.nama')->label('Provinsi'),
                                TextEntry::make('kota.nama')->label('Kota/Kab'),
                                TextEntry::make('kecamatan.nama')->label('Kecamatan'),
                                TextEntry::make('kelurahan.nama')->label('Kelurahan'),
                                TextEntry::make('city')->label('City (LN)'),
                                TextEntry::make('state_province')->label('State/Province (LN)'),
                                TextEntry::make('kode_pos'),
                            ])->columns(1), // Single column layout for address
                        InfolistSection::make('Kontak')
                            ->schema([
                                TextEntry::make('nomor_telepon')->copyable()->icon('heroicon-m-phone'),
                                TextEntry::make('email')->copyable()->icon('heroicon-m-envelope'),
                            ])->columns(1),
                        InfolistSection::make('Keluarga')
                            ->schema([
                                TextEntry::make('nama_ayah'),
                                TextEntry::make('status_ayah')->badge(),
                                TextEntry::make('nomor_telepon_ayah')->copyable()->icon('heroicon-m-phone'),
                                TextEntry::make('pekerjaan_ayah'),
                                TextEntry::make('daerahSambungAyah.nama')->label('Daerah Ayah'),
                                TextEntry::make('nama_ibu'),
                                TextEntry::make('status_ibu')->badge(),
                                TextEntry::make('nomor_telepon_ibu')->copyable()->icon('heroicon-m-phone'),
                                TextEntry::make('pekerjaan_ibu'),
                                TextEntry::make('daerahSambungIbu.nama')->label('Daerah Ibu'),
                                TextEntry::make('nama_wali'),
                                TextEntry::make('hubungan_wali')->badge(),
                                TextEntry::make('nomor_telepon_wali')->copyable()->icon('heroicon-m-phone'),
                                TextEntry::make('pekerjaan_wali'),
                                TextEntry::make('daerahSambungWali.nama')->label('Daerah Wali'),
                            ])->columns(1),
                    ])->grow(false), // Right column does not grow
                ])->from('md') // Apply split layout from medium screens up
            ]);
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
