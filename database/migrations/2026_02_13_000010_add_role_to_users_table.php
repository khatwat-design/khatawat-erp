<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Role column already exists from 2026_02_10_162816.
        // This migration ensures staff roles (manager, sales, warehouse) are supported.
        if (Schema::hasColumn('users', 'role')) {
            return;
        }
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role', 30)->default('owner')->after('store_id');
        });
    }

    public function down(): void
    {
        // No-op: role is from previous migration
    }
};
