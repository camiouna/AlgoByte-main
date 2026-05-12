<script setup>
import AuthBackgroundToggle from '@/Components/AuthBackgroundToggle.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    username: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Register" />

    <div class="register-page">
        <AuthBackgroundToggle />

        <Link href="/" class="logo">ALGO BYTE</Link>

        <section class="register-card">
            <div class="icon" aria-hidden="true">PC</div>

            <h2>Become a member of Algobyte</h2>
            <p class="subtitle">
                Enter your name, email, and password to create a new account
            </p>

            <form @submit.prevent="submit">
                <div class="field-block">
                    <label for="username">Username</label>
                    <input
                        id="username"
                        v-model="form.username"
                        type="text"
                        placeholder="AlgoByte Dev"
                        required
                        autofocus
                        autocomplete="name"
                    />
                    <InputError :message="form.errors.username" />
                </div>

                <div class="field-block">
                    <label for="email">Email Address</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        placeholder="dev@algobyte.io"
                        required
                        autocomplete="username"
                    />
                    <InputError :message="form.errors.email" />
                </div>

                <div class="row">
                    <div class="field">
                        <label for="password">Password</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="********"
                            required
                            autocomplete="new-password"
                        />
                        <InputError :message="form.errors.password" />
                    </div>

                    <div class="field">
                        <label for="password_confirmation">Confirm</label>
                        <input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="********"
                            required
                            autocomplete="new-password"
                        />
                        <InputError :message="form.errors.password_confirmation" />
                    </div>
                </div>

                <button class="btn" type="submit" :disabled="form.processing">
                    <span v-if="form.processing">CREATING...</span>
                    <span v-else>CREATE ACCOUNT -></span>
                </button>
            </form>

            <p class="login-text">
                Already have an account?
                <Link :href="route('login')">Log in</Link>
            </p>
        </section>
    </div>
</template>

<style scoped>
.register-page {
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
    padding: 84px 20px 32px;
    background: var(--auth-page-bg);
    background-size: var(--auth-page-bg-size);
    font-family: Arial, sans-serif;
}

.logo {
    position: absolute;
    top: 25px;
    left: 30px;
    color: #087f95;
    font-size: 20px;
    font-weight: 700;
    text-decoration: none;
}

.register-card {
    width: min(420px, 100%);
    padding: 40px;
    border: 1px solid rgba(111, 227, 255, 0.12);
    border-radius: 12px;
    background: linear-gradient(145deg, #1b2533, #0f1722);
    box-shadow: 0 24px 70px rgba(6, 55, 65, 0.18);
    color: white;
    text-align: center;
}

.icon {
    width: 42px;
    height: 42px;
    display: inline-grid;
    place-items: center;
    margin-bottom: 15px;
    border: 1px solid rgba(111, 227, 255, 0.18);
    border-radius: 10px;
    color: #7be3ef;
    font-size: 13px;
    font-weight: 800;
}

.register-card h2 {
    margin: 0 0 10px;
    color: #ffffff;
    font-size: 24px;
    font-weight: 800;
    line-height: 1.25;
}

.subtitle {
    margin: 0 0 25px;
    color: #9aa4b2;
    font-size: 14px;
    line-height: 1.6;
}

form {
    text-align: left;
}

.field-block,
.field {
    min-width: 0;
}

label {
    display: block;
    margin-bottom: 6px;
    color: #7f8ea3;
    font-size: 12px;
    font-weight: 700;
}

input {
    width: 100%;
    margin-bottom: 10px;
    padding: 12px;
    border: 1px solid transparent;
    border-radius: 6px;
    background: #000;
    color: #fff;
    outline: none;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

input::placeholder {
    color: #596475;
}

input:focus {
    border-color: rgba(111, 227, 255, 0.55);
    box-shadow: 0 0 0 3px rgba(0, 234, 255, 0.12);
}

.row {
    display: flex;
    gap: 12px;
    margin-top: 8px;
}

.field {
    flex: 1;
}

.btn {
    width: 100%;
    margin-top: 10px;
    padding: 14px;
    border: none;
    border-radius: 8px;
    background: linear-gradient(90deg, #59c6d2, #7be3ef);
    color: #063741;
    cursor: pointer;
    font-weight: 800;
    transition: 0.3s;
}

.btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(0, 255, 255, 0.2);
}

.btn:disabled {
    cursor: not-allowed;
    opacity: 0.65;
}

.login-text {
    margin: 20px 0 0;
    color: #9aa4b2;
    font-size: 13px;
}

.login-text a {
    color: #6fe3ff;
    font-weight: 700;
    text-decoration: none;
}

@media (max-width: 560px) {
    .register-card {
        padding: 30px 22px;
    }

    .row {
        flex-direction: column;
        gap: 0;
    }
}
</style>
