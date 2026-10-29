@extends('layouts.app')
@section('title', 'Laporan - ' . config('ikpa.name'))

@push('styles')
<style>
    /* CSS untuk media print */
    @@media print {
        /* Sembunyikan semua elemen kecuali laporan */
        body > * {
            display: none;
        }
        
        .main-content {
            display: block !important;
            position: static !important;
            margin: 0 !important;
            padding: 20px !important;
        }
        
        .main-content > * {
            display: block !important;
        }
        
        .no-print {
            display: none !important;
        }
        
        .laporan-kop {
            display: block !important;
            page-break-after: avoid;
        }
        
        .panel-card {
            box-shadow: none !important;
            border: none !important;
            page-break-inside: avoid;
        }
        
        table {
            page-break-inside: avoid;
            border: 1px solid #ddd;
        }
        
        th {
            background-color: #2c3e50 !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }
        
        tr {
            page-break-inside: avoid;
        }
        
        .table-responsive {
            overflow: visible !important;
        }
    }

    /* Custom styling untuk tab laporan */
    .laporan-tabs {
        border-bottom: 2px solid #f0f0f0;
        padding: 0 0 10px 0;
    }

    .laporan-tabs .nav-link {
        color: #666;
        border: none;
        border-bottom: 3px solid transparent;
        border-radius: 0;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .laporan-tabs .nav-link:hover {
        color: #1d4ed8;
        border-bottom-color: #1d4ed8;
    }

    .laporan-tabs .nav-link.active {
        color: #1d4ed8;
        border-bottom-color: #1d4ed8;
        background-color: transparent;
    }

    /* Panel card styling */
    .panel-card {
        background: white;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        padding: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        margin-bottom: 20px;
    }

    .panel-title {
        color: #2c3e50;
        font-size: 14px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 15px;
    }

    /* Table styling */
    .table {
        margin-bottom: 0;
    }

    .table thead {
        background-color: #f8f9fa;
    }

    .table thead th {
        border-color: #dee2e6;
        color: #2c3e50;
        font-weight: 600;
        padding: 12px 10px;
        font-size: 13px;
    }

    .table tbody td {
        padding: 10px;
        border-color: #dee2e6;
        font-size: 13px;
    }

    /* Badge styling */
    .badge {
        padding: 6px 10px;
        font-size: 12px;
    }
</style>
@endpush

@section('content')

    <!-- ==================== TAB JENIS LAPORAN ==================== -->
    <ul class="nav nav-pills laporan-tabs mb-3 no-print">
        <li class="nav-item">
            <a class="nav-link {{ $jenisLaporan === 'satker' ? 'active' : '' }}" href="{{ route('laporan', ['jenis' => 'satker']) }}">
                <i class="bi bi-list-columns-reverse me-1"></i> Rekap Seluruh Satker
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $jenisLaporan === 'ba' ? 'active' : '' }}" href="{{ route('laporan', ['jenis' => 'ba']) }}">
                <i class="bi bi-diagram-3 me-1"></i> Rekap Berdasarkan BA
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ $jenisLaporan === 'periode' ? 'active' : '' }}" href="{{ route('laporan', ['jenis' => 'periode']) }}">
                <i class="bi bi-calendar3 me-1"></i> Rekap Berdasarkan Periode
            </a>
        </li>
    </ul>

    <div class="panel-card">

    <!-- ==================== TOOLBAR: FILTER PERIODE + AKSI ==================== -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 no-print">
        <div>
            <form method="get" class="d-flex align-items-center gap-2">
                <input type="hidden" name="jenis" value="{{ $jenisLaporan }}">
                <label class="form-label mb-0 small fw-semibold">Pilih Periode:</label>
                <select name="periode" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
                    @foreach ($daftarPeriode as $key => $label)
                        <option value="{{ $key }}" {{ (string) $key === $periodeKey ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('laporan.export', ['format' => 'excel'] + $queryExport) }}" class="btn btn-sm btn-success">
                <i class="bi bi-file-earmark-excel"></i> Export Excel
            </a>
            <a href="{{ route('laporan.export', ['format' => 'pdf'] + $queryExport) }}" class="btn btn-sm btn-danger" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> Export PDF
            </a>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="printLaporan()">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
    </div>

        <!-- ==================== FORM FILTER (KPPN, BA, Satker) ==================== -->
