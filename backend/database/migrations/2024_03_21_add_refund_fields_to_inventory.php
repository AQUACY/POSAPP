<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->boolean('is_non_refundable')->default(false);
            $table->boolean('requires_condition_check')->default(false);
            $table->string('condition_notes')->nullable();
            $table->decimal('refund_restriction_amount', 10, 2)->nullable();
            $table->integer('refund_restriction_days')->nullable();
        });
    }

    public function down()
    {
        Schema::table('inventories', function (Blueprint $table) {
            $table->dropColumn([
                'is_non_refundable',
                'requires_condition_check',
                'condition_notes',
                'refund_restriction_amount',
                'refund_restriction_days'
            ]);
        });
    }
}; 