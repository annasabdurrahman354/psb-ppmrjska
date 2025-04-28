<?php

namespace App\Filament\Resources;

use App\Enums\JenisKelamin;
use App\Enums\UsersStatus;
use App\Filament\Resources\SantriResource\Pages;
use App\Filament\Resources\SantriResource\RelationManagers;
use App\Models\Santri;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SantriResource extends Resource
{
    protected static ?string $model = Santri::class;
    protected static ?string $slug = 'santri';
    protected static ?string $modelLabel = 'Santri';
    protected static ?string $pluralModelLabel = 'Santri';
    protected static ?string $recordTitleAttribute = 'nama';
    protected static ?string $navigationLabel = 'Santri';
    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'Pengguna';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // --- Main Row Layout (Always Visible Part) ---
                Split::make([
                    // Avatar (Minimal Width)
                    //ImageColumn::make('avatar')
                    //    ->label('Avatar')
                    //    ->defaultImageUrl(url('/images/default-avatar.png'))
                    //    ->circular()
                    //    ->grow(false),

                    // Stack 1: Name, NIS, Current Class (Essential Info)
                    Stack::make([
                        TextColumn::make('nama')
                            ->label('Nama')
                            ->weight(FontWeight::Bold)
                            ->searchable()
                            ->sortable(),
                        TextColumn::make('biodata.nis')
                            ->label('NIS')
                            ->searchable()
                            ->color('gray')
                            ->size(TextColumn\TextColumnSize::Small)
                            ->copyable()
                            ->copyMessage('NIS copied')
                            ->placeholder('No NIS'),
                        // Display current class name - adjust relationship access if needed
                        TextColumn::make('kelasSantri.kelas.nama_kelas')
                            ->label('Current Class')
                            ->badge()
                            ->color('info')
                            ->size(TextColumn\TextColumnSize::Small)
                            ->placeholder('No Class Assigned')
                            ->visibleFrom('xs'), // Show even on smallest screens if available
                    ])->space(1),

                    // Stack 2: Gender, Status Tinggal, Status Pondok (Visible from Medium)
                    Stack::make([
                        IconColumn::make('biodata.jenis_kelamin')
                            ->label('Gender')
                            ->icon(fn ($state): string => match ($state) {
                                JenisKelamin::LAKI_LAKI => 'heroicon-o-user',
                                JenisKelamin::PEREMPUAN => 'heroicon-s-user',
                                default => 'heroicon-o-question-mark-circle',
                            })
                            ->tooltip(fn ($state): string => $state?->getLabel() ?? 'Unknown'),
                        TextColumn::make('biodata.status_tinggal')
                            ->label('Status Tinggal')
                            ->badge(),
                        TextColumn::make('status') // Added Status Pondok
                            ->label('Status')
                            ->badge(),
                    ])->space(1)
                        ->visibleFrom('md'), // Visible from medium screens

                    // Stack 3: Email & User Status (Visible from Large)
                    Stack::make([
                        TextColumn::make('email')
                            ->label('Email')
                            ->searchable()
                            ->icon('heroicon-m-envelope')
                            ->size(TextColumn\TextColumnSize::Small)
                            ->copyable()
                            ->copyMessage('Email copied')
                            ->placeholder('No Email'),
                        IconColumn::make('status')
                            ->label('User Status')
                            ->icon(fn ($state): string => match ($state) {
                                UsersStatus::AKTIF => 'heroicon-o-check-badge',
                                UsersStatus::NONAKTIF => 'heroicon-o-x-circle',
                                default => 'heroicon-o-question-mark-circle',
                            })
                            ->tooltip(fn ($state): string => $state?->getLabel() ?? 'Unknown'),
                    ])->space(1)
                        ->alignment(Alignment::End) // Align this stack to the end on larger screens
                        ->visibleFrom('lg'), // Visible from large screens

                ])->from('lg'), // Apply horizontal split layout from 'lg' breakpoint upwards

                // --- Collapsible Panel for Additional Details (Visible from lg) ---
                Panel::make([
                    Split::make([ // Use Split inside Panel for better arrangement
                        Stack::make([
                            TextColumn::make('biodata.tempat_lahir')
                                ->label('Place of Birth')
                                ->placeholder('N/A'),
                            TextColumn::make('biodata.tanggal_lahir')
                                ->label('Date of Birth')
                                ->date('d M Y')
                                ->placeholder('N/A'),
                            TextColumn::make('biodata.kota.nama')
                                ->label('City')
                                ->placeholder('N/A'),
                            TextColumn::make('biodata.alamat_lengkap')
                                ->label('Full Address')
                                ->wrap() // Allow address to wrap
                                ->placeholder('N/A'),
                        ])->space(1),

                        Stack::make([
                            TextColumn::make('biodata.nama_ayah')
                                ->label('Nama Ayah')
                                ->placeholder('N/A'),
                            TextColumn::make('biodata.nama_ibu')
                                ->label('Nama Ibu')
                                ->placeholder('N/A'),
                        ])->space(1),
                    ])->from('xl'), // Split inside panel applies from xl
                ])
                    ->collapsible() // Make the panel collapsible
                    ->collapsed(true) // Start collapsed by default
                    ->visibleFrom('lg'), // Only show the panel trigger from large screens upwards

            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                // Filter by Gender using the Enum
                SelectFilter::make('biodata.jenis_kelamin')
                    ->label('Gender')
                    ->options(JenisKelamin::class)
                    ->searchable(),

                // Filter by Current Class (adjust relationship if needed)
                SelectFilter::make('kelasSantri.kelas')
                    ->label('Current Class')
                    ->relationship('kelasSantri.kelas', 'nama_kelas') // Assumes 'nama_kelas' on Kelas model
                    ->searchable()
                    ->preload(),

                // Filter by User Status using the Enum
                SelectFilter::make('status')
                    ->label('User Status')
                    ->options(UsersStatus::class)
                    ->searchable(),

                // TrashedFilter::make(),
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
            ])
            ->defaultSort('nama', 'asc');
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
            'index' => Pages\ListSantris::route('/'),
            'create' => Pages\CreateSantri::route('/create'),
            'view' => Pages\ViewSantri::route('/{record}'),
            'edit' => Pages\EditSantri::route('/{record}/edit'),
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
