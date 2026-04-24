<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PembelianBarang extends Model
{
    use HasFactory;

    protected $table = 'pembelian_barang';

    protected $fillable = [
        'pembelian_id',
        'barang_id',
        'harga_beli',
        'harga_jual',
        'jml',
    ];

    // relasi ke pembelian (header)
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }

    // relasi ke barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    /**
     * otomatis tambah stok saat pembelian
     */
    protected static function booted()
    {
        static::created(function ($data) {
            if ($data->barang) {
                $data->barang->increment('stok', $data->jml);
            }
        });
    }
}