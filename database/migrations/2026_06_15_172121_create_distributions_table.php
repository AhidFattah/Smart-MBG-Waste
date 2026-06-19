<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('distributions', function ($table) {
        $table->id();
        // Relasi ke tabel schools (asumsi kamu pakai default users/schools id)
        $table->unsignedBigInteger('school_id'); 
        $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
        $table->integer('total_porsi_dikirim'); // Mengikuti jumlah siswa (min 100)
        $table->date('tanggal_distribusi');
        $table->enum('status_pengiriman', ['Diproses', 'Dalam_Perjalanan', 'Diterima'])->default('Diterima');
        $table->timestamps();
    });
}   

    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};