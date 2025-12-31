<!-- Step 3: Role Selection -->
<h2 class="card-title text-2xl mb-2">What's your role?</h2>
<p class="text-base-content/60 mb-6">This helps us set appropriate permissions and features</p>

<form action="/onboarding/step3" method="POST">
    @csrf

    @error('role')
        <div class="alert alert-error mb-4">{{ $message }}</div>
    @enderror

    <div class="space-y-3">
        @foreach($roles as $key => $role)
            <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <input type="radio" name="role" value="{{ $key }}"
                       class="radio radio-primary mt-1"
                       {{ old('role', $user['role'] ?? 'parent') === $key ? 'checked' : '' }} required>
                <div class="ml-3">
                    <div class="font-medium">{{ $role['title'] }}</div>
                    <div class="text-sm text-base-content/60">{{ $role['description'] }}</div>
                </div>
            </label>
        @endforeach
    </div>

    <div class="card-actions justify-between mt-8">
        <a href="javascript:history.back()" onclick="document.getElementById('back-form').submit(); return false;" class="btn btn-ghost">Back</a>
        <button type="submit" class="btn btn-primary">Continue</button>
    </div>
</form>

<form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>
