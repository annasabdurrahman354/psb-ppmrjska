<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kota;
use App\Models\Kecamatan;
use App\Models\Kelurahan;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse; // Gunakan JsonResponse untuk return API
use Illuminate\Support\Collection as LaravelCollection; // Alias

class OptionsController extends Controller
{
    // ... (method create(), store(), success() dari sebelumnya) ...

    /**
     * Mengambil data Kota berdasarkan provinsi_id.
     * Diharapkan request memiliki query parameter 'provinsi_id'.
     */
    public function getKota(Request $request): JsonResponse // Ubah return type
    {
        $provinsiId = $request->query('provinsi_id');

        // Validasi sederhana (opsional tapi bagus)
        if (!$provinsiId) {
            return response()->json([], 400); // Bad request jika parameter hilang
        }

        $kota = Kota::where('provinsi_id', $provinsiId)
            ->orderBy('nama')
            ->pluck('nama', 'id'); // Ambil nama dan id

        return response()->json($kota); // Return sebagai JSON
    }

    /**
     * Mengambil data Kecamatan berdasarkan kota_id.
     * Diharapkan request memiliki query parameter 'kota_id'.
     */
    public function getKecamatan(Request $request): JsonResponse // Ubah return type
    {
        $kotaId = $request->query('kota_id');

        if (!$kotaId) {
            return response()->json([], 400);
        }

        $kecamatan = Kecamatan::where('kota_id', $kotaId)
            ->orderBy('nama')
            ->pluck('nama', 'id');

        return response()->json($kecamatan);
    }

    /**
     * Mengambil data Kelurahan berdasarkan kecamatan_id.
     * Diharapkan request memiliki query parameter 'kecamatan_id'.
     */
    public function getKelurahan(Request $request): JsonResponse // Ubah return type
    {
        $kecamatanId = $request->query('kecamatan_id');

        if (!$kecamatanId) {
            return response()->json([], 400);
        }

        $kelurahan = Kelurahan::where('kecamatan_id', $kecamatanId)
            ->orderBy('nama')
            ->pluck('nama', 'id');

        return response()->json($kelurahan);
    }
}
