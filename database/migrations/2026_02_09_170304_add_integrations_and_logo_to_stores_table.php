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
            $table->string('telegram_bot_token')->nullable()->after('integrations_config');
            $table->string('telegram_channel_id')->nullable()->after('telegram_bot_token');
            $table->string('google_sheets_token')->nullable()->after('telegram_channel_id');
            $table->string('logo_url')->nullable()->after('google_sheets_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->dropColumn([
                'telegram_bot_token',
                'telegram_channel_id',
                'google_sheets_token',
                'logo_url',
            ]);
        });
    }
};
