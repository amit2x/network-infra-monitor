<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bandwidth_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->integer('port_number')->nullable();
            $table->bigInteger('in_octets')->default(0);
            $table->bigInteger('out_octets')->default(0);
            $table->bigInteger('in_bandwidth_bps')->nullable();
            $table->bigInteger('out_bandwidth_bps')->nullable();
            $table->float('in_utilization_percent', 8, 2)->nullable();
            $table->float('out_utilization_percent', 8, 2)->nullable();
            $table->bigInteger('port_speed')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamps();
            
            $table->index(['device_id', 'port_number', 'collected_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bandwidth_data');
    }
};