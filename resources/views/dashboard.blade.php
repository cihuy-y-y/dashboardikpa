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
