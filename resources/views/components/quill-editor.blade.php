@props([
    'name' => 'content',
    'value' => '',
    'placeholder' => 'Enter your content...',
    'height' => '200px',
    'toolbar' => 'basic'
])

@php
    $editorId = 'quill-' . $name . '-' . uniqid();
    $inputId = $name . '-input';

    // Define toolbar configurations
    $toolbars = [
        'basic' => [
            ['bold', 'italic', 'underline'],
            [['list' => 'ordered'], ['list' => 'bullet']],
            ['clean']
        ],
        'standard' => [
            ['bold', 'italic', 'underline', 'strike'],
            [['list' => 'ordered'], ['list' => 'bullet']],
            [['indent' => '-1'], ['indent' => '+1']],
            ['link'],
            ['clean']
        ],
        'full' => [
            [['header' => [1, 2, 3, false]]],
            ['bold', 'italic', 'underline', 'strike'],
            [['color' => []], ['background' => []]],
            [['list' => 'ordered'], ['list' => 'bullet']],
            [['indent' => '-1'], ['indent' => '+1']],
            [['align' => []]],
            ['link', 'image'],
            ['clean']
        ],
    ];

    $selectedToolbar = $toolbars[$toolbar] ?? $toolbars['basic'];
@endphp

<div class="quill-editor-wrapper" data-editor-id="{{ $editorId }}" data-input-id="{{ $inputId }}">
    <input type="hidden" name="{{ $name }}" id="{{ $inputId }}" value="{{ old($name, $value) }}">
    <div id="{{ $editorId }}" style="min-height: {{ $height }};"></div>
</div>

@once
@push('styles')
<style>
    .quill-editor-wrapper .ql-container {
        font-size: 14px;
    }
    .quill-editor-wrapper .ql-editor {
        min-height: inherit;
    }
    .quill-editor-wrapper .ql-toolbar.ql-snow {
        border-radius: 0.5rem 0.5rem 0 0;
        border-color: oklch(var(--bc) / 0.2);
        background: oklch(var(--b2) / 0.5);
    }
    .quill-editor-wrapper .ql-container.ql-snow {
        border-radius: 0 0 0.5rem 0.5rem;
        border-color: oklch(var(--bc) / 0.2);
    }
    .quill-editor-wrapper .ql-editor.ql-blank::before {
        color: oklch(var(--bc) / 0.4);
        font-style: normal;
    }
</style>
@endpush

@push('scripts')
@vite('resources/js/vendor/quill.js')
<script>
window.initQuillEditors = window.initQuillEditors || [];
</script>
@endpush
@endonce

@push('scripts')
<script>
(function() {
    const editorId = '{{ $editorId }}';
    const inputId = '{{ $inputId }}';
    const placeholder = @json($placeholder);
    const toolbarConfig = @json($selectedToolbar);

    function initEditor() {
        const editorEl = document.getElementById(editorId);
        const inputEl = document.getElementById(inputId);

        if (!editorEl || editorEl.classList.contains('ql-container')) return;

        const quill = new Quill('#' + editorId, {
            theme: 'snow',
            placeholder: placeholder,
            modules: {
                toolbar: toolbarConfig
            }
        });

        // Set initial content
        if (inputEl.value) {
            quill.root.innerHTML = inputEl.value;
        }

        // Update hidden input on text change
        quill.on('text-change', function() {
            inputEl.value = quill.root.innerHTML;
        });

        // Store reference for form submission
        window.initQuillEditors.push({ id: editorId, quill: quill, input: inputEl });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditor);
    } else {
        // Small delay to ensure Quill is loaded
        setTimeout(initEditor, 10);
    }
})();
</script>
@endpush
