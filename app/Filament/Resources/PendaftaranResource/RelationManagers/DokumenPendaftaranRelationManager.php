<?php
// app/Filament/Resources/PendaftaranResource/RelationManagers/DokumenPendaftaranRelationManager.php

namespace App\Filament\Resources\PendaftaranResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload; // <-- Added
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class DokumenPendaftaranRelationManager extends RelationManager
{
    protected static string $relationship = 'dokumenPendaftaran';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Dokumen Pendaftaran Wajib');
    }

    public function form(Form $form): Form
    {
        // Added Spatie File Upload to match the wizard form
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Dokumen')
                    ->columnSpan(2),
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->columnSpan(2),

                // --- Field Upload Spatie Media Library ---
                SpatieMediaLibraryFileUpload::make('template_dokumen_file') // Same temporary name as in wizard
                ->label('Template Dokumen (Opsional)')
                    ->collection('template') // Same collection name
                    ->reorderable()
                    ->columnSpanFull()
                    ->helperText('Unggah file template jika dokumen ini memilikinya.'),
            ])->columns(2);
    }

    public function table(Table $table): Table
    {
        // Added a column to indicate if a template exists
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('nama'),
                Tables\Columns\TextColumn::make('keterangan')
                    ->limit(50)
                    ->tooltip(fn (Model $record): ?string => $record->keterangan), // Show full text on hover
                Tables\Columns\IconColumn::make('template_exists')
                    ->label('Template?')
                    ->boolean()
                    ->getStateUsing(fn (Model $record): bool => $record->hasMedia('template_dokumen')), // Check if media exists
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
