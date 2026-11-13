
@extends('layouts.app')

@section('title', 'Peringatan IKPA - ' . config('ikpa.name'))

@section('content')

@php
    $fmt = fn ($nilai) => number_format((float) $nilai, 2, ',', '.');
@endphp

<div class="container-fluid">

    <div class="mb-4">
        <h4 class="fw-bold">Peringatan Nilai IKPA</h4>
        <p class="text-muted">
            Daftar satker dengan nilai IKPA di bawah target 89.
        </p>
    </div>

    {{-- FILTER PERIODE DAN BATAS --}}
    <div class="filter-card">
        <form method="GET">
            <div class="row g-3 align-items-end">

                <div class="col-md-5">
                    <label class="form-label">Periode</label>
                    <select name="periode" class="form-select">
                        @foreach ($daftarPeriode as $p)
                            <option value="{{ $p }}"
                                {{ $p == $periode ? 'selected' : '' }}>
                                {{ $p }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-5">
                    <label class="form-label">Batas Nilai IKPA</label>
                    <input type="number"
                        name="batas"
                        class="form-control"
                        min="0"
                        max="100"
                        step="0.01"
                        value="{{ $batas }}">
                </div>

                <div class="col-md-2 d-grid">
                    <button class="btn btn-primary">
                        <i class="bi bi-search"></i>
                        Tampilkan
                    </button>
                </div>

            </div>
        </form>
    </div>

    {{-- RINGKASAN --}}
    <div class="row g-3 mt-2">

        <div class="col-md-6">
            <div class="summary-card">
                <div>
                    <div class="summary-label">TOTAL SATKER</div>
                    <div class="summary-value">
                        {{ $semua->count() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="summary-card">
                <div>
                    <div class="summary-label">SATKER DI BAWAH TARGET</div>
                    <div class="summary-value text-danger">
                        {{ $peringatan->count() }}
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- INFORMASI PERINGATAN --}}
    <div class="mt-3">

        @if ($semua->isEmpty())

            <div class="alert alert-info">
                Belum tersedia data IKPA untuk periode ini.
            </div>

        @elseif ($peringatan->isEmpty())

            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                Semua satker memenuhi target nilai IKPA
                {{ $fmt($batas) }}.
            </div>

        @else

            <div class="alert alert-warning">
                <i class="bi bi-exclamation-triangle-fill"></i>
                Terdapat <strong>{{ $peringatan->count() }}
                satker</strong> dengan nilai IKPA di bawah
                {{ $fmt($batas) }}.
            </div>

            {{-- DAFTAR SATKER --}}
            <div class="panel-card">

                <h6 class="panel-title mb-3">
                    <i class="bi bi-bell-fill"></i>
                    Daftar Satker yang Perlu Diperhatikan
                </h6>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">

                        <thead class="table-light">
                            <tr>
                                <th>No</th>
                                <th>Kode Satker</th>
                                <th>Nama Satker</th>
                                <th>Nilai IKPA</th>
                                <th>Selisih</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach ($peringatan as $index => $r)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $r->kode_satker }}</td>
                                    <td>{{ $r->nama_satker }}</td>

                                    <td class="fw-bold text-danger">
                                        {{ $fmt($r->nilai_akhir) }}
                                    </td>

                                    <td>
                                        -{{ $fmt($r->selisih) }}
                                    </td>

                                    <td>
                                        <span class="badge bg-danger">
                                            Di bawah target
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>

                    </table>
                </div>

            </div>

        @endif

    </div>

</div>

@endsection
