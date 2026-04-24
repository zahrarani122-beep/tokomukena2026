<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Barang;
use App\Models\PembelianBarang;
use App\Models\Pembelian;
use Filament\Notifications\Notification;

class CreatePembelian extends CreateRecord
{
    protected static string $resource = PembelianResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['no_faktur'] = Pembelian::getKodeFaktur();
        $data['tagihan']   = 0; // sementara, akan diupdate di afterCreate
        return $data;
    }

    protected function afterCreate(): void
    {
        $total = 0;

        foreach ($this->data['items'] ?? [] as $item) {

            // CASE 1: Barang Baru
            if (($item['is_barang_baru'] ?? '0') === '1' && !empty($item['nama_barang_baru'])) {

                // Ambil foto, FileUpload mengembalikan array
                $foto = 'default.png';
                if (!empty($item['foto_baru'])) {
                    $foto = is_array($item['foto_baru'])
                        ? array_values($item['foto_baru'])[0]
                        : $item['foto_baru'];
                }

                $barang = Barang::create([
                    'kode_barang'  => Barang::getKodeBarang(),
                    'nama_barang'  => $item['nama_barang_baru'],
                    'harga_barang' => $item['harga_jual'],
                    'stok'         => 0,
                    'foto'         => $foto,
                    'rating'       => $item['rating_baru'] ?? 0,
                ]);

            // CASE 2: Barang Lama
            } else {
                $barang = Barang::find($item['barang_id']);
                if (!$barang) continue;
            }

            // ✅ Tambah stok HANYA SEKALI
            $barang->increment('stok', $item['jml']);

            // Simpan detail pembelian
            PembelianBarang::create([
                'pembelian_id' => $this->record->id,
                'barang_id'    => $barang->id,
                'harga_beli'   => $item['harga_beli'],
                'harga_jual'   => $item['harga_jual'],
                'jml'          => $item['jml'],
                'tgl'          => $this->record->tgl ?? now(),
            ]);

            $total += $item['jml'] * $item['harga_beli'];
        }

        // Update tagihan
        $this->record->update(['tagihan' => $total]);

        Notification::make()
            ->title('Pembelian berhasil disimpan')
            ->success()
            ->send();
    }
}