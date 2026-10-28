<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Pengganti data.php lama.
 * Semua fungsi pengambil data Dashboard IKPA dikumpulkan di sini.
 * Cara pakai:  IkpaData::getFilterOptions();  IkpaData::getRingkasanNilai($filters);
 *
 * Logika perhitungan dibiarkan SAMA dengan data.php lama. Yang diganti
 * hanya cara mengakses database (mysqli -> Laravel DB + prepared statement).
 */
class IkpaData
{
    /**
     * Jalankan query SELECT dan kembalikan array of array (sama seperti
     * hasil mysqli_fetch_assoc yang lama). Jika error, dicatat ke log dan
     * dikembalikan array kosong.
     */
    private static function query(string $sql, array $bindings = []): array
    {
        try {
            return array_map(fn ($row) => (array) $row, DB::select($sql, $bindings));
        } catch (\Throwable $e) {
            Log::error('IkpaData query gagal: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Ambil baris data monitoring_ikpa dengan filter opsional.
     */
    public static function getMonitoringRows($filters = [])
    {
        $where = [];
        $bind = [];
        $filters = array_map(fn ($value) => trim((string) $value), $filters);

        if (!empty($filters['periode'])) {
            $where[] = 'periode = ?';
            $bind[] = $filters['periode'];
        }
        if (!empty($filters['kppn'])) {
            $where[] = 'kode_kppn = ?';
            $bind[] = $filters['kppn'];
        }
        if (!empty($filters['ba'])) {
            $where[] = 'kode_ba = ?';
            $bind[] = $filters['ba'];
        }
        if (!empty($filters['satker'])) {
            $where[] = 'kode_satker = ?';
            $bind[] = $filters['satker'];
        }
        if (!empty($filters['tahun'])) {
            $where[] = 'YEAR(tanggal_upload) = ?';
            $bind[] = $filters['tahun'];
        }
        if (!empty($filters['bulan'])) {
            $where[] = 'MONTH(tanggal_upload) = ?';
            $bind[] = $filters['bulan'];
        }

        $sql = 'SELECT
                    periode, kode_kppn, kode_ba, kode_satker, nama_satker,
                    revisi_dipa, deviasi_halaman_iii, nilai_aspek_perencanaan,
                    penyerapan_anggaran, belanja_kontraktual, penyelesaian_tagihan,
                    pengelolaan_up_tup, nilai_aspek_pelaksanaan, capaian_output,
                    nilai_aspek_hasil, nilai_total, konversi_bobot, dispensasi_spm,
                    nilai_akhir, tanggal_upload
                FROM monitoring_ikpa';

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        return self::query($sql, $bind);
    }

    /**
     * Daftar master satker (untuk halaman Data Satker).
     */
    public static function getMasterSatker($filters = [])
    {
        $where = [];
        $bind = [];
        if (!empty($filters['kppn'])) {
            $where[] = 'kode_kppn = ?';
            $bind[] = $filters['kppn'];
        }
        if (!empty($filters['ba'])) {
            $where[] = 'kode_ba = ?';
            $bind[] = $filters['ba'];
        }
        if (!empty($filters['satker'])) {
            $where[] = 'kode_satker = ?';
            $bind[] = $filters['satker'];
        }

        $sql = 'SELECT
                    kode_satker,
                    MAX(nama_satker) AS nama_satker,
                    MAX(kode_ba) AS kode_ba,
                    MAX(kode_kppn) AS kode_kppn
                FROM monitoring_ikpa';

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY kode_satker ORDER BY kode_satker ASC';

        $data = [];
        foreach (self::query($sql, $bind) as $row) {
            $row['nama_ba'] = '';
            $row['nama_kppn'] = 'KPPN Solok';
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Daftar periode untuk laporan (dipakai di Tahap D).
     */
    public static function getDaftarPeriodeLaporan()
    {
        $bulanNama = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        $result = [];
        foreach (self::query('SELECT DISTINCT periode FROM monitoring_ikpa ORDER BY periode ASC') as $row) {
            $periode = $row['periode'];
            $result[$periode] = $bulanNama[$periode] ?? 'Periode ' . $periode;
        }

        return $result;
    }

    /**
     * Rekap Nilai IKPA seluruh satker untuk satu periode (dipakai di Tahap D).
     */
    public static function getRekapSeluruhSatker($periodeKey = '01', $filters = [])
    {
        $where = [];
        $bind = [];

        if (!empty($periodeKey)) {
            $where[] = 'periode = ?';
            $bind[] = $periodeKey;
        }
        if (!empty($filters['kppn'])) {
            $where[] = 'kode_kppn = ?';
            $bind[] = $filters['kppn'];
        }
        if (!empty($filters['ba'])) {
            $where[] = 'kode_ba = ?';
            $bind[] = $filters['ba'];
        }
        if (!empty($filters['satker'])) {
            $where[] = 'kode_satker = ?';
            $bind[] = $filters['satker'];
        }

        $sql = 'SELECT
                    periode, kode_kppn, kode_ba, kode_satker, nama_satker,
                    revisi_dipa,
                    deviasi_halaman_iii AS deviasi_dipa,
                    penyerapan_anggaran AS penyerapan,
                    belanja_kontraktual, penyelesaian_tagihan,
                    pengelolaan_up_tup AS up_tup,
                    capaian_output, nilai_aspek_perencanaan, nilai_aspek_pelaksanaan,
                    nilai_aspek_hasil, nilai_total, konversi_bobot, dispensasi_spm, nilai_akhir
                FROM monitoring_ikpa';

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' ORDER BY nilai_akhir DESC, nama_satker ASC';

        $rekap = [];
        foreach (self::query($sql, $bind) as $row) {
            $rekap[] = [
                'periode'              => $row['periode'],
                'kode_satker'          => $row['kode_satker'],
                'nama_satker'          => $row['nama_satker'],
                'kode_ba'              => $row['kode_ba'],
                'kode_kppn'            => $row['kode_kppn'],
                'revisi_dipa'          => (float) $row['revisi_dipa'],
                'deviasi_dipa'         => (float) $row['deviasi_dipa'],
                'penyerapan'           => (float) $row['penyerapan'],
                'belanja_kontraktual'  => (float) $row['belanja_kontraktual'],
                'penyelesaian_tagihan' => (float) $row['penyelesaian_tagihan'],
                'up_tup'               => (float) $row['up_tup'],
                'capaian_output'       => (float) $row['capaian_output'],
                'nilai_perencanaan'    => (float) $row['nilai_aspek_perencanaan'],
                'nilai_pelaksanaan'    => (float) $row['nilai_aspek_pelaksanaan'],
                'nilai_hasil'          => (float) $row['nilai_aspek_hasil'],
                'nilai_total'          => (float) $row['nilai_total'],
                'konversi_bobot'       => (float) $row['konversi_bobot'],
                'dispensasi_spm'       => (float) $row['dispensasi_spm'],
                'nilai_akhir'          => (float) $row['nilai_akhir'],
            ];
        }

        return $rekap;
    }

    /**
     * Rekap Nilai IKPA per BA (dipakai di Tahap D).
     */
    public static function getRekapPerBA($periodeKey = '01', $filters = [])
    {
        $where = [];
        $bind = [];

        if (!empty($periodeKey)) {
            $where[] = 'periode = ?';
            $bind[] = $periodeKey;
        }
        if (!empty($filters['kppn'])) {
            $where[] = 'kode_kppn = ?';
            $bind[] = $filters['kppn'];
        }
        if (!empty($filters['ba'])) {
            $where[] = 'kode_ba = ?';
            $bind[] = $filters['ba'];
        }
        if (!empty($filters['satker'])) {
            $where[] = 'kode_satker = ?';
            $bind[] = $filters['satker'];
        }

        $sql = 'SELECT
                    kode_ba,
                    MAX(kode_ba) AS nama_ba,
                    COUNT(DISTINCT kode_satker) AS jumlah_satker,
                    MIN(nilai_akhir) AS nilai_terendah,
                    MAX(nilai_akhir) AS nilai_tertinggi,
                    AVG(nilai_akhir) AS rata_rata,
                    SUM(nilai_akhir) AS total_nilai
                FROM monitoring_ikpa';

        if (!empty($where)) {
            $sql .= ' WHERE ' . implode(' AND ', $where);
        }

        $sql .= ' GROUP BY kode_ba ORDER BY rata_rata DESC';

        $hasil = [];
        foreach (self::query($sql, $bind) as $row) {
            $hasil[] = [
                'kode_ba'         => $row['kode_ba'],
                'nama_ba'         => $row['nama_ba'] ?: $row['kode_ba'],
                'jumlah_satker'   => (int) $row['jumlah_satker'],
                'nilai_tertinggi' => round((float) $row['nilai_tertinggi'], 2),
                'nilai_terendah'  => round((float) $row['nilai_terendah'], 2),
                'rata_rata'       => round((float) $row['rata_rata'], 2),
                'total_nilai'     => round((float) $row['total_nilai'], 2),
            ];
        }

        return $hasil;
    }

    // =====================================================================
    // Fungsi perhitungan di bawah ini dipindahkan dari data.php tanpa ubahan
    // logika (hanya dibungkus sebagai metode static).
    // =====================================================================

    /**
     * Data filter dropdown (Periode, KPPN, BA, Satker)
     */
    public static function getFilterOptions()
    {
        $rows = self::getMonitoringRows();

        $periode = [];
        $kppn = [];
        $ba = [];
        $satker = [];
        $tahun = [];
        $seenPeriode = [];
        $seenKppn = [];
        $seenBa = [];
        $seenSatker = [];
        $seenTahun = [];

        foreach ($rows as $row) {
            $periodeValue = trim((string) ($row['periode'] ?? ''));
            if ($periodeValue !== '' && !isset($seenPeriode[$periodeValue])) {
                $seenPeriode[$periodeValue] = true;
                $periodeLabel = $periodeValue;
                $ts = strtotime($periodeValue);
                if ($ts !== false) {
                    $periodeLabel = date('m - F Y', $ts);
                }
                $periode[] = ['value' => $periodeValue, 'label' => $periodeLabel];
            }

            $kppnValue = trim((string) ($row['kode_kppn'] ?? ''));
            if ($kppnValue !== '' && !isset($seenKppn[$kppnValue])) {
                $seenKppn[$kppnValue] = true;
                $kppn[] = ['value' => $kppnValue, 'label' => $kppnValue];
            }

            $baValue = trim((string) ($row['kode_ba'] ?? ''));
            if ($baValue !== '' && !isset($seenBa[$baValue])) {
                $seenBa[$baValue] = true;
                $ba[] = ['value' => $baValue, 'label' => $baValue];
            }

            $satkerValue = trim((string) ($row['kode_satker'] ?? ''));
            $satkerLabel = trim((string) ($row['nama_satker'] ?? ''));
            if ($satkerValue !== '' && !isset($seenSatker[$satkerValue])) {
                $seenSatker[$satkerValue] = true;
                $label = $satkerValue;
                if ($satkerLabel !== '') {
                    $label = $satkerValue . ' - ' . $satkerLabel;
                }
                $satker[] = ['value' => $satkerValue, 'label' => $label];
            }

            // Tahun (dari tanggal_upload jika ada, fallback ke periode)
            $tahunValue = '';
            $tanggalUpload = trim((string) ($row['tanggal_upload'] ?? ''));
            if ($tanggalUpload !== '') {
                $ts = strtotime($tanggalUpload);
                if ($ts !== false) {
                    $tahunValue = date('Y', $ts);
                }
            }
            if ($tahunValue === '') {
                $ts2 = strtotime($row['periode'] ?? '');
                if ($ts2 !== false) {
                    $tahunValue = date('Y', $ts2);
                }
            }
            if ($tahunValue !== '' && !isset($seenTahun[$tahunValue])) {
                $seenTahun[$tahunValue] = true;
                $tahun[] = ['value' => $tahunValue, 'label' => $tahunValue];
            }
        }

        if (empty($periode) && empty($kppn) && empty($ba) && empty($satker) && empty($tahun)) {
            return [
                'periode' => [['value' => '', 'label' => 'Belum ada data']],
                'kppn' => [['value' => '', 'label' => 'Belum ada data']],
                'ba' => [['value' => '', 'label' => 'Belum ada data']],
                'satker' => [['value' => '', 'label' => 'Belum ada data']],
                'tahun' => [['value' => '', 'label' => 'Belum ada data']],
            ];
        }

        return [
            'periode' => $periode,
            'kppn' => $kppn,
            'ba' => $ba,
            'satker' => $satker,
            'tahun' => $tahun,
        ];
    }

    /**
     * Ringkasan kartu (card) nilai utama di bagian atas dashboard
     */
    public static function getRingkasanNilai($filters = [])
    {
        $rows = self::getMonitoringRows($filters);
        if (empty($rows)) {
            return [
                'nilai_akhir' => 0.00,
                'nilai_total' => 0.00,
                'konversi_bobot' => 0.00,
                'dispensasi_spm' => 0.00,
            ];
        }

        $count = count($rows);
        $nilaiAkhirs = array_sum(array_map(function ($row) {
            return (float) ($row['nilai_akhir'] ?? 0);
        }, $rows));
        $nilaiTotals = array_sum(array_map(function ($row) {
            return (float) ($row['nilai_total'] ?? 0);
        }, $rows));
        $konversi = array_sum(array_map(function ($row) {
            return (float) ($row['konversi_bobot'] ?? 0);
        }, $rows));
        $dispensasi = array_sum(array_map(function ($row) {
            return (float) ($row['dispensasi_spm'] ?? 0);
        }, $rows));

        return [
            'nilai_akhir' => round($nilaiAkhirs / $count, 2),
            'nilai_total' => round($nilaiTotals / $count, 2),
            'konversi_bobot' => round($konversi / $count, 2),
            'dispensasi_spm' => round($dispensasi / $count, 2),
        ];
    }

    /**
     * Data 3 aspek penilaian IKPA beserta indikator turunannya
     */
    public static function getAspekPenilaian($filters = [])
    {
        $rows = self::getMonitoringRows($filters);

        if (empty($rows)) {
            return [];
        }

        $avg = function ($field) use ($rows) {
            $total = 0;
            foreach ($rows as $row) {
                $total += (float)($row[$field] ?? 0);
            }
            return round($total / count($rows), 2);
        };

        /* ===========================
           PERENCANAAN
        =========================== */

        $revisi = $avg('revisi_dipa');
        $deviasi = $avg('deviasi_halaman_iii');

        $naRevisi  = round($revisi * 0.10, 2);
        $naDeviasi = round($deviasi * 0.15, 2);

        $nilaiAspekPerencanaan = round($naRevisi + $naDeviasi, 2);

        /* ===========================
           PELAKSANAAN
        =========================== */

        $penyerapan = $avg('penyerapan_anggaran');
        $belanja    = $avg('belanja_kontraktual');
        $tagihan    = $avg('penyelesaian_tagihan');
        $uptup      = $avg('pengelolaan_up_tup');

        $naPenyerapan = round($penyerapan * 0.20, 2);
        $naBelanja    = round($belanja * 0.10, 2);
        $naTagihan    = round($tagihan * 0.10, 2);
        $naUPTUP      = round($uptup * 0.10, 2);

        $nilaiAspekPelaksanaan =
            $naPenyerapan +
            $naBelanja +
            $naTagihan +
            $naUPTUP;

        /* ===========================
           HASIL
        =========================== */
        $capaian = $avg('capaian_output');
        $naCapaian = round($capaian * 0.25, 2);
        $nilaiAspekHasil = $naCapaian;

        return [
            'perencanaan' => [
                'judul' => 'Kualitas Perencanaan Anggaran',
                'bobot_aspek' => 25,
                // nilai 0-100
                'nilai_aspek' => $avg('nilai_aspek_perencanaan'),
                // nilai berbobot (yang tampil di baris NILAI ASPEK)
                'nilai_akhir_aspek' => $nilaiAspekPerencanaan,
                'indikator' => [

                    [
                        'nama' => 'Revisi DIPA',
                        'nilai' => $revisi,
                        'bobot' => 10,
                        'nilai_akhir' => $naRevisi
                    ],

                    [
                        'nama' => 'Deviasi Halaman III DIPA',
                        'nilai' => $deviasi,
                        'bobot' => 15,
                        'nilai_akhir' => $naDeviasi
                    ]
                ]
            ],

            'pelaksanaan' => [
                'judul' => 'Kualitas Pelaksanaan Anggaran',
                'bobot_aspek' => 50,
                'nilai_aspek' => $avg('nilai_aspek_pelaksanaan'),
                'nilai_akhir_aspek' => $nilaiAspekPelaksanaan,
                'indikator' => [

                    [
                        'nama' => 'Penyerapan Anggaran',
                        'nilai' => $penyerapan,
                        'bobot' => 20,
                        'nilai_akhir' => $naPenyerapan
                    ],

                    [
                        'nama' => 'Belanja Kontraktual',
                        'nilai' => $belanja,
                        'bobot' => 10,
                        'nilai_akhir' => $naBelanja
                    ],

                    [
                        'nama' => 'Penyelesaian Tagihan',
                        'nilai' => $tagihan,
                        'bobot' => 10,
                        'nilai_akhir' => $naTagihan
                    ],

                    [
                        'nama' => 'Pengelolaan UP dan TUP',
                        'nilai' => $uptup,
                        'bobot' => 10,
                        'nilai_akhir' => $naUPTUP
                    ]
                ]
            ],

            'hasil' => [
                'judul' => 'Kualitas Hasil Pelaksanaan Anggaran',
                'bobot_aspek' => 25,
                'nilai_aspek' => $avg('nilai_aspek_hasil'),
                'nilai_akhir_aspek' => $nilaiAspekHasil,
                'indikator' => [
                    [
                        'nama' => 'Capaian Output',
                        'nilai' => $capaian,
                        'bobot' => 25,
                        'nilai_akhir' => $naCapaian
                    ]
                ]
            ]
        ];
    }

    /**
     * Data tren nilai akhir per bulan (untuk line chart)
     */
    public static function getTrendBulanan($filters = [])
    {
        $rows = self::getMonitoringRows($filters);
        if (empty($rows)) {
            return ['label' => [], 'data' => [], 'perubahan' => 0, 'perubahan_label' => '0,00%', 'trend_status' => 'stabil'];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $periode = trim((string) ($row['periode'] ?? ''));
            if ($periode === '') {
                continue;
            }

            $ts = strtotime($periode);
            $key = $periode;
            $label = $periode;
            if ($ts !== false) {
                $key = date('Y-m', $ts);
                $label = date('M', $ts);
            }

            if (!isset($grouped[$key])) {
                $grouped[$key] = ['label' => $label, 'total' => 0, 'count' => 0];
            }
            $grouped[$key]['total'] += (float) ($row['nilai_akhir'] ?? 0);
            $grouped[$key]['count']++;
        }

        if (empty($grouped)) {
            return ['label' => [], 'data' => [], 'perubahan' => 0, 'perubahan_label' => '0,00%', 'trend_status' => 'stabil'];
        }

        ksort($grouped);
        $labels = [];
        $data = [];
        foreach ($grouped as $item) {
            $labels[] = $item['label'];
            $data[] = round($item['total'] / $item['count'], 2);
        }

        $totalData = count($data);

        $last = $data[$totalData - 1] ?? 0;
        $previous = $data[$totalData - 2] ?? $last;
        $perubahan = 0;
        if ($previous != 0) {
            $perubahan = (($last - $previous) / abs($previous)) * 100;
        } elseif ($last > 0) {
            $perubahan = 100;
        }

        $trendStatus = 'stabil';
        if ($perubahan > 0) {
            $trendStatus = 'naik';
        } elseif ($perubahan < 0) {
            $trendStatus = 'turun';
        }

        return [
            'label' => $labels,
            'data' => $data,
            'perubahan' => round($perubahan, 2),
            'perubahan_label' => ($perubahan >= 0 ? '+' : '') . number_format($perubahan, 2, ',', '.') . '%',
            'trend_status' => $trendStatus,
        ];
    }

    /**
     * Data Top 10 Satker berdasarkan Nilai Akhir (untuk bar chart & list)
     */
    public static function getTop10Satker($filters = [])
    {
        $rows = self::getMonitoringRows($filters);
        if (empty($rows)) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $kode = trim((string) ($row['kode_satker'] ?? ''));
            $nama = trim((string) ($row['nama_satker'] ?? ''));
            if ($kode === '') {
                continue;
            }

            $key = $kode;
            if (!isset($grouped[$key])) {
                $grouped[$key] = ['nama' => ($nama !== '' ? $nama : $kode), 'total' => 0, 'count' => 0];
            }
            $grouped[$key]['total'] += (float) ($row['nilai_akhir'] ?? 0);
            $grouped[$key]['count']++;
        }

        usort($grouped, function ($a, $b) {
            return ($b['total'] / $b['count']) <=> ($a['total'] / $a['count']);
        });

        $result = [];
        foreach (array_slice($grouped, 0, 10) as $item) {
            $result[] = [
                'nama' => $item['nama'],
                'nilai' => round($item['total'] / $item['count'], 2),
            ];
        }

        return $result;
    }

    /**
     * Data detail tabel Nilai IKPA per Satker (baris bawah dashboard)
     */
    public static function getDetailSatker($filters = [])
    {
        $rows = self::getMonitoringRows($filters);
        if (empty($rows)) {
            return [];
        }

        usort($rows, function ($a, $b) {
            return ((float) ($b['nilai_akhir'] ?? 0)) <=> ((float) ($a['nilai_akhir'] ?? 0));
        });

        $data = [];
        $no = 1;
        foreach ($rows as $row) {
            $data[] = [
                'no' => $no,
                'kode_satker' => trim((string) ($row['kode_satker'] ?? '')),
                'uraian_satker' => trim((string) ($row['nama_satker'] ?? '')),
                'revisi_dipa' => (float) ($row['revisi_dipa'] ?? 0),
                'deviasi_dipa' => (float) ($row['deviasi_halaman_iii'] ?? 0),
                'nilai_aspek_1' => 0.00,
                'penyerapan' => (float) ($row['penyerapan_anggaran'] ?? 0),
                'belanja_kontraktual' => (float) ($row['belanja_kontraktual'] ?? 0),
                'penyelesaian_tagihan' => (float) ($row['penyelesaian_tagihan'] ?? 0),
                'up_tup' => (float) ($row['pengelolaan_up_tup'] ?? 0),
                'nilai_aspek_2' => (float) ($row['nilai_aspek_pelaksanaan'] ?? 0),
                'capaian_output' => (float) ($row['capaian_output'] ?? 0),
                'nilai_aspek_3' => (float) ($row['nilai_aspek_hasil'] ?? 0),
                'nilai_total' => (float) ($row['nilai_total'] ?? 0),
                'konversi_bobot' => (float) ($row['konversi_bobot'] ?? 0),
                'dispensasi_spm' => (float) ($row['dispensasi_spm'] ?? 0),
                'nilai_akhir' => (float) ($row['nilai_akhir'] ?? 0),
            ];
            $no++;
        }

        return $data;
    }

    public static function getSemuaSatker($filters = [])
    {
        $rows = self::getMonitoringRows($filters);
        if (empty($rows)) {
            return [];
        }

        $grouped = [];
        foreach ($rows as $row) {
            $kode = trim((string) ($row['kode_satker'] ?? ''));
            $nama = trim((string) ($row['nama_satker'] ?? ''));
            if ($kode === '') {
                continue;
            }

            if (!isset($grouped[$kode])) {
                $grouped[$kode] = [
                    'kode_satker' => $kode,
                    'nama_satker' => $nama !== '' ? $nama : $kode,
                    'nilai_akhir' => 0.0,
                    'indikator' => [
                        'revisi_dipa' => 0.0,
                        'deviasi_dipa' => 0.0,
                        'penyerapan' => 0.0,
                        'belanja_kontraktual' => 0.0,
                        'penyelesaian_tagihan' => 0.0,
                        'up_tup' => 0.0,
                        'capaian_output' => 0.0,
                    ],
                    'count' => 0,
                ];
            }

            $grouped[$kode]['nilai_akhir'] += (float) ($row['nilai_akhir'] ?? 0);
            $grouped[$kode]['indikator']['revisi_dipa'] += (float) ($row['revisi_dipa'] ?? 0);
            $grouped[$kode]['indikator']['deviasi_dipa'] += (float) ($row['deviasi_halaman_iii'] ?? 0);
            $grouped[$kode]['indikator']['penyerapan'] += (float) ($row['penyerapan_anggaran'] ?? 0);
            $grouped[$kode]['indikator']['belanja_kontraktual'] += (float) ($row['belanja_kontraktual'] ?? 0);
            $grouped[$kode]['indikator']['penyelesaian_tagihan'] += (float) ($row['penyelesaian_tagihan'] ?? 0);
            $grouped[$kode]['indikator']['up_tup'] += (float) ($row['pengelolaan_up_tup'] ?? 0);
            $grouped[$kode]['indikator']['capaian_output'] += (float) ($row['capaian_output'] ?? 0);
            $grouped[$kode]['count']++;
        }

        $result = [];
        foreach ($grouped as $item) {
            $count = max(1, (int) $item['count']);
            $result[] = [
                'kode_satker' => $item['kode_satker'],
                'nama_satker' => $item['nama_satker'],
                'nilai_akhir' => round($item['nilai_akhir'] / $count, 2),
                'indikator' => array_map(function ($value) use ($count) {
                    return round($value / $count, 2);
                }, $item['indikator']),
            ];
        }

        usort($result, function ($a, $b) {
            return $b['nilai_akhir'] <=> $a['nilai_akhir'];
        });

        return $result;
    }

    public static function getPerkembanganNilaiBulanan($filters = [])
    {
        return self::getTrendBulanan($filters);
    }

    /**
     * Grafik 2: Rata-rata nilai tiap indikator dari seluruh satker
     */
    public static function getRataRataIndikator($filters = [])
    {
        $satker = self::getSemuaSatker($filters);
        $label  = [
            'revisi_dipa'          => 'Revisi DIPA',
            'deviasi_dipa'         => 'Deviasi Hal. III DIPA',
            'penyerapan'           => 'Penyerapan Anggaran',
            'belanja_kontraktual'  => 'Belanja Kontraktual',
            'penyelesaian_tagihan' => 'Penyelesaian Tagihan',
            'up_tup'               => 'Pengelolaan UP dan TUP',
            'capaian_output'       => 'Capaian Output',
        ];

        $jumlah = array_fill_keys(array_keys($label), 0);
        foreach ($satker as $s) {
            foreach ($s['indikator'] as $key => $nilai) {
                $jumlah[$key] += $nilai;
            }
        }

        $total = count($satker);
        $hasil = [];
        foreach ($label as $key => $namaLabel) {
            $hasil[] = [
                'nama'    => $namaLabel,
                'rata2'   => $total > 0 ? round($jumlah[$key] / $total, 2) : 0,
            ];
        }
        return $hasil;
    }

    /**
     * Grafik 3: Kontribusi tiga aspek terhadap Nilai Akhir
     * (memakai bobot & nilai akhir aspek dari getAspekPenilaian)
     */
    public static function getKontribusiAspek($filters = [])
    {
        $aspek = self::getAspekPenilaian($filters);
        $hasil = [];
        foreach ($aspek as $a) {
            $hasil[] = [
                'nama'  => explode('. ', $a['judul'], 2)[1] ?? $a['judul'],
                'bobot' => $a['bobot_aspek'],
                'nilai' => $a['nilai_akhir_aspek'],
            ];
        }
        return $hasil;
    }

    /**
     * Grafik 4: Distribusi nilai akhir satker berdasarkan rentang nilai
     */
    public static function getDistribusiNilaiSatker($filters = [])
    {
        $satker = self::getSemuaSatker($filters);

        $rentang = [
            '95 - 100'      => 0,
            '90 - 94,99'    => 0,
            '85 - 89,99'    => 0,
            '80 - 84,99'    => 0,
            '< 80'          => 0,
        ];

        foreach ($satker as $s) {
            $n = $s['nilai_akhir'];
            if ($n >= 95)      $rentang['95 - 100']++;
            elseif ($n >= 90)  $rentang['90 - 94,99']++;
            elseif ($n >= 85)  $rentang['85 - 89,99']++;
            elseif ($n >= 80)  $rentang['80 - 84,99']++;
            else               $rentang['< 80']++;
        }

        return [
            'label' => array_keys($rentang),
            'data'  => array_values($rentang),
        ];
    }

    /**
     * Grafik 5a: 10 Satker dengan Nilai Akhir tertinggi
     */
    public static function getTop10SatkerTerbaik($filters = [])
    {
        $rows = self::getMonitoringRows($filters);
        if (empty($rows)) {
            return [];
        }

        $grouped = [];

        foreach ($rows as $row) {
            $kode = trim((string)($row['kode_satker'] ?? ''));
            $nama = trim((string)($row['nama_satker'] ?? ''));

            if ($kode === '') {
                continue;
            }

            if (!isset($grouped[$kode])) {
                $grouped[$kode] = [
                    'nama' => ($nama !== '' ? $nama : $kode),
                    'total' => 0,
                    'count' => 0
                ];
            }

            $grouped[$kode]['total'] += (float)$row['nilai_akhir'];
            $grouped[$kode]['count']++;
        }

        $satker = [];

        foreach ($grouped as $item) {
            $satker[] = [
                'nama' => $item['nama'],
                'nilai' => round($item['total'] / $item['count'], 2)
            ];
        }

        usort($satker, function ($a, $b) {
            return $b['nilai'] <=> $a['nilai'];
        });

        return array_slice($satker, 0, 10);
    }

    /**
     * Grafik 5b: 10 Satker dengan Nilai Akhir terendah
     */
    public static function getTop10SatkerTerbawah($filters = [])
    {
        $rows = self::getMonitoringRows($filters);
        if (empty($rows)) {
            return [];
        }

        $grouped = [];

        foreach ($rows as $row) {
            $kode = trim((string)($row['kode_satker'] ?? ''));
            $nama = trim((string)($row['nama_satker'] ?? ''));

            if ($kode === '') {
                continue;
            }

            if (!isset($grouped[$kode])) {
                $grouped[$kode] = [
                    'nama' => ($nama !== '' ? $nama : $kode),
                    'total' => 0,
                    'count' => 0
                ];
            }

            $grouped[$kode]['total'] += (float)$row['nilai_akhir'];
            $grouped[$kode]['count']++;
        }

        $satker = [];

        foreach ($grouped as $item) {
            $satker[] = [
                'nama' => $item['nama'],
                'nilai' => round($item['total'] / $item['count'], 2)
            ];
        }

        usort($satker, function ($a, $b) {
            return $a['nilai'] <=> $b['nilai'];
        });

        return array_slice($satker, 0, 10);
    }

    /**
     * Faktor penyesuaian nilai tiap periode (dummy), dipetakan dari
     * tren nilai IKPA bulanan (getPerkembanganNilaiBulanan). Periode
     * terakhir (Juli) dipakai sebagai baseline 100%.
     */
    public static function getFaktorPeriode()
    {
        $trend = self::getPerkembanganNilaiBulanan();
        $kunci = array_keys(self::getDaftarPeriodeLaporan());

        $baseline = !empty($trend['data'])
            ? end($trend['data'])
            : 1;

        $faktor = [];

        foreach ($kunci as $i => $k) {

            $nilai = $trend['data'][$i] ?? $baseline;

            $faktor[$k] = $baseline > 0
                ? round($nilai / $baseline, 4)
                : 1;
        }

        return $faktor;
    }

    /**
     * Rekap Nilai IKPA lengkap untuk SELURUH SATKER (wilayah KPPN Solok),
     * termasuk rincian per aspek. Nilai indikator dibangkitkan secara
     * deterministik (seed dari kode satker) supaya konsisten setiap
     * request, dan satker pertama (BNN Kab. Solok) sengaja dibuat
     * sempurna (100) mengikuti contoh pada halaman Beranda.
     *
     * Jika USE_DATABASE = true, ganti fungsi ini dengan query JOIN
     * antara tabel nilai_ikpa dan master_satker untuk periode terpilih.
     */

    /**
     * Konversi key periode ('jan'..'jul') menjadi nomor bulan (1-7)
     */
    public static function getBulanDariPeriodeKey($periodeKey)
    {
        $map = [
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4,
            'mei' => 5, 'jun' => 6, 'jul' => 7,
        ];
        return $map[$periodeKey] ?? null;
    }
}
