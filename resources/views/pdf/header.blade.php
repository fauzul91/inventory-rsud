<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * {
            font-family: "Times New Roman", serif;
            margin: 0;
            padding: 0;
        }

        .header-container {
            width: 100%;
            text-align: center;
            margin-bottom: 10px;
        }

        .logo {
            width: 90px;
            height: auto;
            display: inline-block;
            vertical-align: middle;
            margin-right: 10px;
        }

        .header-text {
            display: inline-block;
            vertical-align: middle;
            text-align: center;
            max-width: 80%;
        }

        .title-big {
            font-size: 24px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .title-main {
            font-size: 32px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .subtitle {
            font-size: 12px;
        }

        .city {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 3px;
        }

        hr {
            border: 1px solid black;
            margin-top: 6px;
        }
    </style>
</head>

<body>

<div class="header-container">

    {{-- LOGO --}}
    <img class="logo" src="{{ public_path('assets/images/logo_dinas_jember.png') }}" alt="Logo Dinas Jember">

    {{-- TEKS --}}
    <div class="header-text">
        <div class="title-big">PEMERINTAH KABUPATEN JEMBER</div>
        <div class="title-main">RUMAH SAKIT DAERAH BALUNG</div>

        <div class="subtitle">
            Jalan Rambipuji No.19 Balung 68161  
            Telp. 0336-621017 / 621595 / 623877 – Fax. 0336-623877
        </div>

        <div class="subtitle">
            Website: rsdbalung.jemberkab.go.id — Email: rsd.balung@jemberkab.go.id
        </div>

        <div class="city">BALUNG – JEMBER</div>
    </div>
</div>

<hr>

</body>
</html>
