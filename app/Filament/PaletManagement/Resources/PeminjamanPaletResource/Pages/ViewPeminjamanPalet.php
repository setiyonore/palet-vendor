<?php

namespace App\Filament\PaletManagement\Resources\PeminjamanPaletResource\Pages;

use App\Filament\PaletManagement\Resources\PeminjamanPaletResource;
use App\Models\PM\GudangPalet;
use App\Models\PM\PaletStok;
use App\Models\PM\PaletStokHistori;
use App\Models\PM\PeminjamanPalet;
use App\Models\PM\PengembalianPalet;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section as FormSection;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\Actions as InfolistActions;
use Filament\Infolists\Components\Actions\Action as InfolistAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ViewPeminjamanPalet extends ViewRecord
{
    protected static string $resource = PeminjamanPaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // --- TOMBOL PENGEMBALIAN DITAMBAHKAN DI SINI ---
            Actions\Action::make('kembalikan')
                ->label('Kembalikan Palet')
                ->icon('heroicon-o-inbox-arrow-down')
                ->color('success')
                // Hanya terlihat jika status 0 dan pengguna punya izin
                ->visible(fn(PeminjamanPalet $record): bool => $record->status === 0 && Auth::user()->can('kembalikan_peminjaman::palet'))
                ->form([
                    FormSection::make('Detail Peminjaman')
                        ->schema([
                            Placeholder::make('no_shipment_info')
                                ->label('No. Shipment')
                                ->content(fn(PeminjamanPalet $record): string => $record->no_shipment),
                            Placeholder::make('gudang_asal_info')
                                ->label('Plant Asal')
                                ->content(fn(PeminjamanPalet $record): string => $record->gudangPalet->nama_gudang ?? '-'),
                            Placeholder::make('nama_shipto_info')
                                ->label('Nama Shipto')
                                ->content(fn(PeminjamanPalet $record): string => $record->nama_shipto ?? '-'),
                            Placeholder::make('nopol_info')
                                ->label('No. Polisi')
                                ->content(fn(PeminjamanPalet $record): string => $record->nopol ?? '-'),
                            Placeholder::make('driver_info')
                                ->label('Driver')
                                ->content(fn(PeminjamanPalet $record): string => $record->nama_driver ?? '-'),
                            Placeholder::make('qty_info')
                                ->label('Total Pinjam')
                                ->content(fn(PeminjamanPalet $record): string => "{$record->qty} unit."),
                        ])->columns(2),
                    Select::make('gudang_id')
                        ->label('Kembalikan ke Plant')
                        ->options(
                            GudangPalet::all()->mapWithKeys(function (GudangPalet $gudang) {
                                return [$gudang->id => "[{$gudang->kode_gudang}] {$gudang->nama_gudang}"];
                            })
                        )
                        ->searchable()
                        ->required(),
                    FormSection::make('Jumlah Palet Kembali (Total harus sama dengan jumlah pinjam)')->schema([
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

                            $stok = PaletStok::where('gudang_id', $data['gudang_id'])->lockForUpdate()->firstOrFail();
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
                })
                ->after(function (PeminjamanPalet $record) {
                    return redirect($this->getResource()::getUrl('view', ['record' => $record]));
                }),
            // --- SELESAI PENAMBAHAN ---

            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Peminjaman')
                    ->schema([
                        TextEntry::make('no_shipment'),
                        TextEntry::make('tgl_peminjaman')->label('Tanggal & Waktu Peminjaman')->dateTime(),
                        TextEntry::make('gudangPalet.nama_gudang')->label('Plant Asal'),
                        TextEntry::make('qty')->label('Jumlah Palet Dipinjam'),
                        TextEntry::make('status')
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
                    ])->columns(2),

                Section::make('Informasi Pengembalian')
                    ->schema([
                        TextEntry::make('pengembalian.tgl_kembali')->label('Tanggal Kembali')->dateTime(),
                        TextEntry::make('pengembalian.gudangPalet.nama_gudang')->label('Dikembalikan ke Plant'),
                        TextEntry::make('pengembalian.qty_kembali_rfi')->label('Jml Kembali RFI'),
                        TextEntry::make('pengembalian.qty_kembali_tbr')->label('Jml Kembali TBR'),
                        TextEntry::make('pengembalian.qty_kembali_ber')->label('Jml Kembali BER'),
                    ])
                    ->columns(2)
                    ->visible(fn(PeminjamanPalet $record): bool => $record->pengembalian !== null),

                Section::make('Dokumen Berita Acara')
                    ->schema([
                        InfolistActions::make([
                            InfolistAction::make('download_ba_peminjaman')
                                ->label('Download BA Peminjaman')
                                ->color('gray')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->url(fn(PeminjamanPalet $record): ?string => $record->berita_acara_path ? Storage::disk('public')->url($record->berita_acara_path) : null)
                                ->openUrlInNewTab()
                                ->visible(fn(PeminjamanPalet $record): bool => !empty($record->berita_acara_path)),
                            InfolistAction::make('download_ba_pengembalian')
                                ->label('Download BA Pengembalian')
                                ->color('gray')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->url(fn(PeminjamanPalet $record): ?string => $record->pengembalian?->ba_kembali_path ? Storage::disk('public')->url($record->pengembalian->ba_kembali_path) : null)
                                ->openUrlInNewTab()
                                ->visible(fn(PeminjamanPalet $record): bool => !empty($record->pengembalian?->ba_kembali_path)),
                        ]),
                    ])
                    ->visible(fn(PeminjamanPalet $record): bool => !empty($record->berita_acara_path) || !empty($record->pengembalian?->ba_kembali_path)),

                Section::make('Detail Pengiriman & Pihak Terkait')
                    ->schema([
                        TextEntry::make('masterVendor.nama_vendor')->label('Vendor'),
                        TextEntry::make('nopol')->label('No. Polisi'),
                        TextEntry::make('nama_driver')->label('Driver'),
                        TextEntry::make('nama_plant')->label('Nama Plant'),
                        TextEntry::make('kode_plant2')->label('Kode Plant'),
                        TextEntry::make('nama_shipto')->label('Nama Shipto'),
                        TextEntry::make('kota_distrik')->label('Kota'),
                    ])->columns(2),
            ]);
    }
}
