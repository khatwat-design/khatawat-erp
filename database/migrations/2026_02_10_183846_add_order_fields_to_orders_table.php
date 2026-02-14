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
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('customer_first_name')->nullable()->after('store_id');
            $table->string('customer_last_name')->nullable()->after('customer_first_name');
            $table->string('address')->nullable()->after('customer_phone');
            $table->string('payment_status')->default('pending')->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn([
                'customer_first_name',
                'customer_last_name',
                'address',
                'payment_status',
            ]);
        });
    }
};
