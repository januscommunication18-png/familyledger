<!-- Step 5: Quick Setup -->
<h2 class="card-title text-2xl mb-2">What do you want to set up first?</h2>
<p class="text-base-content/60 mb-6">Select one or more to get started quickly</p>

<form action="/onboarding/step5" method="POST">
    @csrf

    @error('quick_setup')
        <div class="alert alert-error mb-4">{{ $message }}</div>
    @enderror

    <div class="grid gap-3 sm:grid-cols-2">
        @foreach($quickSetup as $key => $item)
            <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <input type="checkbox" name="quick_setup[]" value="{{ $key }}"
                       class="checkbox checkbox-primary mt-1"
                       {{ in_array($key, $tenant['quick_setup'] ?? []) ? 'checked' : '' }}>
                <div class="ml-3">
                    <div class="font-medium text-sm">{{ $item['title'] }}</div>
                    <div class="text-xs text-base-content/60">{{ $item['description'] }}</div>
                </div>
            </label>
        @endforeach
    </div>

    <div class="card-actions justify-between mt-8">
        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
        <button type="submit" class="btn btn-primary">Continue</button>
    </div>
</form>

<form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>
