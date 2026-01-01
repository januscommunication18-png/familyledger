@props([
    'href',
    'class' => '',
    'title' => 'Download',
])

@php
    $isVerified = session('image_verified', false);
@endphp

@if($isVerified)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</a>
@else
    <a href="#" onclick="handleProtectedDownload(event, '{{ $href }}')" {{ $attributes->merge(['class' => $class]) }}>{{ $slot }}</a>
@endif

@once
@push('scripts')
<script>
function handleProtectedDownload(event, downloadUrl) {
    event.preventDefault();

    if (window.ImageVerification && window.ImageVerification.isVerified()) {
        // Already verified - proceed with download
        window.location.href = downloadUrl;
        return;
    }

    // Not verified - show verification modal
    window.ImageVerification.requireVerification(function() {
        // After verification, mark all protected containers as verified and start download
        document.querySelectorAll('.protected-image-container').forEach(el => {
            el.classList.add('verified');
        });
        window.location.href = downloadUrl;
    });
}
</script>
@endpush
@endonce
