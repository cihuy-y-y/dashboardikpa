<?php

namespace App\Http\Controllers;

use App\Support\IkpaData;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    // Pengganti laporan.php
    public function index(Request $request)
    {
        $jenisLaporan = $request->string('jenis')->toString();
        if (!in_array($jenisLaporan, ['satker', 'ba', 'periode'], true)) {
            $jenisLaporan = 'satker';
        }

        $daftarPeriode = IkpaData::getDaftarPeriodeLaporan();
        $periodeDefault = array_key_first($daftarPeriode) ?: '01';

        $periodeKey = $request->string('periode')->toString();
        if (!array_key_exists($periodeKey, $daftarPeriode)) {
            $periodeKey = (string) $periodeDefault;
        }
        $periodeLabel = $daftarPeriode[$periodeKey] ?? 'Periode ' . $periodeKey;

        $filters = $this->ambilFilter($request);

        $rows = $jenisLaporan === 'ba'
            ? IkpaData::getRekapPerBA($periodeKey, $filters)
            : IkpaData::getRekapSeluruhSatker($periodeKey, $filters);

        return view('laporan', [
            'jenisLaporan'  => $jenisLaporan,
            'jenis'         => $jenisLaporan === 'ba' ? 'ba' : 'satker',
            'daftarPeriode' => $daftarPeriode,
            'periodeKey'    => $periodeKey,
            'periodeLabel'  => $periodeLabel,
            'filterOptions' => IkpaData::getFilterOptions(),
            'filterKppn'    => $filters['kppn'] ?? '',
            'filterBa'      => $filters['ba'] ?? '',
            'filterSatker'  => $filters['satker'] ?? '',
            'rows'          => $rows,
            // dipakai untuk tombol Export Excel / PDF agar filter ikut terbawa
            'queryExport'   => array_merge(['jenis' => $jenisLaporan, 'periode' => $periodeKey], $filters),
        ]);
    }

    // Pengganti laporan-export.php
    public function export(Request $request)
    {
        $format  = $request->string('format', 'excel')->toString();
        $jenis   = $request->string('jenis', 'satker')->toString() === 'ba' ? 'ba' : 'satker';
        $periode = $request->string('periode', '01')->toString();
        $filters = $this->ambilFilter($request);

        $daftarPeriode = IkpaData::getDaftarPeriodeLaporan();
        $periodeLabel  = $daftarPeriode[$periode] ?? 'Periode ' . $periode;

        $rows = $jenis === 'ba'
            ? IkpaData::getRekapPerBA($periode, $filters)
            : IkpaData::getRekapSeluruhSatker($periode, $filters);

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('laporan-pdf', compact('jenis', 'periodeLabel', 'rows'))
            ->setPaper('a4', $jenis === 'ba' ? 'portrait' : 'landscape');
            
            return $pdf->download('Laporan_' . $jenis . '_' . $periode . '.pdf');
        }

        if ($format === 'excel') {
            return $this->exportCsv($jenis, $rows);
        }

        abort(404);
    }

    /** Hanya filter yang terisi (sama seperti kode lama). */
    private function ambilFilter(Request $request): array
    {
        $filters = [];
        foreach (['kppn', 'ba', 'satker'] as $kunci) {
            $nilai = trim($request->string($kunci)->toString());
            if ($nilai !== '') {
                $filters[$kunci] = $nilai;
            }
        }

        return $filters;
    }

    /** File CSV (pemisah titik koma, BOM UTF-8) yang langsung terbuka di Excel. */
    private function exportCsv(string $jenis, array $rows)
    {
        $namaFile = 'Laporan_' . $jenis . '_' . date('Y-m-d_H-i-s') . '.csv';
        $koma = fn ($angka) => str_replace('.', ',', (string) $angka);

        return response()->streamDownload(function () use ($jenis, $rows, $koma) {
            $out = fopen('php://output', 'w');
            fwrite($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM

            if ($jenis === 'ba') {
                fputcsv($out, ['Kode BA', 'Nama BA', 'Jumlah Satker', 'Nilai Tertinggi', 'Nilai Terendah', 'Rata-rata Nilai'], ';', '"', '\\');
                foreach ($rows as $row) {
                    fputcsv($out, [
                        $row['kode_ba'],
                        $row['nama_ba'] ?? '-',
                        $row['jumlah_satker'],
                        $koma($row['nilai_tertinggi']),
                        $koma($row['nilai_terendah']),
                        $koma($row['rata_rata']),
                    ], ';', '"', '\\');
                }
            } else {
                fputcsv($out, ['No', 'Kode Satker', 'Nama Satker', 'Revisi DIPA', 'Deviasi Hal. III', 'Penyerapan',
                    'Belanja Kontraktual', 'Penyelesaian Tagihan', 'UP/TUP', 'Capaian Output', 'Nilai Total', 'Nilai Akhir'], ';', '"', '\\');
                foreach ($rows as $i => $row) {
                    fputcsv($out, [
                        $i + 1,
                        $row['kode_satker'],
                        $row['nama_satker'],
                        $koma($row['revisi_dipa']),
                        $koma($row['deviasi_dipa']),
                        $koma($row['penyerapan']),
                        $koma($row['belanja_kontraktual']),
                        $koma($row['penyelesaian_tagihan']),
                        $koma($row['up_tup']),
                        $koma($row['capaian_output']),
                        $koma($row['nilai_total']),
                        $koma($row['nilai_akhir']),
                    ], ';', '"', '\\');
                }
            }

            fclose($out);
        }, $namaFile, [
            'Content-Type'  => 'text/csv; charset=utf-8',
            'Pragma'        => 'no-cache',
            'Expires'       => '0',
        ]);
    }
}
