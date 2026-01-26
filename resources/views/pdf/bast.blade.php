@php
    setlocale(LC_TIME, 'id_ID.UTF-8');
    \Carbon\Carbon::setLocale('id');
    date_default_timezone_set('Asia/Jakarta');

    $logoPath = public_path('assets/images/logo_dinas_jember.png');
    $checkPath = public_path('assets/icons/check.png');
    $closePath = public_path('assets/icons/close.png');
    $icon = function ($condition, $value) use ($checkPath, $closePath) {
        if (is_null($condition)) {
            return null; // tidak tampil apa-apa
        }

        return $condition === $value ? $checkPath : $closePath;
    };
@endphp

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>BAST - {{ $penerimaan->no_surat }}</title>

    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 14px;
            line-height: 1.6;
        }

        @page {
            margin: 30px 40px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-logo {
            width: 120px;
            vertical-align: middle;
            text-align: center;
        }

        .header-text {
            text-align: center;
        }

        .header-text h1 {
            font-size: 20px;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-text h2 {
            font-size: 28px;
            margin: 0;
            font-weight: bold;
            text-transform: uppercase;
        }

        .header-text p {
            margin: 0;
            font-size: 14px;
        }

        hr {
            border: 1px solid #000;
            margin: 14px 0;
        }

        .title {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            margin-top: 20px;
        }

        .underline {
            text-decoration: underline;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .table-info td {
            padding: 2px;
            vertical-align: top;
        }

        .table-barang th,
        .table-barang td {
            border: 1px solid #000;
            padding: 3px;
            font-size: 14px;
        }

        .table-barang th {
            background: #4d60dc;
            color: #fff;
            text-align: center;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .signature-table td {
            width: 50%;
            text-align: center;
            padding-top: 60px;
        }

        .check-col {
            text-align: center;
            vertical-align: middle;
            padding-top: 20px;
            padding-bottom: 20px;
        }
    </style>
</head>

<body>

    <table class="header-table">
        <tr>
            <td class="header-logo">
                @if (file_exists($logoPath))
                    <img src="{{ $logoPath }}" style="height:120px;">
                @endif
            </td>
            <td class="header-text">
                <h1>PEMERINTAH KABUPATEN JEMBER</h1>
                <h2>RUMAH SAKIT DAERAH BALUNG</h2>
                <p>Jalan Rambipuji No.19 Balung 68161 Telp. 0336-621017 / 621595 / 623877 – Fax. 0336-623877</p>
                <p>Website: rsdbalung.jemberkab.go.id — Email: rsd.balung@jemberkab.go.id</p>
                <p><b>BALUNG – JEMBER</b></p>
            </td>
        </tr>
    </table>

    <hr>

    <div class="title">
        <span class="underline">BERITA ACARA SERAH TERIMA BARANG</span><br>
        NOMOR: {{ $penerimaan->no_surat }}
    </div>

    <p>
        Pada hari ini <b>{{ now()->translatedFormat('l, d F Y') }}</b>, bertempat di Rumah Sakit Daerah Balung,
        kami yang bertanda tangan di bawah ini:
    </p>

    @foreach ($penerimaan->detailPegawai as $dp)
        <table class="table-info" style="margin-bottom: 4px">
            <tr>
                <td width="120">Nama</td>
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

    <p style="margin-top: 12px">
        Berdasarkan Surat Keputusan Bupati Jember Nomor. 100.3.3.21681/35.09.61 I /2025 tanggal 31 Desember 2024 Tentang
        Pejabat Pengelola Anggaran BLUD Rumah Sakit Daerah Balung Jember Tahun Anggaran 2025, telah memeriksa hasil
        pekerjaan dengan teliti dan benar. Pengadaan Belanja {{ ucfirst($penerimaan->category->name) }} RSD Balung
        sumber dana DPA BLUD RSD Balung dengan kode belanja 5.1.02.01.01.0043 tahun
        {{ date('Y', strtotime($penerimaan->created_at)) }} sesuai nota pembelian dengan rincian sebagai berikut:
    </p>

    <table class="table-barang">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Volume</th>
                <th>Satuan</th>
                <th>Harga</th>
                <th>Total</th>
                <th>L</th>
                <th>TL</th>
                <th>B</th>
                <th>TB</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penerimaan->detailBarang as $i => $barang)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $barang->stok->name }}</td>
                    <td class="text-center">{{ $barang->quantity }}</td>
                    <td class="text-center">{{ $barang->stok->satuan->name }}</td>
                    <td class="text-right">{{ number_format($barang->harga, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($barang->total_harga, 0, ',', '.') }}</td>
                    <td class="check-col">
                        @if ($barang->is_layak)
                            <img src="{{ $checkPath }}" width="18">
                        @else
                            <img src="{{ $closePath }}" width="18">
                        @endif
                    </td>
                    <td class="check-col">
                        @if ($barang->is_layak)
                            <img src="{{ $closePath }}" width="18">
                        @else
                            <img src="{{ $checkPath }}" width="18">
                        @endif
                    </td>
                    <td class="check-col">
                        @if ($barang->is_layak)
                            <img src="{{ $checkPath }}" width="18">
                        @else
                            <img src="{{ $closePath }}" width="18">
                        @endif
                    </td>
                    <td class="check-col">
                        @if ($barang->is_layak)
                            <img src="{{ $closePath }}" width="18">
                        @else
                            <img src="{{ $checkPath }}" width="18">
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>
        <b>Total:</b> Rp {{ number_format($penerimaan->detailBarang->sum('total_harga'), 0, ',', '.') }} <br>
        <b>Terbilang:</b> ({{ terbilang($penerimaan->detailBarang->sum('total_harga')) }} rupiah)
    </p>

    <p>
        Bahwa PPK menyatakan MENERIMA laporan hasil pekerjaan tersebut dalam keadaan BAIK, sesuai dengan spesifikasi,
        mutu, kelengkapan dan kondisi nyata. Penyedia dapat menyerahkan kepada PPK dan langsung disimpan oleh Pengurus
        Barang. Demikianlah Berita Acara ini dibuat dengan sebenar-benarnya untuk dapat dipergunakan sebagaimana
        mestinya.
    </p>

    <table class="signature-table">
        <tr>
            <td>
                {{ optional($penerimaan->detailPegawai->first()->pegawai->jabatan)->name }}<br><br><br><br>
                <b class="underline">{{ optional($penerimaan->detailPegawai->first()->pegawai)->name }}</b><br>
                NIP. {{ optional($penerimaan->detailPegawai->first()->pegawai)->nip }}
            </td>
            <br>
            <td>
                {{ optional($penerimaan->detailPegawai->last()->pegawai->jabatan)->name }}<br><br><br><br>
                <b class="underline">{{ optional($penerimaan->detailPegawai->last()->pegawai)->name }}</b><br>
                NIP. {{ optional($penerimaan->detailPegawai->last()->pegawai)->nip }}
            </td>
        </tr>
    </table>

</body>

</html>
