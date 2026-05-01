<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            // SNMP v3 fields
            $table->string('snmp_v3_security_level')->nullable()->after('snmp_polling_interval');
            $table->string('snmp_v3_auth_protocol')->nullable()->after('snmp_v3_security_level');
            $table->string('snmp_v3_auth_username')->nullable()->after('snmp_v3_auth_protocol');
            $table->text('snmp_v3_auth_password')->nullable()->after('snmp_v3_auth_username');
            $table->string('snmp_v3_priv_protocol')->nullable()->after('snmp_v3_auth_password');
            $table->text('snmp_v3_priv_password')->nullable()->after('snmp_v3_priv_protocol');
            $table->string('snmp_v3_context_name')->nullable()->after('snmp_v3_priv_password');
        });
    }

    public function down(): void
    {
        Schema::table('devices', function (Blueprint $table) {
            $table->dropColumn([
                'snmp_v3_security_level',
                'snmp_v3_auth_protocol',
                'snmp_v3_auth_username',
                'snmp_v3_auth_password',
                'snmp_v3_priv_protocol',
                'snmp_v3_priv_password',
                'snmp_v3_context_name',
            ]);
        });
    }
};