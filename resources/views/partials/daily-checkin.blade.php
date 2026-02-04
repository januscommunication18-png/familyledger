{{-- Daily Check-in Partial (without dialog wrapper) --}}
{{-- Usage: @include('partials.daily-checkin') --}}
{{-- Can be used in modal or directly on dashboard --}}

<div id="daily-checkin-container" x-data="dailyCheckin()" x-init="init()">
    {{-- Loading State --}}
    <div x-show="loading" class="flex justify-center items-center py-12">
        <span class="loading loading-spinner loading-lg text-primary"></span>
    </div>

    {{-- Content --}}
    <div x-show="!loading" x-cloak>
        {{-- Header with Date --}}
        <div class="text-center mb-6">
            <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary/10 rounded-full text-primary font-medium">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/><line x1="3" x2="21" y1="10" y2="10"/></svg>
                <span x-text="data.date"></span>
            </div>
        </div>

        {{-- Custody Info --}}
        <template x-if="data.custody_parent">
            <div class="mb-6">
                <div class="alert" :class="data.can_checkin ? 'alert-success' : 'alert-info'">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/></svg>
                    <span x-show="data.can_checkin">
                        Today is <strong x-text="data.custody_parent ? data.custody_parent.charAt(0).toUpperCase() + data.custody_parent.slice(1) : ''"></strong>'s day - You can do check-in!
                    </span>
                    <span x-show="!data.can_checkin">
                        Today is <strong x-text="data.custody_parent ? data.custody_parent.charAt(0).toUpperCase() + data.custody_parent.slice(1) : ''"></strong>'s day - View only mode
                    </span>
                </div>
            </div>
        </template>

        {{-- No Children Message --}}
        <div x-show="data.children && data.children.length === 0" class="text-center py-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-slate-100 flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400"><circle cx="12" cy="8" r="5"/><path d="M20 21a8 8 0 1 0-16 0"/></svg>
            </div>
            <p class="text-slate-500">No child selected for check-in</p>
            <p class="text-sm text-slate-400 mt-1">Select a child from the picker to do daily check-in</p>
        </div>

        {{-- Children List --}}
        <div x-show="data.children && data.children.length > 0" class="space-y-4">
            <template x-for="child in data.children" :key="child.id">
                <div class="card bg-base-100 border border-slate-200 shadow-sm">
                    <div class="card-body p-4">
                        {{-- Child Header --}}
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center ring-2 ring-pink-200">
                                <span class="text-lg font-bold text-white" x-text="child.name.charAt(0).toUpperCase()"></span>
                            </div>
                            <div class="flex-1">
                                <p class="font-semibold text-slate-800" x-text="'You have ' + child.full_name"></p>
                                <p class="text-sm text-slate-500" x-show="child.has_checkin">
                                    Checked in at <span x-text="child.checkin?.time"></span>
                                </p>
                                <p class="text-sm text-slate-500" x-show="!child.has_checkin && data.can_checkin">
                                    Ready for check-in
                                </p>
                                <p class="text-sm text-slate-500" x-show="!child.has_checkin && !data.can_checkin">
                                    No check-in today yet
                                </p>
                            </div>
                            {{-- Status Badge --}}
                            <div x-show="child.has_checkin" class="badge badge-success gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                Done
                            </div>
                        </div>

                        {{-- Existing Check-in Display --}}
                        <div x-show="child.has_checkin" class="bg-slate-50 rounded-xl p-4">
                            <div class="flex items-center gap-4 mb-2">
                                <span class="text-4xl" x-text="child.checkin?.mood_emoji"></span>
                                <div>
                                    <p class="font-medium text-slate-800" x-text="child.checkin?.mood_label"></p>
                                    <p class="text-sm text-slate-500">Checked in by <span x-text="child.checkin?.checked_by"></span></p>
                                </div>
                            </div>
                            <div x-show="child.checkin?.notes" class="mt-3 pt-3 border-t border-slate-200">
                                <p class="text-sm text-slate-600" x-text="child.checkin?.notes"></p>
                            </div>
                        </div>

                        {{-- Check-in Form (only if can check in and not already checked in) --}}
                        <div x-show="!child.has_checkin && data.can_checkin">
                            {{-- Mood Selection --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-slate-700 mb-2">How is <span x-text="child.name"></span> feeling today?</label>
                                <div class="grid grid-cols-5 gap-2">
                                    <template x-for="(mood, key) in data.moods" :key="key">
                                        <button type="button"
                                            @click="selectMood(child.id, key)"
                                            class="flex flex-col items-center p-3 rounded-xl border-2 transition-all hover:scale-105"
                                            :class="selectedMoods[child.id] === key ? 'border-primary bg-primary/10' : 'border-slate-200 hover:border-slate-300'"
                                        >
                                            <span class="text-2xl" x-text="mood.emoji"></span>
                                            <span class="text-xs mt-1 text-slate-600" x-text="mood.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-slate-700 mb-2">Notes (optional)</label>
                                <textarea
                                    x-model="notes[child.id]"
                                    class="textarea textarea-bordered w-full"
                                    rows="2"
                                    placeholder="How was the day? Any updates to share..."
                                ></textarea>
                            </div>

                            {{-- Submit Button --}}
                            <button
                                type="button"
                                @click="submitCheckin(child.id)"
                                :disabled="!selectedMoods[child.id] || submitting[child.id]"
                                class="btn btn-primary w-full"
                            >
                                <span x-show="!submitting[child.id]">Save Check-in</span>
                                <span x-show="submitting[child.id]" class="loading loading-spinner loading-sm"></span>
                            </button>
                        </div>

                        {{-- View Only Message --}}
                        <div x-show="!child.has_checkin && !data.can_checkin" class="text-center py-4">
                            <p class="text-slate-500 text-sm">Waiting for check-in from the other parent</p>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- View History Link --}}
        <div x-show="data.children && data.children.length > 0" class="mt-6 text-center">
            <a href="{{ route('coparenting.checkins') }}" class="text-primary hover:underline text-sm">
                View Check-in History
            </a>
        </div>
    </div>
