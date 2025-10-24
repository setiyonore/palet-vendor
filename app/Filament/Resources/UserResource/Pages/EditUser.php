<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function beforeSave(): void
    {
        // contoh: cegah turunkan role diri sendiri kalau bukan super admin
        if (auth()->id() === $this->record->id && !auth()->user()->hasRole('Super Admin')) {
            Notification::make()
                ->title('Anda tidak bisa mengubah role akun Anda sendiri.')
                ->danger()
                ->send();

            $this->halt();
        }
    }
}
