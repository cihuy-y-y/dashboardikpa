<?php

namespace App\Http\Controllers;

use App\Support\IkpaPanduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LandingController extends Controller
{
    public function index(Request $request)
    {
        $target = IkpaPanduan::TARGET;

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
            ->select(
                'kode_satker', 'nama_satker',
                'revisi_dipa', 'deviasi_halaman_iii', 'penyerapan_anggaran',
                'belanja_kontraktual', 'penyelesaian_tagihan', 'pengelolaan_up_tup',
                'capaian_output', 'dispensasi_spm', 'konversi_bobot',
                'nilai_aspek_perencanaan', 'nilai_aspek_pelaksanaan', 'nilai_aspek_hasil',
                'nilai_total', 'nilai_akhir'
            )
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
        $jumlahSatker    = $semuaSatker->count();
        $rataRata        = $jumlahSatker > 0 ? $semuaSatker->avg('nilai_akhir') : 0;
        $jumlahMemenuhi  = $semuaSatker->where('nilai_akhir', '>=', $target)->count();
        $jumlahPerhatian = $jumlahSatker - $jumlahMemenuhi;
        $nilaiTertinggi  = $semuaSatker->max('nilai_akhir') ?? 0;
        $nilaiTerendah   = $semuaSatker->min('nilai_akhir') ?? 0;
        $teratas         = $semuaSatker->take(5);

        // ---- Tren rata-rata per periode (untuk grafik) ----
        $tren = DB::table('monitoring_ikpa')
            ->whereNotNull('nilai_akhir')
            ->whereNotNull('periode')
            ->selectRaw('periode, AVG(nilai_akhir) AS rata_rata')
            ->groupBy('periode')
            ->orderBy('periode')
            ->get();

        // ---- Riwayat nilai tiap satker (untuk grafik mini di panel rincian) ----
        $riwayat = DB::table('monitoring_ikpa')
            ->whereNotNull('nilai_akhir')
            ->whereNotNull('periode')
            ->select('kode_satker', 'periode', 'nilai_akhir')
            ->orderBy('periode')
            ->get()
            ->groupBy('kode_satker');

        return view('landing', [
            'target'          => $target,
            'daftarPeriode'   => $daftarPeriode,
            'periode'         => $periode,
            'keyword'         => trim($request->string('cari')->toString()),
            'bukaSatker'      => trim($request->string('satker')->toString()),
            'semuaSatker'     => $semuaSatker,
            'teratas'         => $teratas,
            'jumlahSatker'    => $jumlahSatker,
            'rataRata'        => $rataRata,
            'jumlahMemenuhi'  => $jumlahMemenuhi,
            'jumlahPerhatian' => $jumlahPerhatian,
            'nilaiTertinggi'  => $nilaiTertinggi,
            'nilaiTerendah'   => $nilaiTerendah,
            'tren'            => $tren,
            'dataRapor'       => $this->dataRapor($semuaSatker, $riwayat),
            'panduan'         => IkpaPanduan::data(),
        ]);
    }

    /**
     * Data ringkas per satker untuk panel rincian & Asisten IKPA (dikirim ke JavaScript).
     * Nilai kosong dikirim sebagai null supaya tidak dianggap 0.
     */
    private function dataRapor($semuaSatker, $riwayat): array
    {
        $angka = fn ($v) => $v === null || $v === '' ? null : round((float) $v, 2);

        // Rata-rata tiap indikator (pembanding "rata-rata satker lain")
        $peta = [
            'revisi'      => 'revisi_dipa',
            'deviasi'     => 'deviasi_halaman_iii',
            'penyerapan'  => 'penyerapan_anggaran',
            'kontraktual' => 'belanja_kontraktual',
            'tagihan'     => 'penyelesaian_tagihan',
            'uptup'       => 'pengelolaan_up_tup',
            'output'      => 'capaian_output',
        ];

        $rata = [];
        foreach ($peta as $kunci => $kolom) {
            $nilai = $semuaSatker->pluck($kolom)->filter(fn ($v) => $v !== null && $v !== '');
            $rata[$kunci] = $nilai->count() > 0 ? round($nilai->avg(fn ($v) => (float) $v), 2) : null;
        }

        $satker = [];
        foreach ($semuaSatker as $s) {
            $ind = [];
            foreach ($peta as $kunci => $kolom) {
                $ind[$kunci] = $angka($s->$kolom);
            }

            $hist = [];
            foreach ($riwayat->get($s->kode_satker, collect()) as $r) {
                $hist[] = ['l' => formatLabelPeriode($r->periode), 'v' => $angka($r->nilai_akhir)];
            }

            $satker[] = [
                'kode'       => (string) $s->kode_satker,
                'nama'       => (string) $s->nama_satker,
                'peringkat'  => $s->peringkat,
                'nilai'      => $angka($s->nilai_akhir),
                'ind'        => $ind,
                'dispensasi' => $angka($s->dispensasi_spm),
                'konversi'   => $angka($s->konversi_bobot),
                'total'      => $angka($s->nilai_total),
                'aspek'      => [
                    'perencanaan' => $angka($s->nilai_aspek_perencanaan),
                    'pelaksanaan' => $angka($s->nilai_aspek_pelaksanaan),
                    'hasil'       => $angka($s->nilai_aspek_hasil),
                ],
                'riwayat'    => $hist,
            ];
        }

        return ['rata' => $rata, 'satker' => $satker, 'jumlah' => count($satker)];
    }
}
