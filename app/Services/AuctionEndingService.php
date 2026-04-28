<?php

namespace App\Services;

use App\Models\Auction;
use Illuminate\Support\Facades\DB;

class AuctionEndingService
{
    public function endExpired(): void
    {
        Auction::expired()->each(fn (Auction $a) => $this->end($a));
    }

    public function end(Auction $auction): void
    {
        DB::transaction(function () use ($auction) {
            $auction = Auction::lockForUpdate()
                ->where('status', 'active')
                ->find($auction->id);

            if ($auction === null) {
                return;
            }

            $highestBid = $auction->bids()->highestFirst()->first();

            $auction->update([
                'status'          => 'ended',
                'winning_bid_id'  => $highestBid?->id,
                'winning_user_id' => $highestBid?->user_id,
            ]);
        });
    }
}
