<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Collektr') }} — {{ $title ?? 'Auctions' }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-gray-50 text-gray-900 antialiased font-sans min-h-screen flex flex-col">

    {{-- Navbar --}}
    <header class="bg-white border-b border-gray-200 sticky top-0 z-10">
        <div class="max-w-6xl mx-auto px-4 h-14 flex items-center justify-between">

            <a href="{{ route('auctions.index') }}" class="text-base font-semibold tracking-tight text-gray-900">
                Collektr
            </a>

            <nav class="flex items-center gap-5 text-sm">
                <a href="{{ route('auctions.index') }}"
                   class="transition-colors {{ request()->routeIs('auctions.*') ? 'text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-900' }}">
                    Auctions
                </a>

                @auth
                    @if(auth()->user()->is_admin)
                        <a href="{{ route('admin.auctions.index') }}"
                           class="transition-colors {{ request()->routeIs('admin.*') ? 'text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-900' }}">
                            Admin
                        </a>
                    @endif

                    <a href="{{ route('profile.edit') }}"
                       class="transition-colors {{ request()->routeIs('profile.*') ? 'text-gray-900 font-medium' : 'text-gray-500 hover:text-gray-900' }}">
                        {{ auth()->user()->name }}
                    </a>

                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-900 transition-colors">
                            Log out
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-gray-500 hover:text-gray-900 transition-colors">Login</a>
                    <a href="{{ route('register') }}"
                       class="bg-gray-900 text-white text-sm px-3 py-1.5 rounded-lg hover:bg-gray-700 transition-colors">
                        Register
                    </a>
                @endauth
            </nav>
        </div>
    </header>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="max-w-6xl mx-auto px-4 w-full mt-4" x-data="{ show: true }" x-show="show">
            <div class="bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3 flex items-center justify-between">
                <span>{{ session('success') }}</span>
                <button @click="show = false" class="text-green-600 hover:text-green-800 ml-4">&times;</button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="max-w-6xl mx-auto px-4 w-full mt-4" x-data="{ show: true }" x-show="show">
            <div class="bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3 flex items-center justify-between">
                <span>{{ session('error') }}</span>
                <button @click="show = false" class="text-red-600 hover:text-red-800 ml-4">&times;</button>
            </div>
        </div>
    @endif

    {{-- Main content --}}
    <main class="flex-1 max-w-6xl mx-auto px-4 py-8 w-full">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="border-t border-gray-200 mt-auto">
        <div class="max-w-6xl mx-auto px-4 py-4 text-xs text-gray-400 text-center">
            &copy; {{ date('Y') }} Collektr. All rights reserved.
        </div>
    </footer>

    @livewireScripts
</body>
</html>
