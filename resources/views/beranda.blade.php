@extends('layouts.app')
@section('title', 'Beranda - ' . config('ikpa.name'))

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
                        <option value="{{ $opt['value'] }}" {{ ($filters['periode'] === $opt['value']) ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label">Kode BA</label>
                <select name="ba" class="form-select">
                    <option value="">Semua BA</option>
                    @foreach ($filterOptions['ba'] as $opt)
                        <option value="{{ $opt['value'] }}" {{ ($filters['ba'] === $opt['value']) ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label">Kode Satker</label>
                <select name="satker" class="form-select">
                    <option value="">Semua Satker</option>
                    @foreach ($filterOptions['satker'] as $opt)
                        <option value="{{ $opt['value'] }}" {{ ($filters['satker'] === $opt['value']) ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-2 d-grid">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Tampilkan
                </button>
            </div>
        </div>
    </form>

    <!-- ==================== KARTU RINGKASAN ==================== -->
    <div class="row g-3 mt-1">
        <div class="col-6 col-lg-3">
            <div class="summary-card">
                <div class="summary-icon bg-success"><i class="bi bi-graph-up-arrow"></i></div>
                <div>
                    <div class="summary-label">NILAI AKHIR</div>
                    <div class="summary-value text-success">{{ formatAngka($ringkasan['nilai_akhir']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="summary-card">
                <div class="summary-icon bg-primary"><i class="bi bi-clipboard-data"></i></div>
                <div>
                    <div class="summary-label">NILAI TOTAL</div>
                    <div class="summary-value text-primary">{{ formatAngka($ringkasan['nilai_total']) }}</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="summary-card">
                <div class="summary-icon bg-purple"><i class="bi bi-percent"></i></div>
                <div>
                    <div class="summary-label">KONVERSI BOBOT</div>
                    <div class="summary-value text-purple">{{ formatAngka($ringkasan['konversi_bobot']) }}%</div>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="summary-card">
                <div class="summary-icon bg-orange"><i class="bi bi-file-earmark-text"></i></div>
                <div>
                    <div class="summary-label">DISPENSASI SPM</div>
                    <div class="summary-value text-orange">{{ formatAngka($ringkasan['dispensasi_spm']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== 3 ASPEK PENILAIAN ==================== -->
    <div class="row g-3 mt-1">
        @foreach ($aspekPenilaian as $key => $aspek)
            <div class="col-lg-4">
                <div class="panel-card h-100">
                    <h6 class="panel-title">
                        <i class="bi bi-bar-chart-fill me-1"></i> {{ $aspek['judul'] }}
                    </h6>

                    <table class="table table-sm aspek-table align-middle">
                        <thead>
                            <tr>
                                <th>Indikator</th>
                                <th style="width:38%">Nilai</th>
                                <th class="text-end">Bobot</th>
                                <th class="text-end">Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($aspek['indikator'] as $ind)
                                <tr>
                                    <td>{{ $ind['nama'] }}</td>
                                    <td>
                                        <div class="progress">
                                            <div class="progress-bar bg-success" style="width: {{ (float)$ind['nilai'] }}%">
                                                {{ formatAngka($ind['nilai']) }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-end">{{ (int)$ind['bobot'] }}%</td>
                                    <td class="text-end">{{ formatAngka($ind['nilai_akhir']) }}</td>
                                </tr>
                            @endforeach
                            <tr class="fw-bold">
                                <td>NILAI ASPEK</td>
                                <td></td>
                                <td class="text-end">-</td>
                                <td class="text-end">{{ formatAngka($aspek['nilai_akhir_aspek']) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    @if ($key === 'hasil')
                        <div class="text-center mt-2">
                            <div class="chart-canvas-wrap gauge-wrap">
                                <canvas id="gaugeAspek" class="chart-canvas gauge-canvas" height="180"></canvas>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- ==================== GRAFIK: DONUT, TREND, TOP 10 ==================== -->
    <div class="row g-3 mt-1">
        <div class="col-12 col-xl-4">
            <div class="panel-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="panel-title mb-0">KONTRIBUSI BOBOT</h6>
                </div>
                <div class="chart-canvas-wrap donut-wrap">
                    <canvas id="chartKontribusi" class="chart-canvas" height="280"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="panel-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="panel-title mb-0">TREND NILAI AKHIR</h6>
                    <span class="chart-badge {{ ($trend['trend_status'] ?? 'stabil') === 'naik' ? 'up' : (($trend['trend_status'] ?? 'stabil') === 'turun' ? 'down' : 'neutral') }}">
                        {{ $trend['perubahan_label'] ?? '0,00%' }}
                    </span>
                </div>
                <div class="chart-canvas-wrap">
                    <canvas id="chartTrend" class="chart-canvas" height="280"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="panel-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="panel-title mb-0">TOP 10 SATKER</h6>
                </div>
                <div class="chart-canvas-wrap top10-wrap">
                    <div id="chartTop10"></div>
                </div>
            </div>
        </div>
    </div>


    <!-- ==================== TABEL DETAIL NILAI IKPA PER SATKER ==================== -->
    <div class="panel-card mt-3">
        <h6 class="panel-title">DETAIL NILAI IKPA PER SATKER</h6>
        <div class="table-responsive">
            <table id="detailTable" class="table table-bordered table-sm detail-table text-center align-middle">
                <thead class="table-primary">
                    <tr>
                        <th rowspan="2">No</th>
                        <th rowspan="2">Kode Satker</th>
                        <th rowspan="2">Uraian Satker</th>
                        <th colspan="2">Kualitas Perencanaan Anggaran</th>
                        <th colspan="5">Kualitas Pelaksanaan Anggaran</th>
                        <th colspan="2">Kualitas Hasil Pelaksanaan Anggaran</th>
                        <th rowspan="2">Nilai Total</th>
                        <th rowspan="2">Konversi Bobot</th>
                        <th rowspan="2">Dispensasi SPM</th>
                        <th rowspan="2">Nilai Akhir</th>
                    </tr>
                    <tr>
                        <th>Revisi DIPA</th>
                        <th>Deviasi Hal. III DIPA</th>
                        <th>Penyerapan</th>
                        <th>Belanja Kontraktual</th>
                        <th>Penyelesaian Tagihan</th>
                        <th>UP dan TUP</th>
                        <th>Nilai Aspek</th>
                        <th>Capaian Output</th>
                        <th>Nilai Aspek</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($detailSatker as $row)
                        <tr>
                            <td>{{ (int)$row['no'] }}</td>
                            <td>{{ $row['kode_satker'] }}</td>
                            <td class="text-start">{{ $row['uraian_satker'] }}</td>
                            <td>{{ formatAngka($row['revisi_dipa']) }}</td>
                            <td>{{ formatAngka($row['deviasi_dipa']) }}</td>
                            <td>{{ formatAngka($row['penyerapan']) }}</td>
                            <td>{{ formatAngka($row['belanja_kontraktual']) }}</td>
                            <td>{{ formatAngka($row['penyelesaian_tagihan']) }}</td>
                            <td>{{ formatAngka($row['up_tup']) }}</td>
                            <td>{{ formatAngka($row['nilai_aspek_2']) }}</td>
                            <td>{{ formatAngka($row['capaian_output']) }}</td>
                            <td>{{ formatAngka($row['nilai_aspek_3']) }}</td>
                            <td>{{ formatAngka($row['nilai_total']) }}</td>
                            <td>{{ formatAngka($row['konversi_bobot']) }}%</td>
                            <td>{{ formatAngka($row['dispensasi_spm']) }}</td>
                            <td class="fw-bold">{{ formatAngka($row['nilai_akhir']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection

{{-- Data untuk grafik dikirim ke JavaScript melalui variabel global --}}
@push('scripts')
<script>
    const DATA_ASPEK = @json($aspekPenilaian);
    const DATA_TREND = @json($trend);
    const DATA_TOP10 = @json($top10);
    const NILAI_AKHIR = @json($ringkasan['nilai_akhir']);
</script>
@endpush
