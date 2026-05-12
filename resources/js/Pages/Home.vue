<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Home" />

    <AuthenticatedLayout>
        <section class="home-hero">
            <div class="home-grid">
                <div class="hero-copy">
                    <p class="eyebrow">Demo edition</p>
                    <h1>Practice algorithms inside a sharper AlgoByte workspace.</h1>
                    <p>
                        Write solutions, shape test cases, and send code to the execution
                        engine from one dark, focused interface inspired by the Figma design.
                    </p>
                    <div class="hero-actions">
                        <Link :href="route('editor')" class="primary-action">
                            Open editor
                        </Link>
                        <a href="#footer" class="secondary-action">
                            About AlgoByte
                        </a>
                    </div>
                </div>

                <div class="terminal-panel">
                    <div class="terminal-top">
                        <span></span>
                        <span></span>
                        <span></span>
                        <strong>binary-search.ts</strong>
                    </div>
                    <pre><code>function binarySearch(items, target) {
  let left = 0
  let right = items.length - 1

  while (left <= right) {
    const mid = Math.floor((left + right) / 2)
    if (items[mid] === target) return mid
    if (items[mid] < target) left = mid + 1
    else right = mid - 1
  }

  return -1
}</code></pre>
                    <div class="status-strip">
                        <span>Tests: 4 passed</span>
                        <span>Runtime: 42ms</span>
                    </div>
                </div>
            </div>
        </section>
    </AuthenticatedLayout>
</template>

<style scoped>
.home-hero {
    min-height: calc(100vh - 82px);
    display: grid;
    align-items: center;
    padding: 54px 24px;
}

.home-grid {
    width: min(1180px, 100%);
    margin: 0 auto;
    display: grid;
    grid-template-columns: minmax(0, 1fr) minmax(360px, 0.9fr);
    gap: 42px;
    align-items: center;
}

.hero-copy {
    max-width: 720px;
}

.eyebrow {
    margin: 0 0 14px;
    color: var(--ab-teal);
    font-size: 13px;
    font-weight: 800;
    text-transform: uppercase;
}

.hero-copy h1 {
    margin: 0;
    color: var(--ab-text);
    font-size: clamp(42px, 6vw, 72px);
    font-weight: 850;
    line-height: 0.98;
    letter-spacing: 0;
}

.hero-copy p {
    max-width: 650px;
    margin: 24px 0 0;
    color: var(--ab-muted);
    font-size: 18px;
    line-height: 1.75;
}

.hero-actions {
    display: flex;
    flex-wrap: wrap;
    gap: 12px;
    margin-top: 32px;
}

.primary-action,
.secondary-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 44px;
    padding: 0 18px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 800;
    text-decoration: none;
    transition: transform 0.2s ease, border-color 0.2s ease, background 0.2s ease;
}

.primary-action {
    border: 1px solid transparent;
    background: linear-gradient(135deg, var(--ab-cyan), var(--ab-teal));
    color: #031016;
    box-shadow: 0 16px 36px rgba(24, 242, 195, 0.2);
}

.secondary-action {
    border: 1px solid var(--ab-border);
    background: rgba(11, 22, 34, 0.72);
    color: var(--ab-text);
}

.primary-action:hover,
.secondary-action:hover {
    transform: translateY(-2px);
}

.secondary-action:hover {
    border-color: var(--ab-border-strong);
    background: rgba(56, 217, 255, 0.1);
}

.terminal-panel {
    overflow: hidden;
    border: 1px solid var(--ab-border);
    border-radius: 8px;
    background: rgba(5, 10, 16, 0.92);
    box-shadow: var(--ab-shadow);
}

.terminal-top {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 14px 16px;
    border-bottom: 1px solid var(--ab-border);
    background: rgba(11, 22, 34, 0.95);
}

.terminal-top span {
    width: 10px;
    height: 10px;
    border-radius: 999px;
}

.terminal-top span:nth-child(1) {
    background: var(--ab-danger);
}

.terminal-top span:nth-child(2) {
    background: var(--ab-cyan);
}

.terminal-top span:nth-child(3) {
    background: var(--ab-teal);
}

.terminal-top strong {
    margin-left: 8px;
    color: var(--ab-muted);
    font-size: 13px;
}

.terminal-panel pre {
    margin: 0;
    padding: 22px;
    overflow-x: auto;
    color: #d6f5ff;
    font-family: Consolas, 'Courier New', monospace;
    font-size: 14px;
    line-height: 1.8;
}

.status-strip {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    border-top: 1px solid var(--ab-border);
    background: rgba(24, 242, 195, 0.08);
    color: var(--ab-teal);
    font-size: 13px;
    font-weight: 800;
}

@media (max-width: 900px) {
    .home-grid {
        grid-template-columns: 1fr;
    }
}
</style>
