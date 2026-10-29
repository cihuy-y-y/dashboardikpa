<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Backup & restore database (pengganti createDatabaseDump() dan runSqlFile() di pengaturan.php).
 */
class DatabaseBackup
{
    /** Tabel bawaan Laravel yang tidak perlu ikut di-backup (data sementara). */
    private const ABAIKAN = ['sessions', 'cache', 'cache_locks'];

    private const FOLDER = 'backups';

    /** Daftar nama file .sql, terbaru di atas. */
    public static function daftar(): array
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory(self::FOLDER);

        $nama = [];
        foreach ($disk->files(self::FOLDER) as $f) {
            if (strtolower(pathinfo($f, PATHINFO_EXTENSION)) === 'sql') {
                $nama[] = basename($f);
            }
        }
        rsort($nama);

        return $nama;
    }

    /**
     * Path lengkap file backup jika namanya valid & ada, selain itu null.
     * Mencegah path traversal (../).
     */
    public static function lokasiAman(string $nama): ?string
    {
        if (! preg_match('/^[A-Za-z0-9_\-\.]+\.sql$/', $nama) || str_contains($nama, '..')) {
            return null;
        }

        $disk = Storage::disk('local');
        $relatif = self::FOLDER . '/' . $nama;

        return $disk->exists($relatif) ? $disk->path($relatif) : null;
    }

    /** Buat file backup baru, kembalikan nama filenya. */
    public static function buat(): string
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory(self::FOLDER);

        $database = DB::getDatabaseName();
        $nama = $database . '_' . date('Ymd_His') . '.sql';
        $path = $disk->path(self::FOLDER . '/' . $nama);

        $fp = fopen($path, 'w');
        if ($fp === false) {
            throw new \RuntimeException('Tidak bisa menulis file backup.');
        }

        $pdo = DB::getPdo();

        fwrite($fp, "-- MySQL dump of database `$database`\n");
        fwrite($fp, '-- Generated: ' . date('Y-m-d H:i:s') . "\n\n");
        fwrite($fp, "SET FOREIGN_KEY_CHECKS = 0;\n\n");

        $tabel = DB::select(
            'SELECT TABLE_NAME AS nama FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_TYPE = ? ORDER BY TABLE_NAME',
            [$database, 'BASE TABLE']
        );

        foreach ($tabel as $t) {
            $namaTabel = $t->nama;
            if (in_array($namaTabel, self::ABAIKAN, true)) {
                continue;
            }

            $create = (array) DB::select("SHOW CREATE TABLE `{$namaTabel}`")[0];
            fwrite($fp, "--\n-- Table structure for table `{$namaTabel}`\n--\n\n");
            fwrite($fp, "DROP TABLE IF EXISTS `{$namaTabel}`;\n");
            fwrite($fp, $create['Create Table'] . ";\n\n");

            $kepala = null;
            $baris = [];
            foreach (DB::table($namaTabel)->cursor() as $row) {
                $row = (array) $row;

                if ($kepala === null) {
                    $kepala = "INSERT INTO `{$namaTabel}` (`" . implode('`, `', array_keys($row)) . "`) VALUES\n";
                }

                $nilai = [];
                foreach ($row as $v) {
                    $nilai[] = $v === null ? 'NULL' : $pdo->quote((string) $v);
                }
                $baris[] = '(' . implode(', ', $nilai) . ')';

                if (count($baris) >= 100) {
                    fwrite($fp, $kepala . implode(",\n", $baris) . ";\n");
                    $baris = [];
                }
            }
            if (count($baris) > 0) {
                fwrite($fp, $kepala . implode(",\n", $baris) . ";\n");
            }
            fwrite($fp, "\n");
        }

        fwrite($fp, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fclose($fp);

        return $nama;
    }

    /** Jalankan isi file .sql satu pernyataan demi satu. Error akan dilempar sebagai Exception. */
    public static function pulihkan(string $pathFile): void
    {
        $sql = file_get_contents($pathFile);
        if ($sql === false) {
            throw new \RuntimeException('File tidak dapat dibaca.');
        }

        // Buang BOM bila ada
        $sql = preg_replace('/^\xEF\xBB\xBF/', '', $sql);

        try {
            foreach (self::pecahPernyataan($sql) as $pernyataan) {
                DB::unprepared($pernyataan);
            }
        } finally {
            try {
                DB::unprepared('SET FOREIGN_KEY_CHECKS = 1');
            } catch (\Throwable $e) {
                // abaikan
            }
        }
    }

    /**
     * Memecah isi file SQL menjadi pernyataan terpisah (pemisah ';'),
     * dengan memperhatikan tanda kutip dan komentar.
     * Catatan: perintah DELIMITER (trigger/prosedur) tidak didukung.
     */
    public static function pecahPernyataan(string $sql): array
    {
        $hasil = [];
        $buf = '';
        $n = strlen($sql);
        $i = 0;
        $kutip = null;

        while ($i < $n) {
            $c = $sql[$i];
            $d = $i + 1 < $n ? $sql[$i + 1] : '';

            if ($kutip !== null) {
                $buf .= $c;
                if ($c === '\\' && $kutip !== '`') {
                    $buf .= $d;
                    $i += 2;
                    continue;
                }
                if ($c === $kutip) {
                    if ($d === $kutip) {      // kutip ganda dalam string ('')
                        $buf .= $d;
                        $i += 2;
                        continue;
                    }
                    $kutip = null;
                }
                $i++;
                continue;
            }

            if ($c === "'" || $c === '"' || $c === '`') {
                $kutip = $c;
                $buf .= $c;
                $i++;
                continue;
            }

            // Komentar satu baris: "-- ..." atau "# ..."
            $spasiSetelahStrip = $i + 2 >= $n || ctype_space($sql[$i + 2]);
            if (($c === '-' && $d === '-' && $spasiSetelahStrip) || $c === '#') {
                $akhir = strpos($sql, "\n", $i);
                $i = $akhir === false ? $n : $akhir + 1;
                continue;
            }

            // Komentar blok: /* ... */ (kecuali /*! ... */ yang tetap dieksekusi MySQL)
            if ($c === '/' && $d === '*') {
                $akhir = strpos($sql, '*/', $i + 2);
                $akhir = $akhir === false ? $n - 2 : $akhir;
                if (($sql[$i + 2] ?? '') === '!') {
                    $buf .= substr($sql, $i, $akhir + 2 - $i);
                }
                $i = $akhir + 2;
                continue;
            }

            if ($c === ';') {
                $s = trim($buf);
                if ($s !== '') {
                    $hasil[] = $s;
                }
                $buf = '';
                $i++;
                continue;
            }

            $buf .= $c;
            $i++;
        }

        $s = trim($buf);
        if ($s !== '') {
            $hasil[] = $s;
        }

        return $hasil;
    }
}
