<?php

namespace App\Filament\Pages;

use App\Enums\JenisKelamin;
use App\Enums\Role;
use App\Models\CalonSantri;
use App\Models\GelombangPendaftaran;
use App\Models\IndikatorPenilaian;
use App\Models\Pendaftaran;
use App\Enums\StatusPenerimaan;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Get;
use Illuminate\Support\Facades\Auth;
use Mokhosh\FilamentRating\Components\Rating as RatingField;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Mokhosh\FilamentRating\RatingTheme;
use Filament\Notifications\Notification;

class PenilaianDewanPenguji extends Page implements HasTable, HasForms
{
    use HasPageShield;
    use InteractsWithTable;
    use InteractsWithForms;

    protected static string $view = 'filament.pages.penilaian-dewan-penguji';

    protected static ?string $slug = 'penilaian-dewan-penguji';
    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';
    protected static ?string $navigationLabel = 'Penilaian Penguji';
    protected static ?string $title = 'Penilaian Penguji';
    protected static ?string $pluralModelLabel = 'Penilaian Penguji';
    protected static ?string $modelLabel = 'Penilaian Penguji';

    public ?array $data = []; // For modal form data

    public function mount(): void
    {
        // If you need to initialize a record for the page context, do it here
        // For example, if this page was for a specific Pendaftaran record:
        // $pendaftaranTahunIni = Pendaftaran::where('tahun', Carbon::now()->year)->first();
        // $this->record = $pendaftaranTahunIni;
        // $this->form->fill(); // Initialize if there's a page-level form
    }

    protected function getPendaftaranIdTerbaru(): ?string
    {
        return Pendaftaran::orderBy('tahun')->first()->id ?? null;
    }

    protected function getIndikatorPenilaianTerbaru(): \Illuminate\Database\Eloquent\Collection
    {
        $pendaftaranId = $this->getPendaftaranIdTerbaru();
        if (!$pendaftaranId) {
            return collect();
        }
        return IndikatorPenilaian::where('pendaftaran_id', $pendaftaranId)->get();
    }

