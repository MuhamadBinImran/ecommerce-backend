<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->index('name');
            $table->index('price');
            $table->index('is_approved');
            $table->index('is_blocked');
            $table->index(['category_id', 'is_approved', 'is_blocked']);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['price']);
            $table->dropIndex(['is_approved']);
            $table->dropIndex(['is_blocked']);
            $table->dropIndex(['category_id', 'is_approved', 'is_blocked']);
        });
    }
};
