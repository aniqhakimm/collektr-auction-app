<div wire:poll.3000ms>

    {{-- ── Single combined card ─────────────────────────────────────────────── --}}
    <div class="bg-white border border-gray-200 rounded-xl">

        {{-- Current bid header --}}
        <div class="px-6 pt-6 pb-5 border-b border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wide font-medium mb-2">
                {{ $this->totalBids > 0 ? 'Current Bid' : 'Starting Price' }}
            </p>
            <div class="flex items-end justify-between gap-2">
                <p class="text-3xl font-bold text-gray-900 tabular-nums">
                    RM {{ $this->currentHighest }}
                </p>
                <span class="text-xs text-gray-400">
                    {{ $this->totalBids }} {{ Str::plural('bid', $this->totalBids) }}
                </span>
            </div>
        </div>

        {{-- Bid form --}}
        @if($auction->status === 'active' && $auction->auction_end_at->isFuture() && !auth()->user()->is_admin)
            <div class="px-6 py-5 border-b border-gray-100 space-y-4">

                @if($successMessage)
                    <div class="bg-green-50 border border-green-200 text-green-800 text-xs rounded-lg px-3 py-2 flex items-center gap-2"
                         x-data x-init="setTimeout(() => $el.remove(), 4000)">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        {{ $successMessage }}
                    </div>
                @endif

                <p class="text-xs text-gray-500">
                    Enter more than <span class="font-semibold text-gray-800">RM {{ $this->minimumBid }}</span> to outbid
                </p>

                <form wire:submit="placeBid" class="flex gap-2">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-3 flex items-center text-sm text-gray-400 pointer-events-none select-none">RM</span>
                        <input
                            type="number"
                            step="0.01"
                            min="{{ $this->minimumBid }}"
                            wire:model="amount"
                            placeholder="{{ $this->minimumBid }}"
                            autocomplete="off"
                            class="w-full pl-10 pr-3 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:ring-gray-900
                                   {{ $errors->has('amount') ? 'border-red-400 focus:ring-red-400' : 'border-gray-300' }}"
                        >
                    </div>
                    <button
                        type="submit"
                        wire:loading.attr="disabled"
                        class="shrink-0 bg-gray-900 text-white text-sm font-semibold px-5 py-2.5 rounded-lg
                               hover:bg-gray-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
                        <span wire:loading.remove wire:target="placeBid">Place Bid</span>
                        <span wire:loading wire:target="placeBid" class="flex items-center gap-1.5">
                            <svg class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
                            </svg>
                            Placing…
                        </span>
                    </button>
                </form>

                @error('amount')
                    <p class="text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>
        @elseif(auth()->user()->is_admin)
            <div class="px-6 py-4 border-b border-gray-100">
                <p class="text-xs text-gray-400 text-center">Administrators cannot place bids.</p>
            </div>
        @else
            <div class="px-6 py-4 border-b border-gray-100">
                <p class="text-xs text-gray-400 text-center">Auction has ended</p>
            </div>
        @endif

        {{-- Live bid history --}}
        @if($this->recentBids->isNotEmpty())
            <div class="px-5 py-2.5 flex items-center justify-between border-b border-gray-100">
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-widest">Live Bids</span>
                <span class="flex items-center gap-1.5 text-xs text-green-600">
                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse inline-block"></span>
                    Live
                </span>
            </div>

            <ul class="divide-y divide-gray-100 max-h-56 overflow-y-auto">
                @foreach($this->recentBids as $bid)
                    <li wire:key="bid-{{ $bid->id }}"
                        class="flex items-center justify-between px-5 py-3 text-sm
                               {{ $bid->id === $this->newestBidId ? 'bid-flash bg-green-50' : 'bg-white' }}">

                        <div class="flex items-center gap-2.5 min-w-0">
                            <span class="w-7 h-7 rounded-full bg-gray-100 text-gray-600 text-xs font-semibold
                                         flex items-center justify-center shrink-0 select-none">
                                {{ strtoupper(substr($bid->user->name, 0, 1)) }}
                            </span>
                            <span class="text-gray-700 truncate">{{ $bid->user->name }}</span>
                            @if($bid->id === $this->newestBidId)
                                <span class="shrink-0 text-xs bg-green-100 text-green-700 px-1.5 py-0.5 rounded-full font-medium">
                                    Highest
                                </span>
                            @endif
                        </div>

                        <div class="text-right shrink-0 ml-4">
                            <span class="font-semibold text-sm {{ $bid->id === $this->newestBidId ? 'text-green-700' : 'text-gray-900' }}">
                                RM {{ number_format($bid->amount, 2) }}
                            </span>
                            <span class="block text-xs text-gray-400 mt-0.5">{{ $bid->created_at->diffForHumans() }}</span>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="px-6 py-5 text-xs text-gray-400 text-center">No bids yet. Be the first!</div>
        @endif

    </div>

</div>

@push('styles')
<style>
    @keyframes bid-highlight {
        0%   { background-color: #f0fdf4; }
        60%  { background-color: #dcfce7; }
        100% { background-color: #f0fdf4; }
    }
    .bid-flash { animation: bid-highlight 1.8s ease-in-out; }
</style>
@endpush
