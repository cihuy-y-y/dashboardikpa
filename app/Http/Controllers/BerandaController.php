<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MembacaFilter;
use App\Support\IkpaData;
use Illuminate\Http\Request;

class BerandaController extends Controller
{
    use MembacaFilter;

    // Pengganti index.php
    public function index(Request $request)
    {
        $filters = $this->ambilFilter($request);

        return view('beranda', [
            'filters'        => $filters,
            'filterOptions'  => IkpaData::getFilterOptions(),
            'ringkasan'      => IkpaData::getRingkasanNilai($filters),
            'aspekPenilaian' => IkpaData::getAspekPenilaian($filters),
            'trend'          => IkpaData::getTrendBulanan($filters),
            'top10'          => IkpaData::getTop10Satker($filters),
            'detailSatker'   => IkpaData::getDetailSatker($filters),
        ]);
    }
}
