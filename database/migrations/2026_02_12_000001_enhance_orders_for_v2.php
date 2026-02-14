<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('order_number', 50)->nullable()->after('id');
            $table->string('tracking_number')->nullable()->after('payment_status');
            $table->text('seller_notes')->nullable()->after('tracking_number');
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status VARCHAR(30) NOT NULL DEFAULT 'pending'");
        }

        $orders = DB::table('orders')->orderBy('id')->get();
        foreach ($orders as $index => $order) {
            $date = date('Ymd', strtotime($order->created_at));
            $num = str_pad((string) ($index + 1), 4, '0', STR_PAD_LEFT);
            DB::table('orders')->where('id', $order->id)->update(['order_number' => "ORD-{$date}-{$num}"]);
        }

        Schema::create('order_status_history', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->string('status', 30);
            $table->string('notes')->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });

        foreach (DB::table('orders')->get() as $order) {
            DB::table('order_status_history')->insert([
                'order_id' => $order->id,
                'status' => $order->status ?? 'pending',
                'user_id' => null,
                'created_at' => $order->created_at,
                'updated_at' => $order->updated_at,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('order_status_history');

        Schema::table('orders', function (Blueprint $table): void {
            $table->dropColumn(['order_number', 'tracking_number', 'seller_notes']);
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending','shipped','cancelled') NOT NULL DEFAULT 'pending'");
        }
    }
};
