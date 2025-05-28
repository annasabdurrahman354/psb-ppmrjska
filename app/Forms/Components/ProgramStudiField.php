<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid; // Untuk layout internal
use Closure;
use Illuminate\Support\Str;

class ProgramStudiField extends Field
{
    protected string $view = 'forms.components.program-studi-field'; // Path ke custom Blade view

    // Opsi default untuk jenjang, bisa di-override
    protected array|Closure $jenjangOptions = [
        'D1' => 'D1',
        'D2' => 'D2',
        'D3' => 'D3',
        'D4' => 'D4',
        'S1' => 'S1',
        'S2' => 'S2',
        'S3' => 'S3',
        'Profesi' => 'Profesi',
        'Spesialis' => 'Spesialis',
    ];

    // Komponen internal
    public Select $jenjangComponent;
    public TextInput $namaProgramStudiComponent;

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure(); // Memanggil setUp()
        return $static;
    }

    protected function setUp(): void
    {
        parent::setUp();

        // State default untuk komponen ini adalah array
        $this->default(['jenjang' => null, 'nama_program_studi' => null]);

        // Mengubah state saat form dihidrasi (load dari model)
        $this->formatStateUsing(static function (ProgramStudiField $component, $state): array {
            if (is_string($state) && Str::contains($state, '-')) {
                [$jenjang, $nama_program_studi] = explode('-', $state, 2);
                return ['jenjang' => $jenjang, 'nama_program_studi' => $nama_program_studi];
            }
            // Jika state sudah berupa array (misalnya dari update Livewire) atau null/format salah
            return (is_array($state) && isset($state['jenjang']) && isset($state['nama_program_studi']))
                ? $state
                : ['jenjang' => null, 'nama_program_studi' => null];
        });

        // Mengubah state sebelum disimpan ke database
        // Menghapus 'static' dari closure agar $this dapat diakses
        $this->dehydrateStateUsing(function (): ?string {
            // $this sekarang merujuk ke instance ProgramStudiField
            $jenjang = $this->jenjangComponent->getState();
            $namaProgramStudi = $this->namaProgramStudiComponent->getState();

            // Hanya gabungkan jika kedua nilai tidak null (atau sesuai logika yang diinginkan)
            if ($jenjang !== null && $namaProgramStudi !== null) {
                return $jenjang . '-' . $namaProgramStudi;
            }
            // Jika salah satu atau keduanya null, mungkin Anda ingin mengembalikan null
            // atau string kosong, tergantung kebutuhan aplikasi Anda.
            // Jika field ini secara keseluruhan boleh null, maka kembalikan null.
            // Jika tidak, Anda mungkin perlu validasi tambahan atau nilai default.
            // Untuk contoh ini, kita kembalikan null jika salah satu kosong,
            // yang berarti field akan disimpan sebagai NULL di database.
            return null;
        });

        // Mendefinisikan komponen internal (anak)
        $this->jenjangComponent = Select::make('jenjang')
            ->hiddenLabel()
            ->options($this->getJenjangOptions())
            ->required() // Jenjang wajib diisi
            ->live(onBlur: true)
            ->columnSpan(['sm' => 1, 'md' => 1]); // live(onBlur: true) untuk mengurangi request

        $this->namaProgramStudiComponent = TextInput::make('nama_program_studi')
            ->hiddenLabel()
            ->required() // Jenjang wajib diisi
            ->live(onBlur: true)
            ->columnSpan(['sm' => 1, 'md' => 3]);

        // Menjadikan komponen internal sebagai anak dari field ini untuk manajemen state
        $this->childComponents([
            $this->jenjangComponent,
            $this->namaProgramStudiComponent,
        ]);

        // Mengatur layout untuk komponen anak menggunakan Grid di dalam schema field ini
        // Ini akan dirender oleh {{ $getChildComponentContainer() }} di view kustom
        $this->schema([
            Grid::make(['sm' => 1, 'md' => 4])
                ->schema([
                    $this->jenjangComponent,
                    $this->namaProgramStudiComponent,
                ]),
        ]);
    }

    /**
     * Mengatur opsi untuk select jenjang.
     */
    public function jenjangOptions(array|Closure $options): static
    {
        $this->jenjangOptions = $options;
        // Pastikan komponen anak juga diupdate jika sudah diinisialisasi
        if (isset($this->jenjangComponent)) {
            $this->jenjangComponent->options($this->getJenjangOptions());
        }
        return $this;
    }

    /**
     * Mendapatkan opsi jenjang yang telah dievaluasi.
     */
    public function getJenjangOptions(): array
    {
        return $this->evaluate($this->jenjangOptions);
    }
}
