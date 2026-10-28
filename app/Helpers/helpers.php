<?php

// Fungsi bantu global, dipindahkan dari config.php lama.
// Bisa dipanggil langsung di Blade, misalnya: {{ formatAngka($nilai) }}

if (!function_exists('formatAngka')) {
    /**
     * Format angka ke format Indonesia (2 desimal, koma sebagai pemisah desimal)
     */
    function formatAngka($angka, $desimal = 2)
    {
        return number_format((float) $angka, $desimal, ',', '.');
    }
}

if (!function_exists('warnaCapaian')) {
    /**
     * Menentukan warna badge berdasarkan nilai capaian
     */
    function warnaCapaian($nilai)
    {
        if ($nilai >= 90) return 'success';
        if ($nilai >= 75) return 'warning';
        return 'danger';
    }
}

if (!function_exists('formatLabelPeriode')) {
    function formatLabelPeriode($periode)
    {
        $bulanNama = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', '04' => 'April',
            '05' => 'Mei', '06' => 'Juni', '07' => 'Juli', '08' => 'Agustus',
            '09' => 'September', '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
        ];

        // Format baru: "YYYY-MM" (contoh: 2026-01)
        if (preg_match('/^(\d{4})-(\d{2})$/', $periode, $m)) {
            $tahun = $m[1];
            $bulan = $m[2];
            $namaBulan = $bulanNama[$bulan] ?? $bulan;
            return $namaBulan . ' ' . $tahun;
        }

        // Format lama (fallback): cuma "01" tanpa tahun
        if (isset($bulanNama[$periode])) {
            return $bulanNama[$periode] . ' (tahun tidak diketahui)';
        }

        return $periode;
    }
}
