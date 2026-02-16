<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->index('store_id');
            $table->index('order_number');
            $table->index('customer_phone');
            $table->index('customer_name');
            $table->index('status');
            $table->index('created_at');
            $table->index(['customer_first_name', 'customer_last_name']);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['store_id']);
            $table->dropIndex(['order_number']);
            $table->dropIndex(['customer_phone']);
            $table->dropIndex(['customer_name']);
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['customer_first_name', 'customer_last_name']);
        });
    }
};
