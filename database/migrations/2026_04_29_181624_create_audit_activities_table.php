<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('user_name')->nullable();
            $table->string('user_email')->nullable();
            $table->string('user_role')->nullable();
            
            // Action details
            $table->string('action'); // created, updated, deleted, restored, logged_in, logged_out, etc.
            $table->string('module'); // Device, Port, Location, User, Alert, etc.
            $table->string('module_id')->nullable(); // ID of the affected record
            $table->string('module_name')->nullable(); // Name/Title of the affected record
            
            // Old and new values (for updates)
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            
            // Additional info
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('method')->nullable(); // GET, POST, PUT, DELETE
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('browser')->nullable();
            $table->string('platform')->nullable();
            
            // Status
            $table->string('status')->default('success'); // success, failed
            $table->text('error_message')->nullable();
            
            // Additional data
            $table->json('metadata')->nullable();
            
            $table->timestamp('performed_at')->useCurrent();
            $table->timestamps();
            
            // Indexes for faster queries
            $table->index('user_id');
            $table->index('action');
            $table->index('module');
            $table->index('module_id');
            $table->index('performed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_activities');
    }
};