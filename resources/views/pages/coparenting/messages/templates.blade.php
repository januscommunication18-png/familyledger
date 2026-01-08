@extends('layouts.dashboard')

@section('page-name', 'Message Templates')

@section('content')
<div class="p-4 lg:p-6">
    {{-- Header --}}
    <div class="flex items-center gap-4 mb-6">
        <a href="{{ route('coparenting.messages.index') }}" class="btn btn-ghost btn-sm gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
            Back
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Message Templates</h1>
            <p class="text-slate-500">Pre-written messages for common situations</p>
        </div>
    </div>

    <div class="max-w-4xl mx-auto">
        {{-- Info --}}
        <div class="alert bg-blue-50 border border-blue-200 text-blue-800 mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
            <span>These templates help you communicate clearly and professionally. Customize them as needed when starting a conversation.</span>
        </div>

        {{-- Templates by Category --}}
        @foreach($categories as $key => $cat)
        @if($templates->has($key))
        <div class="mb-8">
            <div class="flex items-center gap-2 mb-4">
                <span class="text-2xl">{{ $cat['icon'] }}</span>
                <h2 class="text-lg font-semibold text-slate-800">{{ $cat['label'] }}</h2>
                <span class="badge" style="background-color: {{ $cat['color'] }}20; color: {{ $cat['color'] }}">
                    {{ $templates[$key]->count() }} template{{ $templates[$key]->count() > 1 ? 's' : '' }}
                </span>
            </div>

            <div class="grid gap-4">
                @foreach($templates[$key] as $template)
                <div class="card bg-base-100 shadow-sm hover:shadow-md transition-shadow">
                    <div class="card-body">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h3 class="font-semibold text-slate-800 mb-2">{{ $template->title }}</h3>
                                <p class="text-slate-600 whitespace-pre-wrap">{{ $template->content }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                @if($template->is_system)
                                <span class="badge badge-ghost badge-sm">System</span>
                                @endif
                                <button onclick="copyTemplate('{{ addslashes($template->content) }}')" class="btn btn-ghost btn-sm gap-1" title="Copy to clipboard">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="14" x="8" y="8" rx="2" ry="2"/><path d="M4 16c-1.1 0-2-.9-2-2V4c0-1.1.9-2 2-2h10c1.1 0 2 .9 2 2"/></svg>
                                    Copy
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        @endforeach

        @if($templates->isEmpty())
        <div class="text-center py-16">
            <div class="w-20 h-20 mx-auto rounded-2xl bg-slate-100 flex items-center justify-center mb-6">
                <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" x2="8" y1="13" y2="13"/><line x1="16" x2="8" y1="17" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">No templates available</h3>
            <p class="text-slate-500">Templates will appear here once added.</p>
        </div>
        @endif
    </div>
</div>

<script>
function copyTemplate(content) {
    navigator.clipboard.writeText(content).then(function() {
        // You could add a toast notification here
        alert('Template copied to clipboard!');
    });
}
</script>
@endsection
