<?php

use Carbon\Carbon;
use Illuminate\Support\Str;

if(!function_exists('getCountryList')){

    function getCountryList(): array
    {
        // Negara pertama: Indonesia
        $primaryCountry = ['Indonesia'];

        // Negara tetangga Indonesia
        $neighborCountries = [
            'Malaysia',
            'Singapura',
            'Australia',
            'Filipina',
            'Papua Nugini',
            'Timor Leste',
            'Brunei',
            'Thailand',
            'Vietnam',
            'Kamboja',
            'Laos',
            'Myanmar'
        ];

        // Semua negara lain (daftar lengkap)
        $allCountries = [
            // Daftar semua negara dunia, A-Z
            'Afganistan',
            'Afrika Selatan',
            'Albania',
            'Algeria',
            'Amerika Serikat',
            'Andorra',
            'Angola',
            'Antigua dan Barbuda',
            'Arab Saudi',
            'Argentina',
            'Armenia',
            'Austria',
            'Azerbaijan',
            'Bahama',
            'Bahrain',
            'Bangladesh',
            'Barbados',
            'Belanda',
            'Belarus',
            'Belgia',
            'Belize',
            'Benin',
            'Bhutan',
            'Bolivia',
            'Bosnia dan Herzegovina',
            'Botswana',
            'Brasil',
            'Britania Raya',
            'Brunei', // Sudah di neighborCountries
            'Bulgaria',
            'Burkina Faso',
            'Burundi',
            'Chad',
            'Chili',
            'China',
            'Denmark',
            'Djibouti',
            'Dominika',
            'Ekuador',
            'El Salvador',
            'Eritrea',
            'Estonia',
            'Ethiopia',
            'Filipina', // Sudah di neighborCountries
            'Fiji',
            'Finlandia',
            'Gabon',
            'Gambia',
            'Georgia',
            'Ghana',
            'Grenada',
            'Guatemala',
            'Guinea',
            'Guinea-Bissau',
            'Guinea Khatulistiwa',
            'Guyana',
            'Haiti',
            'Honduras',
            'Hongaria',
            'India',
            'Indonesia', // Sudah di primaryCountry
            'Irak',
            'Iran',
            'Irlandia',
            'Islandia',
            'Israel',
            'Italia',
            'Jamaika',
            'Jepang',
            'Jerman',
            'Yordania',
            'Kazakhstan',
            'Kenya',
            'Kirgistan',
            'Kiribati',
            'Kolombia',
            'Komoro',
            'Kongo',
            'Republik Demokratik Kongo',
            'Korea Selatan',
            'Korea Utara',
            'Kosta Rika',
            'Kroasia',
            'Kuba',
            'Kuwait',
            'Latvia',
            'Lebanon',
            'Lesotho',
            'Liberia',
            'Libya',
            'Liechtenstein',
            'Lituania',
            'Luksemburg',
            'Madagaskar',
            'Makedonia Utara',
            'Maladewa',
            'Malawi',
            'Malaysia', // Sudah di neighborCountries
            'Mali',
            'Malta',
            'Maroko',
            'Marshall Islands',
            'Mauritania',
            'Mauritius',
            'Meksiko',
            'Mesir',
            'Mikronesia',
            'Moldova',
            'Monako',
            'Mongolia',
            'Montenegro',
            'Mozambik',
            'Myanmar', // Sudah di neighborCountries
            'Namibia',
            'Nauru',
            'Nepal',
            'Niger',
            'Nigeria',
            'Norwegia',
            'Oman',
            'Pakistan',
            'Palau',
            'Panama',
            'Pantai Gading',
            'Papua Nugini', // Sudah di neighborCountries
            'Paraguay',
            'Peru',
            'Polandia',
            'Portugal',
            'Qatar',
            'Republik Afrika Tengah',
            'Republik Ceko',
            'Republik Dominika',
            'Rumania',
            'Rusia',
            'Rwanda',
            'Saint Kitts dan Nevis',
            'Saint Lucia',
            'Saint Vincent dan Grenadine',
            'Samoa',
            'San Marino',
            'Sao Tome dan Principe',
            'Selandia Baru',
            'Senegal',
            'Serbia',
            'Seychelles',
            'Sierra Leone',
            'Singapura', // Sudah di neighborCountries
            'Siprus',
            'Slovakia',
            'Slovenia',
            'Solomon Islands',
            'Somalia',
            'Spanyol',
            'Sri Lanka',
            'Sudan',
            'Sudan Selatan',
            'Suriname',
            'Swaziland',
            'Swedia',
            'Swiss',
            'Syria',
            'Tajikistan',
            'Tanjung Verde',
            'Tanzania',
            'Thailand', // Sudah di neighborCountries
            'Timor Leste', // Sudah di neighborCountries
            'Togo',
            'Tonga',
            'Trinidad dan Tobago',
            'Tunisia',
            'Turki',
            'Turkmenistan',
            'Tuvalu',
            'Ukraina',
            'Uni Emirat Arab',
            'Uruguay',
            'Uzbekistan',
            'Vanuatu',
            'Vatican City',
            'Venezuela',
            'Vietnam', // Sudah di neighborCountries
            'Yaman',
            'Yunani',
            'Zambia',
            'Zimbabwe'
        ];

        // Hilangkan yang sudah masuk primaryCountry dan neighborCountries
        $filteredAllCountries = array_diff($allCountries, $primaryCountry, $neighborCountries);

        // Gabungkan semua
        $finalList = array_merge($primaryCountry, $neighborCountries, $filteredAllCountries);

        return $finalList;
    }
}

