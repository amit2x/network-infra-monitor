<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('network_topology', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->foreignId('neighbor_device_id')->nullable()->constrained('devices')->onDelete('set null');
            $table->string('local_interface')->nullable();
            $table->string('remote_interface')->nullable();
            $table->string('connection_type')->default('ethernet');
            $table->string('bandwidth')->nullable();
            $table->string('status')->default('unknown');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('network_topology');
    }
};