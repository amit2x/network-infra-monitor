<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
                        $table->string('employee_id')->unique()->after('id');
            $table->string('department')->nullable()->after('employee_id');
            $table->string('phone')->nullable()->after('department');
            $table->boolean('is_active')->default(true)->after('phone');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
                        $table->dropColumn(['employee_id', 'department', 'phone', 'is_active']);

        });
    }
};
