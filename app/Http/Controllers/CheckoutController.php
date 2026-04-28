<?php

namespace App\Http\Controllers;

use App\Exceptions\CheckoutException;
use App\Models\Auction;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(private CheckoutService $checkoutService) {}

    public function show(Request $request, Auction $auction): View|RedirectResponse
    {
        $checkout = $auction->checkout;

        if (! $checkout || $checkout->user_id !== $request->user()->id) {
            abort(403);
        }

        return view('checkout.show', compact('auction', 'checkout'));
    }

    public function store(Request $request, Auction $auction): RedirectResponse
    {
        try {
            $this->checkoutService->createForWinner($auction, $request->user());
        } catch (CheckoutException $e) {
            return back()->withErrors(['checkout' => $e->getMessage()]);
        }

        return redirect()->route('checkout.show', $auction)
            ->with('success', 'Checkout created successfully.');
    }
}
