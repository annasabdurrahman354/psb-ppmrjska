import React, { useState } from 'react';
import { Head, router, usePage } from '@inertiajs/react';

type CalonSantri = {
  id: number;
  nama: string;
  jenis_kelamin: string;
  tempat_lahir: string;
  tanggal_lahir: string;
  status_mubaligh: boolean;
  pernah_mondok: boolean;
  universitas: string;
  program_studi: string;
  angkatan_kuliah: number;
  status_kuliah: string;
  alamat: string;
  rt: string;
  rw: string;
  provinsi: string;
  kota: string;
  kecamatan: string;
  kelurahan: string;
  city: string;
  state_province: string;
  kode_pos: number;
  kelompok_sambung: string;
  desa_sambung: string;
  daerahSambung: string;
  mulai_mengaji: string;
  bahasa_makna: string;
  bahasa_harian: string;
  status_tinggal: string;
  anak_nomor: string;
  jumlah_saudara: string;
  nama_ayah: string;
  nomor_telepon_ayah: string;
  status_ayah: string;
  nama_ibu: string;
  nomor_telepon_ibu: string;
  status_ibu: string;
  hubungan_wali: string;
  tahun_pendaftaran: number;
  gelombang: number;
};

type PageProps = {
  santri: CalonSantri[];
  tahunList: number[];
  gelombangList: number[];
  selectedTahun?: number;
  selectedGelombang?: number;
};

