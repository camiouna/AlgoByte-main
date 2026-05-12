<script setup>
import AuthBackgroundToggle from '@/Components/AuthBackgroundToggle.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="auth-page">
        <AuthBackgroundToggle />

        <div class="auth-shell">
            <section class="auth-brand">
                <Link href="/" class="brand-mark">
                    <h1>AlgoByte</h1>
                    <span>code lab</span>
                </Link>

                <div class="brand-copy">
                    <p class="eyebrow">Algorithm workspace</p>
                    <h2>Compile, test, and iterate in a focused coding room.</h2>
                    <p>
                        Enter your protected editor, manage test cases, and run code
                        through the Piston execution flow.
                    </p>
                </div>

                <div class="code-preview">
                    <div class="window-controls">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    <pre><code>function solution(input) {
  const verdict = runTests(input)
  return verdict.passed ? "accepted" : "debug"
}</code></pre>
                </div>
            </section>

            <section class="auth-card">
                <div class="auth-card-header">
                    <p>Welcome to</p>
                    <h2>AlgoByte</h2>
                </div>

                <slot />
            </section>
        </div>
    </div>
</template>

<style scoped>
.auth-page {
    min-height: 100vh;
    display: grid;
    place-items: center;
    padding: 32px;
    background: var(--auth-page-bg);
    background-size: var(--auth-page-bg-size);
}

.auth-shell {
    width: min(1080px, 100%);
    min-height: 620px;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(360px, 430px);
    overflow: hidden;
    border: 1px solid var(--ab-border);
    border-radius: 10px;
    background: rgba(7, 17, 27, 0.82);
    box-shadow: 0 24px 70px rgba(6, 55, 65, 0.18);
    backdrop-filter: blur(18px);
}

.auth-brand {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    gap: 36px;
    padding: 48px;
    color: var(--ab-text);
}

.brand-mark {
    width: fit-content;
    text-decoration: none;
}

.brand-mark h1 {
    margin: 0;
    color: #f3fbff;
    font-size: 42px;
    font-weight: 800;
    line-height: 1;
    letter-spacing: 0;
    text-shadow: 0 0 22px rgba(56, 217, 255, 0.35);
}

.brand-mark span {
    display: block;
    margin-top: 7px;
    color: var(--ab-cyan);
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
}

.brand-copy {
    max-width: 560px;
}

.eyebrow {
    margin: 0 0 12px;
    color: var(--ab-teal);
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
}

.brand-copy h2 {
    margin: 0;
    color: var(--ab-text);
    font-size: clamp(34px, 5vw, 54px);
    font-weight: 800;
    line-height: 1.03;
}

.brand-copy p:last-child {
    max-width: 480px;
    margin: 20px 0 0;
    color: var(--ab-muted);
    font-size: 17px;
    line-height: 1.7;
}

.code-preview {
    max-width: 560px;
    padding: 18px;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: rgba(5, 10, 16, 0.92);
    box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.03);
}

.window-controls {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}

.window-controls span {
    width: 10px;
    height: 10px;
    border-radius: 999px;
}

.window-controls span:nth-child(1) {
    background: var(--ab-danger);
}

.window-controls span:nth-child(2) {
    background: var(--ab-cyan);
}

.window-controls span:nth-child(3) {
    background: var(--ab-teal);
}

.code-preview pre {
    margin: 0;
    overflow-x: auto;
    color: #cfeeff;
    font-family: Consolas, 'Courier New', monospace;
    font-size: 15px;
    line-height: 1.7;
}

.auth-card {
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 44px;
    border-left: 1px solid var(--ab-border);
    background: rgba(9, 20, 31, 0.96);
}

.auth-card-header {
    margin-bottom: 28px;
}

.auth-card-header p {
    margin: 0;
    color: var(--ab-cyan);
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
}

.auth-card-header h2 {
    margin: 6px 0 0;
    color: var(--ab-text);
    font-size: 30px;
    font-weight: 800;
}

@media (max-width: 900px) {
    .auth-page {
        padding: 18px;
    }

    .auth-shell {
        grid-template-columns: 1fr;
    }

    .auth-brand {
        padding: 34px;
    }

    .code-preview {
        display: none;
    }

    .auth-card {
        padding: 34px;
        border-left: 0;
        border-top: 1px solid var(--ab-border);
    }
}

@media (max-width: 520px) {
    .auth-page {
        padding: 0;
    }

    .auth-shell {
        min-height: 100vh;
        border-radius: 0;
    }

    .auth-brand,
    .auth-card {
        padding: 26px 20px;
    }
}
</style>