if(!function_exists('proper')){
    function proper($text) {
        if (is_null($text) || $text === '') {
            return '';
        }

        // Make all lowercase safely with UTF-8
        $text = mb_strtolower($text, 'UTF-8');

        // Use regex with Unicode support
        return preg_replace_callback('/(?:^|[^\p{L}\'])(\p{Ll})/u', function ($matches) {
            // Uppercase the captured letter
            return mb_strtoupper($matches[0], 'UTF-8');
        }, $text);
    }

}

if(!function_exists('formatNamaProper')) {
    function formatNamaProper($nama) {
        // Daftar gelar (case-insensitive)
        $gelar = [
            'H.', 'Hj.', 'Ir.', 'Dr.', 'dr.', 'Prof.', 'S.Pd.', 'M.T.', 'M.Sc.', 'M.Eng.', 'S.T.',
            'S.Kom.', 'S.H.', 'S.Sos.', 'S.E.', 'S.Ked.', 'S.Psi.', 'M.A.', 'M.Kom.', 'M.Si.',
            'Ph.D.', 'Sp.A.', 'Sp.B.', 'Sp.OG.', 'Sp.PD.', 'Sp.KK.', 'Sp.M.', 'Sp.THT.', 'Sp.Rad.',
            'Sp.JP.', 'Sp.An.', 'Sp.Or.', 'Sp.N.', 'KH.', 'Ust.', 'Ustz.', 'Tn.', 'Ny.', 'Nn.',
            'R.', 'Drs.', 'Drg.', 'Dra.', 'Lc.', 'H.C.', 'M.Pd.', 'M.B.A.', 'A.Md.', 'A.Md.Keb.',
            'A.Md.Farm.', 'B.Sc.', 'M.M.', 'S.IP.', 'S.Pt.', 'S.I.Kom.', 'S.Si.', 'S.Ap.', 'S.I.P.',
            'S.Tr.Kes.', 'S.Tr.T.', 'S.Tr.H.', 'S.Tr.P.', 'M.Ed.', 'D.D.S.', 'B.A.', 'M.Th.', 'D.Th.'
        ];

        $parts = explode(' ', $nama);
        $gelarDepan = [];
        $gelarBelakang = [];
        $namaUtama = [];

        // Deteksi gelar di awal
        foreach ($parts as $index => $part) {
            if (in_array(ucfirst(strtolower($part)), array_map('ucfirst', array_map('strtolower', $gelar)))) {
                $gelarDepan[] = $part;
            } else {
                $namaUtama = array_slice($parts, $index);
                break;
            }
        }

        // Deteksi gelar di akhir
        $namaSementara = [];
        foreach (array_reverse($namaUtama) as $index => $part) {
            if (in_array(ucfirst(strtolower($part)), array_map('ucfirst', array_map('strtolower', $gelar)))) {
                $gelarBelakang[] = $part;
            } else {
                $namaSementara = array_reverse(array_slice(array_reverse($namaUtama), $index));
                break;
            }
        }
        $gelarBelakang = array_reverse($gelarBelakang);
        $namaUtama = $namaSementara;

        // Format nama utama
        $namaProper = '';
        if (count($namaUtama) > 0) {
            $namaProper = ucfirst(strtolower($namaUtama[0])); // Kata pertama tetap utuh
            if (isset($namaUtama[1])) {
                $namaProper .= ' ' . ucfirst(strtolower($namaUtama[1])); // Kata kedua tetap utuh
            }
            for ($i = 2; $i < count($namaUtama); $i++) {
                $namaProper .= ' ' . strtoupper(substr($namaUtama[$i], 0, 1)) . '.'; // Sisanya disingkat
            }
        }

        // Gabungkan gelar dan nama
        return implode(' ', array_merge($gelarDepan, [$namaProper], $gelarBelakang));
    }
}

if(!function_exists('formattedDateTime')) {
    function formattedDateTime(string $date): string
    {
        return Carbon::parse($date)->translatedFormat('d F Y') . ' pukul ' .Carbon::parse($date)->translatedFormat('H.i');
    }
}

if(!function_exists('cant')) {
    function cant($abilities) {
        return !auth()->user()->can($abilities);
    }
}

if(!function_exists('can')) {
    function can($abilities) {
        return auth()->user()->can($abilities);
    }
}

