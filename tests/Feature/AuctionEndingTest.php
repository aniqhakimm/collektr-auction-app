<?php

namespace Tests\Feature;

use App\Console\Commands\EndExpiredAuctions;
use App\Models\Auction;
use App\Models\Bid;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AuctionEndingTest extends TestCase
{
    use RefreshDatabase;

    private function runCommand(): void
    {
        $this->artisan(EndExpiredAuctions::class)->assertExitCode(0);
    }

    // -------------------------------------------------------------------------
    // Basic ending behaviour
    // -------------------------------------------------------------------------

    #[Test]
    public function expired_active_auction_is_marked_as_ended(): void
    {
        $auction = Auction::factory()->expiredActive()->create();

        $this->runCommand();

        $this->assertDatabaseHas('auctions', [
            'id'     => $auction->id,
            'status' => 'ended',
        ]);
    }

    #[Test]
    public function non_expired_active_auction_is_not_touched(): void
    {
        $auction = Auction::factory()->create([
            'status'         => 'active',
            'auction_end_at' => now()->addHour(),
        ]);

        $this->runCommand();

        $this->assertDatabaseHas('auctions', [
            'id'     => $auction->id,
            'status' => 'active',
        ]);
    }

    #[Test]
    public function already_ended_auction_is_not_re_processed(): void
    {
        $user    = User::factory()->create();
        $auction = Auction::factory()->ended()->create([
            'winning_user_id' => $user->id,
        ]);

        $this->runCommand();

        // Still ended, winning_user_id unchanged.
        $this->assertDatabaseHas('auctions', [
            'id'              => $auction->id,
            'status'          => 'ended',
            'winning_user_id' => $user->id,
        ]);
    }

    // -------------------------------------------------------------------------
    // Winner assignment
    // -------------------------------------------------------------------------

    #[Test]
    public function highest_bidder_is_set_as_winner(): void
    {
        $auction = Auction::factory()->expiredActive()->create();
        $userA   = User::factory()->create();
        $userB   = User::factory()->create();

        $low  = Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $userA->id, 'amount' => 50.00]);
        $high = Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $userB->id, 'amount' => 150.00]);

        $this->runCommand();

        $this->assertDatabaseHas('auctions', [
            'id'              => $auction->id,
            'winning_bid_id'  => $high->id,
            'winning_user_id' => $userB->id,
            'status'          => 'ended',
        ]);
    }

    #[Test]
    public function auction_with_no_bids_ends_with_no_winner(): void
    {
        $auction = Auction::factory()->expiredActive()->create();

        $this->runCommand();

        $this->assertDatabaseHas('auctions', [
            'id'              => $auction->id,
            'status'          => 'ended',
            'winning_bid_id'  => null,
            'winning_user_id' => null,
        ]);
    }

    // -------------------------------------------------------------------------
    // Idempotency
    // -------------------------------------------------------------------------

    #[Test]
    public function running_command_twice_does_not_change_outcome(): void
    {
        $auction = Auction::factory()->expiredActive()->create();
        $user    = User::factory()->create();
        $bid     = Bid::factory()->create(['auction_id' => $auction->id, 'user_id' => $user->id, 'amount' => 75.00]);

        $this->runCommand();
        $this->runCommand(); // second run — must be a no-op

        // Outcome identical after two runs.
        $this->assertDatabaseHas('auctions', [
            'id'              => $auction->id,
            'status'          => 'ended',
            'winning_bid_id'  => $bid->id,
            'winning_user_id' => $user->id,
        ]);

        // No duplicate records created.
        $this->assertEquals(1, Bid::where('auction_id', $auction->id)->count());
    }

    #[Test]
    public function only_expired_auctions_are_processed_when_multiple_exist(): void
    {
        $expired = Auction::factory()->expiredActive()->create();
        $live    = Auction::factory()->create(['auction_end_at' => now()->addHour()]);

        $this->runCommand();

        $this->assertDatabaseHas('auctions', ['id' => $expired->id, 'status' => 'ended']);
        $this->assertDatabaseHas('auctions', ['id' => $live->id,    'status' => 'active']);
    }
}
