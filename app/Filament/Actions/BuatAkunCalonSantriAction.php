<?php

namespace App\Filament\Actions; // Or your appropriate namespace

use App\Models\Pendaftaran;
use App\Models\CalonSantri;
use App\Models\User;
use App\Models\BiodataSantri;
use App\Enums\UserRole;
use App\Enums\StatusPenerimaan;
use App\Enums\JenisKelamin;
use App\Enums\UsersStatus; // Assuming you have this for User status
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Tables\Actions\Action; // This is a Table Header Action
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Str;
use Filament\Support\Enums\MaxWidth;

class BuatAkunCalonSantriAction
{
    public static function make(): Action
    {
        return Action::make('buatAkunCalonSantri')
            ->label('Buat Akun Santri')
            ->icon('heroicon-o-user-plus')
            ->modalHeading('Transfer Calon Santri menjadi Santri Tetap')
            ->modalDescription('Pilih periode pendaftaran dan calon santri yang akan dijadikan santri tetap.')
            ->modalSubmitActionLabel('Proses Santri Terpilih')
            ->modalCancelActionLabel('Tutup')
            ->modalWidth(MaxWidth::SevenExtraLarge) // Adjust width as needed
            ->form([
                Wizard::make([
                    Wizard\Step::make('Pilih Pendaftaran')
                        ->description('Pilih periode pendaftaran untuk menampilkan calon santri.')
                        ->schema([
                            Select::make('pendaftaran_id')
                                ->label('Periode Pendaftaran (Tahun)')
                                ->options(Pendaftaran::orderBy('tahun', 'desc')->pluck('tahun', 'id'))
                                ->default(function () {
                                    // Default to Pendaftaran of the current year if exists
                                    return Pendaftaran::where('tahun', now()->year)->first()?->id;
                                })
                                ->live() // Important for dynamically updating the next step
                                ->required()
                                ->afterStateUpdated(fn (Set $set) => $set('selected_calon_santri_ids', [])), // Reset selection when pendaftaran changes
                        ]),
                    Wizard\Step::make('Pilih Calon Santri')
                        ->description('Pilih calon santri yang telah diterima untuk dijadikan santri tetap.')
                        ->schema([
                            CheckboxList::make('selected_calon_santri_ids')
                                ->label('Calon Santri Diterima')
                                ->options(function (Get $get): array {
                                    $pendaftaranId = $get('pendaftaran_id');
                                    if (!$pendaftaranId) {
                                        return [];
                                    }
                                    return CalonSantri::query()
                                        ->whereHas('penilaian', fn ($q) => $q->where('status_penerimaan', StatusPenerimaan::DITERIMA))
                                        ->whereHas('gelombangPendaftaran', fn ($q) => $q->where('pendaftaran_id', $pendaftaranId))
                                        ->orderBy('nama')
                                        ->pluck('nama', 'id')
                                        ->all();
                                })
                                ->searchable()
                                ->bulkToggleable()
                                ->columns(2) // Adjust columns as needed
                                ->gridDirection('row')
                                ->required() // Ensure at least one is selected if proceeding
                                ->visible(fn (Get $get) => filled($get('pendaftaran_id'))),
                            Placeholder::make('no_calon_santri_placeholder')
                                ->content('Tidak ada calon santri yang berstatus "diterima" pada periode pendaftaran ini atau periode belum dipilih.')
                                ->visible(function (Get $get): bool {
                                    $pendaftaranId = $get('pendaftaran_id');
                                    if (!$pendaftaranId) return true; // Show if no pendaftaran selected yet
                                    return !CalonSantri::query()
                                        ->whereHas('penilaian', fn ($q) => $q->where('status_penerimaan', StatusPenerimaan::DITERIMA))
                                        ->whereHas('gelombangPendaftaran', fn ($q) => $q->where('pendaftaran_id', $pendaftaranId))
                                        ->exists();
                                }),
                        ]),
                ])
                ->skippable() // Allows users to jump between steps if they've already filled previous ones
            ])
            ->action(function (array $data): void {
                $pendaftaranId = $data['pendaftaran_id'];
                $selectedCalonSantriIds = $data['selected_calon_santri_ids'];

                if (empty($selectedCalonSantriIds)) {
                    Notification::make()
                        ->title('Tidak Ada Calon Santri Terpilih')
                        ->body('Silakan pilih minimal satu calon santri untuk diproses.')
                        ->warning()
                        ->send();
                    return;
                }

                // Fetch all accepted calon santri for the selected pendaftaran to determine order for NIS generation
                $allAcceptedCalonSantriForPendaftaran = CalonSantri::query()
                    ->whereHas('penilaian', fn ($q) => $q->where('status_penerimaan', StatusPenerimaan::DITERIMA))
                    ->whereHas('gelombangPendaftaran', fn ($q) => $q->where('pendaftaran_id', $pendaftaranId))
                    ->orderBy('nama')
                    ->pluck('id') // Get an ordered list of IDs
                    ->toArray();

                $processedCount = 0;
                $errorCount = 0;
                $errorMessages = [];

                foreach ($selectedCalonSantriIds as $calonSantriId) {
                    $calonSantri = CalonSantri::with('gelombangPendaftaran.pendaftaran')->find($calonSantriId);

                    if (!$calonSantri) {
                        $errorMessages[] = "Calon santri dengan ID {$calonSantriId} tidak ditemukan.";
                        $errorCount++;
                        continue;
                    }

                    DB::beginTransaction();
                    try {
                        // --- Validation Checks ---
                        if (User::where('email', $calonSantri->email)->exists()) {
                            throw new \Exception("Email {$calonSantri->email} sudah terdaftar ({$calonSantri->nama}).");
                        }
                        if ($calonSantri->nomor_telepon && User::where('nomor_telepon', $calonSantri->nomor_telepon)->exists()) {
                            throw new \Exception("Nomor telepon {$calonSantri->nomor_telepon} sudah terdaftar ({$calonSantri->nama}).");
                        }
                        if ($calonSantri->nomor_induk_kependudukan && BiodataSantri::where('nomor_induk_kependudukan', $calonSantri->nomor_induk_kependudukan)->exists()) {
                            throw new \Exception("NIK {$calonSantri->nomor_induk_kependudukan} sudah terdaftar pada biodata santri lain ({$calonSantri->nama}).");
                        }

                        // --- Create User ---
                        $user = User::create([
                            'nama' => $calonSantri->nama,
                            'nama_panggilan' => $calonSantri->nama_panggilan,
                            'jenis_kelamin' => $calonSantri->jenis_kelamin, // Already an enum instance
                            'nomor_telepon' => $calonSantri->nomor_telepon,
                            'email' => $calonSantri->email,
                            'role' => UserRole::SANTRI,
                            'password' => Hash::make(Carbon::parse($calonSantri->tanggal_lahir)->format('dmY')),
                            'status' => UsersStatus::AKTIF, // Default status for new santri
                            'email_verified_at' => now(), // Assuming verification upon transfer
                        ]);
                        // If using Spatie/laravel-permission, assign role:
                        // $user->assignRole(UserRole::SANTRI->value);

                        // --- Generate Nomor Induk Santri (NIS) ---
                        $genderCode = $calonSantri->jenis_kelamin == JenisKelamin::LAKI_LAKI ? '01' : '02';
                        $orderIndex = array_search($calonSantri->id, $allAcceptedCalonSantriForPendaftaran);
                        if ($orderIndex === false) {
                             throw new \Exception("Tidak dapat menemukan urutan untuk {$calonSantri->nama} dalam daftar penerimaan.");
                        }
                        $orderNumber = str_pad($orderIndex + 1, 3, '0', STR_PAD_LEFT);
                        $nomorIndukSantri = "0102293{$genderCode}{$orderNumber}";

                        // --- Create BiodataSantri ---
                        $biodata = new BiodataSantri();
                        $calonSantriData = $calonSantri->attributesToArray(); // Get all attributes as an array

                        // Prepare data for biodata, excluding User specific fields handled above and ID/timestamps
                        $biodataFillable = $biodata->getFillable();
                        $dataToFillForBiodata = [];

                        foreach ($biodataFillable as $field) {
                            if (array_key_exists($field, $calonSantriData)) {
                                // Directly use the value from CalonSantri.
                                // Enum casting is handled by CalonSantri's $casts and BiodataSantri's $casts on save.
                                $dataToFillForBiodata[$field] = $calonSantri->{$field};
                            }
                        }

                        // Explicitly set fields for BiodataSantri
                        $biodata->fill($dataToFillForBiodata); // Fill common fields
                        $biodata->user_id = $user->id;
                        $biodata->nomor_induk_santri = $nomorIndukSantri;
                        $biodata->tahun_pendaftaran = $calonSantri->gelombangPendaftaran->pendaftaran->tahun;

                        // Ensure these are null as per requirement
                        $biodata->tanggal_lulus = null;
                        $biodata->tanggal_keluar = null;
                        $biodata->alasan_keluar = null;

                        // Save Biodata
                        $biodata->save();

                        DB::commit();
                        $processedCount++;
                    } catch (\Exception $e) {
                        DB::rollBack();
                        $errorMessages[] = "Gagal memproses {$calonSantri->nama}: " . $e->getMessage();
                        $errorCount++;
                    }
                }

                if ($processedCount > 0) {
                    Notification::make()
                        ->title("Berhasil Memproses Santri")
                        ->body("{$processedCount} calon santri telah berhasil dijadikan santri tetap.")
                        ->success()
                        ->send();
                }
                if ($errorCount > 0) {
                    Notification::make()
                        ->title("Terjadi Kesalahan")
                        ->body("Gagal memproses {$errorCount} calon santri. Detail:\n" . implode("\n", $errorMessages))
                        ->danger()
                        ->persistent() // Keep notification until dismissed
                        ->send();
                }
            });
    }
}
