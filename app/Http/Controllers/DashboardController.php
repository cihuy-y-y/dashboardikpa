<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\MembacaFilter;
use App\Support\IkpaData;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    use MembacaFilter;

    // Pengganti dashboard.php
    public function index(Request $request)
    {
        $filters = $this->ambilFilter($request);

        return view('dashboard', [
            'filters'         => $filters,
            'filterOptions'   => IkpaData::getFilterOptions(),
            'perkembangan'    => IkpaData::getPerkembanganNilaiBulanan($filters),
            'rataRataInd'     => IkpaData::getRataRataIndikator($filters),
            'kontribusiAspek' => IkpaData::getKontribusiAspek($filters),
            'distribusiNilai' => IkpaData::getDistribusiNilaiSatker($filters),
            'top10Terbaik'    => IkpaData::getTop10SatkerTerbaik($filters),
            'top10Terbawah'   => IkpaData::getTop10SatkerTerbawah($filters),
        ]);
    }
}
