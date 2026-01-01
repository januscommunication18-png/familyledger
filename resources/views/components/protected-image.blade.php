@props([
    'src',
    'alt' => 'Document image',
    'class' => '',
    'containerClass' => '',
])

@php
    $isVerified = session('image_verified', false);
@endphp

<div class="protected-image-container {{ $containerClass }} {{ $isVerified ? 'verified' : '' }}" onclick="handleProtectedImageClick(event, this, '{{ $src }}')">
    <img
        src="{{ $src }}"
        alt="{{ $alt }}"
        class="protected-image {{ $class }} {{ $isVerified ? 'blur-none' : '' }}"
        data-src="{{ $src }}"
    >
    @unless($isVerified)
        <div class="protected-image-overlay">
            <span class="icon-[tabler--lock] size-8 mb-2"></span>
            <span class="text-sm font-medium">Click to verify & view</span>
        </div>
    @endunless
</div>

@once
@push('scripts')
<script>
function handleProtectedImageClick(event, container, imageSrc) {
    // Prevent event from bubbling to parent (like file upload labels)
    event.preventDefault();
    event.stopPropagation();

    if (window.ImageVerification && window.ImageVerification.isVerified()) {
        // Already verified - could open in lightbox or just do nothing
        return;
    }

    // Not verified - show verification modal
    window.ImageVerification.requireVerification(function() {
        // After verification, mark this container and all others as verified
        document.querySelectorAll('.protected-image-container').forEach(el => {
            el.classList.add('verified');
        });
    });
}
</script>
@endpush
@endonce
