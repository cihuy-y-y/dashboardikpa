<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script>document.documentElement.className += " js";</script>
    <title>Dashboard IKPA - Satuan Kerja Kabupaten Solok</title>
    <meta name="description" content="Lihat nilai Indikator Kinerja Pelaksanaan Anggaran (IKPA) satuan kerja di Kabupaten Solok.">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/logo.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;1,700;1,800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/landing.css') }}?v=3">
</head>
<body>
@php
    $fmt = fn ($n, $d = 2) => number_format((float) $n, $d, ',', '.');
    $labelPeriode = $periode ? formatLabelPeriode($periode) : 'Belum tersedia';
    $persenMemenuhi = $jumlahSatker > 0 ? round($jumlahMemenuhi / $jumlahSatker * 100) : 0;
    $labelList = $tren->map(fn ($t) => formatLabelPeriode($t->periode))->values()->all();
    $nilaiList = $tren->map(fn ($t) => (float) $t->rata_rata)->values()->all();
    $nTren = count($nilaiList);
    $persenExact = $jumlahSatker > 0 ? $jumlahMemenuhi / $jumlahSatker * 100 : 0;

    // ---- Grafik tren (SVG dibuat di server, tanpa Chart.js) ----
    $grafik = $nTren > 0 ? true : null;
    if ($nTren > 0) {
        $W = 640; $H = 300; $kiri = 46; $kanan = 26; $atas = 24; $bawah = 46;
        $semua = array_merge($nilaiList, [(float) $target]);
        $yMin = max(0, floor(min($semua) / 5) * 5 - 5);
        $yMax = min(100, ceil(max($semua) / 5) * 5 + 5);
        if ($yMax - $yMin < 10) { $yMin = max(0, $yMax - 10); }
        $lebar = $W - $kiri - $kanan;
        $tinggi = $H - $atas - $bawah;
        $px = fn ($i) => $nTren === 1 ? $kiri + $lebar / 2 : $kiri + $lebar * $i / ($nTren - 1);
        $py = fn ($v) => $atas + $tinggi * (1 - ($v - $yMin) / ($yMax - $yMin));

        $titik = [];
        foreach ($nilaiList as $i => $v) {
            $titik[] = ['x' => round($px($i), 1), 'y' => round($py($v), 1), 'v' => $v, 'l' => $labelList[$i]];
        }

        $garis = 'M' . $titik[0]['x'] . ' ' . $titik[0]['y'];
        for ($i = 1; $i < $nTren; $i++) {
            $cx = round(($titik[$i - 1]['x'] + $titik[$i]['x']) / 2, 1);
            $garis .= ' C' . $cx . ' ' . $titik[$i - 1]['y'] . ' ' . $cx . ' ' . $titik[$i]['y'] . ' ' . $titik[$i]['x'] . ' ' . $titik[$i]['y'];
        }
        $dasar = $atas + $tinggi;
        $area = $garis . ' L' . $titik[$nTren - 1]['x'] . ' ' . $dasar . ' L' . $titik[0]['x'] . ' ' . $dasar . ' Z';

        $grid = [];
        for ($v = $yMin; $v <= $yMax; $v += 5) { $grid[] = ['y' => round($py($v), 1), 'v' => $v]; }

        $yTarget = round($py($target), 1);
        $langkahLabel = max(1, (int) ceil($nTren / 8));
        $singkat = fn ($l) => mb_substr($l, 0, 3) . ' ' . mb_substr($l, -4);
    }
@endphp

