<?php

namespace Tests\Feature;

use App\Exceptions\BidException;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use App\Services\BidService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BidPlacementTest extends TestCase
{
    use RefreshDatabase;

    private BidService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BidService::class);
    }

    // -------------------------------------------------------------------------
    // First bid rules
    // -------------------------------------------------------------------------

    #[Test]
    public function first_bid_must_meet_starting_price(): void
    {
        $auction = Auction::factory()->create(['starting_price' => 50.00]);
        $user    = User::factory()->create();

        $bid = $this->service->place($auction, $user, 50.00);

        $this->assertDatabaseHas('bids', ['id' => $bid->id, 'amount' => 50.00]);
    }

    #[Test]
    public function first_bid_below_starting_price_is_rejected(): void
    {
        $auction = Auction::factory()->create(['starting_price' => 50.00]);
        $user    = User::factory()->create();

        $this->expectException(BidException::class);

        $this->service->place($auction, $user, 49.99);
    }

    // -------------------------------------------------------------------------
    // Outbidding rules
    // -------------------------------------------------------------------------

    #[Test]
    public function bid_must_be_strictly_higher_than_current_highest(): void
    {
        $auction = Auction::factory()->create(['starting_price' => 10.00]);
        $userA   = User::factory()->create();
        $userB   = User::factory()->create();

        $this->service->place($auction, $userA, 100.00);

        $this->expectException(BidException::class);

        // Equal to the highest — not strictly greater, must be rejected.
        $this->service->place($auction, $userB, 100.00);
    }

    #[Test]
    public function bid_higher_than_current_highest_is_accepted(): void
    {
        $auction = Auction::factory()->create(['starting_price' => 10.00]);
        $userA   = User::factory()->create();
        $userB   = User::factory()->create();

        $this->service->place($auction, $userA, 100.00);
        $bid = $this->service->place($auction, $userB, 100.01);

        $this->assertDatabaseHas('bids', ['id' => $bid->id, 'amount' => 100.01]);
        $this->assertEquals(2, $auction->bids()->count());
    }

    // -------------------------------------------------------------------------
    // Auction status rules
    // -------------------------------------------------------------------------

    #[Test]
    public function cannot_bid_on_a_draft_auction(): void
    {
        $auction = Auction::factory()->draft()->create();
        $user    = User::factory()->create();

        $this->expectException(BidException::class);

        $this->service->place($auction, $user, 50.00);
    }

    #[Test]
    public function cannot_bid_on_an_ended_auction(): void
    {
        $auction = Auction::factory()->ended()->create();
        $user    = User::factory()->create();

        $this->expectException(BidException::class);

        $this->service->place($auction, $user, 50.00);
    }

    // -------------------------------------------------------------------------
    // Expiry rules
    // -------------------------------------------------------------------------

    #[Test]
    public function cannot_bid_after_auction_end_time_has_passed(): void
    {
        // Status is still 'active' but the clock has passed auction_end_at.
        // This simulates the window between expiry and the ending job running.
        $auction = Auction::factory()->expiredActive()->create();
        $user    = User::factory()->create();

        $this->expectException(BidException::class);

        $this->service->place($auction, $user, 50.00);
    }

    #[Test]
    public function bid_placed_exactly_at_end_time_is_rejected(): void
    {
        $now     = now();
        $auction = Auction::factory()->create(['auction_end_at' => $now]);
        $user    = User::factory()->create();

        // Travel time forward 1 second so isPast() is true.
        $this->travel(1)->seconds();

        $this->expectException(BidException::class);

        $this->service->place($auction, $user, 50.00);
    }

    // -------------------------------------------------------------------------
    // Persistence
    // -------------------------------------------------------------------------

    #[Test]
    public function placing_a_bid_persists_correct_data(): void
    {
        $auction = Auction::factory()->create(['starting_price' => 20.00]);
        $user    = User::factory()->create();

        $bid = $this->service->place($auction, $user, 35.50);

        $this->assertDatabaseHas('bids', [
            'auction_id' => $auction->id,
            'user_id'    => $user->id,
            'amount'     => 35.50,
        ]);
        $this->assertEquals($bid->auction_id, $auction->id);
        $this->assertEquals($bid->user_id, $user->id);
    }
}
