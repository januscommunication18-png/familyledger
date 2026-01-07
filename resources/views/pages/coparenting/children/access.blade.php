@extends('layouts.dashboard')

@section('page-name', 'Manage Access - ' . $child->full_name)

@section('content')
<div class="p-4 lg:p-6 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('coparenting.children.show', $child) }}" class="text-sm text-slate-500 hover:text-slate-700 flex items-center gap-1 mb-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m12 19-7-7 7-7"/><path d="M19 12H5"/></svg>
            Back to {{ $child->full_name }}
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Manage Access Permissions</h1>
        <p class="text-slate-500">Control what information co-parents can see and edit for {{ $child->first_name }}.</p>
    </div>

    {{-- Success Message --}}
    @if(session('success'))
    <div class="alert alert-success mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if($coparents->count() > 0)
    <form action="{{ route('coparenting.children.access.update', $child) }}" method="POST">
        @csrf
        @method('PUT')

        @foreach($coparents as $index => $coparent)
        @php
            $permissions = json_decode($coparent->pivot->permissions ?? '{}', true) ?: [];
        @endphp
        <div class="card bg-base-100 shadow-sm mb-6">
            <div class="card-body">
                {{-- Co-parent Header --}}
                <div class="flex items-center gap-4 mb-6 pb-4 border-b border-slate-200">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center">
                        <span class="text-lg font-bold text-white">{{ strtoupper(substr($coparent->user->name ?? 'U', 0, 1)) }}</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-slate-800">{{ $coparent->user->name ?? 'Unknown' }}</h3>
                        <p class="text-sm text-slate-500">{{ $coparent->user->email ?? '' }}</p>
                    </div>
                    <span class="badge badge-{{ $coparent->is_active ? 'success' : 'warning' }} ml-auto">
                        {{ $coparent->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <input type="hidden" name="permissions[{{ $index }}][collaborator_id]" value="{{ $coparent->id }}">

                {{-- Permissions Table --}}
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead>
                            <tr>
                                <th class="bg-slate-50">Information Category</th>
                                <th class="bg-slate-50 text-center w-24">No Access</th>
                                <th class="bg-slate-50 text-center w-24">View</th>
                                <th class="bg-slate-50 text-center w-24">Edit</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($permissionCategories as $key => $category)
                            <tr>
                                <td>
                                    <div>
                                        <p class="font-medium text-slate-800">{{ $category['label'] }}</p>
                                        <p class="text-xs text-slate-500">{{ $category['description'] }}</p>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <input type="radio" name="permissions[{{ $index }}][categories][{{ $key }}]" value="none" class="radio radio-sm" {{ ($permissions[$key] ?? 'none') === 'none' ? 'checked' : '' }}>
                                </td>
                                <td class="text-center">
                                    <input type="radio" name="permissions[{{ $index }}][categories][{{ $key }}]" value="view" class="radio radio-sm radio-primary" {{ ($permissions[$key] ?? 'none') === 'view' ? 'checked' : '' }}>
                                </td>
                                <td class="text-center">
                                    <input type="radio" name="permissions[{{ $index }}][categories][{{ $key }}]" value="edit" class="radio radio-sm radio-success" {{ ($permissions[$key] ?? 'none') === 'edit' ? 'checked' : '' }}>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Quick Actions --}}
                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-slate-200">
                    <button type="button" onclick="setAllPermissions({{ $index }}, 'none')" class="btn btn-xs btn-ghost">Set All: No Access</button>
                    <button type="button" onclick="setAllPermissions({{ $index }}, 'view')" class="btn btn-xs btn-ghost">Set All: View</button>
                    <button type="button" onclick="setAllPermissions({{ $index }}, 'edit')" class="btn btn-xs btn-ghost">Set All: Edit</button>
                </div>
            </div>
        </div>
        @endforeach

        {{-- Submit Button --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('coparenting.children.show', $child) }}" class="btn btn-ghost">Cancel</a>
            <button type="submit" class="btn btn-primary gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                Save Permissions
            </button>
        </div>
    </form>
    @else
    <div class="card bg-base-100 shadow-sm">
        <div class="card-body text-center py-12">
            <div class="w-16 h-16 mx-auto rounded-full bg-slate-100 flex items-center justify-center mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="rgb(148 163 184)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="19" x2="19" y1="8" y2="14"/><line x1="22" x2="16" y1="11" y2="11"/></svg>
            </div>
            <h3 class="text-lg font-semibold text-slate-800 mb-2">No Co-parents Connected</h3>
            <p class="text-slate-500 mb-4">Invite a co-parent to share access to {{ $child->first_name }}'s information.</p>
            <a href="{{ route('coparenting.invite') }}" class="btn btn-primary">Invite Co-parent</a>
        </div>
    </div>
    @endif
</div>

<script>
function setAllPermissions(index, level) {
    const radios = document.querySelectorAll(`input[name^="permissions[${index}][categories]"][value="${level}"]`);
    radios.forEach(radio => radio.checked = true);
}
</script>
@endsection