export default function List() {
  const { santri, tahunList, gelombangList, selectedTahun, selectedGelombang } =
    usePage<PageProps>().props;

  // const [tahun, setTahun] = useState<number | ''>(selectedTahun ?? '');
  // const [gelombang, setGelombang] = useState<number | ''>(selectedGelombang ?? '');
  // const [jenisKelamin, setJenisKelamin] = useState<number | ''>(selectedTahun ?? '');


  // const handleFilter = () => {
  //   router.get(route('santrilist'), { tahun, gelombang, jenisKelamin }, { preserveState: true });
  // };

  const totalLaki = santri.filter((s) => s.jenis_kelamin.toLowerCase() === 'laki-laki').length;
  const totalPerempuan = santri.filter((s) => s.jenis_kelamin.toLowerCase() === 'perempuan').length;
  const totalSantri = santri.length;


  return (
    
    <div className="p-6 max-w-full mx-auto">
      <Head title="Data Calon Santri" />

      <div className="container mx-auto px-4 pt-4 pb-10 z-10 text-center">
        <div className="flex flex-col md:flex-row justify-between items-center mb-4 gap-2">
          
          {/* Logo di atas (di mobile) atau di kanan (di desktop) */}
          <div className="bg-white/90 p-2 rounded-full shadow-lg order-1 md:order-2">
            <div className="w-20 h-20 flex items-center justify-center text-white rounded-full">
              <img
                src="/logo.png"
                alt="PPM Roudlotul Jannah Logo"
                className="relative inline-flex h-20 w-20 z-10 object-cover drop-shadow"
              />
            </div>
          </div>

          {/* Teks di bawah logo (di mobile) atau di kiri (di desktop) */}
          <div className="flex-1 min-w-[250px] order-2 md:order-1">
            <h1 className="text-3xl md:text-4xl font-bold text-green-600 leading-tight">
              PPM Roudlotul Jannah Surakarta
            </h1>
          </div>
        </div>

        <p className="text-base md:text-lg italic font-medium text-zinc-700">
          Daftar Santri Baru yang Mendaftar
        </p>
      </div>



      {/* Filter */}
      {/* <div className="mb-6 flex gap-3">

        <select
          value={tahun}
          onChange={(e) => setTahun(e.target.value === '' ? '' : Number(e.target.value))}
          className="border rounded px-3 py-2"
        >
          <option value="">Semua Tahun</option>
          {tahunList.map((t) => (
            <option key={t} value={t}>{t}</option>
          ))}
        </select>


        <select
          value={gelombang}
          onChange={(e) => setGelombang(e.target.value === '' ? '' : Number(e.target.value))}
          className="border rounded px-3 py-2"
        >
          <option value="">Semua Gelombang</option>
          {gelombangList.map((g) => (
            <option key={g} value={g}>Gelombang {g}</option>
          ))}
        </select>

        <select
          value={jenisKelamin}
          onChange={(e) => setJenisKelamin(e.target.value === '' ? '' : Number(e.target.value))}
          className="border rounded px-3 py-2"
        >
          <option value="">Semua Jenis Kelamin</option>
          <option value="Laki-laki">Laki-laki</option>
          <option value="Perempuan">Perempuan</option>
        </select>


        <button
          onClick={handleFilter}
          className="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white px-5 py-2.5 rounded-lg shadow-md hover:from-blue-700 hover:to-blue-800 transition duration-300 ease-in-out"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            className="h-5 w-5"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L15 14.414V19a1 1 0 01-1.447.894l-4-2A1 1 0 019 17v-2.586L3.293 6.707A1 1 0 013 6V4z" />
          </svg>
          Filter
        </button>
      </div> */}

      {/* Tabel Data */}
      {santri.length === 0 ? (
        <p className="text-gray-500">Tidak ada data santri.</p>
      ) : (
        <div className="overflow-auto mb-16">
          <table className="table-auto border-collapse border border-gray-300 text-sm w-full">
            <thead className="bg-gray-200">
              <tr>
                {[
                  "No", "Tahun", "Gelombang", "Nama", "Jenis Kelamin", "Tempat Lahir", "Tanggal Lahir",
                  "Alamat", "Kelurahan", "Kecamatan", "Kabupaten", "Provinsi", "Kode Pos", "Universitas",
                  "Prodi", "Angkatan", "Status Kuliah", "Mubaligh", "Pernah Mondok", "Kelompok", "Desa",
                  "Daerah", "Mulai Mengaji", "Bahasa Makna", "Bahasa Harian", "Status Tinggal", "Anak Nomor",
                  "Jumlah Saudara", "Nama Ayah", "Status Ayah", "Nomor Telepon Ayah", "Nama Ibu", "Status Ibu",
                  "Nomor Telepon Ibu", "Hubungan Wali"
                ].map((col) => (
                  <th key={col} className="border px-2 py-1 whitespace-nowrap text-center">
                    {col}
                  </th>
                ))}
              </tr>
            </thead>
            <tbody>
              {santri.map((item, index) => (
                <tr key={item.id} className="hover:bg-gray-50">
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{index + 1}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.tahun_pendaftaran}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.gelombang}</td>
                  <td className="border px-2 py-1 whitespace-nowrap">{item.nama}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.jenis_kelamin}</td>
                  <td className="border px-2 py-1 whitespace-nowrap">{item.tempat_lahir}</td>
                  <td className="border px-2 py-1 whitespace-nowrap">{item.tanggal_lahir}</td>
                  <td className="border px-2 py-1 whitespace-nowrap">{item.alamat} RT {item.rt}/RW {item.rw}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.kelurahan}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.kecamatan}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.kota}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.provinsi}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.kode_pos}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.universitas}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.program_studi}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.angkatan_kuliah}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.status_kuliah}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.status_mubaligh ? 'Ya' : 'Tidak'}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.pernah_mondok ? 'Ya' : 'Tidak'}</td>
                  <td className="border px-2 py-1 whitespace-nowrap">{item.kelompok_sambung}</td>
                  <td className="border px-2 py-1 whitespace-nowrap">{item.desa_sambung}</td>
                  <td className="border px-2 py-1 whitespace-nowrap">{item.daerahSambung}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.mulai_mengaji}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.bahasa_makna}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.bahasa_harian}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.status_tinggal}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.anak_nomor}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.jumlah_saudara}</td>
                  <td className="border px-2 py-1 whitespace-nowrap">{item.nama_ayah}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.status_ayah}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.nomor_telepon_ayah}</td>
                  <td className="border px-2 py-1 whitespace-nowrap">{item.nama_ibu}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.status_ibu}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.nomor_telepon_ibu}</td>
                  <td className="border px-2 py-1 text-center whitespace-nowrap">{item.hubungan_wali}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <div className="mt-4 text-sm text-gray-700 w-full max-w-xs space-y-2">
        <div className="flex justify-between p-2 rounded shadow-sm">
          <span><strong>Total Laki-laki:</strong></span>
          <span>{totalLaki}</span>
        </div>
        <div className="flex justify-between p-2 rounded shadow-sm">
          <span><strong>Total Perempuan:</strong></span>
          <span>{totalPerempuan}</span>
        </div>
        <div className="flex justify-between p-2 rounded shadow-sm">
          <span><strong>Total Santri:</strong></span>
          <span>{totalSantri}</span>
        </div>
      </div>
    </div>
  );
}
