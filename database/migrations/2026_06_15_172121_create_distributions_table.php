<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->date('distribution_date');
            $table->foreignId('school_id')->constrained('schools')->onDelete('cascade');
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->integer('qty_sent');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();
            
            $table->index(['distribution_date', 'school_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('distributions');
    }
};