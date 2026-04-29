<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('device_code')->unique();
            $table->enum('type', ['switch', 'router', 'firewall', 'access_point', 'server', 'other']);
            $table->string('vendor');
            $table->string('model');
            $table->string('serial_number')->unique();
            $table->string('ip_address');
            $table->string('mac_address')->nullable();
            $table->string('firmware_version')->nullable();
            $table->enum('status', ['online', 'offline', 'maintenance', 'decommissioned'])->default('offline');
            $table->foreignId('location_id')->constrained()->onDelete('cascade');
            $table->date('procurement_date')->nullable();
            $table->date('installation_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('amc_expiry')->nullable();
            $table->date('eol_date')->nullable();
            $table->text('remarks')->nullable();
            $table->boolean('is_critical')->default(false);
            $table->boolean('monitoring_enabled')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('devices');
    }
};
