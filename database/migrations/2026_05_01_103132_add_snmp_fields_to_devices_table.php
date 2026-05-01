<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->boolean('snmp_enabled')->default(false)->after('monitoring_enabled');
            $table->string('snmp_community')->nullable()->after('snmp_enabled');
            $table->string('snmp_version')->default('2c')->after('snmp_community');
            $table->integer('snmp_port')->default(161)->after('snmp_version');
            $table->integer('snmp_timeout')->default(1)->after('snmp_port');
            $table->boolean('snmp_polling_enabled')->default(false)->after('snmp_timeout');
            $table->integer('snmp_polling_interval')->default(300)->after('snmp_polling_enabled');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'snmp_enabled',
                'snmp_community',
                'snmp_version',
                'snmp_port',
                'snmp_timeout',
                'snmp_polling_enabled',
                'snmp_polling_interval',
            ]);
        });
    }
};