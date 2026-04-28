<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('starting_price', 10, 2);
            $table->timestamp('auction_end_at');
            $table->enum('status', ['draft', 'active', 'ended'])->default('draft');
            $table->string('image_path')->nullable();

            // winning_bid_id / winning_user_id added after bids table exists
            // see: 2024_01_01_100003_add_winner_columns_to_auctions_table

            $table->timestamps();

            $table->index('status');
            $table->index('auction_end_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
