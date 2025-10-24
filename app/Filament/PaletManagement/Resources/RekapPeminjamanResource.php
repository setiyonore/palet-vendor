<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\PeminjamanManualResource;
use App\Filament\PaletManagement\Resources\PeminjamanPaletResource;
use App\Filament\PaletManagement\Resources\RekapPeminjamanResource\Pages;
use App\Models\PM\PeminjamanPalet;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use App\Models\PM\RekapPeminjamanRow;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class RekapPeminjamanResource extends Resource implements HasShieldPermissions
{
    // Model dasar tetap diperlukan, meskipun query-nya ditimpa sepenuhnya di List Page
    protected static ?string $model = RekapPeminjamanRow::class;
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
        ];
    }

    protected static ?string $pluralModelLabel = 'Rekap Belum Kembali';
    protected static ?string $navigationLabel = 'Rekap Belum Kembali';
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tgl_peminjaman')
                    ->label('Tanggal Peminjaman')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('nopol')
                    ->label('No. Polisi')
                    ->searchable(),
                TextColumn::make('nama_vendor')
                    ->label('Nama Vendor')
                    ->searchable(),
                TextColumn::make('qty')
                    ->label('Jumlah Palet')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                // Filter bisa ditambahkan di sini jika perlu
            ])
            ->actions([
                // Menambahkan tombol View Detail yang cerdas
                Tables\Actions\Action::make('view_detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->url(function (Model $record): string {
                        // Arahkan ke resource yang benar berdasarkan sumber data
                        if ($record->source_type === 'api') {
                            return PeminjamanPaletResource::getUrl('view', ['record' => $record->id]);
                        }

                        // Arahkan ke halaman edit untuk peminjaman manual jika halaman view tidak ada
                        return PeminjamanManualResource::getUrl('view', ['record' => $record->id]);
                    })
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->headerActions([
                ExportAction::make()
                    ->label('Export ke Excel')
                    ->exports([
                        ExcelExport::make()
                            ->fromTable() // Mengambil data dan kolom langsung dari tabel
                            ->withFilename('Rekap Palet Belum Kembali - ' . date('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRekapPeminjaman::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
