@extends('layouts.dashboard')

@section('page-name', 'Child Info')

@section('content')
{{-- Child Picker Modal --}}
@include('partials.coparent-child-picker')

<div class="p-4 lg:p-6" x-data>
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Child Information</h1>
            <p class="text-slate-500">Quick access to shared children's information.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Child Switcher --}}
            @include('partials.coparent-child-switcher')
        </div>
    </div>

    @if($children->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($children as $child)
        <a href="{{ route('coparenting.children.show', $child) }}" class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
            <div class="card-body">
                <div class="flex items-center gap-4">
                    @if($child->profile_image_url)
                        <img src="{{ $child->profile_image_url }}" alt="{{ $child->full_name }}" class="w-14 h-14 rounded-full object-cover">
                    @else
                        <div class="w-14 h-14 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                            <span class="text-xl font-bold text-white">{{ strtoupper(substr($child->first_name ?? 'C', 0, 1)) }}</span>
                        </div>
                    @endif
                    <div>
                        <h3 class="font-semibold text-slate-800">{{ $child->full_name }}</h3>
                        <p class="text-sm text-slate-500">{{ $child->age }} years old</p>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @else
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-12">
            <div class="w-20 h-20 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">No Children in Co-parenting</h3>
            <p class="text-slate-500 mb-4">Start by inviting a co-parent and selecting children to share.</p>
            <a href="{{ route('coparenting.invite') }}" class="btn btn-primary">Invite Co-parent</a>
        </div>
    </div>
    @endif
</div>
@endsection
