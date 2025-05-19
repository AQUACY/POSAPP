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
        Schema::table('stock_changes', function (Blueprint $table) {
            $table->string('reference_type')->nullable()->after('reason');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->foreignId('business_id')->nullable()->after('reference_id')->constrained()->onDelete('cascade');
            $table->foreignId('branch_id')->nullable()->after('business_id')->constrained()->onDelete('cascade');
            $table->string('status')->default('pending')->after('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_changes', function (Blueprint $table) {
            $table->dropForeign(['business_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn([
                'reference_type',
                'reference_id',
                'business_id',
                'branch_id',
                'status',
            ]);
        });
    }
}; 