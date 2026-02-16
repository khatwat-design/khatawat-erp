<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function getIndexNames(): array
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql') {
            return collect(DB::select('SHOW INDEX FROM stores'))
                ->pluck('Key_name')
                ->unique()
                ->values()
                ->all();
        }

        if ($driver === 'sqlite') {
            return collect(DB::select("SELECT name FROM sqlite_master WHERE type='index' AND tbl_name='stores'"))
                ->pluck('name')
                ->filter()
                ->values()
                ->all();
        }

        return [];
    }

    public function up(): void
    {
        $indexes = $this->getIndexNames();
        $hasIndex = fn (string $name): bool => in_array($name, $indexes, true);

        Schema::table('stores', function (Blueprint $table) use ($hasIndex): void {
            if (! $hasIndex('stores_subdomain_unique')) {
                $table->unique('subdomain');
            }
            if (! $hasIndex('stores_custom_domain_unique')) {
                $table->unique('custom_domain');
            }
            if (! $hasIndex('stores_api_key_index') && ! $hasIndex('stores_api_key_unique')) {
                $table->index('api_key');
            }
            if (! $hasIndex('stores_status_index')) {
                $table->index('status');
            }
        });
    }

    public function down(): void
    {
        $indexes = $this->getIndexNames();

        Schema::table('stores', function (Blueprint $table) use ($indexes): void {
            if (in_array('stores_subdomain_unique', $indexes, true)) {
                $table->dropUnique(['subdomain']);
            }
            if (in_array('stores_custom_domain_unique', $indexes, true)) {
                $table->dropUnique(['custom_domain']);
            }
            if (in_array('stores_api_key_index', $indexes, true)) {
                $table->dropIndex(['api_key']);
            }
            if (in_array('stores_status_index', $indexes, true)) {
                $table->dropIndex(['status']);
            }
        });
    }
};
