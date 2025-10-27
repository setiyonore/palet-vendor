<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ForcePasswordChange
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Belum login? lanjutkan
        if (!$user) {
            return $next($request);
        }

        // Whitelist route supaya tidak loop
        if ($request->routeIs([
            'filament.admin.auth.*',                 // login, password request, dll di panel admin
            'filament.admin.pages.change-password',  // halaman ganti password
            'logout',
            'password.change',                       // jika kamu buat shim route manual
        ])) {
            return $next($request);
        }

        // Ambil konfigurasi
        $maxDays = (int) config('auth.password_expiration_days', 90);

        // CAST penting! pastikan jadi instance Carbon
        $changedAt = $user->password_changed_at instanceof \Carbon\Carbon
            ? $user->password_changed_at
            : ($user->password_changed_at ? \Carbon\Carbon::parse($user->password_changed_at) : null);

        // Kondisi WAJIB GANTI:
        $mustChange = is_null($changedAt) || $changedAt->addDays($maxDays)->isPast();

        if ($mustChange) {
            // (Opsional) set flash supaya kamu lihat notifikasi
            session()->flash('warning', 'Password Anda sudah kadaluarsa / belum diatur. Silakan ganti password.');
            return redirect()->route('filament.admin.pages.change-password');
        }

        return $next($request);
    }
}
