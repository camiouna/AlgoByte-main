<script setup>
import { computed } from 'vue';
import AuthBackgroundToggle from '@/Components/AuthBackgroundToggle.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps({
    email: {
        type: String,
        required: true,
    },
    token: {
        type: String,
        required: true,
    },
});

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const tokenPreview = computed(() =>
    props.token
        .replace(/[^a-zA-Z0-9]/g, '')
        .slice(0, 6)
        .padEnd(6, '*')
        .split(''),
);

const submit = () => {
    form.post(route('password.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Reset Password" />

    <div class="page">
        <AuthBackgroundToggle />

        <Link href="/" class="logo">
            <h1>AlgoByte</h1>
        </Link>

        <div class="top-icons" aria-hidden="true">
            <span>🛡️</span>
            <span>?</span>
        </div>

        <section class="card">
            <h2>Verify Identity</h2>
            <p class="subtitle">
                Enter a new password for {{ form.email }}
            </p>

            <div class="code-inputs" aria-label="Reset token preview">
                <input
                    v-for="(character, index) in tokenPreview"
                    :key="index"
                    :value="character"
                    maxlength="1"
                    readonly
                />
            </div>

            <form @submit.prevent="submit">
                <InputError class="form-error" :message="form.errors.email" />
                <InputError class="form-error" :message="form.errors.token" />

                <div class="field">
                    <label for="password">NEW PASSWORD</label>
                    <div class="input-group">
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="********"
                            required
                            autocomplete="new-password"
                        />
                        <span aria-hidden="true">🔒</span>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <div class="field">
                    <label for="password_confirmation">CONFIRM PASSWORD</label>
                    <div class="input-group">
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="********"
                            required
                            autocomplete="new-password"
                        />
                        <span aria-hidden="true">✓</span>
                    </div>
                    <InputError :message="form.errors.password_confirmation" />
                </div>

                <button class="btn" type="submit" :disabled="form.processing">
                    <span v-if="form.processing">UPDATING...</span>
                    <span v-else>UPDATE CREDENTIALS</span>
                </button>
            </form>

            <Link :href="route('password.request')" class="resend">
                ↻ Resend code
            </Link>
        </section>
    </div>
</template>

<style scoped>
.page {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    padding: 86px 20px 32px;
    background: var(--auth-page-bg);
    background-size: var(--auth-page-bg-size);
    font-family: Arial, sans-serif;
}

.logo {
    position: absolute;
    top: 25px;
    left: 35px;
    color: #087f95;
    font-weight: 800;
    text-decoration: none;
}

.logo h1 {
    margin: 0;
    font-size: 22px;
    line-height: 1;
}

.top-icons {
    position: absolute;
    top: 25px;
    right: 35px;
    display: flex;
    gap: 15px;
    color: #557080;
    font-size: 18px;
}

.card {
    width: min(380px, 100%);
    padding: 40px;
    border: 1px solid rgba(111, 227, 255, 0.1);
    border-radius: 12px;
    background: linear-gradient(145deg, #1b2533, #0f1722);
    box-shadow: 0 24px 70px rgba(6, 55, 65, 0.18);
    color: white;
    text-align: center;
}

.card h2 {
    margin: 0 0 10px;
    color: #ffffff;
    font-size: 26px;
    font-weight: 800;
}

.subtitle {
    margin: 0 0 25px;
    color: #9aa4b2;
    font-size: 14px;
    line-height: 1.6;
    overflow-wrap: anywhere;
}

.code-inputs {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-bottom: 25px;
}

.code-inputs input {
    width: 45px;
    height: 50px;
    border: none;
    border-radius: 6px;
    background: #000;
    color: white;
    font-size: 18px;
    text-align: center;
}

.field {
    margin-bottom: 18px;
    text-align: left;
}

label {
    color: #7f8ea3;
    font-size: 11px;
    font-weight: 700;
}

.input-group {
    display: flex;
    align-items: center;
    margin-top: 6px;
    padding: 10px;
    border: 1px solid transparent;
    border-radius: 6px;
    background: black;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.input-group:focus-within {
    border-color: rgba(111, 227, 255, 0.55);
    box-shadow: 0 0 0 3px rgba(0, 234, 255, 0.12);
}

.input-group input {
    flex: 1;
    min-width: 0;
    border: none;
    background: transparent;
    color: white;
    outline: none;
}

.input-group input::placeholder {
    color: #596475;
}

.input-group span {
    color: #7f8ea3;
}

.btn {
    width: 100%;
    margin-top: 15px;
    padding: 14px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(90deg, #59c6d2, #00eaff);
    color: #063741;
    cursor: pointer;
    font-weight: 800;
    transition: 0.3s;
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 255, 255, 0.3);
}

.btn:disabled {
    cursor: not-allowed;
    opacity: 0.65;
}

.resend {
    display: inline-block;
    margin-top: 20px;
    color: #7f8ea3;
    cursor: pointer;
    font-size: 13px;
    text-decoration: none;
}

.resend:hover {
    color: #5fe3ff;
}

.form-error {
    margin-bottom: 12px;
    text-align: left;
}

@media (max-width: 520px) {
    .card {
        padding: 32px 22px;
    }

    .code-inputs {
        gap: 7px;
    }

    .code-inputs input {
        width: 38px;
        height: 46px;
    }
}
</style>
