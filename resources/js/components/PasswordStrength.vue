<template>
    <div class="mt-2">
        <div class="flex gap-1">
            <div
                v-for="i in 4"
                :key="i"
                class="h-1 flex-1 rounded-full transition-colors"
                :class="i <= strength ? strengthColor : 'bg-base-300'"
            ></div>
        </div>
        <p class="text-xs mt-1" :class="strengthTextColor">{{ strengthText }}</p>
    </div>
</template>

<script>
export default {
    name: 'PasswordStrength',
    props: {
        password: {
            type: String,
            default: ''
        }
    },
    computed: {
        strength() {
            if (!this.password) return 0;
            let score = 0;
            if (this.password.length >= 8) score++;
            if (this.password.length >= 12) score++;
            if (/[A-Z]/.test(this.password) && /[a-z]/.test(this.password)) score++;
            if (/\d/.test(this.password) && /[^A-Za-z0-9]/.test(this.password)) score++;
            return score;
        },
        strengthText() {
            const texts = ['', 'Weak', 'Fair', 'Good', 'Strong'];
            return texts[this.strength] || '';
        },
        strengthColor() {
            const colors = ['', 'bg-error', 'bg-warning', 'bg-info', 'bg-success'];
            return colors[this.strength] || 'bg-base-300';
        },
        strengthTextColor() {
            const colors = ['', 'text-error', 'text-warning', 'text-info', 'text-success'];
            return colors[this.strength] || 'text-base-content/60';
        }
    }
}
</script>
