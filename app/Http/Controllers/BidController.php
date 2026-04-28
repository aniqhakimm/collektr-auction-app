<?php

namespace App\Http\Controllers;

use App\Exceptions\BidException;
use App\Models\Auction;
use App\Services\BidService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BidController extends Controller
{
    public function __construct(private BidService $bidService) {}

    public function store(Request $request, Auction $auction): JsonResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'decimal:0,2'],
        ]);

        try {
            $bid = $this->bidService->place($auction, $request->user(), (float) $validated['amount']);
        } catch (BidException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'Bid placed successfully.',
            'bid'     => $bid,
        ], 201);
    }
}
