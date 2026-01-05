@extends('layouts.dashboard')

@section('title', 'People Directory')
@section('page-name', 'People Directory')

@section('breadcrumbs')
    <li class="breadcrumbs-separator rtl:rotate-180">
        <span class="icon-[tabler--chevron-right] size-4"></span>
    </li>
    <li aria-current="page">People Directory</li>
@endsection

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center shadow-lg">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">People Directory</h1>
                <p class="text-slate-500">{{ $people->total() }} {{ Str::plural('contact', $people->total()) }}</p>
            </div>
        </div>
        <a href="{{ route('people.create') }}" class="btn btn-primary gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
            Add Person
        </a>
    </div>

    <!-- Search & Filters -->
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body p-4">
            <div class="flex flex-col md:flex-row gap-4">
                <!-- Real-time Search -->
                <div class="flex-1">
                    <div class="relative">
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" id="searchInput"
                            placeholder="Search by name, company, email, phone..."
                            class="w-full pl-10 pr-4 py-2.5 border border-slate-300 rounded-lg text-slate-900 placeholder:text-slate-400 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20">
                    </div>
                </div>

                <!-- Filters -->
                <div class="flex gap-2">
                    <select id="relationshipFilter" class="px-3 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 bg-white">
                        <option value="">All Relationships</option>
                        @foreach($relationships as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>

                    @if($allTags->count() > 0)
                    <select id="tagFilter" class="px-3 py-2.5 border border-slate-300 rounded-lg text-slate-900 focus:border-violet-500 focus:outline-none focus:ring-2 focus:ring-violet-500/20 bg-white">
                        <option value="">All Tags</option>
                        @foreach($allTags as $tag)
                            <option value="{{ $tag }}">{{ $tag }}</option>
                        @endforeach
                    </select>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- People List -->
    @if($people->count() > 0)
        <div id="peopleList" class="space-y-3">
            @foreach($people as $person)
                <a href="{{ route('people.show', $person) }}"
                   class="person-card block card bg-base-100 shadow-sm hover:shadow-md transition-all border border-slate-200 hover:border-violet-300"
                   data-name="{{ strtolower($person->full_name) }}"
                   data-nickname="{{ strtolower($person->nickname ?? '') }}"
                   data-company="{{ strtolower($person->company ?? '') }}"
                   data-email="{{ strtolower($person->primary_email?->email ?? '') }}"
                   data-phone="{{ $person->primary_phone?->phone ?? '' }}"
                   data-relationship="{{ $person->relationship }}"
                   data-tags="{{ strtolower(implode(',', $person->tags ?? [])) }}">
                    <div class="card-body p-4">
                        <div class="flex items-center gap-4">
                            <!-- Avatar - First Letter Only -->
                            <div class="flex-shrink-0">
                                @if($person->profile_image_url)
                                    <img src="{{ $person->profile_image_url }}" alt="{{ $person->full_name }}"
                                         class="w-14 h-14 rounded-xl object-cover shadow-sm">
                                @else
                                    @php
                                        $colors = [
                                            'from-violet-500 to-purple-600',
                                            'from-blue-500 to-cyan-600',
                                            'from-emerald-500 to-teal-600',
                                            'from-amber-500 to-orange-600',
                                            'from-rose-500 to-pink-600',
                                            'from-indigo-500 to-blue-600',
                                        ];
                                        $firstLetter = strtoupper(substr($person->full_name, 0, 1));
                                        $colorIndex = ord($firstLetter) % count($colors);
                                    @endphp
                                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br {{ $colors[$colorIndex] }} flex items-center justify-center shadow-sm">
                                        <span class="text-white font-bold text-xl">{{ $firstLetter }}</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Main Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <h3 class="font-semibold text-slate-900 text-lg">{{ $person->full_name }}</h3>
                                            @if($person->nickname)
                                                <span class="text-slate-500 text-sm">"{{ $person->nickname }}"</span>
                                            @endif
                                        </div>
                                        @if($person->company || $person->job_title)
                                            <p class="text-slate-600 text-sm mt-0.5">
                                                @if($person->job_title){{ $person->job_title }}@endif
                                                @if($person->job_title && $person->company) at @endif
                                                @if($person->company)<span class="font-medium">{{ $person->company }}</span>@endif
                                            </p>
                                        @endif
                                    </div>
                                    <span class="badge badge-{{ $person->relationship_color }} flex-shrink-0">
                                        {{ $person->relationship_name }}
                                    </span>
                                </div>

                                <!-- Contact Details -->
                                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-sm text-slate-600">
                                    @if($person->primary_email)
                                        <div class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                            <span class="truncate max-w-[200px]">{{ $person->primary_email->email }}</span>
                                        </div>
                                    @endif

                                    @if($person->primary_phone)
                                        <div class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                            <span>{{ $person->primary_phone->formatted_phone }}</span>
                                        </div>
                                    @endif

                                    @if($person->date_of_birth)
                                        <div class="flex items-center gap-1.5">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                                            <span>{{ $person->date_of_birth->format('M d, Y') }}</span>
                                        </div>
                                    @endif
                                </div>

                                <!-- Tags -->
                                @if($person->tags && count($person->tags) > 0)
                                    <div class="flex flex-wrap gap-1.5 mt-2">
                                        @foreach(array_slice($person->tags, 0, 4) as $tag)
                                            <span class="badge badge-sm bg-slate-100 text-slate-600 border-0">{{ $tag }}</span>
                                        @endforeach
                                        @if(count($person->tags) > 4)
                                            <span class="badge badge-sm badge-ghost">+{{ count($person->tags) - 4 }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <!-- Arrow -->
                            <div class="flex-shrink-0 text-slate-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
                            </div>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <!-- No Results Message (hidden by default) -->
        <div id="noResults" class="hidden card bg-base-100 shadow-sm">
            <div class="card-body text-center py-12">
                <div class="w-16 h-16 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                </div>
                <h3 class="text-lg font-semibold text-slate-900 mb-1">No matches found</h3>
                <p class="text-slate-500">Try adjusting your search or filters</p>
            </div>
        </div>

        <!-- Pagination -->
        <div class="flex justify-center">
            {{ $people->withQueryString()->links() }}
        </div>
    @else
        <div class="card bg-base-100 shadow-sm">
            <div class="card-body text-center py-16">
                <div class="max-w-md mx-auto">
                    <div class="w-20 h-20 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                    </div>
                    <h3 class="text-xl font-semibold text-slate-900 mb-2">No contacts yet</h3>
                    <p class="text-slate-500 mb-6">Start building your personal directory by adding your first contact.</p>
                    <a href="{{ route('people.create') }}" class="btn btn-primary gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="M12 5v14"/></svg>
                        Add Your First Contact
                    </a>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const relationshipFilter = document.getElementById('relationshipFilter');
    const tagFilter = document.getElementById('tagFilter');
    const peopleList = document.getElementById('peopleList');
    const noResults = document.getElementById('noResults');

    if (!searchInput || !peopleList) return;

    function filterPeople() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const relationship = relationshipFilter ? relationshipFilter.value : '';
        const tag = tagFilter ? tagFilter.value.toLowerCase() : '';

        const cards = peopleList.querySelectorAll('.person-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.dataset.name || '';
            const nickname = card.dataset.nickname || '';
            const company = card.dataset.company || '';
            const email = card.dataset.email || '';
            const phone = card.dataset.phone || '';
            const cardRelationship = card.dataset.relationship || '';
            const tags = card.dataset.tags || '';

            // Check search term
            const matchesSearch = !searchTerm ||
                name.includes(searchTerm) ||
                nickname.includes(searchTerm) ||
                company.includes(searchTerm) ||
                email.includes(searchTerm) ||
                phone.includes(searchTerm);

            // Check relationship filter
            const matchesRelationship = !relationship || cardRelationship === relationship;

            // Check tag filter
            const matchesTag = !tag || tags.includes(tag.toLowerCase());

            if (matchesSearch && matchesRelationship && matchesTag) {
                card.classList.remove('hidden');
                visibleCount++;
            } else {
                card.classList.add('hidden');
            }
        });

        // Show/hide no results message
        if (noResults) {
            if (visibleCount === 0 && (searchTerm || relationship || tag)) {
                noResults.classList.remove('hidden');
            } else {
                noResults.classList.add('hidden');
            }
        }
    }

    // Event listeners
    searchInput.addEventListener('input', filterPeople);
    if (relationshipFilter) relationshipFilter.addEventListener('change', filterPeople);
    if (tagFilter) tagFilter.addEventListener('change', filterPeople);
});
</script>
@endpush
