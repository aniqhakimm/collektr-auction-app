@props(['status'])

@php
    $styles = match($status) {
        'active' => 'bg-green-100 text-green-700',
        'ended'  => 'bg-gray-100 text-gray-500',
        'draft'  => 'bg-yellow-100 text-yellow-700',
        default  => 'bg-gray-100 text-gray-500',
    };
@endphp

<span class="inline-flex items-center shrink-0 px-2 py-0.5 rounded-full text-xs font-medium {{ $styles }}">
    {{ ucfirst($status) }}
</span>
