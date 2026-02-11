<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->string('subdomain')->nullable()->after('slug');
            $table->string('custom_domain')->nullable()->after('subdomain');
            $table->decimal('wallet_balance', 12, 2)->default(0)->after('custom_domain');
            $table->string('theme_id')->default('default')->after('wallet_balance');
            $table->json('theme_config')->nullable()->after('theme_id');
            $table->json('modules_status')->nullable()->after('theme_config');
            $table->json('settings')->nullable()->after('modules_status');
        });

        $stores = DB::table('stores')->select('id', 'name', 'subdomain')->get();
        foreach ($stores as $store) {
            if (! $store->subdomain) {
                $base = Str::slug($store->name ?: 'store');
                $subdomain = $base !== '' ? $base . '-' . $store->id : 'store-' . $store->id;
                DB::table('stores')->where('id', $store->id)->update([
                    'subdomain' => $subdomain,
                ]);
            }
        }

        Schema::table('stores', function (Blueprint $table): void {
            $table->unique('subdomain');
            $table->index('subdomain');
            $table->unique('custom_domain');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stores', function (Blueprint $table): void {
            $table->dropUnique(['subdomain']);
            $table->dropIndex(['subdomain']);
            $table->dropUnique(['custom_domain']);
            $table->dropColumn([
                'subdomain',
                'custom_domain',
                'wallet_balance',
                'theme_id',
                'theme_config',
                'modules_status',
                'settings',
            ]);
        });
    }
};
