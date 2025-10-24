<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        // contoh: auto-assign role "User" jika belum ada
        if ($this->record && $this->record->roles()->count() === 0) {
            $this->record->assignRole('User'); // pastikan role "User" ada
        }
    }
}
