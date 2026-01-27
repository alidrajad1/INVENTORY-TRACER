<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('asset_histories', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('employee_id')->nullable()->constrained();

            $table->enum('action', ['assign', 'return', 'send_repair', 'finish_repair', 'audit', 'relocate']);

            $table->enum('status_before', ['AVAILABLE', 'BORROWED', 'MAINTENANCE'])->nullable();
            $table->enum('status_after', ['AVAILABLE', 'BORROWED', 'MAINTENANCE'])->nullable();

            $table->enum('condition', ['GOOD', 'BAD', 'BROKEN'])->default('GOOD');

            $table->foreignId('location_id')->nullable()->constrained();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_histories');
    }
};
