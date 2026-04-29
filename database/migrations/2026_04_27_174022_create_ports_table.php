<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->integer('port_number');
            $table->enum('type', ['copper', 'sfp', 'sfp_plus', 'qsfp', 'console']);
            $table->enum('status', ['active', 'free', 'down', 'disabled'])->default('free');
            $table->string('service_name')->nullable();
            $table->string('connected_device')->nullable();
            $table->integer('vlan_id')->nullable();
            $table->integer('speed_mbps')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['device_id', 'port_number']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('ports');
    }
};
