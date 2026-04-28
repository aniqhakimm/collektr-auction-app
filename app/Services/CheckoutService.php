<?php

namespace App\Services;

use App\Exceptions\CheckoutException;
use App\Models\Auction;
use App\Models\Checkout;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CheckoutService
{
    private const SHIPPING_FEE       = 10.00;
    private const BUYER_PREMIUM_RATE = 0.05;
    private const BUYER_PREMIUM_MIN  = 2.00;

    public function createForWinner(Auction $auction, User $user): Checkout
    {
        return DB::transaction(function () use ($auction, $user) {
            // Re-fetch with a lock to prevent duplicate checkouts under
            // concurrent requests from the same winner.
            $auction = Auction::lockForUpdate()->findOrFail($auction->id);

            $this->authorize($auction, $user);

            // Use firstOrCreate so the method is idempotent: a second call
            // returns the existing checkout instead of throwing a DB error.
            return Checkout::firstOrCreate(
                ['auction_id' => $auction->id],
                $this->buildSnapshot($auction)
            );
        });
    }

    private function authorize(Auction $auction, User $user): void
    {
        if (! $auction->isEnded()) {
            throw CheckoutException::auctionNotEnded();
        }

        if ($auction->winning_user_id !== $user->id) {
            throw CheckoutException::notWinner();
        }
    }

    private function buildSnapshot(Auction $auction): array
    {
        $bidAmount    = (float) $auction->winningBid->amount;
        $buyerPremium = $this->calculateBuyerPremium($bidAmount);
        $shippingFee  = self::SHIPPING_FEE;

        return [
            'user_id'             => $auction->winning_user_id,
            'bid_id'              => $auction->winning_bid_id,
            'winning_bid_amount'  => $bidAmount,
            'buyer_premium'       => $buyerPremium,
            'shipping_fee'        => $shippingFee,
            'grand_total'         => $bidAmount + $buyerPremium + $shippingFee,
        ];
    }

    private function calculateBuyerPremium(float $bidAmount): float
    {
        return max(
            round($bidAmount * self::BUYER_PREMIUM_RATE, 2),
            self::BUYER_PREMIUM_MIN
        );
    }
}
