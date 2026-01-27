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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            // Unique fields for assets
            $table->string('asset_tag')->nullable()->unique();
            $table->string('serial_number')->unique();
            // Foreign keys
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            // Device details
            $table->string('name')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->year('purchase_year')->nullable();
            $table->integer('period')->nullable();
            $table->string('vendor')->nullable();
            // Status
            $table->enum('status',['AVAILABLE', 'BORROWED', 'MAINTENANCE'])->default('AVAILABLE')->nullable();
            $table->enum('loan_type',['SHORT_TERM', 'LONG_TERM'])->nullable();
            $table->date('due_date')->nullable();
            // Monitoring fields
            $table->timestamp('last_audit_date')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->json('hardware_specs')->nullable();
            // Timestamps
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
