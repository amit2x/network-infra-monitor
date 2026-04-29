<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('monitoring_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_id')->constrained()->onDelete('cascade');
            $table->enum('event_type', ['ping_check', 'status_change', 'snmp_check', 'error']);
            $table->enum('status', ['success', 'failure', 'warning']);
            $table->text('message')->nullable();
            $table->json('details')->nullable();
            $table->decimal('response_time_ms', 10, 2)->nullable();
            $table->timestamps();

            $table->index(['device_id', 'event_type', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('monitoring_logs');
    }
};
