<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('motor', function (Blueprint $table) {
            $table->id();
            $table->string('nama_motor');
            $table->string('jenis_motor'); // contoh: matic, sport
            $table->string('merek_motor'); // contoh: Honda, Yamaha
            $table->string('plat_nomor')->unique();
            $table->enum('status', ['tersedia', 'disewa'])->default('tersedia');
            $table->integer('sewa_perhari'); // harga sewa per hari
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('motor');
    }
};
