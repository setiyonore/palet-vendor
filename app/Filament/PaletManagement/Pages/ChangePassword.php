<?php

namespace App\Filament\PaletManagement\Pages;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;

class ChangePassword extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-key';
    protected static ?string $title = 'Ganti Password';
    protected static string $view = 'filament.pages.change-password'; // pakai view default Page

    // opsional: tampilkan di menu (kalau mau sembunyikan dari sidebar, return false)
    public static function shouldRegisterNavigation(): bool
    {
        return false; // kita taruh di User Menu, bukan sidebar
    }

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Keamanan Akun')
                    ->schema([
                        Forms\Components\TextInput::make('current_password')
                            ->label('Password saat ini')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rule('current_password') // validasi terhadap guard aktif
                            ->dehydrated(false),

                        Forms\Components\TextInput::make('password')
                            ->label('Password baru')
                            ->password()
                            ->revealable()
                            ->required()
                            ->rules([PasswordRule::min(8)->mixedCase()->numbers()->uncompromised()])
                            ->same('password_confirmation'),

                        Forms\Components\TextInput::make('password_confirmation')
                            ->label('Konfirmasi password baru')
                            ->password()
                            ->revealable()
                            ->required()
                            ->dehydrated(false),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            Forms\Components\Actions\Action::make('save')
                ->label('Update Password')
                ->submit('save'),
        ];
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();
        $new = $this->data['password'] ?? null;

        // jaga-jaga
        if (blank($new)) {
            Notification::make()->title('Password baru tidak boleh kosong.')->danger()->send();
            return;
        }

        // set & hash
        $user->password = $new;                     // jika ada mutator, akan auto-hash & set password_changed_at
        if (! method_exists($user, 'setPasswordAttribute')) {
            // fallback: hash manual + set password_changed_at
            $user->password = Hash::make($new);
            $user->password_changed_at = now();
        }
        $user->save();

        // (opsional) logout sesi lain demi keamanan
        // $this->logoutOtherSessions($new);

        Notification::make()
            ->title('Password berhasil diperbarui')
            ->success()
            ->send();

        // kosongkan field form
        $this->form->fill([]);
    }

    protected function logoutOtherSessions(string $newPlainPassword): void
    {
        // only if you use Laravel's multi-session
        if (method_exists(Auth::user(), 'setRememberToken')) {
            request()->session()->regenerate();
            Auth::logoutOtherDevices($newPlainPassword);
        }
    }
}
