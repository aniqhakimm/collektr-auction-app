<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->foreignId('winning_bid_id')->nullable()->constrained('bids')->nullOnDelete();
            $table->foreignId('winning_user_id')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropForeign(['winning_bid_id']);
            $table->dropForeign(['winning_user_id']);
            $table->dropColumn(['winning_bid_id', 'winning_user_id']);
        });
    }
};
