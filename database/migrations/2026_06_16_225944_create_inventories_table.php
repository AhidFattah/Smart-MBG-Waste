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
    Schema::create('inventories', function ($table) {
        $table->id();
        $table->string('nama_bahan');
        $table->string('kategori'); // Beras, Lauk, Sayur, Buah, Lainnya
        $table->integer('stok');
        $table->string('satuan'); // Kg, Liter, Pcs, Butir
        $table->date('tanggal_masuk');
        $table->date('tanggal_kedaluwarsa');
        $table->enum('status_stok', ['Aman', 'Menipis', 'Habis'])->default('Aman');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
