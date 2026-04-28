<?php

namespace App\Services;

use App\Exceptions\BidException;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BidService
{
    public function place(Auction $auction, User $user, float $amount): Bid
    {
        return DB::transaction(function () use ($auction, $user, $amount) {
            // Lock the auction row for the duration of this transaction.
            // This serializes concurrent bids on the same auction — only one
            // transaction can hold this lock at a time.
            $auction = Auction::lockForUpdate()->findOrFail($auction->id);

            $this->validate($auction, $amount);

            return Bid::create([
                'auction_id' => $auction->id,
                'user_id'    => $user->id,
                'amount'     => $amount,
            ]);
        });
    }

    private function validate(Auction $auction, float $amount): void
    {
        if ($auction->status !== 'active') {
            throw BidException::auctionNotActive();
        }

        // Re-check expiry inside the lock — the auction may have expired
        // between the HTTP request arriving and the lock being acquired.
        if ($auction->auction_end_at->isPast()) {
            throw BidException::auctionExpired();
        }

        $floor = $this->minimumBid($auction);

        if ($amount <= $floor) {
            throw BidException::amountTooLow(number_format($floor + 0.01, 2));
        }
    }

    // Returns the amount a new bid must strictly exceed.
    private function minimumBid(Auction $auction): float
    {
        $highest = $auction->bids()->max('amount');

        return $highest !== null
            ? (float) $highest
            : (float) $auction->starting_price - 0.01; // bid >= starting_price
    }
}
