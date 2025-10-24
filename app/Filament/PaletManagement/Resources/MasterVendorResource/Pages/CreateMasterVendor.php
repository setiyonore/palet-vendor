<?php

/**
 * File: app/Filament/PaletManagement/Resources/MasterVendorResource/Pages/CreateMasterVendor.php
 */

namespace App\Filament\PaletManagement\Resources\MasterVendorResource\Pages;

use App\Filament\PaletManagement\Resources\MasterVendorResource;
use App\Imports\PM\MasterVendorImport;
use Filament\Forms\Form;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Livewire\TemporaryUploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\HeadingRowImport;

class CreateMasterVendor extends CreateRecord
{
    protected static string $resource = MasterVendorResource::class;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Impor Vendor dari Excel')
                    ->schema([
                        FileUpload::make('file_path')
                            ->label('File Excel')
                            ->disk('public')
                            ->directory('impor-master-vendor')
                            ->required()
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if (! $state instanceof TemporaryUploadedFile) {
                                    return;
                                }

                                $tmpPath = $state->getRealPath();
                                $headings = (new HeadingRowImport)->toArray($tmpPath)[0][0] ?? [];

                                $required = ['kode_fios', 'kode_vendor_si', 'nama_vendor'];
                                $normalized = array_map(static fn($h) => strtolower(str_replace(' ', '_', trim((string) $h))), $headings);
                                $missing = array_diff($required, $normalized);

                                if (! empty($missing)) {
                                    $set('file_path', null);
                                    throw new \Exception('Header Excel tidak valid. Header yang hilang: ' . implode(', ', $missing));
                                }
                            }),
                    ]),
            ]);
    }

    protected function handleRecordCreation(array $data): Model
    {
        $importer = new MasterVendorImport();

        $relativePath = $data['file_path'];
        Excel::import($importer, $relativePath, 'public');

        $created = $importer->created;
        $failures = method_exists($importer, 'failures') ? $importer->failures() : [];
        $duplicates = $importer->duplicateRows ?? [];

        $totalGagal = count($failures) + count($duplicates);

        if ($totalGagal > 0) {
            $items = [];

            foreach ($duplicates as $dup) {
                $kode = $dup['row']['kode_fios'] ?? ($dup['row']['kode_vendor_si'] ?? '-');
                $items[] = "Duplikat data untuk kode: {$kode}";
            }

            foreach ($failures as $failure) {
                $items[] = 'Baris ' . $failure->row() . ': ' . ($failure->errors()[0] ?? 'Gagal');
            }

            Notification::make()
                ->warning()
                ->title('Impor selesai dengan beberapa kegagalan')
                ->body(
                    '<strong>Impor selesai.</strong><br>'
                        . $created . ' baris berhasil diimpor.<br>'
                        . '<strong>' . $totalGagal . ' baris gagal:</strong><br>- ' . implode('<br>- ', $items)
                )
                ->persistent()
                ->send();
        } else {
            Notification::make()
                ->success()
                ->title('Impor berhasil')
                ->body($created . ' baris data vendor berhasil diimpor.')
                ->send();
        }

        return new (static::getModel());
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
