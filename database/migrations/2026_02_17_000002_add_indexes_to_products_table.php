<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->index('store_id');
            $table->index('name');
            $table->index('status');
            $table->index('price');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table): void {
            $table->dropIndex(['store_id']);
            $table->dropIndex(['name']);
            $table->dropIndex(['status']);
            $table->dropIndex(['price']);
            $table->dropIndex(['created_at']);
        });
    }
};