{{-- ============================ NAVBAR ============================ --}}
<header class="nav" id="nav">
    <div class="nav__bar">
        <a href="#beranda" class="nav__brand" aria-label="Dashboard IKPA">
            <img src="{{ asset('assets/img/login/logo-instansi.png') }}" alt="Kementerian Keuangan RI, DJPb, InTrezz">
        </a>

        <nav class="nav__links" id="navLinks" aria-label="Menu utama">
            <a href="#beranda">Beranda</a>
            <a href="#statistik">Statistik</a>
            <a href="#peringkat">Peringkat</a>
            <a href="#daftar">Daftar Satker</a>
            <a href="#tentang">Tentang IKPA</a>
            <div class="nav__actions nav__actions--mobile">
                @auth
                    <a href="{{ route('beranda') }}" class="btn btn--primary"><i class="bi bi-speedometer2"></i> Dashboard</a>
                @else
                    <a href="{{ route('register') }}" class="btn btn--ghost"><i class="bi bi-person-plus-fill"></i> Register</a>
                    <a href="{{ route('login') }}" class="btn btn--primary"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                @endauth
            </div>
        </nav>

        <div class="nav__actions nav__actions--desktop">
            @auth
                <a href="{{ route('beranda') }}" class="btn btn--primary"><i class="bi bi-speedometer2"></i> Dashboard</a>
            @else
                <a href="{{ route('register') }}" class="btn btn--ghost"><i class="bi bi-person-plus-fill"></i> Register</a>
                <a href="{{ route('login') }}" class="btn btn--primary"><i class="bi bi-box-arrow-in-right"></i> Login</a>
            @endauth
        </div>

        <button class="nav__toggle" id="navToggle" aria-label="Buka menu" aria-expanded="false">
            <i class="bi bi-list"></i>
        </button>
    </div>
</header>

{{-- ============================== HERO ============================== --}}
<section class="hero" id="beranda">
    <svg class="hero__waves" viewBox="0 0 1672 941" preserveAspectRatio="none" aria-hidden="true">
        <defs>
            <linearGradient id="lBright" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#2f8bff"/><stop offset="1" stop-color="#0a55d8"/>
            </linearGradient>
            <linearGradient id="lNavy" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#0a4bc4"/><stop offset="1" stop-color="#012a6e"/>
            </linearGradient>
            <linearGradient id="lHill" x1="0" y1="0" x2="1" y2="1">
                <stop offset="0" stop-color="#9ec9ff" stop-opacity=".9"/><stop offset="1" stop-color="#eef5ff" stop-opacity=".95"/>
            </linearGradient>
        </defs>
        <path d="M900 330 C1050 190 1250 100 1420 108 C1540 114 1640 160 1672 190 L1672 400 Z" fill="url(#lHill)"/>
        <path d="M900 235 C1000 175 1100 70 1165 0 L1240 0 C1180 85 1090 170 1015 212 Z" fill="#b4d3ff" opacity=".85"/>
        <path d="M1015 212 C1110 160 1210 70 1285 0 L1545 0 C1430 80 1310 135 1195 172 C1130 192 1070 205 1015 212 Z" fill="url(#lBright)"/>
        <path d="M1105 190 C1260 130 1430 58 1565 0 L1672 0 L1672 200 C1570 128 1430 108 1305 135 C1225 152 1155 172 1105 190 Z" fill="url(#lNavy)"/>
    </svg>

    <div class="dots dots--a"></div>

    <div class="wrap hero__grid">
        <div class="hero__text reveal">
            <span class="chip"><i class="bi bi-calendar-check-fill"></i> Data Periode {{ $labelPeriode }}</span>

            <p class="hero__hi">Selamat Datang di</p>
            <h1 class="hero__title">Dashboard <span>IKPA</span></h1>
            <p class="hero__area"><i class="bi bi-geo-alt-fill"></i> Satuan Kerja Kabupaten Solok</p>

            <p class="hero__tagline">Transparansi <i>&bull;</i> Akuntabilitas <i>&bull;</i> Kinerja Lebih Baik</p>
            <p class="hero__desc">
                Lihat nilai Indikator Kinerja Pelaksanaan Anggaran (IKPA) satuan kerja Anda,
                bandingkan dengan target, dan pantau perkembangannya setiap periode.
            </p>

            <form class="search" id="heroSearch" action="{{ route('landing') }}#daftar" method="GET" role="search">
                @if ($periode)
                    <input type="hidden" name="periode" value="{{ $periode }}">
                @endif
                <i class="bi bi-search search__icon"></i>
                <input type="text" name="cari" id="heroCari" value="{{ $keyword }}"
                       placeholder="Ketik kode atau nama satker Anda..." autocomplete="off"
                       aria-label="Cari satker">
                <button type="submit" class="btn btn--primary">Cari Nilai</button>
            </form>

            <div class="hero__links">
                <a href="#peringkat"><i class="bi bi-trophy-fill"></i> Lihat peringkat</a>
                <a href="#tentang"><i class="bi bi-info-circle-fill"></i> Apa itu IKPA?</a>
                <button type="button" class="hero__ask" data-buka-asisten><i class="bi bi-chat-dots-fill"></i> Tanya Asisten IKPA</button>
            </div>
        </div>

        <div class="hero__visual reveal">
            <img src="{{ asset('assets/img/login/gedung-kppn.png') }}" alt="Gedung KPPN Solok" class="hero__building">

            <div class="float float--a">
                <div class="float__icon"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <small>Rata-rata IKPA</small>
                    <strong>{{ $fmt($rataRata) }}</strong>
                    <em>Target &ge; {{ $target }}</em>
                </div>
            </div>

            <div class="float float--b">
                <div class="float__icon float__icon--green"><i class="bi bi-patch-check-fill"></i></div>
                <div>
                    <small>Memenuhi target</small>
                    <strong>{{ $jumlahMemenuhi }} <span>dari {{ $jumlahSatker }} satker</span></strong>
                    <div class="mini"><i style="width: {{ $persenMemenuhi }}%"></i></div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ============================ STATISTIK ============================ --}}
