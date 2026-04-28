<?php

namespace App\Livewire;

use App\Exceptions\BidException;
use App\Models\Auction;
use App\Models\Bid;
use App\Services\AuctionEndingService;
use App\Services\BidService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Poll;
use Livewire\Component;

#[Poll(3000)]   // re-render every 3 seconds
class PlaceBid extends Component
{
    public Auction $auction;
    public string  $amount          = '';
    public ?string $successMessage  = null;
    public ?int    $lastKnownBidId  = null;   // tracks newest bid the client has seen

    public function mount(Auction $auction): void
    {
        $this->auction        = $auction;
        $this->lastKnownBidId = $auction->bids()->max('id');
    }

    public function hydrate(): void
    {
        // Runs on every poll cycle — ends the auction if time has passed
        app(AuctionEndingService::class)->endExpired();
        $this->auction->refresh();
    }

    public function dehydrate(): void
    {
        // Clear the success message after it has been sent to the browser once,
        // so subsequent poll re-renders don't re-show it.
        $this->successMessage = null;
    }

    // ── Computed properties (re-read from DB every poll cycle) ────────────────

    #[Computed]
    public function recentBids(): Collection
    {
        return $this->auction
            ->bids()
            ->with('user')
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function currentHighest(): string
    {
        $max = $this->auction->bids()->max('amount');

        return $max !== null
            ? number_format((float) $max, 2)
            : number_format((float) $this->auction->starting_price, 2);
    }

    #[Computed]
    public function minimumBid(): string
    {
        $highest = $this->auction->bids()->max('amount');

        $floor = $highest !== null
            ? (float) $highest
            : (float) $this->auction->starting_price - 0.01;

        return number_format($floor + 0.01, 2);
    }

    #[Computed]
    public function totalBids(): int
    {
        return $this->auction->bids()->count();
    }

    #[Computed]
    public function newestBidId(): ?int
    {
        return $this->auction->bids()->max('id');
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function placeBid(): void
    {
        $this->successMessage = null;

        if (Auth::user()->is_admin) {
            $this->addError('amount', 'Administrators cannot place bids.');
            return;
        }

        $this->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        try {
            app(BidService::class)->place(
                $this->auction,
                Auth::user(),
                (float) $this->amount,
            );
        } catch (BidException $e) {
            $this->addError('amount', $e->getMessage());
            return;
        }

        $this->auction->refresh();
        $this->lastKnownBidId = $this->auction->bids()->max('id');
        $this->amount         = '';
        $this->successMessage = 'Your bid was placed!';
    }
}
