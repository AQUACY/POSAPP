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
        $tables = [
            'sales',
            'sale_items',
            'staff',
            'stock_changes',
            'stock_requests',
            'stock_request_items',
            'users',
            'warehouses',
            'refunds',
            'refund_items',
            'inventories',
            'payments',
            'branches',
            'businesses',
            'categories',
            'customers'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->string('sync_status')->default('synced');
                $table->timestamp('last_sync_at')->nullable();
                $table->string('device_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'sales',
            'sale_items',
            'staff',
            'stock_changes',
            'stock_requests',
            'stock_request_items',
            'users',
            'warehouses',
            'refunds',
            'refund_items',
            'inventories',
            'payments',
            'branches',
            'businesses',
            'categories',
            'customers'
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropColumn(['sync_status', 'last_sync_at', 'device_id']);
            });
        }
    }
};
