<?php

namespace App\Filament\PaletManagement\Resources\PeminjamanPaletResource\Pages;

use App\Filament\PaletManagement\Resources\PeminjamanPaletResource;
use App\Models\PM\PaletStok;
use App\Models\PM\PaletStokHistori;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class CreatePeminjamanPalet extends CreateRecord
{
    protected static string $resource = PeminjamanPaletResource::class;

    public bool $shipmentChecked = false;
    public array $shipmentPayload = [];

    protected function getFormActions(): array
    {
        return [
            $this->getCreateFormAction()->disabled(fn() => !$this->shipmentChecked),
            $this->getCreateAnotherFormAction()->disabled(fn() => !$this->shipmentChecked),
            $this->getCancelFormAction(),
        ];
    }

    protected function handleRecordCreation(array $data): Model
    {

        return DB::transaction(function () use ($data) {
            if (!$this->shipmentChecked || empty($this->shipmentPayload)) {
                throw ValidationException::withMessages([
                    'data.no_shipment' => 'Silakan klik tombol "Cek" untuk mengambil data shipment terlebih dahulu.',
                ]);
            }

            $no_shipment_bersih = trim((string) $data['no_shipment']);
            $qtyPinjam = (int) $data['qty'];
            $gudangId = $data['gudang_id'];

            if ($qtyPinjam <= 0) {
                throw ValidationException::withMessages([
                    'data.qty' => 'Jumlah (Qty) harus diisi dan lebih dari 0.',
                ]);
            }
            $dataFromApi = $this->shipmentPayload['header'] ?? [];
            $fullData = array_merge($dataFromApi, $data);
            $fullData['no_shipment'] = $no_shipment_bersih;
            $fullData['qty'] = $qtyPinjam;
            if (static::getResource()::getModel()::where('no_shipment', $fullData['no_shipment'])->exists()) {
                throw ValidationException::withMessages([
                    'data.no_shipment' => 'Nomor shipment ini sudah melakukan peminjaman palet.',
                ]);
            }

            $nopol = data_get($fullData, 'nopol');
            if ($nopol && static::getResource()::getModel()::where('nopol', $nopol)->where('status', 0)->exists()) {
                throw ValidationException::withMessages([
                    'data.no_shipment' => "Kendaraan dengan Nopol {$nopol} masih memiliki peminjaman palet yang belum dikembalikan.",
                ]);
            }


            $stok = PaletStok::where('gudang_id', $gudangId)->lockForUpdate()->first();
            if (!$stok || $stok->qty_rfi < $qtyPinjam) {
                throw ValidationException::withMessages([
                    'data.qty' => "Stok RFI di gudang tidak mencukupi. Stok tersedia: " . ($stok->qty_rfi ?? 0),
                ]);
            }

            // Log::info('DATA FINAL SEBELUM CREATE:', $fullData);
            $peminjaman = static::getResource()::getModel()::create($fullData);

            $stok->decrement('qty_rfi', $qtyPinjam);
            $stok->save();

            PaletStokHistori::create([
                'gudang_id'      => $gudangId,
                'referensi_id'   => $peminjaman->id,
                'tipe_transaksi' => 'PEMINJAMAN',
                'user_id'        => Auth::id(),
                'tgl_transaksi'  => now(),
                'perubahan_rfi'  => -$qtyPinjam,
                'keterangan'     => "Peminjaman untuk shipment {$peminjaman->no_shipment}",
            ]);

            return $peminjaman;
        });
    }

    protected function afterCreate(): void
    {
        $this->shipmentChecked = false;
        $this->shipmentPayload = [];
        Notification::make()->title('Peminjaman tersimpan dan stok berhasil diperbarui.')->success()->send();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
