<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('type');
            $table->text('address');
            $table->string('email');
            $table->string('tax_id')->nullable();
            $table->json('receipt_settings')->nullable(); // For customizing receipts
            $table->json('report_settings')->nullable(); // For customizing reports
            $table->json('settings')->nullable(); // General business settings
            $table->string('whatsapp_contact');
            $table->string('logo_path')->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('subscription_end_date');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('businesses');
    }
}; 