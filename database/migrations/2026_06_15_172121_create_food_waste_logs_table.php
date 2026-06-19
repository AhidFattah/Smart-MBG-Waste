<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('food_waste_logs', function ($table) {
        $table->id();
        $table->foreignId('distribution_id')->constrained('distributions')->onDelete('cascade');
        $table->integer('qty_habis')->default(0);
        $table->integer('qty_sebagian')->default(0);
        $table->integer('qty_tidak_habis')->default(0);
        $table->decimal('indeks_waste', 5, 2);
        $table->string('rekomendasi_dss');
        $table->string('faktor_penyebab');
        $table->timestamps();
    });
}

    public function down()
    {
        Schema::dropIfExists('waste_logs');
    }
};