<!-- Step 4: Add Family Members -->
<h2 class="card-title text-2xl mb-2">Invite family members</h2>
<p class="text-base-content/60 mb-6">Add people you want to share with. You can skip this and add people later.</p>

<form action="/onboarding/step4" method="POST">
    @csrf

    <div id="members-container">
        <div class="member-row mb-4 p-4 border border-base-300 rounded-lg">
            <div class="flex justify-between items-center mb-3">
                <span class="font-medium">Person 1</span>
            </div>
            <div class="grid gap-3">
                <input type="email" name="members[0][email]" placeholder="Email address" class="input input-bordered w-full">
                <input type="tel" name="members[0][phone]" placeholder="Phone number (optional)" class="input input-bordered w-full">
                <select name="members[0][role]" class="select select-bordered w-full">
                    <option value="">Select role</option>
                    @foreach($roles as $key => $role)
                        <option value="{{ $key }}">{{ $role['title'] }}</option>
                    @endforeach
                </select>
                <input type="text" name="members[0][relationship]" placeholder="Relationship (optional)" class="input input-bordered w-full">
            </div>
        </div>
    </div>

    <div class="flex gap-3 mt-4">
        <button type="button" onclick="addMember()" class="btn btn-outline btn-sm">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add person
        </button>
    </div>

    <div class="card-actions justify-between mt-8">
        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
        <div class="flex gap-2">
            <button type="submit" name="skip" value="1" class="btn btn-ghost">Skip for now</button>
            <button type="submit" class="btn btn-primary">Continue</button>
        </div>
    </div>
</form>

<form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>

<script>
    let memberIndex = 1;
    function addMember() {
        const container = document.getElementById('members-container');
        const html = `
            <div class="member-row mb-4 p-4 border border-base-300 rounded-lg">
                <div class="flex justify-between items-center mb-3">
                    <span class="font-medium">Person ${memberIndex + 1}</span>
                    <button type="button" onclick="this.closest('.member-row').remove()" class="btn btn-ghost btn-sm btn-circle">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="grid gap-3">
                    <input type="email" name="members[${memberIndex}][email]" placeholder="Email address" class="input input-bordered w-full">
                    <input type="tel" name="members[${memberIndex}][phone]" placeholder="Phone number (optional)" class="input input-bordered w-full">
                    <select name="members[${memberIndex}][role]" class="select select-bordered w-full">
                        <option value="">Select role</option>
                        @foreach($roles as $key => $role)
                            <option value="{{ $key }}">{{ $role['title'] }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="members[${memberIndex}][relationship]" placeholder="Relationship (optional)" class="input input-bordered w-full">
                </div>
            </div>`;
        container.insertAdjacentHTML('beforeend', html);
        memberIndex++;
    }
</script>