if(!function_exists('getProgramStudiList')) {
    function getProgramStudiList(){
        return [
            "Administrasi Publik",
            "Agribisnis",
            "Agronomi",
            "Agroteknologi",
            "Akuntansi",
            "Arsitektur",
            "Bahasa Inggris",
            "Bahasa Mandarin",
            "Bahasa Mandarin dan Kebudayaan Tiongkok",
            "Bimbingan dan Konseling",
            "Biologi",
            "Biosain",
            "Bisnis Digital",
            "Budi Daya Ternak",
            "Demografi dan Pencatatan Sipil",
            "Desain Interior",
            "Desain Komunikasi Visual",
            "Ekonomi Pembangunan",
            "Ekonomi dan Studi Pembangunan",
            "Farmasi",
            "Fisika",
            "Hubungan Internasional",
            "Ilmu Administrasi Negara",
            "Ilmu Ekonomi",
            "Ilmu Fisika",
            "Ilmu Gizi",
            "Ilmu Hukum",
            "Ilmu Kedokteran",
            "Ilmu Keolahragaan",
            "Ilmu Kesehatan Masyarakat",
            "Ilmu Komunikasi",
            "Ilmu Lingkungan",
            "Ilmu Linguistik",
            "Ilmu Pendidikan",
            "Ilmu Pertanian",
            "Ilmu Sejarah",
            "Ilmu Tanah",
            "Ilmu Teknik Mesin",
            "Ilmu Teknik Sipil",
            "Ilmu Teknologi Pangan",
            "Informatika",
            "Kajian Budaya",
            "Kebidanan",
            "Kebidanan Terapan",
            "Kedokteran",
            "Kenotariatan",
            "Keperawatan Anestesiologi",
            "Keselamatan dan Kesehatan Kerja",
            "Keuangan dan Perbankan",
            "Kimia",
            "Komunikasi Terapan",
            "Kriya Seni",
            "Linguistik",
            "Manajemen",
            "Manajemen Administrasi",
            "Manajemen Bisnis",
            "Manajemen Pemasaran",
            "Manajemen Perdagangan",
            "Matematika",
            "Pendidikan Administrasi Perkantoran",
            "Pendidikan Akuntansi",
            "Pendidikan Bahasa dan Sastra Indonesia",
            "Pendidikan Bahasa Indonesia",
            "Pendidikan Bahasa Inggris",
            "Pendidikan Bahasa Jawa",
            "Pendidikan Bahasa dan Sastra Daerah",
            "Pendidikan Biologi",
            "Pendidikan Ekonomi",
            "Pendidikan Fisika",
            "Pendidikan Geografi",
            "Pendidikan Guru Pendidikan Anak Usia Dini",
            "Pendidikan Guru Sekolah Dasar",
            "Pendidikan Guru Sekolah Dasar (Kampus Kabupaten Kebumen)",
            "Pendidikan Guru Vokasi",
            "Pendidikan Ilmu Pengetahuan Alam",
            "Pendidikan Jasmani, Kesehatan dan Rekreasi",
            "Pendidikan Kepelatihan Olahraga",
            "Pendidikan Kimia",
            "Pendidikan Luar Biasa",
            "Pendidikan Matematika",
            "Pendidikan Pancasila dan Kewarganegaraan",
            "Pendidikan Pancasila dan Kewarganegaraan",
            "Pendidikan Profesi Bidan",
            "Pendidikan Profesi Guru",
            "Pendidikan Profesi Guru SD",
            "Pendidikan Sains",
            "Pendidikan Sejarah",
            "Pendidikan Seni",
            "Pendidikan Seni Rupa",
            "Pendidikan Sosiologi Antropologi",
            "Pendidikan Teknik Bangunan",
            "Pendidikan Teknik Informatika & Komputer",
            "Pendidikan Teknik Mesin",
            "Pengelolaan Hutan",
            "Penyuluhan Pembangunan",
            "Penyuluhan Pembangunan/Pemberdayaan Masyarakat",
            "Penyuluhan dan Komunikasi Pertanian",
            "Perencanaan Wilayah dan Kota",
            "Perpajakan",
            "Perpustakaan",
            "Peternakan",
            "Profesi Apoteker",
            "Profesi Dokter",
            "Program Profesi Insinyur",
            "Psikologi",
            "Sains Data",
            "Sastra Arab",
            "Sastra Daerah",
            "Sastra Indonesia",
            "Sastra Inggris",
            "Seni Rupa",
            "Seni Rupa Murni",
            "Sosiologi",
            "Statistika",
            "Teknik Elektro",
            "Teknik Industri",
            "Teknik Informatika",
            "Teknik Kimia",
            "Teknik Mesin",
            "Teknik Sipil",
            "Teknologi Hasil Pertanian",
            "Teknologi Pendidikan",
            "Usaha Perjalanan Wisata"
        ];
    }
}
