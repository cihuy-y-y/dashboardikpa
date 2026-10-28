<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

trait MembacaFilter
{
    /**
     * Ambil filter dari URL (?tahun=...&periode=...&ba=...&satker=...&kppn=...).
     * Pengganti $_GET['x'] ?? '' pada kode lama.
     */
    protected function ambilFilter(Request $request): array
    {
        $filters = [];
        foreach (['tahun', 'periode', 'kppn', 'ba', 'satker'] as $kunci) {
            $filters[$kunci] = $request->string($kunci)->toString();
        }

        return $filters;
    }
}
