<x-layouts.app title="Admin — Auctions">

    <div class="space-y-6">

        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900">Manage Auctions</h1>
            <a href="{{ route('admin.auctions.create') }}"
               class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-700">
                + New Auction
            </a>
        </div>

        @if($auctions->isEmpty())
            <p class="text-gray-500 text-sm">No auctions yet.</p>
        @else
            <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Title</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Ends</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Starting Price</th>
                            <th class="px-4 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($auctions as $auction)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $auction->title }}</td>
                                <td class="px-4 py-3">
                                    <x-auction-status-badge :status="$auction->status" />
                                </td>
                                <td class="px-4 py-3 text-gray-500">
                                    {{ $auction->auction_end_at->format('d M Y, H:i') }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    RM {{ number_format($auction->starting_price, 2) }}
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('admin.auctions.edit', $auction) }}"
                                           class="text-gray-500 hover:text-gray-900 text-xs underline">Edit</a>

                                        <form method="POST" action="{{ route('admin.auctions.destroy', $auction) }}"
                                              onsubmit="return confirm('Delete this auction? This cannot be undone.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="text-red-500 hover:text-red-700 text-xs underline">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div>{{ $auctions->links() }}</div>
        @endif

    </div>

</x-layouts.app>
