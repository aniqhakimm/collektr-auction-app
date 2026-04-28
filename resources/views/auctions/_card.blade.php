@php $ended = $auction->isEnded(); @endphp

<a href="{{ route('auctions.show', $auction) }}"
   class="group block bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-md transition-shadow
          {{ $ended ? 'opacity-60 grayscale' : '' }}">

    {{-- Image --}}
    <div class="aspect-video bg-gray-100 overflow-hidden">
        @if($auction->image_path)
            <img src="{{ Storage::url($auction->image_path) }}"
                 alt="{{ $auction->title }}"
                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @else
            <div class="w-full h-full flex items-center justify-center text-gray-300">
                <svg class="w-12 h-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                          d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
        @endif
    </div>

    {{-- Content --}}
    <div class="p-4 space-y-3">

        {{-- Title + status --}}
        <div class="flex items-start justify-between gap-2">
            <h2 class="text-sm font-medium text-gray-900 leading-snug line-clamp-2">
                {{ $auction->title }}
            </h2>
            <x-auction-status-badge :status="$auction->status" />
        </div>

        {{-- Category --}}
        @if($auction->category)
            <span class="inline-block text-xs text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">
                {{ $auction->category->name }}
            </span>
        @endif

        {{-- Price --}}
        <div>
            @if($auction->highestBid)
                <p class="text-xs text-gray-500">Current bid</p>
                <p class="text-lg font-semibold text-gray-900">
                    RM {{ number_format($auction->highestBid->amount, 2) }}
                </p>
            @else
                <p class="text-xs text-gray-500">Starting price</p>
                <p class="text-lg font-semibold text-gray-900">
                    RM {{ number_format($auction->starting_price, 2) }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">No bids yet</p>
            @endif
        </div>

        {{-- End time --}}
        <div class="text-xs text-gray-500 flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            @if($ended)
                Ended {{ $auction->auction_end_at->diffForHumans() }}
            @else
                Ends {{ $auction->auction_end_at->diffForHumans() }}
            @endif
        </div>

    </div>
</a>
