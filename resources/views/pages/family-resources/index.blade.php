@extends('layouts.dashboard')

@section('title', 'Family Resources')
@section('page-name', 'Family Resources')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">Family Resources</li>
@endsection

@section('page-title', 'Family Resources')
@section('page-description', 'Store and organize important family documents and resources.')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="alert alert-success">
            <span class="icon-[tabler--check] size-5"></span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Landing Section with Video Placeholder -->
    @if($counts['total'] === 0)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-12">
            <div class="max-w-2xl mx-auto">
                <!-- Video Placeholder -->
                <div class="aspect-video bg-gradient-to-br from-teal-100 to-cyan-100 rounded-xl mb-8 flex items-center justify-center">
                    <div class="text-center">
                        <span class="icon-[tabler--player-play-filled] size-16 text-teal-500/40"></span>
                        <p class="text-sm text-base-content/60 mt-2">Watch: How to organize your family resources</p>
                    </div>
                </div>

                <h2 class="text-2xl font-bold mb-4">Organize Your Family Resources</h2>
                <p class="text-base-content/70 mb-8">
                    Keep all your important family documents in one secure place. From emergency plans
                    to rental agreements, home warranties and more - everything your family needs,
                    organized and accessible.
                </p>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('family-resources.create') }}" class="btn btn-primary btn-lg gap-2">
                        <span class="icon-[tabler--plus] size-5"></span>
                        Add Family Resource
                    </a>
                    <button class="btn btn-outline btn-lg gap-2" disabled>
                        <span class="icon-[tabler--file-certificate] size-5"></span>
                        Create Digital Will
                        <span class="badge badge-sm badge-warning">Coming Soon</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Document Types Overview -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <a href="{{ route('family-resources.create', ['type' => 'emergency']) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="card-body items-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center mb-2 group-hover:bg-red-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-red-600"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                </div>
                <h3 class="font-semibold text-sm">Emergency</h3>
                <span class="badge badge-ghost badge-sm">{{ $counts['emergency'] }}</span>
            </div>
        </a>

        <a href="{{ route('family-resources.create', ['type' => 'evacuation_plan']) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="card-body items-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center mb-2 group-hover:bg-orange-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-orange-600"><path d="M13 4h3a2 2 0 0 1 2 2v14"/><path d="M2 20h3"/><path d="M13 20h9"/><path d="M10 12v.01"/><path d="M13 4.562v16.157a1 1 0 0 1-1.242.97L5 20V5.562a2 2 0 0 1 1.515-1.94l4-1A2 2 0 0 1 13 4.561Z"/></svg>
                </div>
                <h3 class="font-semibold text-sm">Evacuation Plan</h3>
                <span class="badge badge-ghost badge-sm">{{ $counts['evacuation'] }}</span>
            </div>
        </a>

        <a href="{{ route('family-resources.create', ['type' => 'fire_extinguisher']) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="card-body items-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-rose-100 flex items-center justify-center mb-2 group-hover:bg-rose-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-rose-600"><path d="M15 6.5V3a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v3.5"/><path d="M9 18h6"/><path d="M18 3h-3"/><path d="M11 3a6 6 0 0 0-6 6v11"/><path d="M19 9a6 6 0 0 0-6-6"/><path d="M19 9v11a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1"/></svg>
                </div>
                <h3 class="font-semibold text-sm">Fire Extinguisher</h3>
                <span class="badge badge-ghost badge-sm">{{ $counts['fire'] }}</span>
            </div>
        </a>

        <a href="{{ route('family-resources.create', ['type' => 'rental_agreement']) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="card-body items-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center mb-2 group-hover:bg-blue-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-blue-600"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/></svg>
                </div>
                <h3 class="font-semibold text-sm">Rental / Lease</h3>
                <span class="badge badge-ghost badge-sm">{{ $counts['rental'] }}</span>
            </div>
        </a>

        <a href="{{ route('family-resources.create', ['type' => 'home_warranty']) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="card-body items-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center mb-2 group-hover:bg-emerald-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-emerald-600"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                </div>
                <h3 class="font-semibold text-sm">Home Warranty</h3>
                <span class="badge badge-ghost badge-sm">{{ $counts['warranty'] }}</span>
            </div>
        </a>

        <a href="{{ route('family-resources.create', ['type' => 'other']) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow group">
            <div class="card-body items-center text-center p-4">
                <div class="w-12 h-12 rounded-full bg-gray-100 flex items-center justify-center mb-2 group-hover:bg-gray-200 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-gray-600"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
                </div>
                <h3 class="font-semibold text-sm">Other</h3>
                <span class="badge badge-ghost badge-sm">{{ $counts['other'] }}</span>
            </div>
        </a>
    </div>

    <!-- Resources List -->
    @if($counts['total'] > 0)
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body">
            <div class="flex items-center justify-between mb-6">
                <h2 class="card-title">All Family Resources</h2>
                <a href="{{ route('family-resources.create') }}" class="btn btn-primary btn-sm gap-2">
                    <span class="icon-[tabler--plus] size-4"></span>
                    Add Resource
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($resources as $resource)
                <div class="card bg-base-200/50 hover:bg-base-200 transition-colors">
                    <div class="card-body p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-lg bg-{{ $resource->document_type_color }}-100 flex items-center justify-center shrink-0">
                                @switch($resource->document_type)
                                    @case('emergency')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-red-600"><path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3"/><path d="M12 9v4"/><path d="M12 17h.01"/></svg>
                                        @break
                                    @case('evacuation_plan')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-orange-600"><path d="M13 4h3a2 2 0 0 1 2 2v14"/><path d="M2 20h3"/><path d="M13 20h9"/><path d="M10 12v.01"/><path d="M13 4.562v16.157a1 1 0 0 1-1.242.97L5 20V5.562a2 2 0 0 1 1.515-1.94l4-1A2 2 0 0 1 13 4.561Z"/></svg>
                                        @break
                                    @case('fire_extinguisher')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-rose-600"><path d="M15 6.5V3a1 1 0 0 0-1-1h-2a1 1 0 0 0-1 1v3.5"/><path d="M9 18h6"/><path d="M18 3h-3"/><path d="M11 3a6 6 0 0 0-6 6v11"/><path d="M19 9a6 6 0 0 0-6-6"/><path d="M19 9v11a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1"/></svg>
                                        @break
                                    @case('rental_agreement')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-blue-600"><path d="M15 21v-8a1 1 0 0 0-1-1h-4a1 1 0 0 0-1 1v8"/><path d="M3 10a2 2 0 0 1 .709-1.528l7-5.999a2 2 0 0 1 2.582 0l7 5.999A2 2 0 0 1 21 10v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
                                        @break
                                    @case('home_warranty')
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-emerald-600"><path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/></svg>
                                        @break
                                    @default
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="text-gray-600"><path d="M20 20a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.69-.9L9.6 3.9A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>
                                @endswitch
                            </div>
                            <div class="flex-1 min-w-0">
                                <h3 class="font-semibold text-sm truncate">{{ $resource->name }}</h3>
                                <p class="text-xs text-base-content/60">{{ $resource->document_type_name }}</p>
                            </div>
                            <span class="badge badge-{{ $resource->status_color }} badge-sm">{{ $resource->status_name }}</span>
                        </div>

                        <div class="mt-3 text-xs text-base-content/60 space-y-1">
                            @if($resource->digital_copy_date)
                            <div class="flex items-center gap-1">
                                <span class="icon-[tabler--calendar] size-3.5"></span>
                                <span>Added: {{ $resource->digital_copy_date->format('M j, Y') }}</span>
                            </div>
                            @endif

                            <div class="flex items-center gap-1">
                                <span class="icon-[tabler--files] size-3.5"></span>
                                <span>{{ $resource->files->count() }} file(s)</span>
                            </div>
                        </div>

                        <div class="card-actions justify-end mt-3 pt-3 border-t border-base-300">
                            <a href="{{ route('family-resources.show', $resource) }}" class="btn btn-ghost btn-xs gap-1">
                                <span class="icon-[tabler--eye] size-4"></span>
                                View
                            </a>
                            <a href="{{ route('family-resources.edit', $resource) }}" class="btn btn-ghost btn-xs gap-1">
                                <span class="icon-[tabler--edit] size-4"></span>
                                Edit
                            </a>
                            <button type="button" onclick="showDeleteModal('{{ route('family-resources.destroy', $resource) }}')" class="btn btn-ghost btn-xs text-error gap-1">
                                <span class="icon-[tabler--trash] size-4"></span>
                                Delete
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="fixed inset-0 bg-black/50 transition-opacity" onclick="hideDeleteModal()"></div>
    <div class="fixed inset-0 flex items-center justify-center p-4">
        <div class="bg-base-100 rounded-xl shadow-xl max-w-sm w-full p-6 relative z-10">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-full bg-error/10 flex items-center justify-center">
                    <span class="icon-[tabler--alert-triangle] size-5 text-error"></span>
                </div>
                <h3 class="font-bold text-lg">Delete Resource?</h3>
            </div>
            <p class="text-base-content/70 mb-6">Are you sure you want to delete this family resource? This action cannot be undone and all associated files will be permanently deleted.</p>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="hideDeleteModal()" class="btn btn-ghost">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-error">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function showDeleteModal(url) {
    document.getElementById('deleteForm').action = url;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function hideDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        hideDeleteModal();
    }
});
</script>
@endsection
