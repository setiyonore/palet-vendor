<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\PeminjamanManualResource;
use App\Filament\PaletManagement\Resources\PeminjamanPaletResource;
use App\Filament\PaletManagement\Resources\RekapPeminjamanResource\Pages;
use App\Models\PM\RekapPeminjamanRow;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class RekapPeminjamanResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = RekapPeminjamanRow::class;

    protected static ?string $pluralModelLabel = 'Rekap Belum Kembali';
    protected static ?string $navigationLabel = 'Rekap Belum Kembali';
    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 1;

    /* ======================
     * NAV & ACCESS GUARDS
     * ====================== */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_rekap::peminjaman') ?? false;
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_rekap::peminjaman') ?? false;
    }

    public static function canCreate(): bool
    {
        return false; // rekap tidak dibuat manual
    }

    // Kalau mau ketat, aksi "lihat detail" juga pakai can('view_rekap::peminjaman')
    public static function canView(Model $record): bool
    {
        return auth()->user()?->can('view_rekap::peminjaman') ?? false;
    }

    public static function getPermissionPrefixes(): array
    {
        // hanya view & view_any untuk rekap
        return ['view', 'view_any'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('tgl_peminjaman')->label('Tanggal Peminjaman')->dateTime()->sortable(),
                TextColumn::make('nopol')->label('No. Polisi')->searchable(),
                TextColumn::make('nama_vendor')->label('Nama Vendor')->searchable(),
                TextColumn::make('qty')->label('Jumlah Palet')->numeric()->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\Action::make('view_detail')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('gray')
                    ->visible(fn() => auth()->user()?->can('view_rekap::peminjaman') ?? false)
                    ->url(function (Model $record): string {
                        // NOTE: pastikan id yang dipakai memang id record tujuan
                        if ($record->source_type === 'api') {
                            return PeminjamanPaletResource::getUrl('view', ['record' => $record->id]);
                        }
                        return PeminjamanManualResource::getUrl('view', ['record' => $record->id]);
                    })
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([])
            ->headerActions([
                ExportAction::make()
                    ->label('Export ke Excel')
                    ->visible(fn() => auth()->user()?->can('view_any_rekap::peminjaman') ?? false)
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
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
}
