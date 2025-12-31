<!-- Step 6: Security & Finish -->
<h2 class="card-title text-2xl mb-2">Security & privacy</h2>
<p class="text-base-content/60 mb-6">Your data is encrypted and only shared with people you approve</p>

<form action="/onboarding/step6" method="POST">
    @csrf

    <div class="space-y-4 mb-6">
        <div class="flex items-start gap-3 p-4 bg-base-200 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
            </svg>
            <div>
                <div class="font-medium">End-to-end encryption</div>
                <div class="text-sm text-base-content/60">Your sensitive documents and data are encrypted at rest and in transit</div>
            </div>
        </div>

        <div class="flex items-start gap-3 p-4 bg-base-200 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>
            <div>
                <div class="font-medium">Role-based permissions</div>
                <div class="text-sm text-base-content/60">Control exactly who can view and edit different types of information</div>
            </div>
        </div>

        <div class="flex items-start gap-3 p-4 bg-base-200 rounded-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-success flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z" />
            </svg>
            <div>
                <div class="font-medium">Emergency access</div>
                <div class="text-sm text-base-content/60">Designate trusted contacts who can access critical information when needed</div>
            </div>
        </div>
    </div>

    <div class="divider"></div>

    <div class="space-y-3">
        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="email_notifications" value="1" class="checkbox checkbox-primary" checked>
            <div>
                <div class="font-medium">Email notifications</div>
                <div class="text-sm text-base-content/60">Get updates about important events and changes</div>
            </div>
        </label>

        <label class="flex items-center gap-3 cursor-pointer">
            <input type="checkbox" name="enable_2fa" value="1" class="checkbox checkbox-primary">
            <div>
                <div class="font-medium">Two-factor authentication</div>
                <div class="text-sm text-base-content/60">Recommended for extra security</div>
            </div>
        </label>
    </div>

    <p class="text-xs text-base-content/50 mt-6">
        By continuing, you agree to our <a href="/terms" class="link link-primary">Terms of Service</a> and
        <a href="/privacy" class="link link-primary">Privacy Policy</a>. We never sell your data.
    </p>

    <div class="card-actions justify-between mt-8">
        <a href="javascript:void(0)" onclick="document.getElementById('back-form').submit()" class="btn btn-ghost">Back</a>
        <button type="submit" class="btn btn-primary">Complete Setup</button>
    </div>
</form>

<form id="back-form" action="/onboarding/back" method="POST" class="hidden">@csrf</form>
