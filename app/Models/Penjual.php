<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Penjual extends Model
{
    use HasFactory;

    protected $table = 'penjual'; // Nama tabel eksplisit

    protected $guarded = []; // semua kolom boleh diisi

    public static function getKodePenjual()
    {
        // query kode penjual
        $sql = "SELECT IFNULL(MAX(kode_penjual), 'J-00000') as kode_penjual 
                FROM penjual ";
        $kodepenjual = DB::select($sql);

        // cacah hasilnya
        foreach ($kodepenjual as $kdpjl) {
            $kd = $kdpjl->kode_penjual;
        }

        // Mengambil substring lima digit akhir dari string J-00000
        $noawal = substr($kd, -5);
        $noakhir = $noawal + 1; // menambahkan 1, hasilnya adalah integer cth 1
        $noakhir = 'J-' . str_pad($noakhir, 5, "0", STR_PAD_LEFT); // menyambung dengan string J-00001
        return $noakhir;
    }

    // relasi ke tabel pembelian
    public function pembelian()
    {
        return $this->hasMany(Pembelian::class, 'penjual_id');
    }
}