<main>
<section class="wrap stats" id="statistik">
    <div class="stats__head reveal">
        <div>
            <span class="eyebrow">Ringkasan</span>
            <h2>Statistik Nilai IKPA</h2>
        </div>

        @if ($daftarPeriode->count() > 0)
            <form method="GET" action="{{ route('landing') }}#statistik" class="period">
                <label for="periodeSelect"><i class="bi bi-calendar3"></i> Periode</label>
                <select name="periode" id="periodeSelect" onchange="this.form.submit()">
                    @foreach ($daftarPeriode as $p)
                        <option value="{{ $p }}" {{ $p == $periode ? 'selected' : '' }}>{{ formatLabelPeriode($p) }}</option>
                    @endforeach
                </select>
                @if ($keyword !== '')
                    <input type="hidden" name="cari" value="{{ $keyword }}">
                @endif
                <noscript><button class="btn btn--primary" type="submit">Tampilkan</button></noscript>
            </form>
        @endif
    </div>

    <div class="stats__grid">
        <article class="stat reveal">
            <div class="stat__icon"><i class="bi bi-buildings-fill"></i></div>
            <div>
                <small>Jumlah Satker</small>
                <strong data-count="{{ $jumlahSatker }}" data-decimals="0">{{ $fmt($jumlahSatker, 0) }}</strong>
                <em>Terdata pada periode ini</em>
            </div>
        </article>

        <article class="stat reveal">
            <div class="stat__icon stat__icon--blue"><i class="bi bi-bar-chart-line-fill"></i></div>
            <div>
                <small>Rata-rata Nilai IKPA</small>
                <strong data-count="{{ round($rataRata, 2) }}" data-decimals="2">{{ $fmt($rataRata) }}</strong>
                <em>Tertinggi {{ $fmt($nilaiTertinggi) }} &middot; Terendah {{ $fmt($nilaiTerendah) }}</em>
            </div>
        </article>

        <article class="stat reveal">
            <div class="stat__icon stat__icon--green"><i class="bi bi-patch-check-fill"></i></div>
            <div>
                <small>Memenuhi Target</small>
                <strong data-count="{{ $jumlahMemenuhi }}" data-decimals="0">{{ $fmt($jumlahMemenuhi, 0) }}</strong>
                <em>{{ $persenMemenuhi }}% satker bernilai &ge; {{ $target }}</em>
            </div>
        </article>

        <article class="stat reveal">
            <div class="stat__icon stat__icon--red"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div>
                <small>Perlu Perhatian</small>
                <strong data-count="{{ $jumlahPerhatian }}" data-decimals="0">{{ $fmt($jumlahPerhatian, 0) }}</strong>
                <em>Bernilai di bawah {{ $target }}</em>
            </div>
        </article>
    </div>
</section>

