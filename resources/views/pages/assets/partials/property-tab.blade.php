<!-- Property Tab -->
<div class="flex justify-between items-center mb-6">
    <h2 class="text-lg font-semibold">Property</h2>
    <a href="{{ route('assets.create', ['category' => 'property']) }}" class="btn btn-primary btn-sm gap-2">
        <span class="icon-[tabler--plus] size-4"></span>
        Add Property
    </a>
</div>

@if($propertyAssets->count() > 0)
    <div class="grid gap-4">
        @foreach($propertyAssets as $asset)
            <div class="border border-base-200 rounded-xl p-4 hover:border-primary/30 transition-colors">
                <div class="flex items-start justify-between">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                            <span class="icon-[tabler--home] size-6 text-primary"></span>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-semibold text-base-content">{{ $asset->name }}</h3>
                                <span class="badge badge-sm badge-{{ $asset->getStatusColor() }}">{{ $statuses[$asset->status] ?? $asset->status }}</span>
                            </div>
                            <p class="text-sm text-base-content/60">{{ $asset->type_name }} | {{ $asset->ownership_type_name }}</p>
                            @if($asset->full_location)
                                <p class="text-sm text-base-content/60 mt-1">
                                    <span class="icon-[tabler--map-pin] size-4 inline-block align-middle mr-1"></span>
                                    {{ $asset->full_location }}
                                </p>
                            @endif
                            @if($asset->owners->count() > 0)
                                <p class="text-sm text-base-content/60 mt-1">
                                    {{ $asset->owners->count() > 1 ? 'Owners' : 'Owner' }}: {{ $asset->owners->map(fn($o) => $o->owner_name)->join(', ') }}
                                </p>
                            @endif
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        @if($asset->current_value)
                            <div class="text-right">
                                <div class="text-lg font-bold text-success">{{ $asset->formatted_current_value }}</div>
                                <div class="text-xs text-base-content/60">Current Value</div>
                            </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <a href="{{ route('assets.show', $asset) }}" class="btn btn-ghost btn-sm btn-square" title="View">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                            </a>
                            <a href="{{ route('assets.edit', $asset) }}" class="btn btn-ghost btn-sm btn-square" title="Edit">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.85 2.85 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
                            </a>
                            <button type="button" onclick="confirmDelete('{{ route('assets.destroy', $asset) }}')" class="btn btn-ghost btn-sm btn-square text-error" title="Delete">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                @if($asset->is_insured)
                    <div class="mt-3 pt-3 border-t border-base-200">
                        <span class="badge badge-sm badge-success gap-1">
                            <span class="icon-[tabler--shield-check] size-3"></span>
                            Insured
                        </span>
                        @if($asset->insurance_provider)
                            <span class="text-sm text-base-content/60 ml-2">{{ $asset->insurance_provider }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach
    </div>
@else
    <div class="text-center py-12 text-base-content/60">
        <span class="icon-[tabler--home-off] size-16 opacity-30"></span>
        <p class="mt-4 text-lg font-medium">No properties</p>
        <p class="text-sm">Add your first property to start tracking your real estate</p>
        <a href="{{ route('assets.create', ['category' => 'property']) }}" class="btn btn-primary mt-4">
            <span class="icon-[tabler--plus] size-4"></span>
            Add Property
        </a>
    </div>
@endif