    public function table(Table $table): Table
    {
        $pendaftaranIdTerbaru = $this->getPendaftaranIdTerbaru();
        $indikatorColumns = [];

        if ($pendaftaranIdTerbaru) {
            $indikators = $this->getIndikatorPenilaianTerbaru();
            foreach ($indikators as $indikator) {
                $indikatorColumns[] = Tables\Columns\TextColumn::make('penilaian.detailPenilaian.' . $indikator->id)
                    ->label('Nilai '.$indikator->nama)
                    ->state(function (CalonSantri $record) use ($indikator) {
                        if ($record->penilaian) {
                            $detail = $record->penilaian->detailPenilaian->firstWhere('indikator_penilaian_id', $indikator->id);
                            return $detail ? $detail->nilai : '-';
                        }
                        return '-';
                    })
                    ->numeric();
            }
        }

        // First, find the latest 'awal_pendaftaran' date for the current year.
        $latestGelombangId = GelombangPendaftaran::whereHas('pendaftaran', function (Builder $query) use ($pendaftaranIdTerbaru) {
                $query->where('id', $pendaftaranIdTerbaru);
            })->orderBy('awal_pendaftaran', 'desc')->first()->id;

        // Prepare the base query for CalonSantri
        $calonSantriQuery = CalonSantri::query()
            ->with(['penilaian.penguji', 'penilaian.detailPenilaian.indikatorPenilaian']);

        if ($latestGelombangId) {
            $calonSantriQuery->whereHas('gelombangPendaftaran', function (Builder $subQuery) use ($latestGelombangId) {
                $subQuery->where('id', $latestGelombangId);
            });
        } else {
            // If no gelombang found for the current year, return an empty result.
            $calonSantriQuery->whereRaw('1 = 0');
        }

        return $table
            ->query($calonSantriQuery)
            ->columns(array_merge([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('penilaian.penguji.nama')
                    ->label('Penguji')
                    ->searchable()
                    ->sortable(),
            ], $indikatorColumns, [ // Merge dynamic indicator columns here
                RatingColumn::make('penilaian.rekomendasi_penguji')
                    ->label('Rekomendasi')
                    ->stars(5)
                    ->theme(RatingTheme::HalfStars)
                    ->color('warning')
                    ->state(fn (CalonSantri $record): ?float => $record->penilaian?->rekomendasi_penguji / 2),
                Tables\Columns\TextColumn::make('penilaian.catatan')
                    ->label('Catatan')
                    ->wrap()
                    ->lineClamp(2)
                    ->searchable(),
                Tables\Columns\TextColumn::make('penilaian.status_penerimaan')
                    ->label('Status')
                    ->badge()
                    ->searchable(),
            ]))
            ->filters([
                Tables\Filters\SelectFilter::make('jenis_kelamin')
                    ->label('Jenis Kelamin')
                    ->options(JenisKelamin::class)
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->where('jenis_kelamin', $value)
                        );
                    }),
            ], Tables\Enums\FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\Action::make('buatPenilaian')
                    ->label('Buat Penilaian')
                    ->icon('heroicon-o-plus-circle')
                    ->fillForm(function (CalonSantri $record): array {
                        // Pre-fill form for creation, especially if some data can be derived
                        $indikators = $this->getIndikatorPenilaianTerbaru();
                        $detailPenilaianDefaults = $indikators->map(fn(IndikatorPenilaian $indikator) => [
                            'indikator_penilaian_id' => $indikator->id,
                            'nilai' => 0,
                        ])->all();

                        return [
                            'calon_santri_id' => $record->id,
                            'penguji_id' => Auth::id(),
                            'detailPenilaian' => $detailPenilaianDefaults,
                            'status_penerimaan' => StatusPenerimaan::BELUM_DITENTUKAN,
                        ];
                    })
                    ->form([
                        Forms\Components\Hidden::make('calon_santri_id')
                            ->required(),
                        Forms\Components\Placeholder::make('calon_santri')
                            ->content(fn (Get $get) => CalonSantri::where('id', $get('calon_santri_id'))->first()->nama),
                        Forms\Components\Hidden::make('penguji_id')
                            ->default(fn () => Auth::id())
                            ->required(),
                        Forms\Components\Repeater::make('detailPenilaian')
                            ->label('Penilaian Indikator')
                            ->schema([
                                // Schema ini mendefinisikan field untuk SATU baris repeater
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Hidden::make('indikator_penilaian_id'),
                                    Forms\Components\Placeholder::make('nama_indikator')
                                        ->label('Indikator')
                                        ->content(function (Get $get): string {
                                            // Ambil ID indikator dari data baris ini
                                            $indikatorId = $get('indikator_penilaian_id');
                                            if (!$indikatorId) {
                                                return 'Indikator tidak ditemukan';
                                            }
                                            // Cari model Indikator untuk mendapatkan nama dan bobot
                                            $indikator = IndikatorPenilaian::find($indikatorId);
                                            return $indikator ? "{$indikator->nama} (Bobot: {$indikator->bobot})" : 'Tidak Ditemukan';
                                        }),
                                    Forms\Components\TextInput::make('nilai')
                                        ->label('Nilai')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->default(0)
                                        ->required(),
                                ])
                            ])
                            ->columns(1)
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->columnSpanFull(),
                        RatingField::make('rekomendasi_penguji')
                            ->label('Rekomendasi')
                            ->stars(10)
                            ->color('warning')
                            ->allowZero()
                            ->default(0),
                    ])
                    ->action(function (array $data, CalonSantri $record): void {
                        $penilaian = $record->penilaian()->create([
                            'penguji_id' => $data['penguji_id'],
                            'catatan' => $data['catatan'],
                            'rekomendasi_penguji' => $data['rekomendasi_penguji'],
                            'status_penerimaan' => StatusPenerimaan::BELUM_DITENTUKAN->value,
                            'calon_santri_id' => $record->id, // Ensure this is set
                        ]);

                        if (isset($data['detailPenilaian']) && is_array($data['detailPenilaian'])) {
                            foreach ($data['detailPenilaian'] as $detail) {
                                $penilaian->detailPenilaian()->create([
                                    'indikator_penilaian_id' => $detail['indikator_penilaian_id'],
                                    'nilai' => $detail['nilai'],
                                ]);
                            }
                        }
                        Notification::make()
                            ->title('Penilaian berhasil disimpan')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (CalonSantri $record): bool => !$record->penilaian), // Only show if no assessment exists

                Tables\Actions\Action::make('editPenilaian')
                    ->label('Edit Penilaian')
                    ->icon('heroicon-o-pencil-square')
                    ->fillForm(function (CalonSantri $record): array {
                        if (!$record->penilaian) {
                            return []; // Should not happen if action is visible
                        }
                        $indikators = $this->getIndikatorPenilaianTerbaru();
                        $detailPenilaianData = $indikators->map(function(IndikatorPenilaian $indikator) use ($record) {
                            $detail = $record->penilaian->detailPenilaian->firstWhere('indikator_penilaian_id', $indikator->id);
                            return [
                                'indikator_penilaian_id' => $indikator->id,
                                'nama_indikator' => $indikator->nama . ' (Bobot: ' . $indikator->bobot . ')',
                                'nilai' => $detail?->nilai ?? 0,
                            ];
                        })->all();

                        return [
                            'calon_santri_id' => $record->id, // Keep for context if needed
                            'penguji_id' => $record->penilaian->penguji_id, // Keep original penguji
                            'catatan' => $record->penilaian->catatan,
                            'rekomendasi_penguji' => $record->penilaian->rekomendasi_penguji,
                            'status_penerimaan' => $record->penilaian->status_penerimaan,
                            'detailPenilaian' => $detailPenilaianData,
                        ];
                    })
                    ->form([
                        Forms\Components\Hidden::make('calon_santri_id'),
                        Forms\Components\Placeholder::make('calon_santri_nama_edit_placeholder') // Unique key
                            ->label('Nama Calon Santri')
                            ->content(fn (Get $get) => CalonSantri::find($get('calon_santri_id'))?->nama ?? 'N/A'),
                        Forms\Components\Hidden::make('penguji_id'), // Keep original penguji, not editable here
                        Forms\Components\Repeater::make('detailPenilaian')
                            ->label('Penilaian Indikator')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\Hidden::make('indikator_penilaian_id'),
                                    Forms\Components\Placeholder::make('nama_indikator_edit')
                                        ->label('Indikator')
                                        ->content(function (Get $get): string {
                                            $indikatorId = $get('indikator_penilaian_id');
                                            if (!$indikatorId) {
                                                return 'Indikator tidak ditemukan';
                                            }
                                            $indikator = IndikatorPenilaian::find($indikatorId);
                                            return $indikator ? "{$indikator->nama} (Bobot: {$indikator->bobot})" : 'Tidak Ditemukan';
                                        }),
                                    Forms\Components\TextInput::make('nilai')
                                        ->label('Nilai (0-100)')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(100)
                                        ->required(),
                                ])
                            ])
                            ->columns(1)
                            ->reorderable(false)
                            ->addable(false)
                            ->deletable(false)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->columnSpanFull(),
                        RatingField::make('rekomendasi_penguji')
                            ->label('Rekomendasi')
                            ->stars(10)
                            ->color('warning')
                            ->allowZero(),
                    ])
                    ->action(function (array $data, CalonSantri $record): void {
                        if ($record->penilaian) {
                            $record->penilaian->update([
                                'catatan' => $data['catatan'],
                                'rekomendasi_penguji' => $data['rekomendasi_penguji'],
                            ]);

                            if (isset($data['detailPenilaian']) && is_array($data['detailPenilaian'])) {
                                foreach ($data['detailPenilaian'] as $detailData) {
                                    $record->penilaian->detailPenilaian()
                                        ->updateOrCreate(
                                            ['indikator_penilaian_id' => $detailData['indikator_penilaian_id']],
                                            ['nilai' => $detailData['nilai']]
                                        );
                                }
                            }
                            Notification::make()
                                ->title('Penilaian berhasil diperbarui')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal memperbarui')
                                ->body('Penilaian tidak ditemukan.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (CalonSantri $record): bool => !!$record->penilaian),


                Tables\Actions\Action::make('ubahStatusPenerimaan')
                    ->label('Ubah Status')
                    ->icon('heroicon-o-check-badge')
                    ->form([
                        Forms\Components\Select::make('status_penerimaan')
                            ->label('Status Penerimaan Baru')
                            ->options(StatusPenerimaan::class)
                            ->default(fn (CalonSantri $record) => $record->penilaian?->status_penerimaan)
                            ->required(),
                    ])
                    ->action(function (CalonSantri $record, array $data): void {
                        if ($record->penilaian) {
                            $record->penilaian->status_penerimaan = $data['status_penerimaan'];
                            $record->penilaian->save();
                            Notification::make()
                                ->title('Status penerimaan berhasil diubah')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Gagal mengubah status')
                                ->body('Calon santri ini belum memiliki data penilaian.')
                                ->danger()
                                ->send();
                        }
                    })
                    ->visible(fn (CalonSantri $record): bool => authUserHasRole(Role::PANITIA_PSB->value)),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }
}

