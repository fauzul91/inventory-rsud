@php
    setlocale(LC_TIME, 'id_ID.UTF-8');
    \Carbon\Carbon::setLocale('id');
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8" />
    <title>BAST - {{ $penerimaan->no_surat }}</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        * {
            font-family: "Times New Roman", serif;
        }

        @page {
            margin: 30px 40px;
        }

        hr {
            border: 1px solid black;
        }
    </style>
</head>

<body class="text-[14px] leading-relaxed">
    <div class="text-center mb-2 flex items-center justify-center gap-4">
        <img src="{{ public_path('http://127.0.0.1:8001/assets/images/logo_dinas_jember.png') }}"
             class="w-[90px] h-auto" alt="Logo Dinas Jember">

        <div class="text-center max-w-[80%]">
            <div class="font-bold text-[24px] uppercase">PEMERINTAH KABUPATEN JEMBER</div>
            <div class="font-bold text-[32px] uppercase">RUMAH SAKIT DAERAH BALUNG</div>

            <div class="text-[12px]">
                Jalan Rambipuji No.19 Balung 68161
                Telp. 0336-621017 / 621595 / 623877 – Fax. 0336-623877
            </div>

            <div class="text-[12px]">
                Website: rsdbalung.jemberkab.go.id — Email: rsd.balung@jemberkab.go.id
            </div>

            <div class="font-bold text-[18px] uppercase mt-1">
                BALUNG – JEMBER
            </div>
        </div>
    </div>

    <hr class="my-3">

    <h3 class="text-center text-[20px] uppercase font-bold mt-4">
        <span class="underline">BERITA ACARA SERAH TERIMA BARANG</span> <br>
        NOMOR: {{ $penerimaan->no_surat }}
    </h3>

    <p class="mt-4">
        Pada hari ini <b>{{ now()->translatedFormat('l, d F Y') }}</b>, bertempat di Rumah Sakit Daerah Balung,
        kami yang bertanda tangan di bawah ini:
    </p>

    @foreach ($penerimaan->detailPegawai as $dp)
        <table class="w-full mt-3">
            <tr>
                <td class="w-40">Nama</td>
                <td>: {{ $dp->pegawai->name }}</td>
            </tr>
            <tr>
                <td>NIP</td>
                <td>: {{ $dp->pegawai->nip }}</td>
            </tr>
            <tr>
                <td>Jabatan</td>
                <td>: {{ $dp->pegawai->jabatan->name }}</td>
            </tr>
            <tr>
                <td>Alamat Satker</td>
                <td>: {{ $dp->alamat_staker }}</td>
            </tr>
        </table>
    @endforeach

    <p class="mt-4">
        Berdasarkan Surat Keputusan Bupati Jember Nomor. 100.3.3.21681/35.09.61 I /2025 tanggal 31 Desember 2024
        Tentang Pejabat Pengelola Anggaran BLUD Rumah Sakit Daerah Balung Jember Tahun Anggaran 2025, telah memeriksa
        hasil pekerjaan dengan teliti dan benar. Pengadaan Belanja Natura dan Pakan Natura - Belanja Makanan dan
        Minuman Bahan Basah RSD Balung sumber dana DPA BLUD RSD Balung dengan kode belanja 5.1.02.01.01.0043 tahun 2025
        sesuai nota pembelian dengan rincian sebagai berikut:
    </p>

    <table class="w-full border-collapse mt-3">
        <thead>
            <tr>
                <th class="border p-1 bg-[#4d60dc] text-white">No</th>
                <th class="border p-1 bg-[#4d60dc] text-white">Nama Barang</th>
                <th class="border p-1 bg-[#4d60dc] text-white">Volume</th>
                <th class="border p-1 bg-[#4d60dc] text-white">Satuan</th>
                <th class="border p-1 bg-[#4d60dc] text-white">Harga</th>
                <th class="border p-1 bg-[#4d60dc] text-white">Total Harga</th>
                <th class="border p-1 bg-[#4d60dc] text-white">Layak</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($penerimaan->detailBarang as $i => $barang)
                <tr>
                    <td class="border p-1 text-center">{{ $i + 1 }}</td>
                    <td class="border p-1">{{ $barang->stok->name }}</td>
                    <td class="border p-1 text-center">{{ $barang->quantity }}</td>
                    <td class="border p-1 text-center">{{ $barang->stok->satuan->name }}</td>
                    <td class="border p-1 text-right">{{ number_format($barang->harga) }}</td>
                    <td class="border p-1 text-right">{{ number_format($barang->total_harga) }}</td>
                    <td class="border p-1 text-center">{{ $barang->is_layak ? '✔' : '✘' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p class="mt-4">
        <b>Total:</b> Rp {{ number_format($penerimaan->detailBarang->sum('total_harga')) }} <br>
        <b>Terbilang:</b> ({{ terbilang($penerimaan->detailBarang->sum('total_harga')) }} rupiah)
    </p>

    <p>
        Bahwa PPK menyatakan MENERIMA laporan hasil pekerjaan tersebut dalam keadaan BAIK, sesuai dengan spesifikasi,
        mutu, kelengkapan dan kondisi nyata. Penyedia dapat menyerahkan kepada PPK dan langsung disimpan oleh Pengurus
        Barang.
    </p>

    <p>
        Demikianlah Berita Acara ini dibuat dengan sebenar-benarnya untuk dapat dipergunakan sebagaimana mestinya.
    </p>

    <table class="w-full mt-10">
        <tr>
            <td class="text-center w-1/2">

                {{ optional($penerimaan->detailPegawai->first()->pegawai->jabatan)->name }} <br><br><br><br>

                <b class="underline">
                    {{ optional($penerimaan->detailPegawai->first()->pegawai)->name ?? '____' }}
                </b> <br>

                NIP. {{ optional($penerimaan->detailPegawai->first()->pegawai)->nip ?? '-' }}
            </td>

            <td class="text-center w-1/2">

                {{ optional($penerimaan->detailPegawai->last()->pegawai->jabatan)->name }} <br><br><br><br>

                <b class="underline">
                    {{ optional($penerimaan->detailPegawai->last()->pegawai)->name ?? '____' }}
                </b> <br>

                NIP. {{ optional($penerimaan->detailPegawai->last()->pegawai)->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
