<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // buyer
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // optional primary seller for the order (useful for seller-scoped views/reports)
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->nullOnDelete();

            // unique order identifier shown to users
            $table->string('order_number')->unique();

            // amounts
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('shipping', 12, 2)->default(0);
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // status: processing, shipped, delivered, cancelled, refunded, disputed
            $table->enum('status', ['processing','shipped','delivered','cancelled','refunded','disputed'])
                ->default('processing');

            // shipping address and arbitrary metadata (payment / gateway / tracking)
            $table->json('shipping_address')->nullable();
            $table->json('meta')->nullable();

            // quick lookup fields
            $table->index(['user_id', 'seller_id']);
            $table->index('status');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
