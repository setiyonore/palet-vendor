<?php

namespace App\Filament\PaletManagement\Resources\PengembalianPaletResource\Pages;

use App\Filament\PaletManagement\Resources\PengembalianPaletResource;
use App\Models\PM\PengembalianPalet;
use Filament\Actions;
use Filament\Infolists\Components\Actions as InfolistActions;
use Filament\Infolists\Components\Actions\Action as InfolistAction;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Storage;

class ViewPengembalianPalet extends ViewRecord
{
    protected static string $resource = PengembalianPaletResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Ringkasan Pengembalian')
                    ->schema([
                        TextEntry::make('identifier')
                            ->label('No. Peminjaman')
                            ->getStateUsing(fn(PengembalianPalet $record): string => $record->peminjamanPalet?->no_shipment ?? $record->peminjamanManual?->nopol ?? '-'),
                        TextEntry::make('tgl_kembali')->label('Tanggal Kembali')->dateTime(),
                        TextEntry::make('gudangPalet.nama_gudang')->label('Dikembalikan ke Plant'),
                        TextEntry::make('processor')->label('Diproses Oleh'),
                    ])->columns(2),

                Section::make('Rincian Palet Kembali')
                    ->schema([
                        TextEntry::make('qty_kembali_rfi')->label('Jml Kembali RFI'),
                        TextEntry::make('qty_kembali_tbr')->label('Jml Kembali TBR'),
                        TextEntry::make('qty_kembali_ber')->label('Jml Kembali BER'),
                    ])->columns(3),

                Section::make('Detail Peminjaman Asal')
                    ->schema([
                        TextEntry::make('plant_asal')
                            ->label('Plant Asal')
                            ->getStateUsing(fn(PengembalianPalet $record): string => $record->peminjamanPalet?->gudangPalet?->nama_gudang ?? $record->peminjamanManual?->gudangPalet?->nama_gudang ?? '-'),

                        TextEntry::make('tgl_pinjam')
                            ->label('Tanggal Pinjam')
                            ->dateTime()
                            ->getStateUsing(fn(PengembalianPalet $record) => $record->peminjamanPalet?->tgl_peminjaman ?? $record->peminjamanManual?->tgl_pinjam),

                        TextEntry::make('total_pinjam')
                            ->label('Total Palet Dipinjam')
                            ->getStateUsing(fn(PengembalianPalet $record): int => $record->peminjamanPalet?->qty ?? $record->peminjamanManual?->qty ?? 0),
                    ])->columns(3),

                Section::make('Dokumen Berita Acara')
                    ->schema([
                        InfolistActions::make([
                            InfolistAction::make('download_ba_pengembalian')
                                ->label('Download BA Pengembalian')
                                ->color('gray')
                                ->icon('heroicon-o-arrow-down-tray')
                                ->url(fn(PengembalianPalet $record): ?string => $record->ba_kembali_path ? Storage::disk('public')->url($record->ba_kembali_path) : null)
                                ->openUrlInNewTab(),
                        ]),
                    ])
                    ->visible(fn(PengembalianPalet $record): bool => !empty($record->ba_kembali_path)),
            ]);
    }
}
