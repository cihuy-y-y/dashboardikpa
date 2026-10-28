<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * Memasukkan baris-baris hasil baca Excel ke tabel monitoring_ikpa.
 * Pengganti isi perulangan di aksi.php lama. Aturan kolom Excel TIDAK berubah:
 * data dimulai dari baris ke-5 (index 4), kolom C = KPPN, D = BA, E = Satker, dst.
 */
class IkpaImporter
{
    /** Urutan kolom Excel (index 2 dst) => nama kolom di database */
    private const KOLOM_TEKS = [
        2 => 'kode_kppn',
        3 => 'kode_ba',
        4 => 'kode_satker',
        5 => 'nama_satker',
        6 => 'keterangan',
    ];

    private const KOLOM_ANGKA = [
        7  => 'revisi_dipa',
        8  => 'deviasi_halaman_iii',
        9  => 'nilai_aspek_perencanaan',
        10 => 'penyerapan_anggaran',
        11 => 'belanja_kontraktual',
        12 => 'penyelesaian_tagihan',
        13 => 'pengelolaan_up_tup',
        14 => 'nilai_aspek_pelaksanaan',
        15 => 'capaian_output',
        16 => 'nilai_aspek_hasil',
        17 => 'nilai_total',
        18 => 'konversi_bobot',
        19 => 'dispensasi_spm',
        20 => 'nilai_akhir',
    ];

    /**
     * @param  array  $rows     hasil $sheet->toArray()
     * @param  string $periode  format "YYYY-MM", contoh 2026-01
     * @return array ['baru' => int, 'diperbarui' => int, 'sama' => int, 'galat' => string[]]
     */
    public static function import(array $rows, string $periode): array
    {
        $kolom = array_merge(['periode'], array_values(self::KOLOM_TEKS), array_values(self::KOLOM_ANGKA));

        // Kolom yang diperbarui jika kombinasi (periode + kode_satker) sudah ada
        $kolomUpdate = array_diff($kolom, ['periode', 'kode_satker']);

        $sql = 'INSERT INTO monitoring_ikpa (' . implode(', ', $kolom) . ') VALUES ('
            . implode(', ', array_fill(0, count($kolom), '?')) . ') ON DUPLICATE KEY UPDATE '
            . implode(', ', array_map(fn ($k) => "$k = VALUES($k)", $kolomUpdate))
            . ', tanggal_upload = CURRENT_TIMESTAMP';

        $hasil = ['baru' => 0, 'diperbarui' => 0, 'sama' => 0, 'galat' => []];

        foreach ($rows as $index => $row) {
            // 4 baris pertama adalah judul/header
            if ($index < 4) {
                continue;
            }

            $kodeSatker = trim((string) ($row[4] ?? ''));
            if (empty($kodeSatker)) {
                continue;
            }

            $nilai = [$periode];
            foreach (self::KOLOM_TEKS as $i => $nama) {
                $nilai[] = trim((string) ($row[$i] ?? ''));
            }
            foreach (self::KOLOM_ANGKA as $i => $nama) {
                $nilai[] = self::angka($row[$i] ?? null);
            }

            try {
                $affected = DB::affectingStatement($sql, $nilai);

                if ($affected == 1) {
                    $hasil['baru']++;
                } elseif ($affected == 2) {
                    $hasil['diperbarui']++;
                } else {
                    $hasil['sama']++;
                }
            } catch (\Throwable $e) {
                $pesan = $e instanceof \Illuminate\Database\QueryException
                    ? ($e->errorInfo[2] ?? 'gagal menyimpan')
                    : $e->getMessage();
                $hasil['galat'][] = 'Baris Excel ' . ($index + 1) . ' (satker ' . $kodeSatker . '): ' . $pesan;
            }
        }

        return $hasil;
    }

    /**
     * Ubah isi sel Excel menjadi angka yang aman untuk kolom DECIMAL.
     * "95,5" -> "95.5", "100%" -> "100", kosong / bukan angka -> "0"
     */
    private static function angka($nilai): string
    {
        if ($nilai === null) {
            return '0';
        }

        $s = trim(str_replace(['%', ','], ['', '.'], (string) $nilai));

        return is_numeric($s) ? $s : '0';
    }
}
