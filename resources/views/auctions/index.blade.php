<x-layouts.app title="All Auctions">

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-semibold text-gray-900">Auctions</h1>
            <span class="text-sm text-gray-500">{{ $auctions->total() }} item{{ $auctions->total() !== 1 ? 's' : '' }}</span>
        </div>

        {{-- Category filter --}}
        @if($categories->isNotEmpty())
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('auctions.index') }}"
                   class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors
                          {{ ! $activeCategory ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-400' }}">
                    All
                </a>
                @foreach($categories as $category)
                    <a href="{{ route('auctions.index', ['category' => $category->slug]) }}"
                       class="px-3 py-1.5 rounded-full text-sm font-medium transition-colors
                              {{ $activeCategory === $category->slug ? 'bg-gray-900 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:border-gray-400' }}">
                        {{ $category->name }}
                    </a>
                @endforeach
            </div>
        @endif

        {{-- Grid --}}
        @if($auctions->isEmpty())
            <div class="text-center py-20 text-gray-400">
                <p class="text-lg">No auctions found{{ $activeCategory ? ' in this category' : '' }}.</p>
                @if($activeCategory)
                    <a href="{{ route('auctions.index') }}" class="mt-3 inline-block text-sm text-gray-500 underline">
                        View all auctions
                    </a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($auctions as $auction)
                    @include('auctions._card', ['auction' => $auction])
                @endforeach
            </div>

            <div>{{ $auctions->links() }}</div>
        @endif

    </div>

</x-layouts.app>
