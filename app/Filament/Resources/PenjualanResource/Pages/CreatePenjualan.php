<?php

namespace App\Filament\Resources\PenjualanResource\Pages;

use App\Filament\Resources\PenjualanResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use App\Models\Penjualan;
use App\Models\PenjualanBarang;
use App\Models\Pembayaran;
use Illuminate\Support\Facades\DB;
use Filament\Notifications\Notification;

class CreatePenjualan extends CreateRecord
{
    protected static string $resource = PenjualanResource::class;

    protected function beforeCreate(): void
    {
        $this->data['status'] = $this->data['status'] ?? 'pesan';
    }

    protected function getFormActions(): array
    {
        return [
            Actions\Action::make('bayar')
                ->label('Bayar')
                ->color('success')
                ->action(fn () => $this->simpanPembayaran())
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pembayaran')
                ->modalDescription('Apakah Anda yakin ingin menyimpan pembayaran ini?')
                ->modalButton('Ya, Bayar'),
        ];
    }

    protected function simpanPembayaran(): void
    {
        $penjualan = $this->record ?? Penjualan::latest()->first();

        Pembayaran::create([
            'penjualan_id'     => $penjualan->id,
            'tgl_bayar'        => now(),
            'jenis_pembayaran' => 'tunai',
            'transaction_time' => now(),
            'gross_amount'     => $penjualan->tagihan,
            'order_id'         => $penjualan->no_faktur,
        ]);

        $penjualan->update(['status' => 'bayar']);

        Notification::make()
            ->title('Pembayaran Berhasil!')
            ->success()
            ->send();
    }
}