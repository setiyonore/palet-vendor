<?php

namespace App\Filament\PaletManagement\Pages;

use App\Models\PM\GudangPalet;
use App\Models\PM\PaletStok;
use App\Models\PM\PaletStokHistori;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StokAdjustment extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-arrows-up-down';
    protected static ?string $navigationLabel = 'Penyesuaian Stok';
    protected static ?string $navigationGroup = 'Inventori';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Auth::user()->can('page_StokAdjustment');
    }

    protected static string $view = 'filament.pm.stok-adjustment';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('gudang_id')
                    ->label('Pilih Plant')
                    ->options(
                        GudangPalet::all()->mapWithKeys(function ($gudang) {
                            return [$gudang->id => "[{$gudang->kode_gudang}] {$gudang->nama_gudang}"];
                        })
                    )
                    ->searchable()
                    ->required(),

                Select::make('tipe_palet')
                    ->label('Tipe Palet')
                    ->options([
                        'rfi' => 'RFI (Ready For Issue)',
                        'tbr' => 'TBR (To Be Repaired)',
                        'ber' => 'BER (Beyond Economical Repair)',
                    ])
                    ->required(),

                Select::make('tipe_penyesuaian')
                    ->label('Tipe Penyesuaian')
                    ->options([
                        'tambah' => 'Tambah Stok',
                        'kurang' => 'Kurangi Stok',
                    ])
                    ->required(),

                TextInput::make('jumlah')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->label('Jumlah Penyesuaian'),

                Textarea::make('keterangan')
                    ->required()
                    ->label('Alasan Penyesuaian'),
            ])
            ->statePath('data')
            ->columns(2);
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label('Simpan Penyesuaian')
                ->submit('submit'),
        ];
    }

    public function submit(): void
    {
        $formData = $this->form->getState();

        try {
            DB::transaction(function () use ($formData) {
                $gudangId = $formData['gudang_id'];
                $tipePalet = $formData['tipe_palet'];
                $tipePenyesuaian = $formData['tipe_penyesuaian'];
                $jumlah = (int) $formData['jumlah'];
                $keterangan = $formData['keterangan'];

                $stok = PaletStok::where('gudang_id', $gudangId)->lockForUpdate()->firstOrFail();
                $kolomStok = 'qty_' . $tipePalet;
                $perubahanStok = ($tipePenyesuaian === 'tambah') ? $jumlah : -$jumlah;

                if ($tipePenyesuaian === 'kurang' && $stok->{$kolomStok} < $jumlah) {
                    throw ValidationException::withMessages([
                        'data.jumlah' => "Stok {$tipePalet} tidak mencukupi untuk dikurangi. Stok saat ini: {$stok->{$kolomStok}}",
                    ]);
                }

                $stok->{$kolomStok} += $perubahanStok;
                $stok->last_updated = now();
                $stok->save();

                PaletStokHistori::create([
                    'gudang_id' => $gudangId,
                    'tipe_transaksi' => 'ADJUSTMENT',
                    'user_id'        => Auth::id(),
                    'tgl_transaksi' => now(),
                    'perubahan_' . $tipePalet => $perubahanStok,
                    'keterangan' => $keterangan,
                ]);
            });

            Notification::make()
                ->success()
                ->title('Penyesuaian Stok Berhasil')
                ->body('Jumlah stok palet telah berhasil diperbarui.')
                ->send();

            $this->form->fill();
        } catch (ValidationException $e) {
            Notification::make()->danger()->title('Validasi Gagal')->body($e->getMessage())->send();
        } catch (\Exception $e) {
            Notification::make()->danger()->title('Terjadi Kesalahan')->body($e->getMessage())->send();
        }
    }
}
