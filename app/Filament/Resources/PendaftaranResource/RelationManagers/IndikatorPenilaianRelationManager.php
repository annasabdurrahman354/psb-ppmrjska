<?php
// app/Filament/Resources/PendaftaranResource/RelationManagers/IndikatorPenilaianRelationManager.php

namespace App\Filament\Resources\PendaftaranResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class IndikatorPenilaianRelationManager extends RelationManager
{
    protected static string $relationship = 'indikatorPenilaian';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Indikator Penilaian');
    }

    public function form(Form $form): Form
    {
       return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255)
                    ->label('Nama Indikator'),
                Forms\Components\TextInput::make('bobot')
                    ->required()
                    ->numeric()
                    ->step(0.01)
                    ->minValue(0.01)
                    ->maxValue(1) // Individual bobot still max 1
                    ->label('Bobot Penilaian'),
            ]);
    }

    public function table(Table $table): Table
    {
        // Table definition remains the same
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                Tables\Columns\TextColumn::make('nama'),
                Tables\Columns\TextColumn::make('bobot')
                    ->numeric(2) // Display with 2 decimal places
                    ->sortable(),
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
