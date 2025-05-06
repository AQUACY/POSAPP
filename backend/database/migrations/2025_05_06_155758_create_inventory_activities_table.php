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
        Schema::create('inventory_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_id')->constrained('inventories')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->string('action_type'); // in, out, update, create, delete
            $table->integer('quantity')->nullable();
            $table->integer('old_quantity')->nullable();
            $table->integer('new_quantity')->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->string('reference_type')->nullable(); // sale, refund, adjustment, etc.
            $table->string('reference_id')->nullable(); // ID of the related transaction
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['inventory_id', 'created_at']);
            $table->index(['branch_id', 'created_at']);
            $table->index(['action_type', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventory_activities');
    }
};
