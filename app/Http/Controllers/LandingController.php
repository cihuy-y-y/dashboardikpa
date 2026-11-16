<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingController extends Controller
{
    /** Batas nilai IKPA yang dianggap memenuhi target */
    private const TARGET = 89;

    public function index(Request $request)
    {
        // ---- Daftar periode & periode terpilih (default: terbaru) ----
        $daftarPeriode = DB::table('monitoring_ikpa')
            ->whereNotNull('periode')
            ->distinct()
            ->orderByDesc('periode')
            ->pluck('periode');

        $periode = $request->string('periode')->toString();
        if (! $daftarPeriode->contains($periode)) {
            $periode = $daftarPeriode->first();
        }

        // ---- Seluruh satker pada periode terpilih, urut nilai tertinggi ----
        $semuaSatker = DB::table('monitoring_ikpa')
            ->where('periode', $periode)
            ->whereNotNull('nilai_akhir')
            ->select('kode_satker', 'nama_satker', 'nilai_akhir')
            ->orderByDesc('nilai_akhir')
            ->orderBy('nama_satker')
            ->get();

        // Peringkat: nilai yang sama mendapat peringkat yang sama
        $peringkat = 0;
        $nilaiSebelumnya = null;
        foreach ($semuaSatker as $i => $satker) {
            $satker->nilai_akhir = (float) $satker->nilai_akhir;
            if ($nilaiSebelumnya === null || $satker->nilai_akhir < $nilaiSebelumnya) {
                $peringkat = $i + 1;
            }
            $satker->peringkat = $peringkat;
            $nilaiSebelumnya = $satker->nilai_akhir;
        }

        // ---- Ringkasan ----
        $jumlahSatker   = $semuaSatker->count();
        $rataRata       = $jumlahSatker > 0 ? $semuaSatker->avg('nilai_akhir') : 0;
        $jumlahMemenuhi = $semuaSatker->where('nilai_akhir', '>=', self::TARGET)->count();
        $jumlahPerhatian = $jumlahSatker - $jumlahMemenuhi;
        $nilaiTertinggi = $semuaSatker->max('nilai_akhir') ?? 0;
        $nilaiTerendah  = $semuaSatker->min('nilai_akhir') ?? 0;
        $teratas        = $semuaSatker->take(5);

        // ---- Tren rata-rata per periode (untuk grafik) ----
        $tren = DB::table('monitoring_ikpa')
            ->whereNotNull('nilai_akhir')
            ->whereNotNull('periode')
            ->selectRaw('periode, AVG(nilai_akhir) AS rata_rata')
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        return view('landing', [
            'target'          => self::TARGET,
            'daftarPeriode'   => $daftarPeriode,
            'periode'         => $periode,
            'keyword'         => trim($request->string('cari')->toString()),
            'semuaSatker'     => $semuaSatker,
            'teratas'         => $teratas,
            'jumlahSatker'    => $jumlahSatker,
            'rataRata'        => $rataRata,
            'jumlahMemenuhi'  => $jumlahMemenuhi,
            'jumlahPerhatian' => $jumlahPerhatian,
            'nilaiTertinggi'  => $nilaiTertinggi,
            'nilaiTerendah'   => $nilaiTerendah,
            'tren'            => $tren,
        ]);
    }
}
