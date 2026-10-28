<?php

namespace App\Http\Controllers;

use App\Support\IkpaImporter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;

class UploadController extends Controller
{
    // Pengganti upload.php (tampilan form + riwayat)
    public function index()
    {
        $tahunSekarang = (int) date('Y');

        return view('upload', [
            'daftarTahun'    => range($tahunSekarang + 10, $tahunSekarang - 5),
            'riwayatUpload'  => DB::table('riwayat_upload')
                ->orderByDesc('tanggal_upload')
                ->orderByDesc('id')
                ->get(),
        ]);
    }

    // Pengganti aksi.php (proses upload)
    public function store(Request $request)
    {
        $request->validate([
            'tahun' => ['required', 'regex:/^\d{4}$/'],
            'bulan' => ['required', 'regex:/^(0[1-9]|1[0-2])$/'],
            'file'  => ['required', 'file', 'extensions:xls,xlsx', 'max:20480'],
        ], [
            'tahun.required'   => 'Tahun harus dipilih.',
            'tahun.regex'      => 'Tahun harus dipilih.',
            'bulan.required'   => 'Bulan harus dipilih.',
            'bulan.regex'      => 'Bulan harus dipilih.',
            'file.required'    => 'File harus dipilih.',
            'file.extensions'  => 'File harus berformat Excel (.xls / .xlsx).',
            'file.max'         => 'Ukuran file maksimal 20 MB.',
            'file.uploaded'    => 'File gagal diupload. Cek batas upload_max_filesize di php.ini.',
        ]);

        // Periode selalu berasal dari form: "YYYY-MM"
        $periode = $request->tahun . '-' . $request->bulan;
        $file = $request->file('file');

        @set_time_limit(300);

        try {
            $reader = IOFactory::createReaderForFile($file->getRealPath());
            $sheetData = $reader->load($file->getRealPath())->getActiveSheet()->toArray();
        } catch (\Throwable $e) {
            return back()->withInput()->withErrors([
                'file' => 'File Excel tidak dapat dibaca. Pastikan file .xls / .xlsx tidak rusak.',
            ]);
        }

        $hasil = DB::transaction(function () use ($sheetData, $periode, $file) {
            $hasil = IkpaImporter::import($sheetData, $periode);
            $totalBerhasil = $hasil['baru'] + $hasil['diperbarui'];

            if ($totalBerhasil > 0) {
                // Riwayat: satu baris per periode (YYYY-MM), diperbarui jika sudah ada
                DB::table('riwayat_upload')->updateOrInsert(
                    ['periode' => $periode],
                    [
                        'nama_file'      => $file->getClientOriginalName(),
                        'jumlah_data'    => $totalBerhasil,
                        'tanggal_upload' => DB::raw('NOW()'),
                    ]
                );
            } else {
                $hasil['galat'][] = 'Tidak ada data yang berhasil diupload.';
            }

            return $hasil;
        });

        $redirect = redirect()->route('upload');

        if ($hasil['baru'] + $hasil['diperbarui'] > 0) {
            $redirect->with('hasil', [
                'periode'    => formatLabelPeriode($periode),
                'baru'       => $hasil['baru'],
                'diperbarui' => $hasil['diperbarui'],
                'sama'       => $hasil['sama'],
            ]);
        }

        return $redirect->with('galat', $hasil['galat']);
    }

    // Pengganti ?hapus=... di upload.php
    public function destroy(string $periode)
    {
        DB::transaction(function () use ($periode) {
            DB::table('monitoring_ikpa')->where('periode', $periode)->delete();
            DB::table('riwayat_upload')->where('periode', $periode)->delete();
        });

        return redirect()->route('upload')->with(
            'pesan_sukses',
            'Data periode ' . formatLabelPeriode($periode) . ' berhasil dihapus.'
        );
    }
}
