<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Hanya mengizinkan admin (kolom pengguna.is_admin = 1).
 * Non-admin dikembalikan ke Beranda, sama seperti perilaku admin-approval.php lama.
 */
class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            return redirect()->route('beranda');
        }

        return $next($request);
    }
}
