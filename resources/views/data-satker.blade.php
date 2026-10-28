@extends('layouts.app')
@section('title', 'Data Satker - ' . config('ikpa.name'))

@section('content')

    @if (session('pesan_sukses'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('pesan_sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('pesan_error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('pesan_error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="panel-card mb-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
            <div>
                <h6 class="panel-title mb-1">
                    <i class="bi bi-building me-1"></i> DATA SATKER
                </h6>
                <p class="text-muted small mb-0">
                    Daftar Satuan Kerja (Satker) yang berada di wilayah kerja
                    <strong>KPPN Solok (090)</strong>.
                </p>
            </div>

            <div class="d-flex gap-2">
                <form method="get" class="d-flex gap-2">
                    <input
                        type="text"
                        name="cari"
                        class="form-control form-control-sm"
                        placeholder="Cari kode / nama satker..."
                        value="{{ $kataKunci }}"
                        style="min-width: 240px;">
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="bi bi-search"></i>
                    </button>
                    @if ($kataKunci !== '')
                        <a href="{{ route('data-satker') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
                    @endif
                </form>
            </div>
        </div>

        <div class="table-responsive mt-3">
            <table class="table table-bordered table-hover align-middle master-satker-table">
                <thead class="table-primary text-center">
                    <tr>
                        <th style="width: 50px;">No</th>
                        <th>Kode Satker</th>
                        <th>Nama Satker</th>
                        <th>BA</th>
                        <th>KPPN</th>
                    </tr>
                </thead>
                <tbody>
                    @if (empty($masterSatker))
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                Tidak ada data satker yang cocok dengan pencarian
                                "{{ $kataKunci }}".
                            </td>
                        </tr>
                    @else
                        @foreach ($masterSatker as $i => $s)
                        <tr>
                            <td class="text-center">{{ $i + 1 }}</td>
                            <td><span class="fw-semibold">{{ $s['kode_satker'] }}</span></td>
                            <td>{{ $s['nama_satker'] }}</td>
                            <td>
                                {{ $s['kode_ba'] }}
                                <div class="text-muted small">{{ $s['nama_ba'] }}</div>
                            </td>
                            <td>
                                {{ $s['kode_kppn'] }}
                                <div class="text-muted small">{{ $s['nama_kppn'] }}</div>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <div class="text-muted small mt-2">
            Menampilkan {{ count($masterSatker) }} satker
            {{ $kataKunci !== '' ? 'hasil pencarian "' . $kataKunci . '"' : 'di wilayah kerja KPPN Solok' }}.
        </div>
    </div>
@endsection
