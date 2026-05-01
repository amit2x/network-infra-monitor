<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rack_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rack_id')->constrained()->onDelete('cascade');
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->integer('unit_start')->comment('Starting U position');
            $table->integer('unit_height')->default(1)->comment('Height in U units');
            $table->enum('side', ['front', 'rear'])->default('front');
            $table->integer('position')->nullable();
            $table->string('color')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['rack_id', 'unit_start', 'side'], 'unique_rack_position');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rack_items');
    }
};