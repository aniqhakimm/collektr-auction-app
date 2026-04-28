<x-layouts.app title="Checkout">

    <div class="max-w-lg mx-auto space-y-6">

        {{-- Header --}}
        <div>
            <nav class="text-sm text-gray-500 mb-2">
                <a href="{{ route('auctions.index') }}" class="hover:text-gray-900">Auctions</a>
                <span class="mx-2">/</span>
                <a href="{{ route('auctions.show', $auction) }}" class="hover:text-gray-900">{{ $auction->title }}</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">Checkout</span>
            </nav>
            <h1 class="text-2xl font-semibold text-gray-900">Order Summary</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $auction->title }}</p>
        </div>

        {{-- Breakdown card --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-gray-100">
                    <tr>
                        <td class="px-5 py-3.5 text-gray-600">Winning bid</td>
                        <td class="px-5 py-3.5 text-right font-medium text-gray-900">
                            RM {{ number_format($checkout->winning_bid_amount, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3.5 text-gray-600">
                            Buyer's premium
                            <span class="text-xs text-gray-400">(min. RM 2.00)</span>
                        </td>
                        <td class="px-5 py-3.5 text-right font-medium text-gray-900">
                            RM {{ number_format($checkout->buyer_premium, 2) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="px-5 py-3.5 text-gray-600">Shipping fee</td>
                        <td class="px-5 py-3.5 text-right font-medium text-gray-900">
                            RM {{ number_format($checkout->shipping_fee, 2) }}
                        </td>
                    </tr>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50">
                        <td class="px-5 py-4 font-semibold text-gray-900 text-base">Grand Total</td>
                        <td class="px-5 py-4 text-right font-bold text-xl text-gray-900">
                            RM {{ number_format($checkout->grand_total, 2) }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Info notice --}}
        <div class="text-xs text-gray-400 text-center space-y-1">
            <p>This is a confirmed order summary. All amounts are final.</p>
            <p>Recorded on {{ $checkout->created_at->format('d M Y, H:i') }}</p>
        </div>

        <a href="{{ route('auctions.index') }}"
           class="block text-center text-sm text-gray-600 hover:text-gray-900 underline underline-offset-2">
            &larr; Back to auctions
        </a>

    </div>

</x-layouts.app>
