
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Monitoring IKPA - Dashboard Publik</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        /* =====================================
           RESET DAN DASAR
        ===================================== */

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f4f6f9;
            color: #333;
        }

        /* =====================================
           HEADER
        ===================================== */

        header {
            background: linear-gradient(120deg, #12345a, #1d4d7e);
            color: white;
            padding: 24px 7%;
            box-shadow: 0 4px 15px rgba(18, 52, 90, 0.12);
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-icon {
            width: 48px;
            height: 48px;
            display: flex;
            justify-content: center;
            align-items: center;
            background: rgba(255,255,255,0.14);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 12px;
            font-size: 23px;
        }

        header h1 {
            font-size: 23px;
            font-weight: 700;
            margin-bottom: 5px;
        }

        header p {
            color: #dce6f1;
            font-size: 12px;
        }

        .btn-login {
            display: inline-flex;
            align-items: center;
            gap: 9px;
            background: white;
            color: #12345a;
            padding: 11px 23px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 700;
            white-space: nowrap;
            transition: 0.3s ease;
        }

        .btn-login:hover {
            background: #e5edf5;
            transform: translateY(-2px);
        }

        /* =====================================
           CONTAINER
        ===================================== */

        .container {
            width: 86%;
            max-width: 1200px;
            margin: 35px auto;
        }

        /* =====================================
           JUDUL HALAMAN
        ===================================== */

        .judul {
            margin-bottom: 25px;
        }

        .judul-label {
            color: #1d4d7e;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1.5px;
            margin-bottom: 7px;
        }

        .judul h2 {
            color: #12345a;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 7px;
        }

        .judul p {
            color: #778493;
            font-size: 13px;
        }

        /* =====================================
           RINGKASAN STATISTIK
        ===================================== */

        .ringkasan {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
            margin-bottom: 30px;
        }

        .card {
            position: relative;
            overflow: hidden;
            display: flex;
            align-items: center;
            gap: 18px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            border: 1px solid #e8edf3;
            box-shadow: 0 4px 15px rgba(18, 52, 90, 0.05);
            transition: 0.3s ease;
        }

        .card:hover {
            transform: translateY(-4px);
            box-shadow: 0 9px 22px rgba(18, 52, 90, 0.10);
        }

        .card-icon {
            width: 55px;
            height: 55px;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #eef3f8;
            color: #12345a;
            border-radius: 12px;
            font-size: 25px;
        }

        .card p {
            color: #778493;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .card h3 {
            color: #12345a;
            font-size: 27px;
            font-weight: 700;
        }

        /* =====================================
           PENCARIAN
        ===================================== */

        .pencarian {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid #e8edf3;
            box-shadow: 0 4px 15px rgba(18, 52, 90, 0.05);
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 18px;
        }

        .section-heading i {
            color: #1d4d7e;
            font-size: 20px;
        }

        .section-heading h3 {
            color: #12345a;
            font-size: 16px;
            font-weight: 700;
        }

        .form-cari {
            display: flex;
            gap: 12px;
        }

        .form-cari input {
            flex: 1;
            min-width: 0;
            padding: 13px 15px;
            border: 1px solid #dce4ed;
            border-radius: 8px;
            background: #f8fafc;
            outline: none;
            font-size: 12px;
            transition: 0.3s ease;
        }

        .form-cari input:focus {
            background: white;
            border-color: #1d4d7e;
            box-shadow: 0 0 0 3px rgba(29, 77, 126, 0.10);
        }

        .form-cari button {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            background: #12345a;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .form-cari button:hover {
            background: #1d4d7e;
        }

        /* =====================================
           BAGIAN TABEL
        ===================================== */

        .bagian {
            background: white;
            padding: 25px;
            border-radius: 12px;
            margin-bottom: 25px;
            overflow-x: auto;
            border: 1px solid #e8edf3;
            box-shadow: 0 4px 15px rgba(18, 52, 90, 0.05);
        }

        .bagian h3 {
            color: #12345a;
            font-size: 16px;
            font-weight: 700;
        }

        .bagian-deskripsi {
            margin: 7px 0 20px;
            color: #778493;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 650px;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #edf0f4;
            text-align: left;
            font-size: 12px;
        }

        th {
            background: #eef3f8;
            color: #12345a;
            font-weight: 700;
        }

        td {
            color: #596675;
        }

        tbody tr:hover {
            background: #f8fafc;
        }

        /* =====================================
           STATUS
        ===================================== */

        .status {
            display: inline-block;
            padding: 6px 11px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }

        .aman {
            background: #e0f4e8;
            color: #18743b;
        }

        .peringatan {
            background: #fff0f0;
            color: #b42318;
        }

        .kosong {
            color: #778493;
            padding: 20px 0;
            font-size: 13px;
        }

        /* =====================================
           FOOTER
        ===================================== */

        footer {
            text-align: center;
            padding: 25px 15px;
            color: #778493;
            font-size: 11px;
            margin-top: 35px;
            border-top: 1px solid #e8edf3;
            background: white;
        }

        footer strong {
            color: #12345a;
        }

        /* =====================================
           ANIMASI SCROLL REVEAL
        ===================================== */

        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition:
                opacity 0.6s ease,
                transform 0.6s ease;
        }

        .reveal.show {
            opacity: 1;
            transform: translateY(0);
        }

        @media (prefers-reduced-motion: reduce) {
            .reveal {
                opacity: 1;
                transform: none;
                transition: none;
            }

            .card,
            .btn-login,
            .form-cari button {
                transition: none;
            }
        }

        /* =====================================
           RESPONSIVE
        ===================================== */

        @media (max-width: 768px) {

            header {
                padding: 22px 5%;
            }

            .container {
                width: 92%;
            }

            .header-content {
                align-items: flex-start;
            }

            header h1 {
                font-size: 19px;
            }

            .ringkasan {
                gap: 15px;
            }

            .card {
                padding: 20px;
            }

        }

        @media (max-width: 600px) {

            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-title {
                align-items: flex-start;
            }

            .ringkasan {
                grid-template-columns: 1fr;
            }

            .judul h2 {
                font-size: 20px;
            }

            .form-cari {
                flex-direction: column;
            }

            .form-cari button {
                width: 100%;
            }

            .bagian,
            .pencarian {
                padding: 18px;
            }

        }
    </style>
</head>

<body>

{{-- HEADER --}}
<header>
    <div class="header-content">

        <div class="header-title">
            <div class="header-icon">
                <i class="bi bi-bar-chart-line-fill"></i>
            </div>

            <div>
                <h1>Dashboard Monitoring IKPA</h1>
                <p>Informasi dan Pemantauan Kinerja Pelaksanaan Anggaran Satker</p>
            </div>
        </div>

        <a href="{{ route('login') }}" class="btn-login">
            <i class="bi bi-box-arrow-in-right"></i>
            Login
        </a>

    </div>
</header>

{{-- KONTEN --}}
<div class="container">

    {{-- JUDUL --}}
    <div class="judul reveal">

        <div class="judul-label">
            INFORMASI PUBLIK
        </div>

        <h2>Informasi Kinerja Satker</h2>

        <p>
            Periode: {{ $periode ?? 'Belum tersedia' }}
        </p>

    </div>

    {{-- RINGKASAN --}}
    <div class="ringkasan">

        <div class="card reveal">

            <div class="card-icon">
                <i class="bi bi-buildings"></i>
            </div>

            <div>
                <p>Jumlah Satker</p>

                <h3>
                    {{ number_format($jumlahSatker, 0, ',', '.') }}
                </h3>
            </div>

        </div>

        <div class="card reveal">

            <div class="card-icon">
                <i class="bi bi-graph-up-arrow"></i>
            </div>

            <div>
                <p>Rata-rata Nilai IKPA</p>

                <h3>
                    {{ number_format($rataRata, 2, ',', '.') }}
                </h3>
            </div>

        </div>

    </div>

    {{-- PENCARIAN --}}
    <div class="pencarian reveal">

        <div class="section-heading">
            <i class="bi bi-search"></i>
            <h3>Cari Data Satker</h3>
        </div>

        <form action="{{ url('/') }}" method="GET" class="form-cari">

            <input
                type="text"
                name="cari"
                placeholder="Masukkan kode atau nama satker..."
                value="{{ $keyword }}"
            >

            <button type="submit">
                <i class="bi bi-search"></i>
                Cari Satker
            </button>

        </form>

    </div>

    {{-- HASIL PENCARIAN --}}
    @if($keyword)

        <div class="bagian reveal">

            <div class="section-heading">
                <i class="bi bi-list-check"></i>
                <h3>Hasil Pencarian: "{{ $keyword }}"</h3>
            </div>

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

                                <td>
                                    {{ number_format($satker->nilai_akhir, 2, ',', '.') }}
                                </td>

                                <td>
                                    @if($satker->nilai_akhir >= 89)
                                        <span class="status aman">
                                            Memenuhi Target
                                        </span>
                                    @else
                                        <span class="status peringatan">
                                            Perlu Perhatian
                                        </span>
                                    @endif
                                </td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>

            @else

                <p class="kosong">
                    Data satker tidak ditemukan.
                </p>

            @endif

        </div>

    @endif

    {{-- DATA SATKER PILIHAN --}}
    <div class="bagian reveal">

        <div class="section-heading">
            <i class="bi bi-table"></i>
            <h3>Data Satker Pilihan</h3>
        </div>

        <p class="bagian-deskripsi">
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

                            <td>
                                {{ number_format($satker->nilai_akhir, 2, ',', '.') }}
                            </td>

                            <td>
                                @if($satker->nilai_akhir >= 89)
                                    <span class="status aman">
                                        Memenuhi Target
                                    </span>
                                @else
                                    <span class="status peringatan">
                                        Perlu Perhatian
                                    </span>
                                @endif
                            </td>
                        </tr>

                    @endforeach

                </tbody>

            </table>

        @else

            <p class="kosong">
                Belum ada data satker untuk ditampilkan.
            </p>

        @endif

    </div>

</div>

{{-- FOOTER --}}
<footer>
    <strong>Dashboard Monitoring IKPA</strong>
    <p>Informasi dan Pemantauan Kinerja Pelaksanaan Anggaran Satker</p>
</footer>

{{-- JAVASCRIPT SCROLL REVEAL --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    const elements = document.querySelectorAll('.reveal');

    if (!('IntersectionObserver' in window)) {
        elements.forEach(element => {
            element.classList.add('show');
        });
        return;
    }

    const observer = new IntersectionObserver((entries) => {

        entries.forEach(entry => {

            if (entry.isIntersecting) {

                entry.target.classList.add('show');

                observer.unobserve(entry.target);

            }

        });

    }, {
        threshold: 0.15
    });

    elements.forEach(element => {
        observer.observe(element);
    });

});
</script>

</body>
</html>
