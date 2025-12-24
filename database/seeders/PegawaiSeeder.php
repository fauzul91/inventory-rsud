<?php

namespace Database\Seeders;

use App\Models\Pegawai;
use App\Models\Jabatan;
use Illuminate\Database\Seeder;

class PegawaiSeeder extends Seeder
{
    public function run()
    {
        $pegawais = [
            [
                'nama' => 'dr. Nurullah Hidajahningtyas, MM',
                'nip' => '197107022002122006',
                'jabatan' => 'Direktur',
                'status' => 'active',
            ],
            [
                'nama' => 'Didik Eko Pramono, SE, M.Si',
                'nip' => '197704112003121006',
                'jabatan' => 'Kepala Bidang Keuangan dan Perencanaan',
                'status' => 'active',
            ],
            [
                'nama' => 'Diyah Herawati, SE',
                'nip' => '197404122008012012',
                'jabatan' => 'Bendahara Pengeluaran BLUD',
                'status' => 'active',
            ],
            [
                'nama' => 'Listiana Rahmawati',
                'nip' => '19780512010012002',
                'jabatan' => 'Bendahara Penerimaan BLUD',
                'status' => 'active',
            ],
            [
                'nama' => 'dr. Siti Nurul Qomariyah, M.Kes',
                'nip' => '196802061996032004',
                'jabatan' => 'PeJabat Pelaksana Teknis Kegiatan 1',
                'status' => 'active',
            ],
            [
                'nama' => 'drg. Heruddin',
                'nip' => '198102052010011010',
                'jabatan' => 'PeJabat Pelaksana Teknis Kegiatan 2',
                'status' => 'active',
            ],
            [
                'nama' => 'Arik Dian Wahyudi, Amd.Kep',
                'nip' => '198101082008011006',
                'jabatan' => 'Pejabat Pembuat Komitmen (PPK) 1',
                'status' => 'active',
            ],
            [
                'nama' => 'dr. Rohmat Pujo Santoso',
                'nip' => '196704012000031004',
                'jabatan' => 'Pejabat Pembuat Komitmen (PPK) 2',
                'status' => 'active',
            ],
            [
                'nama' => 'Ns. Dasiyo, S.Kep',
                'nip' => '196810011989011001',
                'jabatan' => 'Pejabat Pembuat Komitmen (PPK) 3',
                'status' => 'active',
            ],
            [
                'nama' => 'Hari Basuki, S.Si.Apt',
                'nip' => '197402042006041015',
                'jabatan' => 'Pejabat Pembuat Komitmen (PPK) 4',
                'status' => 'active',
            ],
            [
                'nama' => 'dr. Heri Purwanto, MMRS',
                'nip' => '197503062006041011',
                'jabatan' => 'Pejabat Pembuat Komitmen (PPK) 5',
                'status' => 'active',
            ],
            [
                'nama' => 'Rangga Andri Ekananta, S.Kep.Ns',
                'nip' => '199405252019031012',
                'jabatan' => 'Pejabat Pembuat Komitmen (PPK) 6',
                'status' => 'active',
            ],
            [
                'nama' => 'Arik Faiqo, S.Farm.,Apt',
                'nip' => '198501192019032010',
                'jabatan' => 'Pejabat Pembuat Komitmen (PPK) 7',
                'status' => 'active',
            ],
            [
                'nama' => 'drg. Ita Roossinta',
                'nip' => '197902182010012008',
                'jabatan' => 'Pejabat Pembuat Komitmen (PPK) 8',
                'status' => 'active',
            ],
            [
                'nama' => 'Mochamad Arif Derit Lupawan, SKM',
                'nip' => '198406041080108063',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Siti Fatimah',
                'nip' => '1984030820100903030',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Dwi Putri Bastiyanti, SKL',
                'nip' => '199410072019032020',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Puspita Tri Lestari, SKM',
                'nip' => '198402222020110090',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Novi Tri Isyana, SE',
                'nip' => '198011252030105036',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Lestari Dwiningsih, S.Gz',
                'nip' => '199206102019032027',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Jihan Adinda Exsanti, A.Md.AB',
                'nip' => '200101232020123293',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Tunggul Akbar N, Amd.Tek.Med',
                'nip' => '198110151020208064',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Ahmad Khoirur Rofiq, SAP',
                'nip' => '198602101010913111',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Ariz Zafitri, A.Md.Kom',
                'nip' => '199204262020315147',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Henrita Atmaningrum, A.Md.Tek.Med',
                'nip' => '198408182006042018',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Siti Khotimah, A.Md.Farm',
                'nip' => '199601012022032002',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Ulfatul Munawaroh, S.Farm, Apt',
                'nip' => '199609032020621203',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Zulallul Iffah Muhayati, A.Md. Farm',
                'nip' => '199310072019032017',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Nurlatifah, S.KM',
                'nip' => '198806022019032011',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Yuliana Dwi Hartini, A.Md.Gz',
                'nip' => '197907202005012004',
                'jabatan' => 'Staf Pendukung Pengadaaan Barang/Jasa (PBJ)',
                'status' => 'active',
            ],
            [
                'nama' => 'Fitri Nurkhasanah, S.Tr',
                'nip' => '199412152019032028',
                'jabatan' => 'Pejabat Pengadaan 1',
                'status' => 'active',
            ],
            [
                'nama' => 'Ikartini Afandi, S.Farm, Apt',
                'nip' => '199804212022032002',
                'jabatan' => 'Pejabat Pengadaan 2',
                'status' => 'active',
            ],
            [
                'nama' => 'Masuko Tri Sutandio, S.Farm. Apt',
                'nip' => '199204292019031008',
                'jabatan' => 'Tim Teknis',
                'status' => 'active',
            ],
            [
                'nama' => 'Rahdiansah HA, Amd.KL',
                'nip' => '197802092005011008',
                'jabatan' => 'Tim Teknis',
                'status' => 'active',
            ],
            [
                'nama' => 'Dian Rizal Irvani, A.Md.TEM',
                'nip' => '199406122019031008',
                'jabatan' => 'Tim Teknis',
                'status' => 'active',
            ],
            [
                'nama' => 'Mohammad Indra Ferlani, S.Kom',
                'nip' => '198701252019031004',
                'jabatan' => 'Tim Teknis',
                'status' => 'active',
            ],
        ];

        foreach ($pegawais as $data) {
            $jabatan = Jabatan::where('name', $data['jabatan'])->first();

            Pegawai::create([
                'name' => $data['nama'],
                'nip' => $data['nip'],
                'status' => $data['status'],
                'jabatan_id' => $jabatan ? $jabatan->id : null,
            ]);
        }
    }
}