<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->enum('role', ['super_admin', 'admin', 'cashier', 'branch_manager', 'inventory_clerk'])->default('admin')->after('phone');
            $table->enum('status', ['pending', 'verified', 'active', 'inactive'])->default('pending')->after('role');
            $table->string('otp')->nullable()->after('status');
            $table->timestamp('otp_expires_at')->nullable()->after('otp');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role', 'status', 'otp', 'otp_expires_at']);
        });
    }
};