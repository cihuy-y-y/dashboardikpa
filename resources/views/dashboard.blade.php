@extends('layouts.app')
@section('title', 'Dashboard - ' . config('ikpa.name'))

@section('content')

    <!-- ==================== FILTER BAR ==================== -->
    <form method="get" class="filter-card">
        <div class="row g-3 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label">Tahun</label>
                <select name="tahun" class="form-select">
                    <option value="">Semua Tahun</option>
                    @foreach ($filterOptions['tahun'] as $opt)
                        <option value="{{ $opt['value'] }}" {{ ($filters['tahun'] == $opt['value']) ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Periode</label>
                <select name="periode" class="form-select">
                    <option value="">Semua Periode</option>
                    @foreach ($filterOptions['periode'] as $opt)
                        <option value="{{ $opt['value'] }}" {{ (($filters['periode'] ?? '') === $opt['value']) ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Kode BA</label>
                <select name="ba" class="form-select">
                    <option value="">Semua BA</option>
                    @foreach ($filterOptions['ba'] as $opt)
                        <option value="{{ $opt['value'] }}" {{ (($filters['ba'] ?? '') === $opt['value']) ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Kode Satker</label>
                <select name="satker" class="form-select">
                    <option value="">Semua Satker</option>
                    @foreach ($filterOptions['satker'] as $opt)
                        <option value="{{ $opt['value'] }}" {{ (($filters['satker'] ?? '') === $opt['value']) ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 d-grid">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-search"></i> Tampilkan
                </button>
            </div>
        </div>
    </form>
<!-- ==================== INDIKATOR PENGINGAT IKPA ==================== -->

@php
    $kategoriTampilan = [
        'A' => [
            'nama' => 'Sangat Baik',
            'rentang' => '95 - 100',
            'warna' => 'success',
            'ikon' => 'bi-check-circle-fill',
            'keterangan' => 'Kinerja sangat baik',
        ],
        'B' => [
            'nama' => 'Baik',
            'rentang' => '89 - kurang dari 95',
            'warna' => 'primary',
            'ikon' => 'bi-check-circle',
            'keterangan' => 'Kinerja baik',
        ],
        'C' => [
            'nama' => 'Cukup',
            'rentang' => '70 - kurang dari 89',
            'warna' => 'warning',
            'ikon' => 'bi-exclamation-circle-fill',
            'keterangan' => 'Perlu perhatian',
        ],
        'D' => [
            'nama' => 'Kurang',
            'rentang' => 'Di bawah 70',
            'warna' => 'danger',
            'ikon' => 'bi-exclamation-triangle-fill',
            'keterangan' => 'Prioritas evaluasi',
        ],
    ];
@endphp

<div class="row g-3 mt-2 mb-3">
    <div class="col-12">
        <div class="panel-card">

            <!-- JUDUL -->
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="panel-title mb-1">
                        <i class="bi bi-bell-fill me-2"></i>
                        Indikator Pengingat IKPA
                    </h5>
                    <small class="text-muted">
                        Klik kategori untuk melihat daftar satker dan detail evaluasinya.
                    </small>
                </div>

                <span class="badge bg-primary">
                    {{ $kategoriPengingat['total'] }} Satker
                </span>
            </div>

            @if ($kategoriPengingat['total'] > 0)

                <!-- KARTU KATEGORI -->
                <div class="row g-3">

                    @foreach ($kategoriTampilan as $kode => $meta)
                        <div class="col-6 col-xl-3">
                            <button
                                type="button"
                                class="btn w-100 p-0 text-start ikpa-category-button"
                                data-kategori-toggle="{{ $kode }}"
                                aria-expanded="false"
                            >
                                <div class="border rounded-3 p-3 h-100">
                                    <div class="text-{{ $meta['warna'] }} fw-semibold mb-2">
                                        <i class="bi {{ $meta['ikon'] }} me-1"></i>
                                        {{ $kode }} - {{ $meta['nama'] }}
                                    </div>

                                    <h3 class="fw-bold text-{{ $meta['warna'] }} mb-1">
                                        {{ $kategoriPengingat[$kode] }}
                                    </h3>

                                    <small class="text-muted">
                                        Nilai {{ $meta['rentang'] }}
                                    </small>

                                    <div class="small text-{{ $meta['warna'] }} mt-2">
                                        {{ $meta['keterangan'] }}
                                    </div>

                                    <div class="small text-muted mt-2">
                                        <i class="bi bi-hand-index-thumb me-1"></i>
                                        Klik untuk melihat satker
                                    </div>
                                </div>
                            </button>
                        </div>
                    @endforeach

                </div>

                <!-- PESAN PENGINGAT -->
                @if ($kategoriPengingat['C'] > 0 || $kategoriPengingat['D'] > 0)
                    <div class="alert alert-warning mt-3 mb-0">
                        <i class="bi bi-bell-fill me-2"></i>
                        <strong>Pengingat IKPA:</strong>
                        Terdapat
                        <strong>
                            {{ $kategoriPengingat['C'] + $kategoriPengingat['D'] }}
                            satker
                        </strong>
                        yang memerlukan perhatian.

                        @if ($kategoriPengingat['D'] > 0)
                            <span class="text-danger fw-semibold">
                                {{ $kategoriPengingat['D'] }} satker kategori D
                                menjadi prioritas evaluasi.
                            </span>
                        @endif
                    </div>
                @else
                    <div class="alert alert-success mt-3 mb-0">
                        <i class="bi bi-check-circle-fill me-2"></i>
                        Tidak terdapat satker kategori C maupun D
                        berdasarkan filter yang dipilih.
                    </div>
                @endif

                <!-- DAFTAR SATKER BERDASARKAN KATEGORI -->
                @foreach ($kategoriTampilan as $kode => $meta)

                    <div
                        class="ikpa-category-panel mt-4 d-none"
                        data-kategori-panel="{{ $kode }}"
                    >
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <h6 class="fw-bold mb-1 text-{{ $meta['warna'] }}">
                                    Daftar Satker Kategori {{ $kode }} - {{ $meta['nama'] }}
                                </h6>
                                <small class="text-muted">
                                    Klik nama satker untuk melihat rincian evaluasi.
                                </small>
                            </div>

                            <span class="badge bg-{{ $meta['warna'] }}">
                                {{ count($daftarSatkerKategori[$kode]) }} Satker
                            </span>
                        </div>

                        @if (count($daftarSatkerKategori[$kode]) > 0)

                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th width="55">No.</th>
                                            <th>Kode Satker</th>
                                            <th>Nama Satker</th>
                                            <th class="text-end">Nilai Akhir</th>
                                            <th class="text-center">Kategori</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach ($daftarSatkerKategori[$kode] as $index => $satkerItem)
                                            <tr>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $satkerItem['kode_satker'] }}</td>

                                                <td>
                                                    <details>
                                                        <summary class="fw-semibold text-primary"
                                                            style="cursor:pointer">
                                                            {{ $satkerItem['nama_satker'] }}
                                                            <small class="text-muted fw-normal">
                                                                (Lihat evaluasi)
                                                            </small>
                                                        </summary>

                                                        <!-- DETAIL EVALUASI SATKER -->
                                                        <div class="border rounded-3 p-3 mt-3 mb-2">

                                                            <h6 class="fw-bold mb-3">
                                                                <i class="bi bi-clipboard-data me-2"></i>
                                                                Rincian Evaluasi Satker
                                                            </h6>

                                                            <div class="row g-3 mb-3">
                                                                <div class="col-md-6">
                                                                    <small class="text-muted">Nama Satker</small>
                                                                    <div class="fw-semibold">
                                                                        {{ $satkerItem['nama_satker'] }}
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <small class="text-muted">Kode Satker</small>
                                                                    <div class="fw-semibold">
                                                                        {{ $satkerItem['kode_satker'] }}
                                                                    </div>
                                                                </div>

                                                                <div class="col-md-3">
                                                                    <small class="text-muted">Nilai Akhir</small>
                                                                    <div class="fw-bold text-{{ $meta['warna'] }}">
                                                                        {{ number_format($satkerItem['nilai_akhir'], 2, ',', '.') }}
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <hr>

                                                            <!-- TIGA ASPEK -->
                                                            <h6 class="fw-bold mb-3">
                                                                Ringkasan Tiga Aspek Penilaian
                                                            </h6>

                                                            <div class="row g-2 mb-4">
                                                                @foreach ([
                                                                    'Perencanaan' => 'Kualitas Perencanaan Anggaran',
                                                                    'Pelaksanaan' => 'Kualitas Pelaksanaan Anggaran',
                                                                    'Hasil' => 'Kualitas Hasil Pelaksanaan Anggaran',
                                                                ] as $aspekKey => $aspekNama)

                                                                    <div class="col-md-4">
                                                                        <div class="border rounded-3 p-3 h-100">
                                                                            <small class="text-muted">
                                                                                {{ $aspekNama }}
                                                                            </small>

                                                                            <h5 class="fw-bold mb-0 mt-2">
                                                                                {{ number_format($satkerItem['aspek'][$aspekKey], 2, ',', '.') }}
                                                                            </h5>

                                                                            <small class="text-muted">
                                                                                Nilai kontribusi berbobot
                                                                            </small>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>

                                                            <!-- TUJUH INDIKATOR -->
                                                            <h6 class="fw-bold mb-2">
                                                                Rincian Tujuh Indikator IKPA
                                                            </h6>

                                                            <div class="table-responsive">
                                                                <table class="table table-sm table-bordered align-middle">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th>Aspek</th>
                                                                            <th>Indikator</th>
                                                                            <th class="text-end">Nilai</th>
                                                                            <th class="text-end">Bobot</th>
                                                                            <th class="text-end">Kontribusi</th>
                                                                        </tr>
                                                                    </thead>

                                                                    <tbody>
                                                                        @foreach ($satkerItem['indikator'] as $indikator)
                                                                            @php
                                                                                $nilaiTerendah = min(
                                                                                    array_column($satkerItem['indikator'], 'nilai')
                                                                                );
                                                                            @endphp

                                                                            <tr class="{{ $indikator['nilai'] == $nilaiTerendah ? 'table-warning' : '' }}">
                                                                                <td>{{ $indikator['aspek'] }}</td>

                                                                                <td>
                                                                                    {{ $indikator['nama'] }}

                                                                                    @if ($indikator['nilai'] == $nilaiTerendah)
                                                                                        <span class="badge bg-warning text-dark">
                                                                                            Nilai terendah
                                                                                        </span>
                                                                                    @endif
                                                                                </td>

                                                                                <td class="text-end fw-semibold">
                                                                                    {{ number_format($indikator['nilai'], 2, ',', '.') }}
                                                                                </td>

                                                                                <td class="text-end">
                                                                                    {{ $indikator['bobot'] }}%
                                                                                </td>

                                                                                <td class="text-end">
                                                                                    {{ number_format($indikator['kontribusi'], 2, ',', '.') }}
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>

                                                            <div class="alert alert-info mb-0">
                                                                <i class="bi bi-info-circle-fill me-2"></i>
                                                                Indikator dengan nilai paling rendah ditandai
                                                                untuk membantu proses evaluasi.
                                                                Penanda ini bukan otomatis berarti indikator
                                                                tidak memenuhi target resmi.
                                                            </div>

                                                        </div>
                                                    </details>
                                                </td>

                                                <td class="text-end fw-bold">
                                                    {{ number_format($satkerItem['nilai_akhir'], 2, ',', '.') }}
                                                </td>

                                                <td class="text-center">
                                                    <span class="badge bg-{{ $meta['warna'] }}">
                                                        {{ $kode }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                        @else
                            <div class="alert alert-secondary">
                                Tidak ada satker dalam kategori {{ $kode }}
                                berdasarkan filter yang dipilih.
                            </div>
                        @endif
                    </div>

                @endforeach

            @else
                <div class="alert alert-secondary mb-0">
                    Belum terdapat data IKPA untuk filter yang dipilih.
                </div>
            @endif

        </div>
    </div>
</div>

<!-- ==================== AKHIR INDIKATOR PENGINGAT ==================== -->

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tombolKategori = document.querySelectorAll('[data-kategori-toggle]');
    const panelKategori = document.querySelectorAll('[data-kategori-panel]');

    tombolKategori.forEach(function (tombol) {
        tombol.addEventListener('click', function () {
            const kategori = this.dataset.kategoriToggle;
            const panelAktif = document.querySelector(
                '[data-kategori-panel="' + kategori + '"]'
            );

            const sedangTerbuka = !panelAktif.classList.contains('d-none');

            panelKategori.forEach(function (panel) {
                panel.classList.add('d-none');
            });

            tombolKategori.forEach(function (item) {
                item.setAttribute('aria-expanded', 'false');
            });

            if (!sedangTerbuka) {
                panelAktif.classList.remove('d-none');
                this.setAttribute('aria-expanded', 'true');

                panelAktif.scrollIntoView({
                    behavior: 'smooth',
                    block: 'nearest'
                });
            }
        });
    });
});
</script>
@endpush


    <!-- ==================== GRAFIK 1 & 3 ==================== -->
    <div class="row g-3 mt-1">
        <div class="col-lg-7">
            <div class="panel-card h-100">
                <div class="d-flex justify-content-between align-items-center panel-title-row">
                    <h6 class="panel-title mb-0">
                        <i class="bi bi-graph-up me-1"></i> Grafik Perkembangan Nilai IKPA per Bulan
                    </h6>
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm btn-download-chart"
                            data-canvas="chartPerkembangan"
                            data-filename="perkembangan-nilai-ikpa">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
                <div class="chart-canvas-wrap chart">
                    <canvas id="chartPerkembangan"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel-card h-100">
                <div class="d-flex justify-content-between align-items-center panel-title-row">
                    <h6 class="panel-title mb-0">
                        <i class="bi bi-pie-chart-fill me-1"></i> Grafik Kontribusi Tiga Aspek
                    </h6>
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm btn-download-chart"
                            data-canvas="chartKontribusiAspek"
                            data-filename="kontribusi-tiga-aspek">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
                <div class="chart-canvas-wrap donut-wrap">
                    <canvas id="chartKontribusiAspek"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== GRAFIK 2 & 4 ==================== -->
    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="panel-card h-100">
                <div class="d-flex justify-content-between align-items-center panel-title-row">
                    <h6 class="panel-title mb-0">
                        <i class="bi bi-bar-chart-line-fill me-1"></i> Grafik Rata-rata Tiap Indikator
                    </h6>
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm btn-download-chart"
                            data-canvas="chartRataIndikator"
                            data-filename="rata-rata-indikator">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartRataIndikator"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel-card h-100">
                <div class="d-flex justify-content-between align-items-center panel-title-row">
                    <h6 class="panel-title mb-0">
                        <i class="bi bi-bar-chart-fill me-1"></i> Grafik Distribusi Nilai Satker
                    </h6>
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm btn-download-chart"
                            data-canvas="chartDistribusiNilai"
                            data-filename="distribusi-nilai-satker">
                        <i class="bi bi-download"></i> 
                    </button>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartDistribusiNilai"></canvas>
                </div>
                <p class="text-muted small mb-0 mt-2">
                    Jumlah satker berdasarkan rentang perolehan Nilai Akhir.
                </p>
            </div>
        </div>
    </div>

    <!-- ==================== GRAFIK 5: TOP 10 & BOTTOM 10 ==================== -->
    <div class="row g-3 mt-1">
        <div class="col-lg-6">
            <div class="panel-card h-100">
                <div class="d-flex justify-content-between align-items-center panel-title-row">
                    <h6 class="panel-title text-success mb-0">
                        <i class="bi bi-trophy-fill me-1"></i> 10 Satker Terbaik
                    </h6>
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm btn-download-chart"
                            data-canvas="chartTop10Terbaik"
                            data-filename="10-satker-terbaik">
                        <i class="bi bi-download"></i> 
                    </button>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartTop10Terbaik"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="panel-card h-100">
                <div class="d-flex justify-content-between align-items-center panel-title-row">
                    <h6 class="panel-title text-danger mb-0">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> 10 Satker Terbawah
                    </h6>
                    <button type="button"
                            class="btn btn-outline-secondary btn-sm btn-download-chart"
                            data-canvas="chartTop10Terbawah"
                            data-filename="10-satker-terbawah">
                        <i class="bi bi-download"></i>
                    </button>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartTop10Terbawah"></canvas>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    /* memastikan tombol download rapi berdampingan dengan judul panel */
    .panel-title-row {
        margin-bottom: 0.75rem;
    }
    .btn-download-chart {
        white-space: nowrap;
        flex-shrink: 0;
    }
</style>
@endpush

{{-- Data untuk grafik dikirim ke JavaScript melalui variabel global --}}
@push('scripts')
<script>
    const DATA_PERKEMBANGAN = @json($perkembangan);
    const DATA_RATA_INDIKATOR = @json($rataRataInd);
    const DATA_KONTRIBUSI_ASPEK = @json($kontribusiAspek);
    const DATA_DISTRIBUSI_NILAI = @json($distribusiNilai);
    const DATA_TOP10_TERBAIK = @json($top10Terbaik);
    const DATA_TOP10_TERBAWAH = @json($top10Terbawah);
</script>

<script src="{{ asset('assets/js/dashboard.js') }}"></script>

{{-- Tombol download grafik sebagai PNG (bekerja langsung pada elemen canvas Chart.js) --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-download-chart').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const canvasId = btn.getAttribute('data-canvas');
            const filename = btn.getAttribute('data-filename') || 'grafik';
            const canvas = document.getElementById(canvasId);

            if (!canvas) {
                alert('Grafik tidak ditemukan.');
                return;
            }

            // Canvas sementara dengan latar putih supaya area transparan tidak jadi hitam
            const tempCanvas = document.createElement('canvas');
            tempCanvas.width = canvas.width;
            tempCanvas.height = canvas.height;

            const ctx = tempCanvas.getContext('2d');
            ctx.fillStyle = '#ffffff';
            ctx.fillRect(0, 0, tempCanvas.width, tempCanvas.height);
            ctx.drawImage(canvas, 0, 0);

            const link = document.createElement('a');
            link.href = tempCanvas.toDataURL('image/png', 1.0);
            link.download = filename + '.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });
});
</script>
@endpush
