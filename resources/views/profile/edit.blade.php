<x-layouts.app title="Profile">

    <div class="max-w-3xl mx-auto space-y-6">

        {{-- Page header --}}
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-gray-900 text-white flex items-center justify-center text-lg font-semibold shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ auth()->user()->name }}</h1>
                <p class="text-sm text-gray-500">{{ auth()->user()->email }}</p>
            </div>
        </div>

        {{-- Tabs --}}
        <div x-data="{ tab: window.location.hash === '#bids' ? 'bids' : 'profile' }">

            <div class="flex gap-1 border-b border-gray-200 mb-6">
                <button @click="tab = 'profile'; history.replaceState(null,'','#profile')"
                        :class="tab === 'profile' ? 'border-b-2 border-gray-900 text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 text-sm transition-colors -mb-px">
                    Profile
                </button>
                <button @click="tab = 'bids'; history.replaceState(null,'','#bids')"
                        :class="tab === 'bids' ? 'border-b-2 border-gray-900 text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-700'"
                        class="px-4 py-2.5 text-sm transition-colors -mb-px">
                    Bid History
                    @if($bids->total() > 0)
                        <span class="ml-1.5 bg-gray-100 text-gray-600 text-xs px-1.5 py-0.5 rounded-full">{{ $bids->total() }}</span>
                    @endif
                </button>
            </div>

            {{-- ── Profile tab ──────────────────────────────────────────────── --}}
            <div x-show="tab === 'profile'" x-cloak>
                <div class="space-y-5">

                    @if(session('status') === 'profile-updated')
                        <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3">
                            Profile updated successfully.
                        </div>
                    @endif

                    {{-- Update name / email --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
                        <h2 class="text-sm font-semibold text-gray-700">Account Information</h2>

                        <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                            @csrf
                            @method('PATCH')

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                                <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900
                                              {{ $errors->has('name') ? 'border-red-400' : '' }}">
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                                <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900
                                              {{ $errors->has('email') ? 'border-red-400' : '' }}">
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit"
                                    class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                                Save Changes
                            </button>
                        </form>
                    </div>

                    {{-- Change password --}}
                    <div class="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
                        <h2 class="text-sm font-semibold text-gray-700">Change Password</h2>

                        @if(session('status') === 'password-updated')
                            <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3">
                                Password updated.
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                            @csrf
                            @method('PUT')

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                                <input type="password" name="current_password"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900
                                              {{ $errors->updatePassword->has('current_password') ? 'border-red-400' : '' }}">
                                @if($errors->updatePassword->has('current_password'))
                                    <p class="mt-1 text-xs text-red-600">{{ $errors->updatePassword->first('current_password') }}</p>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                                <input type="password" name="password"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900
                                              {{ $errors->updatePassword->has('password') ? 'border-red-400' : '' }}">
                                @if($errors->updatePassword->has('password'))
                                    <p class="mt-1 text-xs text-red-600">{{ $errors->updatePassword->first('password') }}</p>
                                @endif
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                                <input type="password" name="password_confirmation"
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-gray-900">
                            </div>

                            <button type="submit"
                                    class="bg-gray-900 text-white text-sm px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                                Update Password
                            </button>
                        </form>
                    </div>

                </div>
            </div>

            {{-- ── Bid History tab ──────────────────────────────────────────── --}}
            <div x-show="tab === 'bids'" x-cloak>

                @if($bids->isEmpty())
                    <div class="text-center py-16 text-gray-400">
                        <p class="text-sm">You haven't placed any bids yet.</p>
                        <a href="{{ route('auctions.index') }}"
                           class="mt-3 inline-block text-sm text-gray-600 underline">Browse auctions</a>
                    </div>
                @else
                    <div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 text-xs text-gray-400 uppercase tracking-wide">
                                    <th class="px-5 py-3 text-left font-medium">Auction</th>
                                    <th class="px-5 py-3 text-left font-medium">Your Bid</th>
                                    <th class="px-5 py-3 text-left font-medium">Status</th>
                                    <th class="px-5 py-3 text-left font-medium">Date</th>
                                    <th class="px-5 py-3 text-left font-medium"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @foreach($bids as $bid)
                                    @php
                                        $auction    = $bid->auction;
                                        $isWinner   = $auction->winning_user_id === auth()->id()
                                                      && $auction->winning_bid_id === $bid->id;
                                        $isHighest  = $auction->highestBid?->id === $bid->id;
                                        $isEnded    = $auction->isEnded();
                                    @endphp
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="px-5 py-3">
                                            <div class="font-medium text-gray-900 line-clamp-1">{{ $auction->title }}</div>
                                            @if($auction->category)
                                                <span class="text-xs text-gray-400">{{ $auction->category->name }}</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 font-semibold text-gray-900 tabular-nums">
                                            RM {{ number_format($bid->amount, 2) }}
                                        </td>
                                        <td class="px-5 py-3">
                                            @if($isWinner)
                                                <span class="inline-flex items-center gap-1 text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded-full font-medium">
                                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                    Won
                                                </span>
                                            @elseif($isEnded)
                                                <span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Ended</span>
                                            @elseif($isHighest)
                                                <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">Highest</span>
                                            @else
                                                <span class="text-xs bg-orange-50 text-orange-600 px-2 py-0.5 rounded-full">Outbid</span>
                                            @endif
                                        </td>
                                        <td class="px-5 py-3 text-gray-400 text-xs">
                                            {{ $bid->created_at->format('d M Y, H:i') }}
                                        </td>
                                        <td class="px-5 py-3">
                                            <a href="{{ route('auctions.show', $auction) }}"
                                               class="text-xs text-gray-500 hover:text-gray-900 underline">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($bids->hasPages())
                        <div class="mt-4">{{ $bids->links() }}</div>
                    @endif
                @endif

            </div>

        </div>
    </div>

</x-layouts.app>
