<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\PeminjamanManualResource\Pages;
use App\Models\PM\PeminjamanManual;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Livewire\TemporaryUploadedFile;
use Maatwebsite\Excel\HeadingRowImport;

class PeminjamanManualResource extends Resource
{
    protected static ?string $model = PeminjamanManual::class;

    protected static ?string $pluralModelLabel = 'Peminjaman Saldo Awal';
    protected static ?string $navigationIcon = 'heroicon-o-document-arrow-up';

    /* === NAV & ACCESS GUARDS === */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_peminjaman::manual') ?? false;
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_peminjaman::manual') ?? false;
    }
    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_peminjaman::manual') ?? false;
    }
    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_peminjaman::manual') ?? false;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Impor Data Excel Peminjaman Manual')
                    ->schema([
                        Forms\Components\FileUpload::make('file_path')
                            ->label('File Excel')
                            ->disk('public')
                            ->directory('impor-peminjaman-manual')
                            ->required()
                            ->acceptedFileTypes([
                                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                                'application/vnd.ms-excel',
                            ])
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                try {
                                    if (!$state instanceof TemporaryUploadedFile) return;

                                    $filePath = $state->getRealPath();
                                    $headings = (new HeadingRowImport)->toArray($filePath)[0][0] ?? [];

                                    $requiredHeadings = [
                                        'nopol',
                                        'qty',
                                        'tgl_pinjam',
                                        'kode_expeditur',
                                        'plant_pinjam',
                                        'no_spj',
                                        'tgl_spj',
                                        'tgl_pod'
                                    ];

                                    $normalized = array_map(
                                        fn($h) => strtolower(str_replace(' ', '_', trim((string) $h))),
                                        $headings
                                    );
                                    $missingHeaders = array_diff($requiredHeadings, $normalized);

                                    if (!empty($missingHeaders)) {
                                        $set('file_path', null);
                                        throw new \Exception(
                                            'Header Excel tidak valid. Header yang hilang: ' . implode(', ', $missingHeaders)
                                        );
                                    }
                                } catch (\Exception $e) {
                                    $set('file_path', null);
                                    throw $e;
                                }
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nopol')->searchable(),
                Tables\Columns\TextColumn::make('masterVendor.nama_vendor')->label('Nama Vendor')->searchable(),
                Tables\Columns\TextColumn::make('qty'),
                Tables\Columns\TextColumn::make('tgl_pinjam')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('gudangPalet.nama_gudang')->label('Plant Pinjam'),
                Tables\Columns\TextColumn::make('importer')->label('Diimpor Oleh'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn(int $state): string => match ($state) {
                        0 => 'warning',
                        1 => 'success',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(int $state): string => match ($state) {
                        0 => 'Belum Dikembalikan',
                        1 => 'Sudah Dikembalikan',
                        default => 'Tidak Diketahui',
                    }),
            ])
            ->filters([])
            ->actions([
                // Edit sengaja dimatikan; kalau suatu saat ingin aktif, guard:
                // Tables\Actions\EditAction::make()
                //     ->visible(fn () => auth()->user()?->can('update_peminjaman::manual') ?? false),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->can('delete_peminjaman::manual') ?? false),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->can('delete_any_peminjaman::manual') ?? false),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPeminjamanManuals::route('/'),
            'create' => Pages\CreatePeminjamanManual::route('/create'),
            'edit'   => Pages\EditPeminjamanManual::route('/{record}/edit'),
            'view'   => Pages\ViewPeminjamanManual::route('/{record}'),
        ];
    }
}