@if ($jumlahSatker > 0)
{{-- ============================ PERINGKAT ============================ --}}
<section class="wrap section" id="peringkat">
    <div class="section__head reveal">
        <span class="eyebrow">Peringkat</span>
        <h2>Satker dengan Nilai IKPA Tertinggi</h2>
        <p>Lima satuan kerja dengan nilai IKPA terbaik pada periode {{ $labelPeriode }}.</p>
    </div>

    <div class="top">
        @foreach ($teratas as $i => $s)
            <article class="top__card top__card--{{ $i + 1 }} reveal">
                <span class="medal medal--{{ $s->peringkat <= 3 ? $s->peringkat : 'x' }}">
                    @if ($s->peringkat <= 3)
                        <i class="bi bi-trophy-fill"></i>
                    @else
                        {{ $s->peringkat }}
                    @endif
                </span>
                <div class="ring" style="--p: {{ min(100, max(0, $s->nilai_akhir)) }}">
                    <span>{{ $fmt($s->nilai_akhir) }}</span>
                </div>
                <h3>{{ $s->nama_satker }}</h3>
                <small>Kode {{ $s->kode_satker }}</small>
                <span class="badge badge--{{ $s->nilai_akhir >= $target ? 'ok' : 'warn' }}">
                    {{ $s->nilai_akhir >= $target ? 'Memenuhi Target' : 'Perlu Perhatian' }}
                </span>
                <button type="button" class="link-rincian" data-rincian="{{ $s->kode_satker }}">
                    Lihat rincian <i class="bi bi-arrow-right"></i>
                </button>
            </article>
        @endforeach
    </div>
</section>

{{-- ============================== GRAFIK ============================== --}}
<section class="wrap section">
    <div class="charts">
        <article class="panel reveal">
            <div class="panel__head">
                <h3><i class="bi bi-graph-up"></i> Perkembangan Rata-rata IKPA</h3>
                <small>Per periode yang telah dipublikasikan</small>
            </div>

            @if ($grafik !== null)
                <div class="chart-scroll">
                    <svg class="trend" viewBox="0 0 {{ $W }} {{ $H }}" role="img" aria-label="Grafik perkembangan rata-rata nilai IKPA per periode">
                        <defs>
                            <linearGradient id="gArea" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0" stop-color="#1d7bff" stop-opacity=".35"/>
                                <stop offset="1" stop-color="#1d7bff" stop-opacity="0"/>
                            </linearGradient>
                        </defs>

                        @foreach ($grid as $g)
                            <line class="trend__grid" x1="{{ $kiri }}" x2="{{ $W - $kanan }}" y1="{{ $g['y'] }}" y2="{{ $g['y'] }}"/>
                            <text class="trend__y" x="{{ $kiri - 10 }}" y="{{ $g['y'] + 4 }}" text-anchor="end">{{ $g['v'] }}</text>
                        @endforeach

                        <line class="trend__target" x1="{{ $kiri }}" x2="{{ $W - $kanan }}" y1="{{ $yTarget }}" y2="{{ $yTarget }}"/>
                        <text class="trend__target-label" x="{{ $W - $kanan }}" y="{{ $yTarget - 6 }}" text-anchor="end">Target {{ $target }}</text>

                        @if ($nTren > 1)
                            <path class="trend__area" d="{{ $area }}"/>
                            <path class="trend__line" pathLength="1" d="{{ $garis }}"/>
                        @endif

                        @foreach ($titik as $i => $t)
                            <circle class="trend__pt" cx="{{ $t['x'] }}" cy="{{ $t['y'] }}" r="6">
                                <title>{{ $t['l'] }}: {{ $fmt($t['v']) }}</title>
                            </circle>
                            @if ($nTren <= 8)
                                <text class="trend__val" x="{{ $t['x'] }}" y="{{ $t['y'] - 14 }}" text-anchor="middle">{{ $fmt($t['v']) }}</text>
                            @endif
                            @if ($i % $langkahLabel === 0 || $i === $nTren - 1)
                                <text class="trend__x" x="{{ $t['x'] }}" y="{{ $H - 16 }}" text-anchor="middle">{{ $singkat($t['l']) }}</text>
                            @endif
                        @endforeach
                    </svg>
                </div>
                @if ($nTren === 1)
                    <p class="chart-note"><i class="bi bi-info-circle"></i> Grafik perkembangan akan tampil lengkap setelah ada lebih dari satu periode.</p>
                @endif
            @else
                <p class="chart-note">Belum ada data perkembangan.</p>
            @endif
        </article>

        <article class="panel reveal">
            <div class="panel__head">
                <h3><i class="bi bi-pie-chart-fill"></i> Status Pencapaian</h3>
                <small>Target nilai IKPA &ge; {{ $target }}</small>
            </div>

            <div class="donut" style="--p: {{ round($persenExact, 2) }}" role="img" aria-label="{{ $persenMemenuhi }} persen satker memenuhi target">
                <div class="donut__in">
                    <strong>{{ $persenMemenuhi }}%</strong>
                    <small>memenuhi target</small>
                </div>
            </div>

            <ul class="legend">
                <li><i class="dot dot--ok"></i> Memenuhi target <b>{{ $jumlahMemenuhi }}</b></li>
                <li><i class="dot dot--warn"></i> Perlu perhatian <b>{{ $jumlahPerhatian }}</b></li>
            </ul>
        </article>
    </div>
