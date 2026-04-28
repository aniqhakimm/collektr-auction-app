<?php

namespace Tests\Feature;

use App\Exceptions\CheckoutException;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\Checkout;
use App\Models\User;
use App\Services\CheckoutService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private CheckoutService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CheckoutService::class);
    }

    // Helper: build a fully ended auction with a known winner.
    private function endedAuctionWithWinner(float $winningAmount = 100.00): array
    {
        $auction = Auction::factory()->ended()->create();
        $winner  = User::factory()->create();
        $bid     = Bid::factory()->create([
            'auction_id' => $auction->id,
            'user_id'    => $winner->id,
            'amount'     => $winningAmount,
        ]);

        $auction->update([
            'winning_bid_id'  => $bid->id,
            'winning_user_id' => $winner->id,
        ]);

        return [$auction->fresh(), $winner, $bid];
    }

    // -------------------------------------------------------------------------
    // Access control
    // -------------------------------------------------------------------------

    #[Test]
    public function winning_user_can_create_checkout(): void
    {
        [$auction, $winner] = $this->endedAuctionWithWinner();

        $checkout = $this->service->createForWinner($auction, $winner);

        $this->assertDatabaseHas('checkouts', [
            'auction_id' => $auction->id,
            'user_id'    => $winner->id,
        ]);
    }

    #[Test]
    public function non_winning_user_cannot_create_checkout(): void
    {
        [$auction] = $this->endedAuctionWithWinner();
        $stranger  = User::factory()->create();

        $this->expectException(CheckoutException::class);

        $this->service->createForWinner($auction, $stranger);
    }

    #[Test]
    public function checkout_cannot_be_created_before_auction_ends(): void
    {
        $user    = User::factory()->create();
        $auction = Auction::factory()->create(['status' => 'active']);

        $this->expectException(CheckoutException::class);

        $this->service->createForWinner($auction, $user);
    }

    // -------------------------------------------------------------------------
    // Buyer premium calculation
    // -------------------------------------------------------------------------

    #[Test]
    public function buyer_premium_is_five_percent_of_winning_bid(): void
    {
        [$auction, $winner] = $this->endedAuctionWithWinner(200.00);

        $checkout = $this->service->createForWinner($auction, $winner);

        // 5% of 200 = 10.00
        $this->assertEquals('10.00', $checkout->buyer_premium);
    }

    #[Test]
    public function buyer_premium_has_minimum_of_rm_two(): void
    {
        // 5% of 20 = 1.00, which is below the RM 2 minimum.
        [$auction, $winner] = $this->endedAuctionWithWinner(20.00);

        $checkout = $this->service->createForWinner($auction, $winner);

        $this->assertEquals('2.00', $checkout->buyer_premium);
    }

    #[Test]
    public function buyer_premium_minimum_applies_at_the_boundary(): void
    {
        // 5% of 40 = exactly 2.00 — minimum should apply (equal, not below).
        [$auction, $winner] = $this->endedAuctionWithWinner(40.00);

        $checkout = $this->service->createForWinner($auction, $winner);

        $this->assertEquals('2.00', $checkout->buyer_premium);
    }

    // -------------------------------------------------------------------------
    // Shipping fee
    // -------------------------------------------------------------------------

    #[Test]
    public function shipping_fee_is_always_rm_ten(): void
    {
        [$auction, $winner] = $this->endedAuctionWithWinner(500.00);

        $checkout = $this->service->createForWinner($auction, $winner);

        $this->assertEquals('10.00', $checkout->shipping_fee);
    }

    // -------------------------------------------------------------------------
    // Grand total accuracy
    // -------------------------------------------------------------------------

    #[Test]
    public function grand_total_is_bid_plus_premium_plus_shipping(): void
    {
        // bid=200, premium=10 (5%), shipping=10 → total=220
        [$auction, $winner] = $this->endedAuctionWithWinner(200.00);

        $checkout = $this->service->createForWinner($auction, $winner);

        $this->assertEquals('200.00', $checkout->winning_bid_amount);
        $this->assertEquals('10.00',  $checkout->buyer_premium);
        $this->assertEquals('10.00',  $checkout->shipping_fee);
        $this->assertEquals('220.00', $checkout->grand_total);
    }

    #[Test]
    public function grand_total_uses_minimum_premium_when_bid_is_low(): void
    {
        // bid=20, premium=2 (minimum), shipping=10 → total=32
        [$auction, $winner] = $this->endedAuctionWithWinner(20.00);

        $checkout = $this->service->createForWinner($auction, $winner);

        $this->assertEquals('32.00', $checkout->grand_total);
    }

    // -------------------------------------------------------------------------
    // Snapshot integrity
    // -------------------------------------------------------------------------

    #[Test]
    public function checkout_stores_snapshot_not_live_data(): void
    {
        [$auction, $winner, $bid] = $this->endedAuctionWithWinner(100.00);

        $checkout = $this->service->createForWinner($auction, $winner);

        // Mutate the bid after checkout creation — snapshot must not change.
        $bid->update(['amount' => 999.00]);
        $checkout->refresh();

        $this->assertEquals('100.00', $checkout->winning_bid_amount);
    }

    // -------------------------------------------------------------------------
    // Duplicate prevention
    // -------------------------------------------------------------------------

    #[Test]
    public function creating_checkout_twice_returns_same_record(): void
    {
        [$auction, $winner] = $this->endedAuctionWithWinner();

        $first  = $this->service->createForWinner($auction, $winner);
        $second = $this->service->createForWinner($auction, $winner);

        $this->assertEquals($first->id, $second->id);
        $this->assertEquals(1, Checkout::where('auction_id', $auction->id)->count());
    }
}
