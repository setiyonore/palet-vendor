<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'password_changed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::updating(function ($user) {
            if ($user->isDirty('password')) {
                $user->password_changed_at = now();
            }
        });
    }

    public function setPasswordAttribute($value): void
    {
        if (blank($value)) {
            // abaikan assignment kosong; biarkan password lama
            return;
        }

        $this->attributes['password'] = Hash::make($value);
        $this->attributes['password_changed_at'] = now();
    }

    /**
     * Logika Gerbang Akses Panel
     * Menggunakan Permission Shield untuk menentukan akses.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        // 1. Super Admin boleh akses ke SEMUA panel
        if ($this->hasRole('super_admin')) {
            return true;
        }

        // 2. Cek Permission untuk Panel Admin (Palet Management)
        // Pastikan permission 'access_panel_admin' sudah dibuat via Seeder
        if ($panel->getId() === 'admin') {
            return $this->can('access_panel_admin');
        }

        // 3. Cek Permission untuk Panel Armada
        // Pastikan permission 'access_panel_armada' sudah dibuat via Seeder
        if ($panel->getId() === 'armada') {
            return $this->can('access_panel_armada');
        }

        // Default: tolak akses jika tidak memenuhi syarat di atas
        return false;
    }
}
