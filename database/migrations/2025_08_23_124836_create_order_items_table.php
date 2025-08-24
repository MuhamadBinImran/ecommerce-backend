<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')->constrained('orders')->onDelete('cascade');

            // product reference (nullable because product may be deleted later)
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();

            // keep seller_id to quickly aggregate seller-level revenue per item
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->nullOnDelete();

            // product snapshot fields (store current name/price)
            $table->string('product_name');
            $table->decimal('price', 12, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->decimal('total', 12, 2);

            // optional JSON (variants, attributes)
            $table->json('meta')->nullable();

            // index for quick lookup
            $table->index(['order_id', 'product_id', 'seller_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
