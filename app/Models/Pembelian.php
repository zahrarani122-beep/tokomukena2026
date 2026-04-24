<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// untuk tambahan db
use Illuminate\Support\Facades\DB;

class Pembelian extends Model
{
    use HasFactory;
    protected $table = 'pembelian'; // Nama tabel eksplisit

    protected $guarded = [];

    public static function getKodeFaktur()
    {
        // query kode faktur pembelian
        $sql = "SELECT IFNULL(MAX(no_faktur), 'B-0000000') as no_faktur 
                FROM pembelian ";
        $kodefaktur = DB::select($sql);

        // cacah hasilnya
        foreach ($kodefaktur as $kdpmbl) {
            $kd = $kdpmbl->no_faktur;
        }
        // Mengambil substring tujuh digit akhir dari string B-0000000
        $noawal = substr($kd, -7);
        $noakhir = $noawal + 1; // menambahkan 1, hasilnya adalah integer cth 1
        $noakhir = 'B-' . str_pad($noakhir, 7, "0", STR_PAD_LEFT); // menyambung dengan string B-0000001
        return $noakhir;
    }

    // relasi ke tabel penjual
    public function penjual()
    {
        return $this->belongsTo(Penjual::class, 'penjual_id');
    }

    // relasi ke tabel pembelian barang
    public function pembelianBarang()
    {
        return $this->hasMany(PembelianBarang::class, 'pembelian_id');
    }
}