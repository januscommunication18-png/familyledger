@extends('backoffice.layouts.app')

@php
    $header = 'Edit Drip Campaign';
@endphp

@section('content')
    <div x-data="campaignEditor()">
        <!-- Back Link -->
        <div class="mb-6">
            <a href="{{ route('backoffice.drip-campaigns.index') }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Back to Drip Campaigns
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Campaign Settings (Left Column) -->
            <div class="lg:col-span-1">
                <form method="POST" action="{{ route('backoffice.drip-campaigns.update', $campaign) }}" class="space-y-6">
                    @csrf
                    @method('PUT')

                    <!-- Basic Information -->
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Campaign Settings</h2>

                        <div class="space-y-4">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Name <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    value="{{ old('name', $campaign->name) }}"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    required
                                >
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Description
                                </label>
                                <textarea
                                    id="description"
                                    name="description"
                                    rows="2"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                >{{ old('description', $campaign->description) }}</textarea>
                            </div>

                            <div>
                                <label for="trigger_type" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Trigger <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="trigger_type"
                                    name="trigger_type"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    required
                                >
                                    @foreach ($triggerTypes as $key => $label)
                                        <option value="{{ $key }}" {{ old('trigger_type', $campaign->trigger_type) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Status <span class="text-red-500">*</span>
                                </label>
                                <select
                                    id="status"
                                    name="status"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    required
                                >
                                    @foreach ($statuses as $key => $label)
                                        <option value="{{ $key }}" {{ old('status', $campaign->status) === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="delay_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Initial Delay (Days)
                                    </label>
                                    <input
                                        type="number"
                                        id="delay_days"
                                        name="delay_days"
                                        value="{{ old('delay_days', $campaign->delay_days) }}"
                                        min="0"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        required
                                    >
                                </div>
                                <div>
                                    <label for="delay_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Hours
                                    </label>
                                    <input
                                        type="number"
                                        id="delay_hours"
                                        name="delay_hours"
                                        value="{{ old('delay_hours', $campaign->delay_hours) }}"
                                        min="0"
                                        max="23"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        required
                                    >
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit"
                                    class="w-full px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                Save Settings
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Variable Placeholders -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Available Variables</h3>
                    <div class="space-y-2 text-xs">
                        <div class="flex justify-between">
                            <code class="text-primary-600 dark:text-primary-400">@{{user_name}}</code>
                            <span class="text-gray-500">User's name</span>
                        </div>
                        <div class="flex justify-between">
                            <code class="text-primary-600 dark:text-primary-400">@{{user_email}}</code>
                            <span class="text-gray-500">User's email</span>
                        </div>
                        <div class="flex justify-between">
                            <code class="text-primary-600 dark:text-primary-400">@{{tenant_name}}</code>
                            <span class="text-gray-500">Family circle name</span>
                        </div>
                        <div class="flex justify-between">
                            <code class="text-primary-600 dark:text-primary-400">@{{plan_name}}</code>
                            <span class="text-gray-500">Current plan</span>
                        </div>
                        <div class="flex justify-between">
                            <code class="text-primary-600 dark:text-primary-400">@{{trial_days_left}}</code>
                            <span class="text-gray-500">Trial days remaining</span>
                        </div>
                        <div class="flex justify-between">
                            <code class="text-primary-600 dark:text-primary-400">@{{app_url}}</code>
                            <span class="text-gray-500">Application URL</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Email Steps (Right Column) -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Email Steps</h2>
                        <button type="button"
                                @click="openStepModal()"
                                class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Add Step
                        </button>
                    </div>

                    @if ($campaign->steps->isEmpty())
                        <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <p class="mb-2">No email steps yet</p>
                            <p class="text-sm">Click "Add Step" to create your first email in this sequence.</p>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach ($campaign->steps as $step)
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 hover:border-primary-300 dark:hover:border-primary-700 transition-colors">
                                    <div class="flex items-start justify-between">
                                        <div class="flex items-start gap-4">
                                            <div class="flex-shrink-0 w-8 h-8 bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 rounded-full flex items-center justify-center font-semibold text-sm">
                                                {{ $step->sequence_order }}
                                            </div>
                                            <div>
                                                <h4 class="font-medium text-gray-900 dark:text-white">{{ $step->subject }}</h4>
                                                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                                    <span class="inline-flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                        </svg>
                                                        {{ $step->getFormattedDelay() }} after previous
                                                    </span>
                                                </p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <button type="button"
                                                    @click="openStepModal({{ json_encode($step) }})"
                                                    class="p-2 text-gray-500 hover:text-primary-600 transition-colors"
                                                    title="Edit Step">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                </svg>
                                            </button>
                                            <form method="POST" action="{{ route('backoffice.drip-campaigns.steps.destroy', [$campaign, $step]) }}" class="inline" onsubmit="return confirm('Are you sure you want to delete this step?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-2 text-gray-500 hover:text-red-600 transition-colors"
                                                        title="Delete Step">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Test Email -->
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Send Test Email</h3>
                    @if ($campaign->steps->isNotEmpty())
                        <form method="POST" action="{{ route('backoffice.drip-campaigns.sendTest', $campaign) }}" class="flex gap-4">
                            @csrf
                            <select name="step_id" class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                                @foreach ($campaign->steps as $step)
                                    <option value="{{ $step->id }}">Step {{ $step->sequence_order }}: {{ Str::limit($step->subject, 40) }}</option>
                                @endforeach
                            </select>
                            <input type="email" name="email" placeholder="test@example.com" class="w-64 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500" required>
                            <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors">
                                Send Test
                            </button>
                        </form>
                    @else
                        <p class="text-gray-500 dark:text-gray-400">Add email steps first to send test emails.</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Step Modal -->
        <div x-show="showStepModal"
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 dark:bg-gray-900 dark:bg-opacity-75" @click="closeStepModal()"></div>

                <div class="relative bg-white dark:bg-gray-800 rounded-xl overflow-hidden shadow-xl transform transition-all sm:my-8 w-full max-w-4xl"
                     @click.stop>
                    <form :action="stepFormAction" method="POST" @submit="submitStepForm">
                        @csrf
                        <input type="hidden" name="_method" :value="editingStep ? 'PUT' : 'POST'">

                        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="editingStep ? 'Edit Email Step' : 'Add Email Step'"></h3>
                        </div>

                        <div class="px-6 py-4 space-y-4 max-h-[70vh] overflow-y-auto">
                            <div>
                                <label for="step_subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Subject Line <span class="text-red-500">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="subject"
                                    id="step_subject"
                                    x-model="stepForm.subject"
                                    placeholder="e.g., Welcome to Family Ledger!"
                                    class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    required
                                >
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label for="step_delay_days" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Delay (Days)
                                    </label>
                                    <input
                                        type="number"
                                        name="delay_days"
                                        id="step_delay_days"
                                        x-model="stepForm.delay_days"
                                        min="0"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        required
                                    >
                                </div>
                                <div>
                                    <label for="step_delay_hours" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                        Hours
                                    </label>
                                    <input
                                        type="number"
                                        name="delay_hours"
                                        id="step_delay_hours"
                                        x-model="stepForm.delay_hours"
                                        min="0"
                                        max="23"
                                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                        required
                                    >
                                </div>
                            </div>

                            <div>
                                <label for="quill-editor" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                    Email Body <span class="text-red-500">*</span>
                                </label>
                                <div id="quill-editor" class="bg-white dark:bg-gray-700 rounded-lg" style="height: 350px;"></div>
                                <input type="hidden" name="body" x-ref="bodyInput">
                            </div>
                        </div>

                        <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-4">
                            <button type="button"
                                    @click="closeStepModal()"
                                    class="px-4 py-2 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 font-medium rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                Cancel
                            </button>
                            <button type="submit"
                                    class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg transition-colors">
                                <span x-text="editingStep ? 'Update Step' : 'Add Step'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <style>
        .ql-toolbar.ql-snow {
            border-top-left-radius: 0.5rem;
            border-top-right-radius: 0.5rem;
            border-color: #d1d5db;
        }
        .ql-container.ql-snow {
            border-bottom-left-radius: 0.5rem;
            border-bottom-right-radius: 0.5rem;
            border-color: #d1d5db;
        }
        .dark .ql-toolbar.ql-snow,
        .dark .ql-container.ql-snow {
            border-color: #4b5563;
        }
        .dark .ql-toolbar.ql-snow {
            background-color: #374151;
        }
        .dark .ql-toolbar.ql-snow .ql-stroke {
            stroke: #d1d5db;
        }
        .dark .ql-toolbar.ql-snow .ql-fill {
            fill: #d1d5db;
        }
        .dark .ql-toolbar.ql-snow .ql-picker {
            color: #d1d5db;
        }
        .dark .ql-editor {
            color: #f3f4f6;
        }
        .dark .ql-editor.ql-blank::before {
            color: #9ca3af;
        }
        .ql-editor {
            min-height: 300px;
            font-size: 14px;
        }
    </style>
    @endpush

    @push('scripts')
    @vite('resources/js/vendor/quill.js')
    <script>
        function campaignEditor() {
            return {
                showStepModal: false,
                editingStep: null,
                stepForm: {
                    subject: '',
                    body: '',
                    delay_days: 0,
                    delay_hours: 0
                },
                quill: null,

                get stepFormAction() {
                    if (this.editingStep) {
                        return '/backoffice/drip-campaigns/{{ $campaign->id }}/steps/' + this.editingStep.id;
                    }
                    return '{{ route('backoffice.drip-campaigns.steps.store', $campaign) }}';
                },

                openStepModal(step = null) {
                    this.editingStep = step;
                    if (step) {
                        this.stepForm = {
                            subject: step.subject,
                            body: step.body,
                            delay_days: step.delay_days,
                            delay_hours: step.delay_hours
                        };
                    } else {
                        this.stepForm = {
                            subject: '',
                            body: '',
                            delay_days: 0,
                            delay_hours: 0
                        };
                    }
                    this.showStepModal = true;

                    this.$nextTick(() => {
                        this.initEditor();
                    });
                },

                closeStepModal() {
                    this.showStepModal = false;
                    this.editingStep = null;
                    if (this.quill) {
                        this.quill = null;
                    }
                },

                initEditor() {
                    const self = this;
                    const editorContainer = document.getElementById('quill-editor');

                    // Clear any existing content
                    editorContainer.innerHTML = '';

                    this.quill = new Quill('#quill-editor', {
                        theme: 'snow',
                        placeholder: 'Compose your email content here...',
                        modules: {
                            toolbar: [
                                [{ 'header': [1, 2, 3, false] }],
                                ['bold', 'italic', 'underline', 'strike'],
                                [{ 'color': [] }, { 'background': [] }],
                                [{ 'align': [] }],
                                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                                ['link', 'image'],
                                ['clean']
                            ]
                        }
                    });

                    // Set initial content
                    if (this.stepForm.body) {
                        this.quill.root.innerHTML = this.stepForm.body;
                    }

                    // Update stepForm.body on text change
                    this.quill.on('text-change', function() {
                        self.stepForm.body = self.quill.root.innerHTML;
                    });
                },

                submitStepForm(e) {
                    if (this.quill) {
                        this.stepForm.body = this.quill.root.innerHTML;
                        this.$refs.bodyInput.value = this.stepForm.body;
                    }
                    return true;
                }
            }
        }
    </script>
    @endpush
@endsection
