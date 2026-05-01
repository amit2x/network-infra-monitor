<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('snmp_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->json('system_info')->nullable();
            $table->float('cpu_usage', 8, 2)->nullable();
            $table->float('memory_usage', 8, 2)->nullable();
            $table->bigInteger('memory_total')->nullable();
            $table->bigInteger('memory_used')->nullable();
            $table->integer('interface_count')->nullable();
            $table->json('interfaces_data')->nullable();
            $table->json('raw_data')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();
            
            $table->index(['device_id', 'collected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('snmp_data');
    }
};