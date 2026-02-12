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
        Schema::table('stores', function (Blueprint $table): void {
            $table->enum('subscription_plan', ['monthly', 'yearly', 'lifetime'])
                ->default('monthly')
                ->after('logo_url');
            $table->date('subscription_expires_at')->nullable()->after('subscription_plan');
            $table->boolean('is_active')->default(true)->after('subscription_expires_at');
            $table->string('manager_phone')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->dropColumn([
                'subscription_plan',
                'subscription_expires_at',
                'is_active',
                'manager_phone',
            ]);
        });
    }
};
