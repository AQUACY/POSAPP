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
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo')->nullable();
            $table->string('address');
            $table->string('phone');
            $table->string('email');
            $table->string('tax_id')->nullable();
            $table->json('receipt_settings')->nullable(); // For customizing receipts
            $table->json('report_settings')->nullable(); // For customizing reports
            $table->json('settings')->nullable(); // General business settings
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
