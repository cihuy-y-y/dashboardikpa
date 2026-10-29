@extends('layouts.app')
@section('title', 'Approval User - ' . config('ikpa.name'))

@section('content')
    <div class="panel-card mb-3">
        <h6 class="panel-title">Persetujuan Registrasi User</h6>

        @if (session('pesan'))
            <div class="alert alert-success">{{ session('pesan') }}</div>
        @endif

        <h6 class="mt-3">Menunggu Persetujuan ({{ count($daftarPending) }})</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($daftarPending as $u)
                        <tr>
                            <td>{{ $u->username }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->tanggal_daftar }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.approval.status', $u->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" name="aksi" value="approve" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-lg"></i> Approve
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.approval.status', $u->id) }}" class="d-inline">
                                    @csrf
                                    <button type="submit" name="aksi" value="reject" class="btn btn-sm btn-danger">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted">Tidak ada user menunggu persetujuan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card">
        <h6 class="panel-title">Riwayat Semua User</h6>
        <div class="table-responsive">
            <table class="table table-bordered table-sm align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Tanggal Daftar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($semuaUser as $u)
                        <tr>
                            <td>{{ $u->username }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                <span class="badge bg-{{ $u->status === 'approved' ? 'success' : ($u->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ $u->status }}
                                </span>
                            </td>
                            <td>{{ $u->tanggal_daftar }}</td>
                            <td>
                                @if ((int) $u->id !== (int) auth()->id())
                                    <form method="POST" action="{{ route('admin.approval.hapus', $u->id) }}"
                                          onsubmit="return confirm('Yakin ingin menghapus user ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>
                                    </form>
                                @else
                                    <span class="badge bg-secondary">Akun Anda</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
