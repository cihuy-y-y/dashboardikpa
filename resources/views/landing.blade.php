<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring IKPA - Dashboard Publik</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #333;
        }

        header {
            background: #12345a;
            color: white;
            padding: 25px 7%;
        }

        header h1 {
            font-size: 25px;
            margin-bottom: 8px;
        }

        header p {
            color: #dce6f1;
        }

        .container {
            width: 86%;
            max-width: 1200px;
            margin: 30px auto;
        }

        .judul {
            margin-bottom: 20px;
        }

        .judul h2 {
            color: #12345a;
            margin-bottom: 8px;
        }

        .ringkasan {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        .card {
            background: white;
            padding: 22px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        .card p {
            color: #666;
            margin-bottom: 10px;
        }

        .card h3 {
            color: #12345a;
            font-size: 28px;
        }

        .pencarian {
            background: white;
            padding: 22px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .pencarian h3 {
            margin-bottom: 15px;
            color: #12345a;
        }

        .form-cari {
            display: flex;
            gap: 10px;
        }

        .form-cari input {
            flex: 1;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            background: #12345a;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #1d4d7e;
        }

        .bagian {
            background: white;
            padding: 22px;
            border-radius: 10px;
            margin-bottom: 25px;
            overflow-x: auto;
        }

        .bagian h3 {
            color: #12345a;
            margin-bottom: 18px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #eef3f8;
            color: #12345a;
        }

        .status {
            display: inline-block;
            padding: 6px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }

        .aman {
            background: #d8f3e2;
            color: #18743b;
        }

        .peringatan {
            background: #ffe0e0;
            color: #b42318;
        }

        .kosong {
            color: #777;
            padding: 15px 0;
        }

        footer {
            text-align: center;
            padding: 20px;
            color: #777;
            margin-top: 30px;
        }
                /* Header dan tombol Login */
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .btn-login {
            background: white;
            color: #12345a;
            padding: 11px 22px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            white-space: nowrap;
        }

        .btn-login:hover {
            background: #e5edf5;
        }

        @media (max-width: 600px) {
            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 600px) {
            .ringkasan {
                grid-template-columns: 1fr;
            }

            .container {
                width: 92%;
            }

            .form-cari {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="header-content">
        <div>
            <h1>Dashboard Monitoring IKPA</h1>
            <p>Informasi dan Pemantauan Kinerja Pelaksanaan Anggaran Satker</p>
        </div>

        <a href="{{ route('login') }}" class="btn-login">
            Login
        </a>
    </div>
</header>

<div class="container">

    <div class="judul">
        <h2>Informasi Kinerja Satker</h2>
        <p>Periode: {{ $periode ?? 'Belum tersedia' }}</p>
    </div>

    <div class="ringkasan">
        <div class="card">
            <p>Jumlah Satker</p>
            <h3>{{ number_format($jumlahSatker, 0, ',', '.') }}</h3>
        </div>

        <div class="card">
            <p>Rata-rata Nilai IKPA</p>
            <h3>{{ number_format($rataRata, 2, ',', '.') }}</h3>
        </div>
    </div>

    <div class="pencarian">
        <h3>Cari Data Satker</h3>

        <form action="{{ url('/') }}" method="GET" class="form-cari">
            <input
                type="text"
                name="cari"
                placeholder="Masukkan kode atau nama satker..."
                value="{{ $keyword }}"
            >

            <button type="submit">Cari Satker</button>
        </form>
    </div>

    @if($keyword)
        <div class="bagian">
            <h3>Hasil Pencarian: "{{ $keyword }}"</h3>

            @if($hasilPencarian->count() > 0)
                <table>
                    <thead>
                        <tr>
                            <th>No.</th>
                            <th>Kode Satker</th>
                            <th>Nama Satker</th>
                            <th>Nilai IKPA</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($hasilPencarian as $index => $satker)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $satker->kode_satker }}</td>
                                <td>{{ $satker->nama_satker }}</td>
                                <td>{{ number_format($satker->nilai_akhir, 2, ',', '.') }}</td>
                                <td>
                                    @if($satker->nilai_akhir >= 89)
                                        <span class="status aman">Memenuhi Target</span>
                                    @else
                                        <span class="status peringatan">Perlu Perhatian</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="kosong">Data satker tidak ditemukan.</p>
            @endif
        </div>
    @endif

    <div class="bagian">
        <h3>Data Satker Pilihan</h3>
        <p style="margin-bottom: 15px; color: #666;">
            Beberapa data satker ditampilkan secara acak dari periode terbaru.
        </p>

        @if($satkerAcak->count() > 0)
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Kode Satker</th>
                        <th>Nama Satker</th>
                        <th>Nilai IKPA</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($satkerAcak as $index => $satker)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $satker->kode_satker }}</td>
                            <td>{{ $satker->nama_satker }}</td>
                            <td>{{ number_format($satker->nilai_akhir, 2, ',', '.') }}</td>
                            <td>
                                @if($satker->nilai_akhir >= 89)
                                    <span class="status aman">Memenuhi Target</span>
                                @else
                                    <span class="status peringatan">Perlu Perhatian</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="kosong">Belum ada data satker untuk ditampilkan.</p>
        @endif
    </div>

</div>

<footer>
    Dashboard Monitoring IKPA
</footer>

</body>
</html>