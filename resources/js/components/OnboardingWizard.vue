<template>
    <div class="card bg-base-100 shadow-xl">
        <div class="card-body">
            <!-- Step 1: Goals -->
            <div v-if="currentStep === 1">
                <h2 class="card-title text-2xl mb-2">Welcome! Let's get started</h2>
                <p class="text-base-content/60 mb-6">What's your primary goal for using this app?</p>
                <p class="text-sm text-base-content/50 mb-4">Select all that apply</p>

                <div class="space-y-3">
                    <label
                        v-for="(goal, key) in goals"
                        :key="key"
                        class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors"
                        :class="selectedGoals.includes(key) ? 'border-primary bg-primary/5' : 'border-base-300 hover:border-primary/50'"
                    >
                        <input
                            type="checkbox"
                            :value="key"
                            v-model="selectedGoals"
                            class="checkbox checkbox-primary mt-1"
                        />
                        <div class="ml-3">
                            <div class="font-medium">{{ goal.title }}</div>
                            <div class="text-sm text-base-content/60">{{ goal.description }}</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Step 2: Household Setup -->
            <div v-if="currentStep === 2">
                <h2 class="card-title text-2xl mb-2">Set up your household</h2>
                <p class="text-base-content/60 mb-6">Define your family unit and preferences</p>

                <div class="space-y-4">
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Household name *</span>
                        </label>
                        <input
                            type="text"
                            v-model="householdName"
                            placeholder="e.g., Philip Family, or Alex and Jamie Co-parenting"
                            class="input input-bordered w-full"
                            required
                        />
                        <label class="label">
                            <span class="label-text-alt text-base-content/50">This is your first family circle</span>
                        </label>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Country / Region *</span>
                        </label>
                        <select
                            ref="countrySelect"
                            @change="onCountryChange"
                            :data-select="countrySelectConfig"
                            class="hidden"
                        >
                            <option value="">Select country</option>
                            <option v-for="(name, code) in countries" :key="code" :value="code" :selected="selectedCountry === code">{{ name }}</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Timezone *</span>
                        </label>
                        <select
                            ref="timezoneSelect"
                            @change="onTimezoneChange"
                            :data-select="timezoneSelectConfig"
                            class="hidden"
                        >
                            <option value="">Select timezone</option>
                            <option v-for="zone in flatTimezones" :key="zone" :value="zone" :selected="selectedTimezone === zone">{{ zone }}</option>
                        </select>
                    </div>

                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Family type (optional)</span>
                        </label>
                        <select
                            ref="familyTypeSelect"
                            @change="onFamilyTypeChange"
                            :data-select="familyTypeSelectConfig"
                            class="hidden"
                        >
                            <option value="">Select family type</option>
                            <option v-for="(name, key) in familyTypes" :key="key" :value="key" :selected="selectedFamilyType === key">{{ name }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Step 3: Role Selection -->
            <div v-if="currentStep === 3">
                <h2 class="card-title text-2xl mb-2">What's your role?</h2>
                <p class="text-base-content/60 mb-6">This helps us set appropriate permissions and features</p>

                <div class="space-y-3">
                    <label
                        v-for="(role, key) in roles"
                        :key="key"
                        class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors"
                        :class="selectedRole === key ? 'border-primary bg-primary/5' : 'border-base-300 hover:border-primary/50'"
                    >
                        <input
                            type="radio"
                            :value="key"
                            v-model="selectedRole"
                            class="radio radio-primary mt-1"
                        />
                        <div class="ml-3">
                            <div class="font-medium">{{ role.title }}</div>
                            <div class="text-sm text-base-content/60">{{ role.description }}</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Step 4: Add Family Members -->
            <div v-if="currentStep === 4">
                <h2 class="card-title text-2xl mb-2">Add key people</h2>
                <p class="text-base-content/60 mb-6">You can add children or family members later. Start with one person.</p>

                <div v-for="(member, index) in familyMembers" :key="index" class="mb-4 p-4 border border-base-300 rounded-lg">
                    <div class="flex justify-between items-center mb-3">
                        <span class="font-medium">Person {{ index + 1 }}</span>
                        <button v-if="familyMembers.length > 1" @click="removeMember(index)" class="btn btn-ghost btn-sm btn-circle">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="grid gap-3">
                        <input type="email" v-model="member.email" placeholder="Email address" class="input input-bordered w-full" />
                        <input type="tel" v-model="member.phone" placeholder="Phone number (optional)" class="input input-bordered w-full" />
                        <select v-model="member.role" class="select select-bordered w-full">
                            <option value="">Select role</option>
                            <option v-for="(role, key) in roles" :key="key" :value="key">{{ role.title }}</option>
                        </select>
                        <input type="text" v-model="member.relationship" placeholder="Relationship (optional)" class="input input-bordered w-full" />
                    </div>
                </div>

                <div class="flex gap-3 mt-4">
                    <button @click="addMember" class="btn btn-outline btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Add person
                    </button>
                    <button @click="skipMembers" class="btn btn-ghost btn-sm">I'll add people later</button>
                </div>
            </div>

            <!-- Step 5: Quick Setup -->
            <div v-if="currentStep === 5">
                <h2 class="card-title text-2xl mb-2">What do you want to set up first?</h2>
                <p class="text-base-content/60 mb-6">Select one or more to get started quickly</p>

                <div class="grid gap-3 sm:grid-cols-2">
                    <label
                        v-for="(item, key) in quickSetup"
                        :key="key"
                        class="flex items-start p-4 border rounded-lg cursor-pointer transition-colors"
                        :class="selectedQuickSetup.includes(key) ? 'border-primary bg-primary/5' : 'border-base-300 hover:border-primary/50'"
                    >
                        <input
                            type="checkbox"
                            :value="key"
                            v-model="selectedQuickSetup"
                            class="checkbox checkbox-primary mt-1"
                        />
                        <div class="ml-3">
                            <div class="font-medium text-sm">{{ item.title }}</div>
                            <div class="text-xs text-base-content/60">{{ item.description }}</div>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Step 6: Security -->
            <div v-if="currentStep === 6">
                <h2 class="card-title text-2xl mb-2">Security & privacy</h2>
                <p class="text-base-content/60 mb-6">Your data is encrypted and only shared with people you approve</p>

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
                        <input type="checkbox" v-model="emailNotifications" class="checkbox checkbox-primary" />
                        <div>
                            <div class="font-medium">Email notifications</div>
                            <div class="text-sm text-base-content/60">Get updates about important events and changes</div>
                        </div>
                    </label>

                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" v-model="enable2FA" class="checkbox checkbox-primary" />
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
            </div>

            <!-- Error message -->
            <div v-if="error" class="alert alert-error mt-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>{{ error }}</span>
            </div>

            <!-- Navigation buttons -->
            <div class="card-actions justify-between mt-8">
                <button
                    v-if="currentStep > 1"
                    @click="goBack"
                    class="btn btn-ghost"
                    :disabled="loading"
                >
                    Back
                </button>
                <div v-else></div>

                <button
                    @click="nextStep"
                    class="btn btn-primary"
                    :disabled="loading || !canProceed"
                    :class="{ 'loading': loading }"
                >
                    {{ currentStep === 6 ? 'Complete Setup' : 'Continue' }}
                </button>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'OnboardingWizard',
    props: {
        initialStep: { type: Number, default: 1 },
        totalSteps: { type: Number, default: 6 },
        goals: { type: Object, required: true },
        countries: { type: Object, required: true },
        familyTypes: { type: Object, required: true },
        roles: { type: Object, required: true },
        quickSetup: { type: Object, required: true },
        timezones: { type: Object, required: true },
        tenant: { type: Object, required: true },
        user: { type: Object, required: true },
    },
    data() {
        return {
            currentStep: this.initialStep,
            loading: false,
            error: null,
            // Step 1
            selectedGoals: this.tenant.goals || [],
            // Step 2
            householdName: this.tenant.name || '',
            selectedCountry: this.tenant.country || '',
            selectedTimezone: this.tenant.timezone || '',
            selectedFamilyType: this.tenant.family_type || '',
            // Step 3
            selectedRole: this.user.role || 'parent',
            // Step 4
            familyMembers: [{ email: '', phone: '', role: '', relationship: '' }],
            // Step 5
            selectedQuickSetup: this.tenant.quick_setup || [],
            // Step 6
            emailNotifications: true,
            enable2FA: false,
        };
    },
    computed: {
        canProceed() {
            switch (this.currentStep) {
                case 1:
                    return this.selectedGoals.length > 0;
                case 2:
                    return this.householdName && this.selectedCountry && this.selectedTimezone;
                case 3:
                    return this.selectedRole;
                case 4:
                    return true; // Optional step
                case 5:
                    return this.selectedQuickSetup.length > 0;
                case 6:
                    return true;
                default:
                    return false;
            }
        },
        flatTimezones() {
            const flat = [];
            for (const region in this.timezones) {
                for (const zone of this.timezones[region]) {
                    flat.push(zone);
                }
            }
            return flat;
        },
        countrySelectConfig() {
            return JSON.stringify({
                placeholder: "Select country",
                toggleTag: "<button type=\"button\" aria-expanded=\"false\"></button>",
                toggleClasses: "advance-select-toggle",
                hasSearch: true,
                searchPlaceholder: "Search countries...",
                dropdownClasses: "advance-select-menu max-h-52 overflow-y-auto",
                optionClasses: "advance-select-option selected:select-active",
                optionTemplate: "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                extraMarkup: "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/70 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
            });
        },
        timezoneSelectConfig() {
            return JSON.stringify({
                placeholder: "Select timezone",
                toggleTag: "<button type=\"button\" aria-expanded=\"false\"></button>",
                toggleClasses: "advance-select-toggle",
                hasSearch: true,
                searchPlaceholder: "Search timezones...",
                dropdownClasses: "advance-select-menu max-h-52 overflow-y-auto",
                optionClasses: "advance-select-option selected:select-active",
                optionTemplate: "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                extraMarkup: "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/70 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
            });
        },
        familyTypeSelectConfig() {
            return JSON.stringify({
                placeholder: "Select family type",
                toggleTag: "<button type=\"button\" aria-expanded=\"false\"></button>",
                toggleClasses: "advance-select-toggle",
                hasSearch: false,
                dropdownClasses: "advance-select-menu max-h-52 overflow-y-auto",
                optionClasses: "advance-select-option selected:select-active",
                optionTemplate: "<div class=\"flex justify-between items-center w-full\"><span data-title></span><span class=\"icon-[tabler--check] shrink-0 size-4 text-primary hidden selected:block\"></span></div>",
                extraMarkup: "<span class=\"icon-[tabler--caret-up-down] shrink-0 size-4 text-base-content/70 absolute top-1/2 end-3 -translate-y-1/2\"></span>"
            });
        },
    },
    methods: {
        async nextStep() {
            this.loading = true;
            this.error = null;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            try {
                let endpoint = `/onboarding/step${this.currentStep}`;
                let body = this.getStepData();

                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                    body: JSON.stringify(body),
                });

                const data = await response.json();

                if (response.ok) {
                    if (data.redirect) {
                        window.location.href = data.redirect;
                    } else if (data.step) {
                        this.currentStep = data.step;
                    }
                } else {
                    this.error = data.error || data.message || 'Something went wrong';
                }
            } catch (err) {
                console.error('Onboarding error:', err);
                this.error = 'Network error. Please try again.';
            } finally {
                this.loading = false;
            }
        },
        async goBack() {
            this.loading = true;
            this.error = null;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

            try {
                const response = await fetch('/onboarding/back', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                    },
                });

                const data = await response.json();
                if (data.step) {
                    this.currentStep = data.step;
                }
            } catch (err) {
                console.error('Error going back:', err);
            } finally {
                this.loading = false;
            }
        },
        getStepData() {
            switch (this.currentStep) {
                case 1:
                    return { goals: this.selectedGoals };
                case 2:
                    return {
                        name: this.householdName,
                        country: this.selectedCountry,
                        timezone: this.selectedTimezone,
                        family_type: this.selectedFamilyType,
                    };
                case 3:
                    return { role: this.selectedRole };
                case 4:
                    const validMembers = this.familyMembers.filter(m => m.email && m.role);
                    return { members: validMembers };
                case 5:
                    return { quick_setup: this.selectedQuickSetup };
                case 6:
                    return {
                        email_notifications: this.emailNotifications,
                        enable_2fa: this.enable2FA,
                    };
                default:
                    return {};
            }
        },
        addMember() {
            this.familyMembers.push({ email: '', phone: '', role: '', relationship: '' });
        },
        removeMember(index) {
            this.familyMembers.splice(index, 1);
        },
        skipMembers() {
            this.familyMembers = [];
            this.nextStep();
        },
        onCountryChange(event) {
            this.selectedCountry = event.target.value;
        },
        onTimezoneChange(event) {
            this.selectedTimezone = event.target.value;
        },
        onFamilyTypeChange(event) {
            this.selectedFamilyType = event.target.value;
        },
        initFlyonUI() {
            // Reinitialize FlyonUI components after Vue renders
            this.$nextTick(() => {
                if (window.HSStaticMethods) {
                    window.HSStaticMethods.autoInit();
                }
            });
        },
    },
    watch: {
        currentStep(newStep) {
            if (newStep === 2) {
                this.initFlyonUI();
            }
        },
    },
    mounted() {
        if (this.currentStep === 2) {
            this.initFlyonUI();
        }
    },
};
</script>
