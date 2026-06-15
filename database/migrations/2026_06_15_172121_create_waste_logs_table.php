<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('waste_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('distribution_id')->unique()->constrained('distributions')->onDelete('cascade');
            $table->integer('qty_habis');
            $table->integer('qty_sebagian');
            $table->integer('qty_tidak_habis');
            $table->decimal('food_waste_index', 5, 2); 
            $table->string('recommendation_status');   
            $table->integer('recommended_next_qty');   
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('waste_logs');
    }
};