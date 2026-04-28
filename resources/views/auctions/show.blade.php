<x-layouts.app :title="$auction->title">

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-6">
        <a href="{{ route('auctions.index') }}" class="hover:text-gray-900">Auctions</a>
        <span class="mx-2">/</span>
        <span class="text-gray-900">{{ $auction->title }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">

        {{-- LEFT: Image + gallery + description (3 cols) --}}
        <div class="lg:col-span-3 space-y-6">

            @php
                $galleryImages = $auction->images;
                $hasCover      = (bool) $auction->image_path;
                $hasGallery    = $galleryImages->isNotEmpty();
                $allImages     = collect();
                if ($hasCover)   { $allImages->push(['url' => Storage::url($auction->image_path), 'cover' => true]); }
                foreach ($galleryImages as $img) { $allImages->push(['url' => Storage::url($img->path), 'cover' => false]); }
            @endphp

            @if($allImages->isNotEmpty())
                {{-- Gallery with Alpine.js --}}
                <div
                    x-data="{
                        images: {{ Js::from($allImages->pluck('url')->values()) }},
                        active: 0,
                        setActive(i) { this.active = i; }
                    }"
                >
                    {{-- Main image --}}
                    <div class="bg-gray-100 rounded-xl overflow-hidden aspect-video mb-3">
                        <template x-for="(url, i) in images" :key="i">
                            <img :src="url" :alt="'Image ' + (i + 1)"
                                 x-show="active === i"
                                 class="w-full h-full object-cover">
                        </template>
                    </div>

                    {{-- Thumbnails (only shown when there's more than one image) --}}
                    <template x-if="images.length > 1">
                        <div class="flex gap-2 overflow-x-auto pb-1">
                            <template x-for="(url, i) in images" :key="i">
                                <button
                                    type="button"
                                    @click="setActive(i)"
                                    :class="active === i
                                        ? 'ring-2 ring-gray-900 ring-offset-1'
                                        : 'ring-1 ring-gray-200 opacity-60 hover:opacity-100'"
                                    class="shrink-0 w-16 h-16 rounded-lg overflow-hidden transition-all focus:outline-none"
                                >
                                    <img :src="url" :alt="'Thumb ' + (i + 1)"
                                         class="w-full h-full object-cover">
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            @else
                {{-- No images placeholder --}}
                <div class="bg-gray-100 rounded-xl overflow-hidden aspect-video">
                    <div class="w-full h-full flex items-center justify-center text-gray-300">
                        <svg class="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                  d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                </div>
            @endif

            {{-- Title + description --}}
            <div class="space-y-3">
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-semibold text-gray-900">{{ $auction->title }}</h1>
                    <x-auction-status-badge :status="$auction->status" />
                </div>

                <p class="text-sm text-gray-500">
                    Starting price: <span class="font-medium text-gray-700">RM {{ number_format($auction->starting_price, 2) }}</span>
                </p>

                @if($auction->description)
                    <div class="prose prose-sm max-w-none text-gray-600 leading-relaxed">
                        {!! nl2br(e($auction->description)) !!}
                    </div>
                @endif
            </div>

        </div>

        {{-- RIGHT: Bid panel (2 cols) --}}
        <div class="lg:col-span-2">
            <div class="sticky top-20 space-y-4">

                {{-- ── Countdown (always first) ──────────────────────────── --}}
                @if($auction->isEnded())
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div>
                            <p class="text-xs text-gray-400 font-medium">Ended</p>
                            <p class="text-sm font-medium text-gray-500">
                                {{ $auction->auction_end_at->format('d M Y, H:i') }}
                            </p>
                        </div>
                    </div>
                @else
                    <div
                        x-data="{
                            end: {{ $auction->auction_end_at->timestamp }},
                            days: 0, hours: 0, mins: 0, secs: 0,
                            expired: false,
                            init() {
                                this.tick();
                                setInterval(() => this.tick(), 1000);
                            },
                            tick() {
                                const diff = this.end - Math.floor(Date.now() / 1000);
                                if (diff <= 0) {
                                    this.expired = true;
                                    this.days = this.hours = this.mins = this.secs = 0;
                                    return;
                                }
                                this.days  = Math.floor(diff / 86400);
                                this.hours = Math.floor((diff % 86400) / 3600);
                                this.mins  = Math.floor((diff % 3600) / 60);
                                this.secs  = diff % 60;
                            },
                            pad(n) { return String(n).padStart(2, '0'); }
                        }"
                        class="bg-white border border-gray-200 rounded-xl p-4"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5 text-xs text-gray-500">
                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse inline-block"></span>
                                Ends in
                            </div>

                            <p x-show="expired" class="text-sm font-medium text-gray-500">
                                Just ended — refresh to see result.
                            </p>

                            <div x-show="!expired" class="flex items-center gap-1 font-mono text-sm font-semibold text-gray-900">
                                <template x-if="days > 0">
                                    <span x-text="days + 'd'"></span>
                                </template>
                                <template x-if="days > 0">
                                    <span class="text-gray-300">:</span>
                                </template>
                                <span x-text="pad(hours) + 'h'"></span>
                                <span class="text-gray-300">:</span>
                                <span x-text="pad(mins) + 'm'"></span>
                                <span class="text-gray-300">:</span>
                                <span x-text="pad(secs) + 's'"
                                      :class="secs <= 10 ? 'text-red-600' : 'text-gray-900'"></span>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- ── Bid panel below countdown ──────────────────────────── --}}
                @if($auction->isEnded())
                    <div class="bg-white border border-gray-200 rounded-xl p-5">
                        <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Final Price</p>
                        <p class="text-3xl font-bold text-gray-900">
                            RM {{ number_format($auction->winningBid?->amount ?? $auction->starting_price, 2) }}
                        </p>
                    </div>

                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 space-y-3">
                        <p class="text-sm font-medium text-gray-700">This auction has ended.</p>

                        @if($auction->winningUser)
                            <p class="text-sm text-gray-500">
                                Won by <span class="font-medium text-gray-800">{{ $auction->winningUser->name }}</span>
                                for <span class="font-medium text-gray-800">RM {{ number_format($auction->winningBid->amount, 2) }}</span>.
                            </p>
                        @else
                            <p class="text-sm text-gray-500">No bids were placed.</p>
                        @endif

                        @auth
                            @if(auth()->id() === $auction->winning_user_id)
                                @if($auction->checkout)
                                    <a href="{{ route('checkout.show', $auction) }}"
                                       class="block w-full text-center bg-gray-900 text-white text-sm px-4 py-2.5 rounded-lg hover:bg-gray-700">
                                        View Checkout
                                    </a>
                                @else
                                    <form method="POST" action="{{ route('checkout.store', $auction) }}">
                                        @csrf
                                        @error('checkout')
                                            <p class="text-xs text-red-600 mb-2">{{ $message }}</p>
                                        @enderror
                                        <button type="submit"
                                                class="w-full bg-gray-900 text-white text-sm px-4 py-2.5 rounded-lg hover:bg-gray-700">
                                            Proceed to Checkout
                                        </button>
                                    </form>
                                @endif
                            @endif
                        @endauth
                    </div>

                @elseauth
                    <livewire:place-bid :auction="$auction" />

                @else
                    <div class="bg-white border border-gray-200 rounded-xl p-5">
                        <p class="text-xs text-gray-500 uppercase tracking-wide font-medium mb-1">Current Bid</p>
                        <p class="text-3xl font-bold text-gray-900">
                            RM {{ number_format($auction->highestBid?->amount ?? $auction->starting_price, 2) }}
                        </p>
                    </div>
                    <div class="bg-gray-50 border border-gray-200 rounded-xl p-5 text-center space-y-3">
                        <p class="text-sm text-gray-600">Sign in to place a bid.</p>
                        <a href="{{ route('login') }}"
                           class="inline-block bg-gray-900 text-white text-sm px-5 py-2 rounded-lg hover:bg-gray-700">
                            Log in
                        </a>
                    </div>
                @endauth

            </div>
        </div>

    </div>

</x-layouts.app>
