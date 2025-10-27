<x-filament-panels::page>
    <x-filament-panels::form id="change-password-form" wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end">
            <x-filament::button type="submit">
                Update Password
            </x-filament::button>
        </div>
    </x-filament-panels::form>
</x-filament-panels::page>