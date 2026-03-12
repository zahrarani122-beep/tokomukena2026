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
        Schema::create('kamars', function (Blueprint $table) {
            $table->id();
           $table->string('no_kamar');
            $table->string('nama_kamar');
            $table->integer('lantai_kamar');
        $table->string('foto_kamar');
        $table->integer('harga_kamar');
        $table->enum('status_kamar', ['Kosong', 'Terisi']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kamars');
    }
};
