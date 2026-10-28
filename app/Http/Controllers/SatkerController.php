<?php

namespace App\Http\Controllers;

use App\Support\IkpaData;
use Illuminate\Http\Request;

class SatkerController extends Controller
{
    // Pengganti data-satker.php
    public function index(Request $request)
    {
        $masterSatker = IkpaData::getMasterSatker();

        // Pencarian sederhana berdasarkan kode/nama satker: ?cari=...
        $kataKunci = trim($request->string('cari')->toString());
        if ($kataKunci !== '') {
            $masterSatker = array_values(array_filter($masterSatker, function ($s) use ($kataKunci) {
                $gabungan = strtolower($s['kode_satker'] . ' ' . $s['nama_satker'] . ' ' . $s['kode_ba'] . ' ' . $s['nama_ba']);
                return str_contains($gabungan, strtolower($kataKunci));
            }));
        }

        return view('data-satker', [
            'masterSatker' => $masterSatker,
            'kataKunci'    => $kataKunci,
        ]);
    }
}
