<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenilaianCalonSantriResource\Pages;
use App\Models\CalonSantri;
use App\Models\GelombangPendaftaran;
use App\Models\IndikatorPenilaian;
use App\Models\Pendaftaran;
use App\Models\PenilaianCalonSantri;
use App\Enums\StatusPenerimaan; // Pastikan enum diimpor
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model as EloquentModel; // Alias untuk Model Eloquent
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Facades\Auth;
use Mokhosh\FilamentRating\Components\Rating as RatingField; // Kolom form
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\RatingTheme;

// Kolom tabel

class PenilaianCalonSantriResource extends Resource
{
    protected static ?string $model = PenilaianCalonSantri::class;
    protected static ?string $slug = 'penilaian-calon-santri';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Penilaian';
    protected static ?string $navigationGroup = 'Pendaftaran';
    protected static ?string $pluralModelLabel = 'Penilaian Calon Santri';
    protected static ?string $modelLabel = 'Penilaian Calon Santri';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('penguji_id')
                    ->relationship('penguji', 'nama') // Asumsi penguji adalah guru
                    ->searchable()
                    ->preload()
                    ->default(fn () => Auth::id())
                    ->disabledOn('edit')
                    ->required(),

                Forms\Components\Select::make('calon_santri_id')
                    ->relationship(
                        'calonSantri',
                        'nama',
                        modifyQueryUsing: fn (Builder $query) => $query->whereDoesntHave('penilaian'),
                    )
                    ->searchable()
                    ->preload()
                    ->live() // Penting untuk repeater dinamis
                    ->afterStateUpdated(function (Set $set, Get $get, ?string $state, ?EloquentModel $record) {
                        // $state adalah calon_santri_id yang baru dipilih/diubah
                        // $record adalah model PenilaianCalonSantri saat ini (null jika membuat baru)

                        if (blank($state)) {
                            $set('detailPenilaian', []);
                            return;
                        }

                        $calonSantri = CalonSantri::with('gelombangPendaftaran.pendaftaran.indikatorPenilaian')->find($state);
                        if (!$calonSantri || !$calonSantri->gelombangPendaftaran || !$calonSantri->gelombangPendaftaran->pendaftaran) {
                            $set('detailPenilaian', []);
                            // Di sini Anda bisa menambahkan notifikasi jika data pendaftaran tidak ditemukan
                            return;
                        }

                        $indikators = $calonSantri->gelombangPendaftaran->pendaftaran->indikatorPenilaian;
                        if ($indikators->isEmpty()) {
                            $set('detailPenilaian', []);
                            // Di sini Anda bisa menambahkan notifikasi jika tidak ada indikator
                            return;
                        }

                        $newDetailPenilaianState = [];

                        // Tentukan apakah kita mencoba memuat nilai yang ada untuk record saat ini
                        // Ini terjadi jika kita berada di halaman edit DAN calon_santri_id yang dipilih SAMA DENGAN calon_santri_id pada record.
                        $shouldLoadExistingNilai = $record && $record->calon_santri_id == $state;

                        $existingDetailsByIndikatorId = [];
                        if ($shouldLoadExistingNilai) {
                            // Ambil detail penilaian yang sudah ada untuk record PenilaianCalonSantri ini
                            // dan kelompokkan berdasarkan indikator_penilaian_id untuk akses mudah
                            $existingDetailsByIndikatorId = $record->detailPenilaian()
                                ->get()
                                ->keyBy('indikator_penilaian_id');
                        }

                        foreach ($indikators as $indikator) {
                            $nilaiDefault = 0; // Nilai default jika tidak ada data sebelumnya
                            $idDetailPenilaian = null;

                            if ($shouldLoadExistingNilai && isset($existingDetailsByIndikatorId[$indikator->id])) {
                                $existingDetail = $existingDetailsByIndikatorId[$indikator->id];
                                $nilaiDefault = $existingDetail->nilai;
                                $idDetailPenilaian = $existingDetail->id; // Penting untuk update
                            }

                            $itemData = [
                                'indikator_penilaian_id' => $indikator->id,
                                'nilai' => $nilaiDefault,
                            ];

                            // Jika item ini adalah record yang sudah ada, sertakan ID-nya
                            // agar repeater tahu ini adalah update, bukan create baru.
                            if ($idDetailPenilaian) {
                                $itemData['id'] = $idDetailPenilaian;
                            }

                            $newDetailPenilaianState[] = $itemData;
                        }
                        $set('detailPenilaian', $newDetailPenilaianState);
                    })
                    ->disabledOn('edit')
                    ->required(),

