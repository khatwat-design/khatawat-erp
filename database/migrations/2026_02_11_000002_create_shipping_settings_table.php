<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_settings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tenant_id')->constrained('stores')->cascadeOnDelete();
            $table->string('governorate');
            $table->decimal('cost', 12, 2);
            $table->timestamps();
            $table->unique(['tenant_id', 'governorate']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_settings');
    }
};
