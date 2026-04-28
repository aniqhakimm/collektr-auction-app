<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::table('auctions', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Category::class);
            $table->dropColumn('category_id');
        });

        Schema::dropIfExists('categories');
    }
};