                Forms\Components\Repeater::make('detailPenilaian')
                    ->label('Penilaian')
                    ->relationship('detailPenilaian')
                    ->hiddenLabel(fn (Get $get) => !$get('calon_santri_id'))
                    ->schema(function (Get $get): array {
                        $calonSantriId = $get('calon_santri_id');
                        if (!$calonSantriId) {
                            return [
                                Forms\Components\Placeholder::make('pilih_calon_santri')
                                    ->label('')
                                    ->content('Pilih calon santri terlebih dahulu untuk mengisi detail penilaian.'),
                            ];
                        }

                        $calonSantri = CalonSantri::find($calonSantriId);
                        $pendaftaranId = $calonSantri?->gelombangPendaftaran?->pendaftaran_id;

                        if (!$pendaftaranId) {
                            return [
                                Forms\Components\Placeholder::make('pendaftaran_tidak_ditemukan')
                                    ->label('')
                                    ->content('Data pendaftaran untuk calon santri ini tidak ditemukan.'),
                            ];
                        }

                        $indikators = IndikatorPenilaian::where('pendaftaran_id', $pendaftaranId)->get();

                        if ($indikators->isEmpty()){
                            return [
                                Forms\Components\Placeholder::make('indikator_kosong')
                                    ->label('')
                                    ->content('Belum ada indikator penilaian untuk pendaftaran ini.'),
                            ];
                        }

                        return $indikators->map(function (IndikatorPenilaian $indikator) {
                            return Forms\Components\Grid::make(2)
                                ->schema([
                                    Forms\Components\Hidden::make('indikator_penilaian_id')
                                        ->default($indikator->id),
                                    Forms\Components\Placeholder::make('nama_indikator')
                                        ->label('Indikator')
                                        ->content($indikator->nama . ' (Bobot: ' . $indikator->bobot . ')'),
                                    Forms\Components\TextInput::make('nilai')
                                        ->label('Nilai')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->required(),
                                ]);
                        })->all();
                    })
                    ->columns(1)
                    ->reorderable(false) // Menonaktifkan reordering jika tidak diperlukan
                    ->addable(false) // Menonaktifkan penambahan item baru secara manual oleh user jika skema sudah tetap
                    ->deletable(false) // Menonaktifkan penghapusan item jika tidak diperlukan
                    ->default(function (Get $get): array { // Mengisi repeater dengan indikator yang ada
                        $calonSantriId = $get('calon_santri_id');
                        if (!$calonSantriId) return [];

                        $calonSantri = CalonSantri::find($calonSantriId);
                        $pendaftaranId = $calonSantri?->gelombangPendaftaran?->pendaftaran_id;
                        if (!$pendaftaranId) return [];

                        $indikators = IndikatorPenilaian::where('pendaftaran_id', $pendaftaranId)->get();
                        return $indikators->map(fn(IndikatorPenilaian $indikator) => [
                            'indikator_penilaian_id' => $indikator->id,
                            'nilai' => 0, // Nilai default
                        ])->all();
                    })
                    ->columnSpanFull(),


                Forms\Components\Textarea::make('catatan')
                    ->columnSpanFull(),

                RatingField::make('rekomendasi_penguji')
                    ->label('Rekomendasi')
                    ->stars(10)
                    ->color('warning')
                    ->allowZero(),

