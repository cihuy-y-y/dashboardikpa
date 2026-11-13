
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PeringatanController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil daftar periode dari database
        $daftarPeriode = DB::table('monitoring_ikpa')
            ->select('periode')
            ->distinct()
            ->orderByDesc('periode')
            ->pluck('periode');

        // Mengambil periode terbaru jika belum ada pilihan
        $periode = $request->input(
            'periode',
            $daftarPeriode->first()
        );

        // Batas minimum IKPA adalah 89
        $batas = (float) $request->input('batas', 89);
        $batas = max(0, min(100, $batas));

        // Mengambil data satker pada periode terpilih
        $semua = DB::table('monitoring_ikpa')
            ->where('periode', $periode)
            ->whereNotNull('nilai_akhir')
            ->select(
                'kode_satker',
                'nama_satker',
                'nilai_akhir'
            )
            ->get();

        // Memilih satker dengan nilai di bawah batas
        $peringatan = $semua
            ->filter(function ($satker) use ($batas) {
                return (float) $satker->nilai_akhir < $batas;
            })
            ->sortBy('nilai_akhir')
            ->values()
            ->map(function ($satker) use ($batas) {
                $satker->selisih = round(
                    $batas - (float) $satker->nilai_akhir,
                    2
                );

                return $satker;
            });

        return view('peringatan', compact(
            'daftarPeriode',
            'periode',
            'batas',
            'semua',
            'peringatan'
        ));
    }
}
