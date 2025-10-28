<?php

namespace App\Filament\PaletManagement\Resources;

use App\Filament\PaletManagement\Resources\PeminjamanPaletResource\Pages;
use App\Models\PM\GudangPalet;
use App\Models\PM\PaletStok;
use App\Models\PM\PaletStokHistori;
use App\Models\PM\PeminjamanPalet;
use App\Models\PM\PengembalianPalet;
use App\Services\ShipmentApiService;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component as Livewire;
use pxlrbt\FilamentExcel\Actions\Tables\ExportAction;
use pxlrbt\FilamentExcel\Exports\ExcelExport;

class PeminjamanPaletResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = PeminjamanPalet::class;
    protected static ?string $pluralModelLabel = 'Transaksi Palet';
    protected static ?string $navigationLabel = 'Transaksi Palet';
    protected static ?string $navigationIcon = 'heroicon-o-arrows-right-left';

    # === NAV & ACCESS GUARDS (baru) ===
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()?->can('view_any_peminjaman::palet') ?? false;
    }
    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_any_peminjaman::palet') ?? false;
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
            'kembalikan', // custom action
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Data Peminjaman')->schema([
                TextInput::make('no_shipment')
                    ->label('No Shipment')
                    ->required()
                    ->suffixAction(
                        Forms\Components\Actions\Action::make('cekNoShipment')
                            ->label('Cek')
                            ->icon('heroicon-o-magnifying-glass')
                            ->action(function (Get $get, Set $set, Livewire $livewire) {
                                $no = trim((string) $get('no_shipment'));
                                $set('no_shipment', $no);

                                $resetForm = function () use ($set, $livewire) {
                                    $livewire->shipmentChecked = false;
                                    $livewire->shipmentPayload = [];
                                    $set('nopol', null);
                                    $set('nama_vendor', null);
                                    $set('nama_driver', null);
                                    $set('nama_shipto', null);
                                    $set('kode_shipto2', null);
                                };

                                if (empty($no)) {
                                    $resetForm();
                                    Notification::make()->title('No Shipment wajib diisi')->warning()->send();
                                    return;
                                }

                                $apiService = app(ShipmentApiService::class);
                                $payload = $apiService->getShipment($no);
                                if (($payload['status'] ?? true) === false || empty($payload['data'])) {
                                    $resetForm();
                                    Notification::make()
                                        ->title(data_get($payload, 'message', 'Data Tidak Ditemukan'))
                                        ->body('Pastikan nomor shipment yang Anda masukkan sudah benar.')
                                        ->danger()
                                        ->send();
                                    return;
                                }

                                $header = data_get($payload, 'data.0');
                                $nopolFromApi = data_get($header, 'nopol');

                                if (PeminjamanPalet::where('no_shipment', $no)->exists()) {
                                    $resetForm();
                                    Notification::make()
                                        ->danger()
                                        ->title('Peminjaman Gagal')
                                        ->body('Nomor shipment ini sudah terdaftar dan melakukan peminjaman.')
                                        ->send();
                                    return;
                                }

                                if ($nopolFromApi) {
                                    $outstandingQty = PeminjamanPalet::query()
                                        ->where('nopol', $nopolFromApi)
                                        ->sum('qty');

                                    if ($outstandingQty > 0) {
                                        $resetForm();
                                        Notification::make()
                                            ->danger()
                                            ->title('Peminjaman Gagal')
                                            ->body("Kendaraan dengan Nopol {$nopolFromApi} masih memiliki tanggungan {$outstandingQty} palet yang belum dikembalikan.")
                                            ->send();
                                        return;
                                    }
                                }

                                $totTonase   = (float) data_get($header, 'tot_tonase_shipment', 0);
                                $defaultQty  = ceil($totTonase / 2);
                                $kodePlant   = data_get($header, 'kode_plant2');
                                $defaultGudangId = optional(
                                    GudangPalet::where('kode_gudang', $kodePlant)->first()
                                )->id;

                                $livewire->shipmentPayload = ['no_shipment' => $no, 'header' => $header];
                                $livewire->shipmentChecked = true;

                                $set('nopol', (string) data_get($header, 'nopol', ''));
                                $set('nama_vendor', (string) data_get($header, 'nama_vendor', ''));
                                $set('nama_driver', (string) data_get($header, 'nama_driver', ''));
                                $set('nama_shipto', (string) data_get($header, 'nama_shipto', ''));
                                $set('kode_shipto2', (string) data_get($header, 'kode_shipto2', ''));
                                $set('qty', $defaultQty);
                                $set('gudang_id', $defaultGudangId);

                                Notification::make()->title('No Shipment ditemukan')->success()->send();
                            })
                    ),
                Select::make('gudang_id')
                    ->label('Ambil dari Plant')
                    ->relationship(name: 'gudangPalet', titleAttribute: 'nama_gudang')
                    ->getOptionLabelFromRecordUsing(fn(GudangPalet $record) => "[{$record->kode_gudang}] {$record->nama_gudang}")
                    ->searchable(['kode_gudang', 'nama_gudang'])
                    ->preload()
                    ->required(),

                TextInput::make('qty')->label('Jumlah')->numeric()->minValue(1)->required(),

                DateTimePicker::make('tgl_peminjaman')->label('Tanggal & Waktu Peminjaman')->default(now())->required(),

                FileUpload::make('berita_acara_path')
                    ->label('Upload Berita Acara Peminjaman (PDF)')
                    ->disk('public')
                    ->directory('berita-acara-peminjaman')
                    ->acceptedFileTypes(['application/pdf'])
                    ->downloadable()
                    ->required()
                    ->openable(),

                TextInput::make('nopol')->label('No Polisi')->disabled()->dehydrated(false)->visible(fn(Get $get) => filled($get('nopol'))),
                TextInput::make('nama_vendor')->label('Vendor')->disabled()->dehydrated(false)->visible(fn(Get $get) => filled($get('nama_vendor'))),
                TextInput::make('nama_driver')->label('Driver')->disabled()->dehydrated(false)->visible(fn(Get $get) => filled($get('nama_driver'))),
                TextInput::make('nama_shipto')->label('Nama Shipto')->disabled()->dehydrated(false)->visible(fn(Get $get) => filled($get('nama_shipto'))),
                TextInput::make('kode_shipto2')->label('Kode Shipto')->disabled()->dehydrated(false)->visible(fn(Get $get) => filled($get('kode_shipto2'))),

                Placeholder::make('info_create_locked')
                    ->label('')
                    ->content('Tombol **Create** akan aktif setelah No Shipment berhasil dicek.')
                    ->visible(function (Livewire $livewire): bool {
                        if ($livewire->record) return false;
                        return ! (bool) ($livewire->shipmentChecked ?? false);
                    })
                    ->extraAttributes(['class' => 'text-yellow-600 text-sm mb-2']),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('tgl_peminjaman', 'desc')
            ->columns([
                TextColumn::make('no_shipment')->searchable(),
                TextColumn::make('tgl_peminjaman')->dateTime()->label('Tgl. Pinjam')->sortable(),
                TextColumn::make('gudangPalet.nama_gudang')->label('Plant Asal')->sortable()->searchable(),
                TextColumn::make('nopol')->searchable(),
                TextColumn::make('masterVendor.nama_vendor')->label('Vendor')->searchable()->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('qty')->label('Jumlah'),
                TextColumn::make('status')
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
            ->filters([
                SelectFilter::make('status')->options(['0' => 'Belum Dikembalikan', '1' => 'Sudah Dikembalikan']),
                SelectFilter::make('gudang_id')->label('Gudang Asal')->relationship('gudangPalet', 'nama_gudang'),
            ])
            ->actions([
                # === ACTION GUARDS (baru) ===
                Tables\Actions\ViewAction::make()
                    ->visible(fn() => auth()->user()?->can('view_peminjaman::palet') ?? false),

                Tables\Actions\EditAction::make()
                    ->visible(fn() => auth()->user()?->can('update_peminjaman::palet') ?? false),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn() => auth()->user()?->can('delete_peminjaman::palet') ?? false)
                    ->before(
                        function (\Filament\Tables\Actions\DeleteAction $action, \App\Models\PM\PeminjamanPalet $record) {
                            if ($record->pengembalian()->exists()) {
                                \Filament\Notifications\Notification::make()
                                    ->danger()
                                    ->title('Tidak bisa dihapus')
                                    ->body('Transaksi tidak dapat dihapus karena sudah diinputkan pengembalian')
                                    ->send();
                                $action->halt();
                            }
                        }
                    ),

                Tables\Actions\Action::make('kembalikan')
                    ->label('Kembalikan Palet')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('success')
                    ->visible(
                        fn(PeminjamanPalet $record) =>
                        $record->status === 0
                            && (auth()->user()?->can('kembalikan_peminjaman::palet') ?? false)
                    )
                    ->form([
                        Section::make('Detail Peminjaman')->schema([
                            Placeholder::make('no_shipment_info')->label('No. Shipment')->content(fn(PeminjamanPalet $record): string => $record->no_shipment),
                            Placeholder::make('gudang_asal_info')->label('Plant Asal')->content(fn(PeminjamanPalet $record): string => $record->gudangPalet->nama_gudang ?? '-'),
                            Placeholder::make('nama_shipto_info')->label('Nama Shipto')->content(fn(PeminjamanPalet $record): string => $record->nama_shipto ?? '-'),
                            Placeholder::make('nopol_info')->label('No. Polisi')->content(fn(PeminjamanPalet $record): string => $record->nopol ?? '-'),
                            Placeholder::make('driver_info')->label('Driver')->content(fn(PeminjamanPalet $record): string => $record->nama_driver ?? '-'),
                            Placeholder::make('qty_info')->label('Total Pinjam')->content(fn(PeminjamanPalet $record): string => "{$record->qty} unit."),
                        ])->columns(2),
                        Select::make('gudang_id')
                            ->label('Kembalikan ke Plant')
                            ->options(GudangPalet::all()->mapWithKeys(fn(GudangPalet $g) => [$g->id => "[{$g->kode_gudang}] {$g->nama_gudang}"]))
                            ->searchable()
                            ->required(),
                        Section::make('Jumlah Palet Kembali (Total harus sama dengan jumlah pinjam)')->schema([
                            TextInput::make('qty_kembali_rfi')->numeric()->label('Jumlah RFI')->default(fn(PeminjamanPalet $record) => $record->qty)->required(),
                            TextInput::make('qty_kembali_tbr')->numeric()->label('Jumlah TBR')->default(0)->required(),
                            TextInput::make('qty_kembali_ber')->numeric()->label('Jumlah BER')->default(0)->required(),
                        ])->columns(3),
                        FileUpload::make('ba_kembali_path')
                            ->label('Upload Berita Acara Pengembalian (PDF)')
                            ->disk('public')
                            ->directory('berita-acara-pengembalian')
                            ->acceptedFileTypes(['application/pdf'])
                            ->required(),
                    ])
                    ->action(function (PeminjamanPalet $record, array $data): void {
                        $totalKembali = (int)$data['qty_kembali_rfi'] + (int)$data['qty_kembali_tbr'] + (int)$data['qty_kembali_ber'];

                        if ($totalKembali !== $record->qty) {
                            Notification::make()->danger()->title('Jumlah Tidak Sesuai')->body("Total palet yang dikembalikan ({$totalKembali}) harus sama dengan jumlah yang dipinjam ({$record->qty}).")->send();
                            return;
                        }

                        try {
                            DB::transaction(function () use ($record, $data) {
                                $pengembalian = PengembalianPalet::create([
                                    'peminjaman_palet_id' => $record->id,
                                    'gudang_id'           => $data['gudang_id'],
                                    'tgl_kembali'         => now(),
                                    'qty_kembali_rfi'     => $data['qty_kembali_rfi'],
                                    'qty_kembali_tbr'     => $data['qty_kembali_tbr'],
                                    'qty_kembali_ber'     => $data['qty_kembali_ber'],
                                    'ba_kembali_path'     => $data['ba_kembali_path'],
                                    'user_id'             => Auth::id(),
                                ]);

                                $record->update(['status' => 1]);

                                $stok = \App\Models\PM\PaletStok::where('gudang_id', $data['gudang_id'])->lockForUpdate()->firstOrFail();
                                $stok->increment('qty_rfi', $data['qty_kembali_rfi']);
                                $stok->increment('qty_tbr', $data['qty_kembali_tbr']);
                                $stok->increment('qty_ber', $data['qty_kembali_ber']);

                                PaletStokHistori::create([
                                    'gudang_id'      => $data['gudang_id'],
                                    'referensi_id'   => $pengembalian->id,
                                    'tipe_transaksi' => 'PENGEMBALIAN',
                                    'user_id'        => Auth::id(),
                                    'tgl_transaksi'  => now(),
                                    'perubahan_rfi'  => $data['qty_kembali_rfi'],
                                    'perubahan_tbr'  => $data['qty_kembali_tbr'],
                                    'perubahan_ber'  => $data['qty_kembali_ber'],
                                    'keterangan'     => "Pengembalian dari shipment {$record->no_shipment}",
                                ]);
                            });
                            Notification::make()->success()->title('Pengembalian Berhasil')->body('Stok palet telah berhasil diperbarui.')->send();
                        } catch (\Exception $e) {
                            Notification::make()->danger()->title('Terjadi Kesalahan')->body($e->getMessage())->send();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn() => auth()->user()?->can('delete_any_peminjaman::palet') ?? false),
                ]),
            ])
            ->headerActions([
                ExportAction::make()
                    ->label('Export Excel')
                    ->visible(fn() => auth()->user()?->can('view_any_peminjaman::palet') ?? false)
                    ->exports([
                        ExcelExport::make()
                            ->fromTable()
                            ->withFilename('Rekap Transaksi Palet - ' . date('Y-m-d'))
                            ->withWriterType(\Maatwebsite\Excel\Excel::XLSX)
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPeminjamanPalets::route('/'),
            'create' => Pages\CreatePeminjamanPalet::route('/create'),
            'edit'   => Pages\EditPeminjamanPalet::route('/{record}/edit'),
            'view'   => Pages\ViewPeminjamanPalet::route('/{record}'),
        ];
    }
}
