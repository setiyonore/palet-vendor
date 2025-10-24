<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\PengembalianPaletResource\Pages;
use App\Models\PM\GudangPalet;
use App\Models\PM\PeminjamanManual;
use App\Models\PM\PengembalianPalet;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PengembalianPaletResource extends Resource
{
    protected static ?string $model = PengembalianPalet::class;
    protected static ?string $pluralModelLabel = 'Pengembalian Palet';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square';
    protected static bool $shouldRegisterNavigation = true;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Pilih Peminjaman Manual')
                ->schema([
                    Select::make('peminjaman_manual_id')
                        ->label('Pilih Peminjaman (Nopol)')
                        ->searchable()
                        ->preload(false) // jangan preload semua data
                        ->reactive()
                        ->live()
                        ->required()
                        ->hint('Ketik minimal 2 huruf Nopol untuk mencari...')
                        ->getSearchResultsUsing(function (string $search): array {
                            if (strlen($search) < 2) {
                                return []; // belum cukup panjang
                            }

                            return PeminjamanManual::query()
                                ->where('status', 0)
                                ->where('nopol', 'ILIKE', "%{$search}%")
                                ->limit(20)
                                ->pluck('nopol', 'id')
                                ->toArray();
                        })
                        ->getOptionLabelUsing(
                            fn($value): ?string =>
                            PeminjamanManual::find($value)?->nopol
                        )
                        ->afterStateUpdated(function (Set $set, $state, Forms\Components\Component $component) {
                            if (blank($state)) {
                                $set('qty_kembali_rfi', 0);
                                $set('qty_kembali_tbr', 0);
                                $set('qty_kembali_ber', 0);
                                return;
                            }

                            $peminjaman = PeminjamanManual::with(['gudangPalet', 'masterVendor'])->find($state);

                            if ($peminjaman) {
                                $set('qty_kembali_rfi', $peminjaman->qty ?? 0);
                                $set('qty_kembali_tbr', 0);
                                $set('qty_kembali_ber', 0);
                            }

                            // refresh info section
                            $component->getLivewire()->dispatch('$refresh');
                        }),
                ]),

            Section::make('Informasi Peminjaman')
                ->schema(function (Get $get) {
                    $peminjamanId = $get('peminjaman_manual_id');

                    if (!$peminjamanId) {
                        return [
                            Placeholder::make('info')->content('Pilih data peminjaman terlebih dahulu.')
                        ];
                    }

                    $record = PeminjamanManual::with(['gudangPalet', 'masterVendor'])->find($peminjamanId);
                    if (!$record) {
                        return [
                            Placeholder::make('not_found')->content('Data peminjaman tidak ditemukan.')
                        ];
                    }

                    return [
                        Placeholder::make('nopol_info')->label('No. Polisi')->content($record->nopol ?? '-'),
                        Placeholder::make('vendor_info')->label('Nama Vendor')->content($record->masterVendor->nama_vendor ?? '-'),
                        Placeholder::make('plant_asal_info')->label('Plant Asal Peminjaman')->content($record->gudangPalet->nama_gudang ?? '-'),
                        Placeholder::make('qty_pinjam')->label('Jumlah Palet Dipinjam')->content("{$record->qty} Palet"),
                    ];
                })
                ->columns(2)
                ->reactive()
                ->visible(fn(Get $get) => filled($get('peminjaman_manual_id'))),

            Section::make('Detail Pengembalian')
                ->schema([
                    Select::make('gudang_id')
                        ->label('Kembalikan ke Plant')
                        ->relationship(name: 'gudangPalet', titleAttribute: 'nama_gudang')
                        ->getOptionLabelFromRecordUsing(fn($record) => "[{$record->kode_gudang}] {$record->nama_gudang}")
                        ->searchable(['kode_gudang', 'nama_gudang'])
                        ->preload()
                        ->required(),
                    DateTimePicker::make('tgl_kembali')
                        ->label('Tanggal & Waktu Pengembalian')
                        ->required()
                        ->default(now()),
                ])
                ->columns(2),

            Section::make('Jumlah Palet Kembali (Berdasarkan Kondisi)')
                ->schema([
                    TextInput::make('qty_kembali_rfi')
                        ->label('Jumlah RFI')
                        ->numeric()
                        ->required()
                        ->minValue(0),
                    TextInput::make('qty_kembali_tbr')
                        ->label('Jumlah TBR')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->default(0),
                    TextInput::make('qty_kembali_ber')
                        ->label('Jumlah BER')
                        ->numeric()
                        ->required()
                        ->minValue(0)
                        ->default(0),
                ])
                ->columns(3),

            FileUpload::make('ba_kembali_path')
                ->label('Upload Berita Acara Pengembalian (PDF)')
                ->disk('public')
                ->directory('berita-acara-pengembalian')
                ->acceptedFileTypes(['application/pdf'])
                ->downloadable()
                ->openable()
                ->required()
                ->columnSpanFull(),

            Textarea::make('keterangan')
                ->label('Keterangan')
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('peminjamanManual.nopol')
                    ->label('Nopol')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('gudangPalet.nama_gudang')
                    ->label('Dikembalikan Ke Plant')
                    ->sortable(),
                TextColumn::make('tgl_kembali')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('qty_kembali_rfi')->label('Qty RFI'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereNotNull('peminjaman_manual_id')
            ->with(['peminjamanManual', 'gudangPalet']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPengembalianPalets::route('/'),
            'create' => Pages\CreatePengembalianPalet::route('/create'),
            'edit'   => Pages\EditPengembalianPalet::route('/{record}/edit'),
            'view'   => Pages\ViewPengembalianPalet::route('/{record}'),
        ];
    }
}
