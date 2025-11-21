<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;

class RedirectToCorrectPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Pastikan user sudah login
        if (! $user) {
            return $next($request);
        }

        // Ambil ID panel yang sedang diakses (admin atau armada)
        // Catatan: Filament::getCurrentPanel() bisa null jika di luar konteks panel,
        // tapi karena ini dipasang di PanelProvider, harusnya aman.
        $currentPanelId = Filament::getCurrentPanel()?->getId();

        // 1. Jika User adalah 'Armada' tapi sedang di Panel 'admin'
        if (
            $currentPanelId === 'admin' &&
            ! $user->hasRole('super_admin') &&
            ! $user->can('access_panel_admin') &&
            $user->can('access_panel_armada')
        ) {
            return redirect()->to('/armada');
        }

        // 2. Jika User adalah 'Admin' (Bukan super admin) tapi sedang di Panel 'armada'
        // (Opsional, aktifkan jika admin biasa dilarang masuk armada)
        if (
            $currentPanelId === 'armada' &&
            ! $user->hasRole('super_admin') &&
            ! $user->can('access_panel_armada') &&
            $user->can('access_panel_admin')
        ) {
            return redirect()->to('/admin');
        }

        return $next($request);
    }
}
