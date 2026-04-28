<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Category;
use App\Services\AuctionEndingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuctionController extends Controller
{
    public function index(Request $request): View
    {
        app(AuctionEndingService::class)->endExpired();

        $categories = Category::orderBy('name')->get();
        $activeCategory = $request->query('category');

        $auctions = Auction::with(['highestBid', 'category'])
            ->when($activeCategory, fn ($q) => $q->whereHas('category', fn ($q) => $q->where('slug', $activeCategory)))
            ->orderByDesc('auction_end_at')
            ->paginate(12)
            ->withQueryString();

        return view('auctions.index', compact('auctions', 'categories', 'activeCategory'));
    }

    public function show(Auction $auction): View
    {
        app(AuctionEndingService::class)->endExpired();

        $auction->refresh()->load(['highestBid', 'bids.user', 'winningUser', 'images', 'category']);

        return view('auctions.show', compact('auction'));
    }
}
