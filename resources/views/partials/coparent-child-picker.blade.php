{{-- Co-parent Child Picker Modal --}}
{{-- This modal shows when user has multiple co-parented children and needs to select one --}}

@php
    use App\Services\CoparentChildSelector;
    $pickerData = CoparentChildSelector::getPickerData();
@endphp

<div x-data="coparentChildPicker({{ json_encode($pickerData) }})"
     x-init="init()"
     @open-child-picker.window="openPicker()"
     @keydown.escape.window="closePicker()">

    {{-- Modal Backdrop --}}
    <div x-show="showPicker"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-black/50 z-[60]"
         x-cloak>
    </div>

    {{-- Modal Content --}}
    <div x-show="showPicker"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="fixed inset-0 z-[60] flex items-center justify-center p-4"
         x-cloak>

        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md" @click.stop>
            {{-- Modal Header --}}
            <div class="px-6 py-5 border-b border-slate-200">
                <div class="flex items-center gap-3">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-xl text-slate-800">Select Child</h3>
                        <p class="text-sm text-slate-500">Choose which child's co-parenting data to view</p>
                    </div>
                </div>
                {{-- Close button only if not required --}}
                <button x-show="!forceSelection && selectedChild"
                        @click="closePicker()"
                        class="absolute top-4 right-4 btn btn-sm btn-circle btn-ghost">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <div class="p-6">
                <div class="space-y-3">
                    <template x-for="child in children" :key="child.id">
                        <button @click="selectChild(child.id)"
                                class="w-full flex items-center gap-4 p-4 rounded-xl border-2 transition-all hover:border-primary/50 hover:bg-primary/5"
                                :class="pendingSelection === child.id ? 'border-primary bg-primary/10' : 'border-slate-200'">
                            {{-- Avatar --}}
                            <div class="w-14 h-14 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center ring-2 ring-offset-2"
                                 :class="pendingSelection === child.id ? 'ring-primary' : 'ring-transparent'">
                                <template x-if="child.avatar_url">
                                    <img :src="child.avatar_url" :alt="child.name" class="w-full h-full rounded-full object-cover">
                                </template>
                                <template x-if="!child.avatar_url">
                                    <span class="text-xl font-bold text-white" x-text="child.name.charAt(0).toUpperCase()"></span>
                                </template>
                            </div>

                            {{-- Info --}}
                            <div class="flex-1 text-left">
                                <p class="font-semibold text-slate-800" x-text="child.full_name"></p>
                                <p class="text-sm text-slate-500" x-show="child.age" x-text="child.age + ' years old'"></p>
                            </div>

                            {{-- Selected indicator --}}
                            <div x-show="pendingSelection === child.id" class="text-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                        </button>
                    </template>
                </div>

                {{-- Confirm Button --}}
                <div class="mt-6">
                    <button @click="confirmSelection()"
                            :disabled="!pendingSelection"
                            class="btn btn-primary w-full">
                        <span x-show="!loading">Continue</span>
                        <span x-show="loading" class="loading loading-spinner loading-sm"></span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function coparentChildPicker(initialData) {
    return {
        showPicker: false,
        forceSelection: initialData.needs_selection,
        children: initialData.children,
        selectedChild: initialData.selected_child,
        pendingSelection: null,
        loading: false,

        init() {
            // Set pending selection to current selection
            this.pendingSelection = this.selectedChild?.id || null;

            // Auto-show if selection is needed
            if (this.forceSelection) {
                this.showPicker = true;
            }
        },

        openPicker() {
            this.pendingSelection = this.selectedChild?.id || null;
            this.showPicker = true;
        },

        closePicker() {
            if (this.forceSelection && !this.selectedChild) {
                return; // Can't close if selection is required
            }
            this.showPicker = false;
        },

        selectChild(childId) {
            this.pendingSelection = childId;
        },

        async confirmSelection() {
            if (!this.pendingSelection) return;

            this.loading = true;
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
                if (!csrfToken) {
                    console.error('CSRF token not found');
                    alert('Session error. Please refresh the page.');
                    return;
                }

                const response = await fetch('{{ route("coparenting.select-child") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify({ child_id: this.pendingSelection })
                });

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Server error:', response.status, errorText);
                    alert('Failed to select child. Please try again.');
                    return;
                }

                const result = await response.json();
                if (result.success) {
                    // Reload page to show filtered data
                    window.location.reload();
                } else {
                    console.error('Selection failed:', result.message);
                    alert(result.message || 'Failed to select child.');
                }
            } catch (error) {
                console.error('Failed to select child:', error);
                alert('Network error. Please check your connection and try again.');
            } finally {
                this.loading = false;
            }
        }
    };
}
</script>
@endpush
