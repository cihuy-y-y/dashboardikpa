@extends('layouts.app')
@section('title', 'Upload Excel - ' . config('ikpa.name'))

@section('content')
    <div class="container-fluid">
        <div class="panel-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="panel-title mb-1">
                        <i class="bi bi-upload me-2"></i>
                        Upload Data Excel IKPA
                    </h4>
                    <small class="text-muted">
                        Upload file Excel IKPA sesuai periode (bulan &amp; tahun) yang akan dimonitor.
                    </small>
                </div>
            </div>

            {{-- Pesan hasil proses --}}
            @if (session('pesan_sukses'))
                <div class="alert alert-success">{{ session('pesan_sukses') }}</div>
            @endif

            @if ($errors->any() || !empty(session('galat')))
                <div class='alert alert-danger'>
                    <ul class="mb-0">
                        @foreach ($errors->all() as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                        @foreach ((array) session('galat') as $pesan)
                            <li>{{ $pesan }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('hasil'))
                <div class='alert alert-success'>
                    Upload selesai untuk periode <b>{{ session('hasil')['periode'] }}</b>.<br>
                    Data baru : <b>{{ session('hasil')['baru'] }}</b><br>
                    Data diperbarui : <b>{{ session('hasil')['diperbarui'] }}</b><br>
                    Data tidak berubah : <b>{{ session('hasil')['sama'] }}</b>
                </div>
            @endif

            <form action="{{ route('upload.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-md-3">
                        <label class="form-label">Tahun</label>
                        <select class="form-select" name="tahun" required>
                            <option value="">-- Pilih Tahun --</option>
                            @foreach ($daftarTahun as $th)
                                <option value="{{ $th }}" {{ (string) old('tahun') === (string) $th ? 'selected' : '' }}>{{ $th }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bulan</label>
                        <select class="form-select" name="bulan" required>
                            <option value="">-- Pilih Bulan --</option>
                            @foreach (['01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April', '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus', '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember'] as $kode => $nama)
                                <option value="{{ $kode }}" {{ old('bulan') === $kode ? 'selected' : '' }}>{{ $kode }} - {{ $nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">File Excel</label>
                        <input type="file" class="form-control" name="file" accept=".xlsx,.xls" required>
                    </div>
                    <div class="col-md-2 d-grid">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary" name="submit">
                            <i class="bi bi-upload"></i>
                            Upload
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <div class="panel-card mt-4">
            <h5 class="panel-title">
                <i class="bi bi-clock-history me-2"></i>
                Riwayat Upload
            </h5>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th width="60">No</th>
                            <th>Periode</th>
                            <th>Nama File</th>
                            <th>Jumlah Satker</th>
                            <th>Tanggal Upload</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($riwayatUpload as $index => $row)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ formatLabelPeriode($row->periode) }}</td>
                                <td>{{ $row->nama_file }}</td>
                                <td>{{ $row->jumlah_data ?: '-' }}</td>
                                <td>{{ date('d M Y H:i', strtotime($row->tanggal_upload)) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('upload.destroy', $row->periode) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus seluruh data periode {{ formatLabelPeriode($row->periode) }} ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            <i class="bi bi-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">Belum ada riwayat upload.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