                Forms\Components\Select::make('status_penerimaan')
                    ->label('Status')
                    ->options(StatusPenerimaan::class)
                    ->default(StatusPenerimaan::BELUM_DITENTUKAN)
                    ->disabledOn('create')
                    ->dehydrated()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('calonSantri.gelombangPendaftaran.pendaftaran.tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('calonSantri.gelombangPendaftaran.nomor_gelombang')
                    ->label('Gelombang')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('calonSantri.nama')
                    ->label('Nama')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('nilai_akhir')
                    ->label('Nilai Akhir')
                    ->numeric()
                    ->state(function (PenilaianCalonSantri $record): float { // Pindahkan ke accessor model
                        return $record->detailPenilaian->reduce(function ($carry, $detail) {
                             return $carry + ($detail->nilai * ($detail->indikatorPenilaian?->bobot ?? 0));
                        }, 0);
                    }),
                Tables\Columns\TextColumn::make('penguji.nama')
                    ->label('Penguji')
                    ->sortable()
                    ->searchable(),
                RatingColumn::make('rekomendasi_penguji')
                    ->label('Rekomendasi')
                    ->sortable()
                    ->stars(5)
                    ->theme(RatingTheme::HalfStars)
                    ->state(function (PenilaianCalonSantri $record): float { // Pindahkan ke accessor model
                        return $record->rekomendasi_penguji / 2;
                    }),
                Tables\Columns\TextColumn::make('catatan')
                    ->label('Catatan')
                    ->searchable()
                    ->wrap()
                    ->lineClamp(2),
                Tables\Columns\TextColumn::make('status_penerimaan')
                    ->label('Status')
                    ->badge()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('pendaftaran_tahun')
                    ->label('Tahun')
                    ->options(
                        Pendaftaran::query()
                            ->orderBy('tahun', 'desc')
                            ->pluck('tahun', 'tahun')
                    )
                    ->default(Pendaftaran::orderBy('tahun', 'desc')->first()->tahun ?? '')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $tahun): Builder => $query->whereHas(
                                'calonSantri.gelombangPendaftaran.pendaftaran',
                                fn(Builder $q) => $q->where('tahun', $tahun)
                            )
                        );
                    }),

                SelectFilter::make('pendaftaran_gelombang')
                    ->label('Gelombang')
                    ->options([
                        1 => 1,
                        2 => 2,
                        3 => 3
                    ])
                    ->default(GelombangPendaftaran::orderBy('awal_pendaftaran', 'desc')->first()->nomor_gelombang ?? '')
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $nomor): Builder => $query->whereHas(
                                'calonSantri.gelombangPendaftaran',
                                fn(Builder $q) => $q->where('nomor_gelombang', $nomor)
                            )
                        );
                    }),

            ], Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('ubahStatusPenerimaan')
                    ->label('Ubah Status')
                    ->icon('heroicon-o-check-badge')
                    ->form([
                        Forms\Components\Select::make('status_penerimaan')
                            ->label('Status Penerimaan Baru')
                            ->options(StatusPenerimaan::class)
                            ->default(fn (PenilaianCalonSantri $record) => $record->status_penerimaan)
                            ->required(),
                    ])
                    ->action(function (PenilaianCalonSantri $record, array $data): void {
                        $record->status_penerimaan = $data['status_penerimaan'];
                        $record->save();
                        Notification::make()
                            ->title('Status penerimaan berhasil diubah')
                            ->success()
                            ->send();
                    }),
                //Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenilaianCalonSantris::route('/'),
            'create' => Pages\CreatePenilaianCalonSantri::route('/create'),
            'edit' => Pages\EditPenilaianCalonSantri::route('/{record}/edit'),
            'view' => Pages\ViewPenilaianCalonSantri::route('/{record}'),
        ];
    }

    /**
     * Modifikasi query global untuk eager load relasi yang sering diakses.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'calonSantri.gelombangPendaftaran.pendaftaran',
                'penguji',
                'detailPenilaian.indikatorPenilaian' // Eager load indikatorPenilaian
            ]);
    }
}
