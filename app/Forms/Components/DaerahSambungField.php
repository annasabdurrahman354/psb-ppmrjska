<?php

namespace App\Forms\Components;

use App\Models\Daerah;
use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Set;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class DaerahSambungField extends Field
{
    protected string $view = 'forms.components.program-studi-field'; // Path ke custom Blade view

    public TextInput $daerahSambungInput;
    public ?string $daerahSambungSuggestion = null;
    public $daerahSambungId = null;

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();
        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // State default untuk komponen ini adalah array
        $this->default(['daerahSambungInput' => null]);

        $this->formatStateUsing(static function (DaerahSambungField $component, $state): array {
            if ($state) {
                return Daerah::find($state)->id;
            }
            // Jika state sudah berupa array (misalnya dari update Livewire) atau null/format salah
            return ['daerahSambungInput' => null];
        });

        // Mengubah state sebelum disimpan ke database
        $this->dehydrateStateUsing(static function ($state): ?string {
            if ($this->daerahSambungId) {
                return $this->daerahSambungId;
            }
            return null;
        });

        // Mendefinisikan komponen internal (anak)
        $this->daerahSambungInput = TextInput::make('daerahSambungInput')
            ->label('Daerah Sambung')
            ->live(debounce: '500ms')
            ->afterStateUpdated(function (Set $set, ?string $state) {
                if (empty($state)) {
                    $this->daerahSambungSuggestion = null;
                    $set('daerah_sambung_id', null);
                    return;
                }

                // Gunakan Quest untuk fuzzy search pada model Daerah
                $bestMatch = Daerah::whereFuzzy('nama', $state)
                    ->orderByFuzzy('nama') // Urutkan berdasarkan relevansi
                    ->first();

                if ($bestMatch) {
                    $this->daerahSambungSuggestion = '<span style="color: green;">Daerah ditemukan: <strong>' . htmlspecialchars($bestMatch->nama) . '</strong>';
                    $this->daerahSambungId = $bestMatch->id;
                } else {
                    $this->daerahSambungSuggestion = '<span style="color: red;">Daerah tidak ditemukan.</span>';
                    $this->daerahSambungId = null;
                }
            })
            ->helperText(fn () => $this->daerahSambungSuggestion ? new HtmlString($this->daerahSambungSuggestion) : null)
            ->required() // Make the input field itself required
            ->rules([
                fn (): Closure => function (string $attribute, $value, Closure $fail) {
                    if ($this->daerahSambungSuggestion) {
                        dd($this->daerahSambungSuggestion);
                    }
                },
            ]);

        // Mengatur layout untuk komponen anak menggunakan Grid di dalam schema field ini
        // Ini akan dirender oleh {{ $getChildComponentContainer() }} di view kustom
        $this->schema([
            Grid::make(1)
                ->schema([
                    $this->daerahSambungInput,
                ]),
        ]);
    }
}
