{{-- Co-parent Child Switcher (Header Component) --}}
{{-- Shows selected child with option to switch --}}

@php
    use App\Services\CoparentChildSelector;
    $selectedChild = CoparentChildSelector::getSelectedChild();
    $hasMultiple = CoparentChildSelector::hasMultipleChildren();
@endphp

@if($selectedChild)
<div class="flex items-center gap-2">
    <button onclick="window.dispatchEvent(new CustomEvent('open-child-picker'))"
            class="flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 hover:border-primary/50 hover:bg-primary/5 transition-all {{ $hasMultiple ? 'cursor-pointer' : 'cursor-default' }}">
        {{-- Avatar --}}
        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
            @if($selectedChild->profile_image_url)
                <img src="{{ $selectedChild->profile_image_url }}" alt="{{ $selectedChild->first_name }}" class="w-full h-full rounded-full object-cover">
            @else
                <span class="text-sm font-bold text-white">{{ strtoupper(substr($selectedChild->first_name ?? 'C', 0, 1)) }}</span>
            @endif
        </div>

        {{-- Name --}}
        <span class="font-medium text-slate-700">{{ $selectedChild->first_name ?? $selectedChild->name }}</span>

        {{-- Dropdown Arrow (only if multiple children) --}}
        @if($hasMultiple)
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="m6 9 6 6 6-6"/></svg>
        @endif
    </button>
</div>
@endif
