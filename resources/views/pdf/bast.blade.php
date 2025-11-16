<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>BAST - {{ $penerimaan->no_surat }}</title>

    <style>
        body {
            font-family: "Times New Roman", serif;
            font-size: 14px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .kop-title {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .kop-sub {
            font-size: 14px;
        }
        hr {
            border: 1px solid black;
            margin: 10px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        table th, table td {
            border: 1px solid black;
            padding: 6px;
            text-align: left;
        }
        table th {
            background: #f2f2f2;
        }
        .no-border td {
            border: none !important;
        }
        .signature {
            margin-top: 40px;
            width: 100%;
        }
    </style>
</head>

<body>

    {{-- HEADER / KOP --}}
    <div class="header">
        <div class="kop-title">PEMERINTAH KABUPATEN JEMBER</div>
        <div class="kop-title">RUMAH SAKIT DAERAH BALUNG</div>
        <div class="kop-sub">
            Jalan Rambipuji No.19 Balung 68161  
            Telp. 0336-621017 / 621595 / 623877 – Fax. 0336-623877
        </div>
    </div>
    <hr>

    {{-- TITLE --}}
    <h3 style="text-align:center; text-transform:uppercase; margin-top:20px;">
        BERITA ACARA SERAH TERIMA BARANG <br>
        NOMOR: {{ $penerimaan->no_surat }}
    </h3>

    {{-- PARAGRAF PEMBUKA --}}
    <p>
        Pada hari ini <b>{{ now()->translatedFormat('l, d F Y') }}</b>, bertempat di Rumah Sakit Daerah Balung,
        kami yang bertanda tangan di bawah ini:
    </p>

    {{-- DATA PEGAWAI --}}
    @foreach ($penerimaan->detailPegawai as $dp)
        <table class="no-border">
            <tr><td>Nama</td><td>: {{ $dp->pegawai->name }}</td></tr>
            <tr><td>NIP</td><td>: {{ $dp->pegawai->nip }}</td></tr>
            <tr><td>Jabatan</td><td>: {{ $dp->pegawai->jabatan->name }}</td></tr>
            <tr><td>Alamat Satker</td><td>: {{ $dp->alamat_staker }}</td></tr>
        </table>
        <br>
    @endforeach

    {{-- PARAGRAF --}}
    <p>
        Berdasarkan pemeriksaan barang sesuai Surat / Nota pembelian, berikut rincian barang yang diterima:
    </p>

    {{-- TABEL BARANG --}}
    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Barang</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Total Harga</th>
                <th>Layak</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($penerimaan->detailBarang as $i => $barang)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $barang->stok->name }}</td>
                <td>{{ $barang->quantity }}</td>
                <td>{{ number_format($barang->harga) }}</td>
                <td>{{ number_format($barang->total_harga) }}</td>
                <td>{{ $barang->is_layak ? '✔' : '✘' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- TOTAL --}}
    <p style="margin-top:10px;">
        <b>Total:</b> Rp {{ number_format($penerimaan->detailBarang->sum('total_harga')) }}
        <br>
        <b>Terbilang:</b> ({{ terbilang($penerimaan->detailBarang->sum('total_harga')) }} rupiah)
    </p>

    {{-- TANDA TANGAN --}}
    <table class="signature">
        <tr>
            <td style="text-align:center;">
                Pejabat Pembuat Komitmen<br><br><br><br>
                <b>{{ $penerimaan->detailPegawai->first()->pegawai->name ?? '____' }}</b> <br>
                NIP. {{ $penerimaan->detailPegawai->first()->pegawai->nip ?? '-' }}
            </td>

            <td style="text-align:center;">
                Pengurus Barang Persediaan<br><br><br><br>
                <b>{{ $penerimaan->detailPegawai->last()->pegawai->name ?? '____' }}</b> <br>
                NIP. {{ $penerimaan->detailPegawai->last()->pegawai->nip ?? '-' }}
            </td>
        </tr>
    </table>

</body>
</html>
