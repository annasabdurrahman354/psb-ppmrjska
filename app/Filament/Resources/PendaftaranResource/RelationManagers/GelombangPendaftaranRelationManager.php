<?php
// app/Filament/Resources/PendaftaranResource/RelationManagers/GelombangPendaftaranRelationManager.php

namespace App\Filament\Resources\PendaftaranResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Section; // Added for consistency
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class GelombangPendaftaranRelationManager extends RelationManager
{
    protected static string $relationship = 'gelombangPendaftaran';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Gelombang Pendaftaran');
    }


    public function form(Form $form): Form
    {
        // This form mirrors the structure within the Wizard's repeater step
        return $form
            ->schema([
                Forms\Components\TextInput::make('nomor_gelombang')
                    ->required()
                    ->numeric()
                    ->label('Nomor Gelombang'),
                Forms\Components\DateTimePicker::make('awal_pendaftaran')
                    ->required()
                    ->label('Awal Pendaftaran'),
                Forms\Components\DateTimePicker::make('akhir_pendaftaran')
                    ->required()
                    ->label('Akhir Pendaftaran'),
                Forms\Components\TextInput::make('link_grup')
                    ->url()
                    ->label('Link Grup')
                    ->columnSpanFull(), // Span full width

                Section::make('Timeline Kegiatan')
                    ->schema([
                        Repeater::make('timeline')
                            ->label('') // Hide default repeater label
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
                            ->itemLabel(fn (array $state): ?string => $state['nama_kegiatan'] ?? null),
                    ])
                    ->collapsible()
                    ->columnSpanFull(), // Span full width
            ])->columns(2); // Set columns for the main fields
    }

    public function table(Table $table): Table
    {
        // Table definition remains the same as before
        return $table
            ->defaultSort('nomor_gelombang', 'asc')
            ->recordTitleAttribute('nomor_gelombang')
            ->columns([
                Tables\Columns\TextColumn::make('nomor_gelombang')
                    ->label('Gelombang Ke')
                    ->sortable(),
                Tables\Columns\TextColumn::make('awal_pendaftaran')
                    ->dateTime()
                    ->label('Awal')
                    ->sortable(),
                Tables\Columns\TextColumn::make('akhir_pendaftaran')
                    ->dateTime()
                    ->label('Akhir')
                    ->sortable(),
                Tables\Columns\TextColumn::make('pendaftar_count')
                    ->label('Jumlah Pendaftar')
                    ->counts('pendaftar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('link_grup')
                    ->url(fn (Model $record): ?string => $record->link_grup)
                    ->openUrlInNewTab()
                    ->label('Link Grup')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('timeline')
                    ->label('Jumlah Timeline')
                    ->getStateUsing(fn (Model $record): int => count($record->timeline ?? []))
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make()
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    // Optional: If using SoftDeletes on GelombangPendaftaran
    // public static function getEloquentQuery(): Builder
    // {
    //     return parent::getEloquentQuery()
    //         ->withoutGlobalScopes([
    //             SoftDeletingScope::class,
    //         ]);
    // }
}
