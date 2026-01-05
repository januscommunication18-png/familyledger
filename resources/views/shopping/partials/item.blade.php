<div id="item-{{ $item->id }}" class="item-row {{ $item->is_checked ? 'item-checked' : '' }} flex items-center gap-3 p-3 rounded-lg hover:bg-slate-50 group transition-colors">
    <!-- Checkbox -->
    <button type="button" onclick="toggleItem({{ $item->id }})"
        class="flex-shrink-0 w-6 h-6 rounded-full border-2 {{ $item->is_checked ? 'bg-emerald-500 border-emerald-500' : 'border-slate-300 hover:border-emerald-400' }} flex items-center justify-center transition-colors">
        @if($item->is_checked)
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
        @endif
    </button>

    <!-- Item Details -->
    <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2">
            <span class="{{ $item->is_checked ? 'line-through text-slate-400' : 'text-slate-800' }} font-medium">
                {{ $item->name }}
            </span>
            @if($item->quantity)
                <span class="badge badge-sm badge-ghost">{{ $item->quantity }}</span>
            @endif
        </div>
        @if($item->notes)
            <p class="text-xs text-slate-400 mt-0.5">{{ $item->notes }}</p>
        @endif
    </div>

    <!-- Added By Avatar -->
    @if($item->addedBy)
        <div class="flex-shrink-0 w-6 h-6 rounded-full bg-slate-200 flex items-center justify-center" title="Added by {{ $item->addedBy->name }}">
            <span class="text-xs font-medium text-slate-600">{{ strtoupper(substr($item->addedBy->name, 0, 1)) }}</span>
        </div>
    @endif

    <!-- Delete Button -->
    <button type="button" onclick="deleteItem({{ $item->id }})"
        class="flex-shrink-0 opacity-0 group-hover:opacity-100 transition-opacity btn btn-ghost btn-xs btn-circle text-slate-400 hover:text-rose-500">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
    </button>
</div>
