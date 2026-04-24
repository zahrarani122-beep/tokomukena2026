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
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjual_id')->constrained('penjual')->onDelete('cascade');
            $table->string('no_faktur');
            $table->enum('status', ['lunas', 'hutang'])->default('lunas'); // pilih lunas/hutang
            $table->datetime('tgl');
            $table->decimal('tagihan', 15, 2)->default(0); // dihitung dari total harga_beli * jml
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};