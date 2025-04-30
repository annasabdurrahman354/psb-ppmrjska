<?php
// app/Filament/Resources/PendaftaranResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\PendaftaranResource\Pages;
use App\Filament\Resources\PendaftaranResource\RelationManagers;
use App\Models\Pendaftaran;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PendaftaranResource extends Resource
{
    protected static ?string $model = Pendaftaran::class;
    protected static ?string $slug = 'pendaftaran';
    protected static ?string $navigationIcon = 'heroicon-o-document-plus';
    protected static ?string $navigationLabel = 'Pendaftaran';
    protected static ?string $navigationGroup = 'Pendaftaran';
    protected static ?string $pluralModelLabel = 'Pendaftaran';
    protected static ?string $modelLabel = 'Pendaftaran';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Wizard::make([
                    // Step 1: Info Pendaftaran & Kontak (Sama seperti sebelumnya)
                    Wizard\Step::make('Informasi Pendaftaran')
                        ->icon('heroicon-o-information-circle')
                        ->description('Masukkan tahun pendaftaran dan detail kontak.')
                        ->schema([
                            // ... (Konten Step 1 tidak berubah) ...
                            Forms\Components\TextInput::make('tahun')
                                ->required()
                                ->numeric()
                                ->minValue(now()->year - 1)
                                ->maxValue(date('Y') + 5)
                                ->default(now()->year)
                                ->label('Tahun Pendaftaran'),

                            Forms\Components\Section::make('Kontak Panitia')
                                ->schema([
                                    Repeater::make('kontak_panitia')
                                        ->label('')
                                        ->schema([
                                            TextInput::make('nama')->required()->label('Nama'),
                                            TextInput::make('jabatan')->required()->label('Jabatan'),
                                            TextInput::make('nomor_telepon')
                                                ->label('Nomor Telepon')
                                                ->tel()
                                                ->required(),
                                        ])
                                        ->columns(3)
                                        ->addActionLabel('Tambah Kontak Panitia')
                                        ->defaultItems(1)
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['nama'] ?? null)
                                        ->minItems(1),
                                ]),

                            Forms\Components\Section::make('Kontak Pengurus')
                                ->schema([
                                    Repeater::make('kontak_pengurus')
                                        ->label('')
                                        ->schema([
                                            TextInput::make('nama')->required()->label('Nama'),
                                            TextInput::make('jabatan')->required()->label('Jabatan'),
                                            TextInput::make('nomor_telepon')
                                                ->label('Nomor Telepon')
                                                ->tel()
                                                ->required(),
                                        ])
                                        ->columns(3)
                                        ->addActionLabel('Tambah Kontak Pengurus')
                                        ->defaultItems(1)
                                        ->collapsible()
                                        ->itemLabel(fn (array $state): ?string => $state['nama'] ?? null)
                                        ->minItems(1),
                                ]),
                        ]),

                    // Step 2: Dokumen Wajib (Sama seperti sebelumnya)
                    Wizard\Step::make('Dokumen Wajib')
                        ->icon('heroicon-o-document-text')
                        ->description('Tentukan dokumen yang wajib diunggah pendaftar dan unggah template jika ada.')
                        ->schema([
                            // ... (Konten Step 2 tidak berubah) ...
                            Repeater::make('dokumenPendaftaran')
                                ->relationship()
                                ->schema([
                                    TextInput::make('nama')
                                        ->required()
                                        ->maxLength(255)
                                        ->label('Nama Dokumen')
                                        ->columnSpan(2),
                                    Textarea::make('keterangan')
                                        ->label('Keterangan')
                                        ->columnSpan(2),
                                    SpatieMediaLibraryFileUpload::make('template_dokumen_file')
                                        ->label('Template Dokumen (Opsional)')
                                        ->collection('dokumen_pendaftaran_template')
                                        ->reorderable()
                                        ->columnSpanFull()
                                        ->helperText('Unggah file template jika dokumen ini memilikinya (misal: form surat pernyataan).'),

                                ])
                                ->addActionLabel('Tambah Dokumen')
                                ->columns(2)
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['nama'] ?? null)
                                ->minItems(1),
                        ]),

                    // Step 3: Indikator Penilaian (Dimodifikasi)
                    Wizard\Step::make('Indikator Penilaian')
                        ->icon('heroicon-o-adjustments-horizontal')
                        ->description('Tentukan indikator dan bobot penilaian. Total bobot harus 1.')
                        ->schema([
                            Repeater::make('indikatorPenilaian')
                                ->relationship()
                                ->schema([
                                    TextInput::make('nama')
                                        ->required()
                                        ->maxLength(255)
                                        ->label('Nama Indikator'),
                                    TextInput::make('bobot')
                                        ->required()
                                        ->numeric()
                                        ->step(0.1)
                                        ->minValue(0.1) // Bobot tidak boleh 0 atau negatif
                                        ->maxValue(1) // Bobot maksimal 1
                                        ->label('Bobot Penilaian')
                                        ->live(debounce: 500), // <-- Buat field bobot live agar validasi repeater berjalan
                                ])
                                ->addActionLabel('Tambah Indikator')
                                ->columns(2)
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => $state['nama'] ?? null)
                                ->live() // <-- Buat repeater live agar validasi berjalan saat item ditambah/dihapus
                                ->rules([ // <-- Tambahkan rules pada repeater
                                    function () {
                                        return function (string $attribute, $value, Closure $fail) {
                                            // $attribute akan berisi 'indikatorPenilaian'
                                            // $value akan berisi array dari state item repeater
                                            $totalBobot = collect($value)
                                                ->pluck('bobot') // Ambil semua nilai bobot
                                                ->map(fn ($bobot) => is_numeric($bobot) ? (float)$bobot : 0) // Konversi ke float, anggap 0 jika bukan angka
                                                ->sum();

                                            // Gunakan perbandingan dengan toleransi kecil untuk float
                                            if (abs($totalBobot - 1.0) > 0.0001) {
                                                // Pesan error dalam Bahasa Indonesia
                                                $fail('Total bobot untuk semua indikator penilaian harus sama dengan 1. Total saat ini: ' . $totalBobot);
                                            }
                                        };
                                    }
                                ])
                                ->validationMessages([ // <-- Pesan kustom jika diperlukan (opsional)
                                    // Jika ingin pesan kustom spesifik untuk rule closure ini
                                    // 'rule_closure' => 'Total bobot harus tepat 1.',
                                ])
                                ->minItems(1),
                        ]),

                    // Step 4: Gelombang Pendaftaran (Sama seperti sebelumnya)
                    Wizard\Step::make('Gelombang Pendaftaran')
                        ->icon('heroicon-o-calendar-days')
                        ->description('Atur gelombang pendaftaran dan timeline kegiatannya.')
                        ->schema([
                            // ... (Konten Step 4 tidak berubah) ...
                            Repeater::make('gelombangPendaftaran')
                                ->relationship()
                                ->orderColumn('nomor_gelombang')
                                ->schema([
                                    TextInput::make('nomor_gelombang')
                                        ->required()
                                        ->numeric()
                                        ->label('Nomor Gelombang')
                                        ->live(),
                                    DateTimePicker::make('awal_pendaftaran')
                                        ->required()
                                        ->label('Awal Pendaftaran'),
                                    DateTimePicker::make('akhir_pendaftaran')
                                        ->required()
                                        ->label('Akhir Pendaftaran'),
                                    TextInput::make('link_grup')
                                        ->url()
                                        ->label('Link Grup'),

                                    Section::make('Timeline Kegiatan')
                                        ->schema([
                                            Repeater::make('timeline')
                                                ->label('')
                                                ->schema([
                                                    TextInput::make('nama_kegiatan')
                                                        ->label('Nama Kegiatan')
                                                        ->required(),
                                                    DatePicker::make('tanggal')
                                                        ->label('Tanggal Kegiatan')
                                                        ->required(),
                                                ])
                                                ->columns(2)
                                                ->addActionLabel('Tambah Kegiatan Timeline')
                                                ->collapsible()
                                                ->itemLabel(fn (array $state): ?string => $state['nama_kegiatan'] ?? null)
                                                ->minItems(2),
                                        ])->collapsible(),
                                ])
                                ->addActionLabel('Tambah Gelombang')
                                ->columns(2)
                                ->collapsible()
                                ->itemLabel(fn (array $state): ?string => 'Gelombang ' . ($state['nomor_gelombang'] ?? '?'))
                                ->minItems(1),
                        ]),
                ])
                    ->columnSpanFull(),
            ]);
    }

    // ... (Table, Relations, Pages, EloquentQuery methods tetap sama) ...
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun Pendaftaran')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('gelombangPendaftaran.count')
                    ->label('Jumlah Gelombang')
                    ->counts('gelombangPendaftaran')
                    ->sortable(),
                Tables\Columns\TextColumn::make('dokumenPendaftaran.count')
                    ->label('Jumlah Dokumen Wajib')
                    ->counts('dokumenPendaftaran')
                    ->sortable(),
                Tables\Columns\TextColumn::make('indikatorPenilaian.count')
                    ->label('Jumlah Indikator Nilai')
                    ->counts('indikatorPenilaian')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\GelombangPendaftaranRelationManager::class,
            RelationManagers\DokumenPendaftaranRelationManager::class,
            RelationManagers\IndikatorPenilaianRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPendaftarans::route('/'),
            'create' => Pages\CreatePendaftaran::route('/create'),
            'view' => Pages\ViewPendaftaran::route('/{record}'),
            'edit' => Pages\EditPendaftaran::route('/{record}/edit'),
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