</section>

{{-- ============================ DAFTAR SATKER ============================ --}}
<section class="wrap section" id="daftar">
    <div class="section__head reveal">
        <span class="eyebrow">Daftar Lengkap</span>
        <h2>Nilai IKPA Seluruh Satker</h2>
        <p>Cari satker Anda berdasarkan kode atau nama, lalu klik <b>Rincian</b> untuk melihat nilai tiap indikator (UP/TUP, Penyerapan, Capaian Output, dan lainnya) beserta langkah menaikkan nilai bila masih di bawah target.</p>
    </div>

    <div class="panel reveal">
        <div class="toolbar">
            <div class="toolbar__search">
                <i class="bi bi-search"></i>
                <input type="text" id="tblCari" value="{{ $keyword }}" placeholder="Cari kode atau nama satker..." autocomplete="off" aria-label="Cari satker">
            </div>

            <div class="seg" id="tblFilter" role="group" aria-label="Filter status">
                <button type="button" class="seg__btn is-active" data-filter="semua">Semua</button>
                <button type="button" class="seg__btn" data-filter="ok">Memenuhi Target</button>
                <button type="button" class="seg__btn" data-filter="warn">Perlu Perhatian</button>
            </div>

            <select id="tblUrut" class="toolbar__sort" aria-label="Urutkan">
                <option value="peringkat">Urut: Peringkat</option>
                <option value="nama">Urut: Nama (A-Z)</option>
                <option value="kode">Urut: Kode Satker</option>
            </select>
        </div>

        <div class="table-wrap">
            <table class="tbl" id="tbl">
                <thead>
                    <tr>
                        <th>Peringkat</th>
                        <th>Kode Satker</th>
                        <th>Nama Satker</th>
                        <th>Nilai IKPA</th>
                        <th>Status</th>
                        <th>Rincian</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($semuaSatker as $s)
                        <tr data-nama="{{ strtolower($s->nama_satker) }}"
                            data-kode="{{ $s->kode_satker }}"
                            data-peringkat="{{ $s->peringkat }}"
                            data-status="{{ $s->nilai_akhir >= $target ? 'ok' : 'warn' }}">
                            <td><span class="rank">{{ $s->peringkat }}</span></td>
                            <td class="mono">{{ $s->kode_satker }}</td>
                            <td class="nama">{{ $s->nama_satker }}</td>
                            <td>
                                <div class="score">
                                    <b>{{ $fmt($s->nilai_akhir) }}</b>
                                    <div class="bar {{ $s->nilai_akhir >= $target ? '' : 'bar--warn' }}">
                                        <i style="width: {{ min(100, max(0, $s->nilai_akhir)) }}%"></i>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge--{{ $s->nilai_akhir >= $target ? 'ok' : 'warn' }}">
                                    {{ $s->nilai_akhir >= $target ? 'Memenuhi Target' : 'Perlu Perhatian' }}
                                </span>
                            </td>
                            <td>
                                <button type="button" class="btn-rincian" data-rincian="{{ $s->kode_satker }}">
                                    <i class="bi bi-clipboard2-data-fill"></i> Rincian
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="empty" id="tblKosong" hidden>
                <i class="bi bi-search"></i>
                <p>Satker tidak ditemukan. Coba kata kunci atau filter lain.</p>
            </div>
        </div>

        <div class="pager">
            <span id="pagerInfo"></span>
            <div class="pager__btns" id="pagerBtns"></div>
        </div>
    </div>
