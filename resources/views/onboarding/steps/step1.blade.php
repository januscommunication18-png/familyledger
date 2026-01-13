<!-- Step 1: Goals -->
<h2 class="card-title text-2xl mb-2">Welcome! Let's get started</h2>
<p class="text-base-content/60 mb-6">What's your primary goal for using this app?</p>
<p class="text-sm text-base-content/50 mb-4">Select all that apply</p>

@error('goals')
    <div class="alert alert-error mb-4">{{ $message }}</div>
@enderror

<form action="/onboarding/step1" method="POST" id="step1-form">
    @csrf
    <div class="space-y-3">
        @foreach($goals as $key => $goal)
            <label class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors hover:border-primary/50 has-[:checked]:border-primary has-[:checked]:bg-primary/5">
                <input type="checkbox" name="goals[]" value="{{ $key }}"
                       class="checkbox checkbox-primary mt-1"
                       {{ in_array($key, $tenant['goals'] ?? []) ? 'checked' : '' }}>
                <div class="ml-3">
                    <div class="font-medium">{{ $goal['title'] }}</div>
                    <div class="text-sm text-base-content/60">{{ $goal['description'] }}</div>
                </div>
            </label>
        @endforeach
    </div>
</form>

<div class="card-actions justify-between mt-8">
    <form action="{{ route('onboarding.skip') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-ghost">Skip for now</button>
    </form>
    <button type="submit" form="step1-form" class="btn btn-primary">Continue</button>
</div>
