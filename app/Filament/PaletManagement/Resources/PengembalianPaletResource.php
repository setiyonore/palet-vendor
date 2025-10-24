<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\PengembalianPaletResource\Pages;
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
use Illuminate\Database\Eloquent\Model;

class PengembalianPaletResource extends Resource
{
    protected static ?string $model = PengembalianPalet::class;
    protected static ?string $pluralModelLabel = 'Pengembalian Palet';
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-on-square';

    /* ======================
     * NAV & ACCESS GUARDS
     * ====================== */
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_pengembalian::palet') ?? false;
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_pengembalian::palet') ?? false;
    }
    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_pengembalian::palet') ?? false;
    }
    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_pengembalian::palet') ?? false;
    }
    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_pengembalian::palet') ?? false;
    }
    public static function canDeleteAny(): bool
    {
        return auth()->user()?->can('delete_any_pengembalian::palet') ?? false;
    }
    public static function getPermissionPrefixes(): array
    {
        return ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            // --- FORM DISERDERHANAKAN: HANYA UNTUK PEMINJAMAN MANUAL ---
            Section::make('Pilih Peminjaman Manual')->schema([
                Select::make('peminjaman_manual_id')
                    ->label('Pilih Peminjaman (Nopol)')
                    ->relationship(
                        name: 'peminjamanManual',
                        titleAttribute: 'nopol',
                        modifyQueryUsing: fn(Builder $query, ?Model $record) => $query
                            ->where('status', 0)
                            ->orWhere('id', $record?->peminjaman_manual_id)
                    )
                    ->searchable()
                    ->preload()
                    ->live()
                    ->required()
                    ->afterStateUpdated(function (Set $set, $state) {
                        if (is_null($state)) {
                            $set('qty_kembali_rfi', 0);
                            return;
                        }
                        $peminjaman = PeminjamanManual::find($state);
                        $set('qty_kembali_rfi', $peminjaman?->qty ?? 0);
                        $set('qty_kembali_tbr', 0);
                        $set('qty_kembali_ber', 0);
                    }),
            ]),

            Section::make('Informasi Peminjaman')
                ->schema(function (Get $get) {
                    $peminjamanId = $get('peminjaman_manual_id');
                    if (!$peminjamanId) {
                        return [Placeholder::make('info')->content('Pilih data peminjaman terlebih dahulu.')];
                    }

                    $record = PeminjamanManual::with('gudangPalet', 'masterVendor')->find($peminjamanId);
                    if (!$record) return [];

                    return [
                        Placeholder::make('nopol_info')->label('No. Polisi')->content($record->nopol ?? '-'),
                        Placeholder::make('vendor_info')->label('Nama Vendor')->content($record->masterVendor->nama_vendor ?? '-'),
                        Placeholder::make('plant_asal_info')->label('Plant Asal Peminjaman')->content($record->gudangPalet->nama_gudang ?? '-'),
                        Placeholder::make('qty_pinjam')->label('Jumlah Palet Dipinjam')->content("{$record->qty} Palet"),
                    ];
                })
                ->columns(2)
                ->visible(fn(Get $get) => filled($get('peminjaman_manual_id'))),

            Section::make('Detail Pengembalian')->schema([
                Select::make('gudang_id')
                    ->label('Kembalikan ke Plant')
                    ->relationship(name: 'gudangPalet', titleAttribute: 'nama_gudang')
                    ->getOptionLabelFromRecordUsing(fn($record) => "[{$record->kode_gudang}] {$record->nama_gudang}")
                    ->searchable(['kode_gudang', 'nama_gudang'])
                    ->preload()
                    ->required(),
                DateTimePicker::make('tgl_kembali')->label('Tanggal & Waktu Pengembalian')->required()->default(now()),
            ])->columns(2),

            Section::make('Jumlah Palet Kembali (Berdasarkan Kondisi)')->schema([
                TextInput::make('qty_kembali_rfi')->label('Jumlah RFI')->numeric()->required()->minValue(0),
                TextInput::make('qty_kembali_tbr')->label('Jumlah TBR')->numeric()->required()->minValue(0)->default(0),
                TextInput::make('qty_kembali_ber')->label('Jumlah BER')->numeric()->required()->minValue(0)->default(0),
            ])->columns(3),

            FileUpload::make('ba_kembali_path')
                ->label('Upload Berita Acara Pengembalian (PDF)')
                ->disk('public')
                ->directory('berita-acara-pengembalian')
                ->acceptedFileTypes(['application/pdf'])
                ->downloadable()
                ->openable()
                ->required()
                ->columnSpanFull(),
            Textarea::make('keterangan')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('peminjamanManual.nopol')->label('Nopol')->searchable()->sortable(),
                TextColumn::make('gudangPalet.nama_gudang')->label('Dikembalikan Ke Plant')->sortable(),
                TextColumn::make('tgl_kembali')->dateTime()->sortable(),
                TextColumn::make('qty_kembali_rfi')->label('Qty RFI'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->visible(fn() => auth()->user()?->can('view_pengembalian::palet') ?? false),
                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()?->can('update_pengembalian::palet') ?? false),
                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->can('delete_pengembalian::palet') ?? false),
            ]);
    }

    /** Tampilkan hanya pengembalian dari Peminjaman Manual. */
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
