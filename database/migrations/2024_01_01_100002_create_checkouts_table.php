<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('checkouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->unique()->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('bid_id')->constrained('bids')->restrictOnDelete();

            // Snapshot values — never recalculate from live data
            $table->decimal('winning_bid_amount', 10, 2);
            $table->decimal('buyer_premium', 10, 2);
            $table->decimal('shipping_fee', 10, 2);
            $table->decimal('grand_total', 10, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkouts');
    }
};
