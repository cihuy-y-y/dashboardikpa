<?php

namespace App\Http\Controllers;

use App\Models\Pengguna;
use Illuminate\Http\Request;

/**
 * Pengganti admin-approval.php. Hanya admin (lihat middleware AdminOnly di routes/web.php).
 */
class ApprovalController extends Controller
{
    public function index()
    {
        return view('admin.approval', [
            'daftarPending' => Pengguna::where('status', 'pending')->orderBy('tanggal_daftar')->get(),
            'semuaUser'     => Pengguna::orderByDesc('tanggal_daftar')->get(),
        ]);
    }

    public function ubahStatus(Request $request, Pengguna $pengguna)
    {
        $request->validate(['aksi' => ['required', 'in:approve,reject']]);

        if ((int) $pengguna->id === (int) $request->user()->id) {
            return redirect()->route('admin.approval')->with('pesan', 'Anda tidak dapat mengubah status akun yang sedang digunakan.');
        }

        $status = $request->aksi === 'approve' ? 'approved' : 'rejected';
        $pengguna->status = $status;
        $pengguna->save();

        return redirect()->route('admin.approval')
            ->with('pesan', "Status user \"{$pengguna->username}\" berhasil diubah menjadi \"{$status}\".");
    }

    public function destroy(Request $request, Pengguna $pengguna)
    {
        if ((int) $pengguna->id === (int) $request->user()->id) {
            return redirect()->route('admin.approval')->with('pesan', 'Anda tidak dapat menghapus akun yang sedang digunakan.');
        }

        $nama = $pengguna->username;
        $pengguna->delete();

        return redirect()->route('admin.approval')->with('pesan', "User \"{$nama}\" berhasil dihapus.");
    }
}
