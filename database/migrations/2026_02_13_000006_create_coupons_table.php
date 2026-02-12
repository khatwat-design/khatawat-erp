<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('discount_type', 20)->default('percentage'); // percentage, fixed
            $table->decimal('discount_value', 12, 2)->default(0);
            $table->decimal('min_order_amount', 12, 2)->nullable();
            $table->integer('max_uses')->nullable();
            $table->integer('used_count')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('coupons', function (Blueprint $table): void {
            $table->unique(['store_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
