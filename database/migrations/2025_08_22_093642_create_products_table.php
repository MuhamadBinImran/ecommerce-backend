<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
{
Schema::create('products', function (Blueprint $table) {
$table->id();
$table->foreignId('seller_id')->constrained('sellers')->onDelete('cascade');
$table->foreignId('category_id')->constrained('product_categories')->onDelete('cascade');
$table->string('name');
$table->text('description')->nullable();
$table->decimal('price', 12, 2);
$table->integer('stock')->default(0);
$table->boolean('is_approved')->default(false);
$table->boolean('is_blocked')->default(false);
$table->timestamps();
});
}

public function down(): void
{
Schema::dropIfExists('products');
}
};
