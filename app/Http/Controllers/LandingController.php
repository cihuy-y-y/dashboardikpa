<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil periode terbaru
        $periode = DB::table('monitoring_ikpa')
            ->whereNotNull('periode')
            ->orderByDesc('periode')
            ->value('periode');

        // Query dasar untuk periode terbaru
        $dataSatker = DB::table('monitoring_ikpa')
            ->where('periode', $periode)
            ->whereNotNull('nilai_akhir');

        // Ringkasan data
        $jumlahSatker = (clone $dataSatker)
            ->distinct('kode_satker')
            ->count('kode_satker');

        $rataRata = (clone $dataSatker)
            ->avg('nilai_akhir') ?? 0;

        // Menampilkan 6 satker secara acak
        $satkerAcak = (clone $dataSatker)
            ->select('kode_satker', 'nama_satker', 'nilai_akhir')
            ->inRandomOrder()
            ->limit(6)
            ->get();

        // Pencarian berdasarkan kode atau nama satker
        $keyword = $request->input('cari');

        $hasilPencarian = collect();

        if ($keyword) {
            $hasilPencarian = (clone $dataSatker)
                ->where(function ($query) use ($keyword) {
                    $query->where('kode_satker', 'like', "%{$keyword}%")
                        ->orWhere('nama_satker', 'like', "%{$keyword}%");
                })
                ->select('kode_satker', 'nama_satker', 'nilai_akhir')
                ->orderBy('nama_satker')
                ->get();
        }

        return view('landing', compact(
            'periode',
            'jumlahSatker',
            'rataRata',
            'satkerAcak',
            'keyword',
            'hasilPencarian'
        ));
    }
}