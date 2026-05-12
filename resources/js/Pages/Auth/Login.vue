<script setup>
import { ref } from 'vue';
import AuthBackgroundToggle from '@/Components/AuthBackgroundToggle.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});
const showPassword = ref(false);

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Log in" />

    <div class="login-page">
        <AuthBackgroundToggle />

        <Link href="/" class="logo">
            <h1>ALGOBYTE</h1>
            <span>PRECISION MONOLITH</span>
        </Link>

        <section class="login-card">
            <h2>Login</h2>
            <p class="subtitle">Access your account</p>

            <div v-if="status" class="status-message">
                {{ status }}
            </div>

            <form @submit.prevent="submit">
                <div class="field">
                    <label for="email">IDENTITY</label>
                    <div class="input-group">
                        <span class="icon">@</span>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="Username or Email"
                            required
                            autofocus
                            autocomplete="username"
                        />
                    </div>
                    <InputError :message="form.errors.email" />
                </div>

                <div class="field">
                    <div class="label-row">
                        <label for="password">KEYPHRASE</label>
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="forgot"
                        >
                            FORGOT?
                        </Link>
                    </div>

                    <div class="input-group">
                        <span class="icon" aria-hidden="true">🔒</span>
                        <input
                            id="password"
                            v-model="form.password"
                            :type="showPassword ? 'text' : 'password'"
                            placeholder="********"
                            required
                            autocomplete="current-password"
                        />
                        <button
                            class="icon eye"
                            type="button"
                            :aria-label="showPassword ? 'Hide password' : 'Show password'"
                            @click="showPassword = !showPassword"
                        >
                            {{ showPassword ? 'HIDE' : 'SHOW' }}
                        </button>
                    </div>
                    <InputError :message="form.errors.password" />
                </div>

                <label class="remember-row">
                    <Checkbox name="remember" v-model:checked="form.remember" />
                    <span>Remember me</span>
                </label>

                <button class="btn" type="submit" :disabled="form.processing">
                    <span v-if="form.processing">LOGGING IN...</span>
                    <span v-else>LOG IN</span>
                </button>
            </form>

            <p class="bottom">
                New to the AlgoByte?
                <Link :href="route('register')">Create Account -></Link>
            </p>
        </section>
    </div>
</template>

<style scoped>
.login-page {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    padding: 92px 20px 32px;
    background: var(--auth-page-bg);
    background-size: var(--auth-page-bg-size);
    font-family: Arial, sans-serif;
}

.logo {
    position: absolute;
    top: 30px;
    left: 40px;
    text-decoration: none;
}

.logo h1 {
    margin: 0;
    color: #087f95;
    font-size: 22px;
    font-weight: 800;
    line-height: 1;
}

.logo span {
    display: block;
    margin-top: 6px;
    color: #557080;
    font-size: 11px;
    letter-spacing: 2px;
}

.login-card {
    width: min(380px, 100%);
    padding: 40px;
    border: 1px solid rgba(111, 227, 255, 0.1);
    border-radius: 12px;
    background: linear-gradient(145deg, #1b2533, #0f1722);
    box-shadow: 0 24px 70px rgba(6, 55, 65, 0.18);
    color: white;
    text-align: center;
}

.login-card h2 {
    margin: 0;
    color: #ffffff;
    font-size: 26px;
    font-weight: 800;
}

.subtitle {
    margin: 8px 0 30px;
    color: #9aa4b2;
    font-size: 14px;
}

.status-message {
    margin-bottom: 18px;
    padding: 10px 12px;
    border: 1px solid rgba(95, 227, 255, 0.2);
    border-radius: 8px;
    background: rgba(0, 234, 255, 0.08);
    color: #8df2ff;
    font-size: 13px;
    text-align: left;
}

.field {
    margin-bottom: 20px;
    text-align: left;
}

label {
    color: #7f8ea3;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
}

.label-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.forgot {
    color: #7f8ea3;
    font-size: 11px;
    font-weight: 700;
    text-decoration: none;
}

.forgot:hover {
    color: #5fe3ff;
}

.input-group {
    display: flex;
    align-items: center;
    margin-top: 6px;
    padding: 10px;
    border: 1px solid transparent;
    border-radius: 6px;
    background: #000;
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

.icon {
    margin: 0 6px;
    color: #7f8ea3;
}

.eye {
    border: 0;
    background: transparent;
    cursor: pointer;
    font-size: 10px;
    font-weight: 800;
    letter-spacing: 1px;
    padding: 0;
}

.eye:hover {
    color: #5fe3ff;
}

.remember-row {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: -4px;
    color: #9aa4b2;
    font-size: 12px;
    letter-spacing: 0;
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
    letter-spacing: 1px;
    transition: 0.3s;
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 255, 255, 0.25);
}

.btn:disabled {
    cursor: not-allowed;
    opacity: 0.65;
}

.bottom {
    margin: 25px 0 0;
    color: #9aa4b2;
    font-size: 13px;
    text-align: center;
}

.bottom a {
    margin-left: 5px;
    color: #5fe3ff;
    font-weight: 700;
    text-decoration: none;
}

@media (max-width: 520px) {
    .logo {
        left: 24px;
    }

    .login-card {
        padding: 32px 22px;
    }
}
</style>