</section>
@else
<section class="wrap section">
    <div class="panel empty-state reveal">
        <i class="bi bi-hourglass-split"></i>
        <h3>Data nilai IKPA belum tersedia</h3>
        <p>Data akan tampil setelah petugas mengunggah nilai IKPA periode terbaru.</p>
    </div>
</section>
@endif

{{-- ============================ TENTANG IKPA ============================ --}}
<section class="wrap section" id="tentang">
    <div class="section__head reveal">
        <span class="eyebrow">Tentang IKPA</span>
        <h2>Bagaimana Nilai IKPA Dihitung?</h2>
        <p>
            Nilai IKPA dihitung dari tujuh indikator berbobot pada tiga aspek, lalu dikurangi nilai
            Dispensasi SPM. Aturan tahun 2024 mengacu pada <b>{{ $panduan['sumber'] }}</b>.
        </p>
    </div>

    @php
        $indikatorList = collect($panduan['indikator']);
        $grupAspek = [
            ['judul' => 'Kualitas Perencanaan Anggaran', 'kode' => 'Perencanaan', 'ikon' => 'bi-clipboard2-check-fill', 'warna' => ''],
            ['judul' => 'Kualitas Implementasi Pelaksanaan Anggaran', 'kode' => 'Implementasi Pelaksanaan', 'ikon' => 'bi-cash-coin', 'warna' => 'aspek__icon--blue'],
            ['judul' => 'Kualitas Hasil Pelaksanaan Anggaran', 'kode' => 'Hasil', 'ikon' => 'bi-bullseye', 'warna' => 'aspek__icon--green'],
        ];
    @endphp

    <div class="aspek">
        @foreach ($grupAspek as $g)
            @php $daftarIndikator = $indikatorList->where('aspek', $g['kode']); @endphp
            <article class="aspek__card reveal">
                <div class="aspek__icon {{ $g['warna'] }}"><i class="bi {{ $g['ikon'] }}"></i></div>
                <h3>{{ $g['judul'] }}</h3>
                <span class="aspek__bobot">Bobot aspek {{ $daftarIndikator->sum('bobot') }}%</span>
                <ul>
                    @foreach ($daftarIndikator as $ik)
                        <li><span>{{ $ik['nama'] }}</span><b>{{ $ik['bobot'] }}%</b></li>
                    @endforeach
                    @if ($g['kode'] === 'Implementasi Pelaksanaan')
                        <li class="aspek__pengurang"><span>Dispensasi SPM</span><b>pengurang</b></li>
                    @endif
                </ul>
            </article>
        @endforeach
    </div>

    <div class="sub-head reveal">
        <h3><i class="bi bi-award-fill"></i> Kategori Nilai IKPA</h3>
        <p>Satker dinyatakan memenuhi target apabila nilai akhir &ge; {{ $target }} (kategori Baik atau Sangat Baik).</p>
    </div>
    <div class="kategori reveal">
        @foreach ($panduan['kategori'] as $k)
            <div class="kategori__item kategori__item--{{ strtolower($k['kode']) }}">
                <b>{{ $k['kode'] }}</b>
                <div><strong>{{ $k['nama'] }}</strong><small>{{ $k['aturan'] }}</small></div>
            </div>
        @endforeach
    </div>

    <div class="sub-head reveal">
        <h3><i class="bi bi-calendar-check-fill"></i> Jadwal yang Tidak Boleh Terlewat</h3>
        <p>Ketepatan waktu pada hal-hal berikut berpengaruh langsung pada nilai IKPA satker.</p>
    </div>
    <div class="jadwal reveal">
        @foreach ($panduan['jadwal'] as $j)
            <div class="jadwal__item">
                <i class="bi {{ $j['ikon'] }}"></i>
                <div><b>{{ $j['kapan'] }}</b><p>{{ $j['apa'] }}</p></div>
            </div>
        @endforeach
    </div>
</section>