<form method="get" class="row g-2 align-items-end mb-3 no-print">
    <input type="hidden" name="jenis" value="{{ $jenisLaporan }}">
    <input type="hidden" name="periode" value="{{ $periodeKey }}">

    <div class="col-auto">
        <label class="form-label small fw-semibold mb-1">KPPN</label>
        <select name="kppn" class="form-select form-select-sm">
            <option value="">Semua KPPN</option>
            @foreach ($filterOptions['kppn'] as $opt)
                <option value="{{ $opt['value'] }}" {{ $opt['value'] === $filterKppn ? 'selected' : '' }}>
                    {{ $opt['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-auto">
        <label class="form-label small fw-semibold mb-1">BA</label>
        <select name="ba" class="form-select form-select-sm">
            <option value="">Semua BA</option>
            @foreach ($filterOptions['ba'] as $opt)
                <option value="{{ $opt['value'] }}" {{ $opt['value'] === $filterBa ? 'selected' : '' }}>
                    {{ $opt['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-auto">
        <label class="form-label small fw-semibold mb-1">Satker</label>
        <select name="satker" class="form-select form-select-sm">
            <option value="">Semua Satker</option>
            @foreach ($filterOptions['satker'] as $opt)
                <option value="{{ $opt['value'] }}" {{ $opt['value'] === $filterSatker ? 'selected' : '' }}>
                    {{ $opt['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary">
            <i class="bi bi-funnel"></i> Terapkan Filter
        </button>
        <a href="{{ route('laporan', ['jenis' => $jenisLaporan, 'periode' => $periodeKey]) }}" class="btn btn-sm btn-outline-secondary">
            Reset
        </a>
    </div>
</form>

        <!-- ==================== KOP LAPORAN (khusus tampil saat print) ==================== -->
        <div class="laporan-kop text-center d-none d-print-block mb-3">
            <h5 class="mb-0">{{ config('ikpa.name') }}</h5>
            <div class="small">
                @if ($jenisLaporan === 'ba')
                    Rekap Nilai IKPA Berdasarkan BA
                @elseif ($jenisLaporan === 'periode')
                    Rekap Nilai IKPA Berdasarkan Periode
                @else
                    Rekap Nilai IKPA Seluruh Satker
                @endif
                &mdash; Periode {{ $periodeLabel }}
            </div>
            <hr>
        </div>

        <h6 class="panel-title">
            @if ($jenisLaporan === 'ba')
                REKAP NILAI IKPA BERDASARKAN BA
            @elseif ($jenisLaporan === 'periode')
                REKAP NILAI IKPA &mdash; PERIODE {{ mb_strtoupper($periodeLabel) }}
            @else
                REKAP NILAI IKPA SELURUH SATKER
            @endif
        </h6>

    <div class="table-responsive">
    @if ($jenis === 'ba')
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Kode BA</th>
                    <th>Nama BA</th>
                    <th>Jumlah Satker</th>
                    <th>Nilai Tertinggi</th>
                    <th>Nilai Terendah</th>
                    <th>Rata-rata Nilai Akhir</th>
                </tr>
            </thead>
            <tbody>
                @if (empty($rows))
                    <tr><td colspan="6" class="text-center text-muted">Belum ada data</td></tr>
                @else
@foreach ($rows as $r)
                    <tr>
                        <td>{{ $r['kode_ba'] }}</td>
                        <td>{{ $r['nama_ba'] ?: '-' }}</td>
                        <td>{{ $r['jumlah_satker'] }}</td>
                        <td>{{ formatAngka($r['nilai_tertinggi']) }}</td>
                        <td>{{ formatAngka($r['nilai_terendah']) }}</td>
                        <td><strong>{{ formatAngka($r['rata_rata']) }}</strong></td>
                    </tr>
                @endforeach
@endif
            </tbody>
        </table>

    @else {{-- jenis = satker (tab Seluruh Satker maupun Berdasarkan Periode) --}}
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>No</th>
                    <th>Kode Satker</th>
                    <th>Nama Satker</th>
                    <th>Revisi DIPA</th>
                    <th>Deviasi Hal. III</th>
                    <th>Penyerapan</th>
                    <th>Belanja Kontraktual</th>
                    <th>Penyelesaian Tagihan</th>
                    <th>UP/TUP</th>
                    <th>Capaian Output</th>
                    <th>Nilai Total</th>
                    <th>Nilai Akhir</th>
                </tr>
            </thead>
            <tbody>
                @if (empty($rows))
                    <tr><td colspan="12" class="text-center text-muted">Belum ada data</td></tr>
                @else
@foreach ($rows as $i => $r)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $r['kode_satker'] }}</td>
                        <td>{{ $r['nama_satker'] }}</td>
                        <td>{{ formatAngka($r['revisi_dipa']) }}</td>
                        <td>{{ formatAngka($r['deviasi_dipa']) }}</td>
                        <td>{{ formatAngka($r['penyerapan']) }}</td>
                        <td>{{ formatAngka($r['belanja_kontraktual']) }}</td>
                        <td>{{ formatAngka($r['penyelesaian_tagihan']) }}</td>
                        <td>{{ formatAngka($r['up_tup']) }}</td>
                        <td>{{ formatAngka($r['capaian_output']) }}</td>
                        <td>{{ formatAngka($r['nilai_total']) }}</td>
                        <td>
                            <span class="badge bg-{{ warnaCapaian($r['nilai_akhir']) }}">
                                {{ formatAngka($r['nilai_akhir']) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
@endif
            </tbody>
        </table>
    @endif
    

        <div class="text-muted small mt-2 no-print">
            Menampilkan {{ count($rows) }} baris data
            &mdash; Sumber Data: file Excel yang diupload setiap periode.
        </div>
    </div>
    </div>{{-- /.panel-card --}}
@endsection

@push('scripts')
<script>
function printLaporan() {
    // Simpan konten asli
    const originalContent = document.body.innerHTML;
    
    // Ambil elemen yang akan di-print
    const laporanKop = document.querySelector('.laporan-kop');
    const panelCard = document.querySelector('.panel-card');
    const mainContent = document.querySelector('.main-content');
    
    // Buat konten baru untuk print
    let printContent = '';
    
    if (laporanKop) {
        printContent += laporanKop.outerHTML;
    }
    
    if (panelCard) {
        printContent += panelCard.outerHTML;
    }
    
    // Ganti konten halaman
    document.body.innerHTML = '<main class="main-content" style="padding: 20px;">' + printContent + '</main>';
    
    // Trigger print
    window.print();
    
    // Restore konten asli
    document.body.innerHTML = originalContent;
    
    // Re-attach event listeners jika diperlukan
    location.reload();
}
</script>
@endpush
