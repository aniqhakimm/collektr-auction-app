<x-layouts.app title="Admin — New Auction">

    <div class="max-w-xl mx-auto space-y-6">

        <div>
            <nav class="text-sm text-gray-500 mb-1">
                <a href="{{ route('admin.auctions.index') }}" class="hover:text-gray-900">Admin</a>
                <span class="mx-2">/</span>
                <span class="text-gray-900">New Auction</span>
            </nav>
            <h1 class="text-xl font-semibold text-gray-900">New Auction</h1>
        </div>

        <form method="POST" action="{{ route('admin.auctions.store') }}" enctype="multipart/form-data"
              class="bg-white border border-gray-200 rounded-xl p-6 space-y-5">
            @csrf

            @include('admin.auctions._form')

            <div class="flex gap-3 pt-2">
                <button type="submit"
                        class="bg-gray-900 text-white text-sm px-5 py-2.5 rounded-lg hover:bg-gray-700">
                    Create Auction
                </button>
                <a href="{{ route('admin.auctions.index') }}"
                   class="text-sm px-5 py-2.5 rounded-lg border border-gray-300 text-gray-600 hover:bg-gray-50">
                    Cancel
                </a>
            </div>
        </form>

    </div>

</x-layouts.app>