{{-- ============================ AJAKAN LOGIN ============================ --}}
<section class="wrap section">
    <div class="cta reveal">
        <div class="cta__text">
            <h2>Petugas Satker atau KPPN?</h2>
            <p>Masuk ke dashboard untuk melihat detail lengkap per indikator, grafik perkembangan, laporan, dan unggah data IKPA.</p>
            <div class="cta__btns">
                @auth
                    <a href="{{ route('beranda') }}" class="btn btn--white"><i class="bi bi-speedometer2"></i> Buka Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn--white"><i class="bi bi-box-arrow-in-right"></i> Login</a>
                    <a href="{{ route('register') }}" class="btn btn--outline"><i class="bi bi-person-plus-fill"></i> Daftar Akun</a>
                @endauth
            </div>
        </div>
        <div class="cta__shield">@include('auth.partials.perisai')</div>
    </div>
</section>
</main>

{{-- ============================== FOOTER ============================== --}}
<footer class="footer">
    <div class="wrap footer__grid">
        <div>
            <img src="{{ asset('assets/img/login/logo-instansi.png') }}" alt="Kementerian Keuangan RI, DJPb, InTrezz" class="footer__logo">
            <p>Dashboard IKPA - Informasi dan pemantauan kinerja pelaksanaan anggaran satuan kerja Kabupaten Solok.</p>
        </div>
        <div>
            <h4>Menu</h4>
            <a href="#statistik">Statistik</a>
            <a href="#peringkat">Peringkat</a>
            <a href="#daftar">Daftar Satker</a>
            <a href="#tentang">Tentang IKPA</a>
        </div>
        <div>
            <h4>Akses</h4>
            <a href="{{ route('login') }}">Login Petugas</a>
            <a href="{{ route('register') }}">Daftar Akun</a>
        </div>
    </div>
    <div class="footer__bottom">
        <div class="wrap">
            <small>Sumber data: file Excel IKPA yang diunggah setiap periode &nbsp;|&nbsp; &copy; {{ date('Y') }} {{ config('ikpa.name') }}</small>
        </div>
    </div>
</footer>

{{-- ===================== PANEL RINCIAN SATKER (diisi JavaScript) ===================== --}}
<div class="modal" id="modalRapor" hidden>
    <div class="modal__backdrop" data-tutup></div>
    <div class="modal__panel" role="dialog" aria-modal="true" aria-labelledby="raporJudul" tabindex="-1">
        <button type="button" class="modal__close" data-tutup aria-label="Tutup"><i class="bi bi-x-lg"></i></button>
        <div id="raporIsi"></div>
    </div>
</div>

{{-- ============================= ASISTEN IKPA ============================= --}}
<button type="button" class="fab" id="fabAsisten" aria-label="Buka Asisten IKPA" aria-expanded="false" aria-controls="chat">
    <i class="bi bi-chat-dots-fill"></i><span>Asisten IKPA</span>
</button>
<div class="fab-tip" id="fabTip" hidden>Bingung cara menaikkan nilai IKPA? Tanya saya!</div>

<section class="chat" id="chat" hidden aria-label="Asisten IKPA">
    <header class="chat__head">
        <div class="chat__avatar"><i class="bi bi-robot"></i></div>
        <div class="chat__title"><b>Asisten IKPA</b><small>Panduan {{ $panduan['sumber'] }}</small></div>
        <button type="button" class="chat__x" id="chatTutup" aria-label="Tutup asisten"><i class="bi bi-x-lg"></i></button>
    </header>
    <div class="chat__ctx" id="chatCtx" hidden></div>
    <div class="chat__msgs" id="chatMsgs" aria-live="polite"></div>
    <div class="chat__chips" id="chatChips"></div>
    <form class="chat__form" id="chatForm" autocomplete="off">
        <input type="text" id="chatInput" placeholder="Tulis pertanyaan atau nama satker..." aria-label="Tulis pertanyaan" maxlength="200">
        <button type="submit" aria-label="Kirim"><i class="bi bi-send-fill"></i></button>
    </form>
    <p class="chat__note">Jawaban bersifat panduan. Perhitungan resmi mengikuti {{ $panduan['sumber'] }} dan data OMSPAN/SAKTI.</p>
</section>

<script>
    window.IKPA_DATA = {
        panduan: @json($panduan),
        rapor: @json($dataRapor),
        periodeLabel: @json($labelPeriode),
        bukaSatker: @json($bukaSatker)
    };
</script>
<script src="{{ asset('assets/js/landing.js') }}?v=3"></script>
<script src="{{ asset('assets/js/asisten.js') }}?v=3"></script>
</body>
</html>