</div>

@push('scripts')
<script>
function dailyCheckin() {
    return {
        loading: true,
        data: {
            children: [],
            custody_parent: null,
            can_checkin: false,
            user_parent_role: null,
            moods: {},
            date: ''
        },
        selectedMoods: {},
        notes: {},
        submitting: {},

        async init() {
            await this.fetchData();
        },

        async fetchData() {
            this.loading = true;
            try {
                const response = await fetch('{{ route("coparenting.daily-checkin.data") }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                const result = await response.json();
                if (result.success) {
                    this.data = result.data;
                    // Initialize state for each child
                    this.data.children.forEach(child => {
                        this.selectedMoods[child.id] = null;
                        this.notes[child.id] = '';
                        this.submitting[child.id] = false;
                    });
                }
            } catch (error) {
                console.error('Failed to fetch check-in data:', error);
            } finally {
                this.loading = false;
            }
        },

        selectMood(childId, mood) {
            this.selectedMoods[childId] = mood;
        },

        async submitCheckin(childId) {
            if (!this.selectedMoods[childId]) return;

            this.submitting[childId] = true;
            try {
                const response = await fetch('{{ route("coparenting.daily-checkin.store") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        child_id: childId,
                        mood: this.selectedMoods[childId],
                        notes: this.notes[childId] || null
                    })
                });
                const result = await response.json();
                if (result.success) {
                    // Update child data with new check-in
                    const childIndex = this.data.children.findIndex(c => c.id === childId);
                    if (childIndex !== -1) {
                        this.data.children[childIndex].has_checkin = true;
                        this.data.children[childIndex].checkin = {
                            mood: result.data.mood,
                            mood_emoji: result.data.mood_emoji,
                            mood_label: result.data.mood_label,
                            notes: result.data.notes,
                            checked_by: result.data.checked_by,
                            time: result.data.time
                        };
                    }
                    // Show success toast if available
                    if (window.showToast) {
                        window.showToast(result.message, 'success');
                    }
                } else {
                    if (window.showToast) {
                        window.showToast(result.message || 'Failed to save check-in', 'error');
                    }
                }
            } catch (error) {
                console.error('Failed to submit check-in:', error);
                if (window.showToast) {
                    window.showToast('Failed to save check-in', 'error');
                }
            } finally {
                this.submitting[childId] = false;
            }
        }
    };
}
</script>
@endpush
