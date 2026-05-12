<script setup>
import { computed, onMounted, ref } from 'vue';

const modes = ['light', 'dark', 'blue'];
const mode = ref('dark');
const buttonLabel = computed(() => {
    if (mode.value === 'light') return 'Light Mode';
    if (mode.value === 'blue') return 'Blue Mode';
    return 'Dark Mode';
});

const applyMode = (value) => {
    mode.value = value;
    const root = document.documentElement;
    root.dataset.authBg = value;

    if (value === 'light') {
        root.style.colorScheme = 'light';
        root.style.setProperty('--site-bg', '#d8e2e7');
        root.style.setProperty('--site-text', '#18303c');
        root.style.setProperty('--site-muted', '#5f7380');
        root.style.setProperty('--site-panel', 'rgba(227, 234, 239, 0.94)');
        root.style.setProperty('--site-panel-strong', 'rgba(238, 243, 246, 0.96)');
        root.style.setProperty('--site-card', 'rgba(244, 247, 249, 0.86)');
        root.style.setProperty('--site-input', 'rgba(245, 249, 251, 0.96)');
        root.style.setProperty('--site-input-focus', 'rgba(255, 255, 255, 0.98)');
        root.style.setProperty('--site-editor-top', 'rgba(214, 223, 229, 0.96)');
        root.style.setProperty('--site-border', 'rgba(36, 93, 117, 0.18)');
        root.style.setProperty('--site-border-strong', 'rgba(36, 93, 117, 0.34)');
        root.style.setProperty('--site-border-soft', 'rgba(36, 93, 117, 0.12)');
        root.style.setProperty('--site-shadow', '0 20px 56px rgba(61, 78, 89, 0.18)');
        root.style.setProperty('--site-placeholder', '#70838d');
        root.style.setProperty('--site-warning', '#895d10');
        root.style.setProperty('--grid-opacity', '0.03');
    } else if (value === 'blue') {
        root.style.colorScheme = 'dark';
        root.style.setProperty('--site-bg', '#001f3f');
        root.style.setProperty('--site-text', '#e0f2fe');
        root.style.setProperty('--site-muted', '#9eb6cb');
        root.style.setProperty('--site-panel', 'rgba(4, 24, 49, 0.94)');
        root.style.setProperty('--site-panel-strong', 'rgba(8, 36, 66, 0.96)');
        root.style.setProperty('--site-card', 'rgba(8, 31, 58, 0.84)');
        root.style.setProperty('--site-input', 'rgba(3, 20, 41, 0.94)');
        root.style.setProperty('--site-input-focus', 'rgba(6, 28, 53, 0.98)');
        root.style.setProperty('--site-editor-top', 'rgba(7, 30, 56, 0.96)');
        root.style.setProperty('--site-border', 'rgba(123, 227, 239, 0.18)');
        root.style.setProperty('--site-border-strong', 'rgba(123, 227, 239, 0.34)');
        root.style.setProperty('--site-border-soft', 'rgba(123, 227, 239, 0.12)');
        root.style.setProperty('--site-shadow', '0 24px 80px rgba(0, 0, 0, 0.34)');
        root.style.setProperty('--site-placeholder', '#7f9ab2');
        root.style.setProperty('--site-warning', '#ffcf8a');
        root.style.setProperty('--grid-opacity', '0.03');
    } else {
        root.style.colorScheme = 'dark';
        root.style.setProperty('--site-bg', '#05080d');
        root.style.setProperty('--site-text', '#e8f7ff');
        root.style.setProperty('--site-muted', '#89a9b8');
        root.style.setProperty('--site-panel', 'rgba(5, 10, 16, 0.96)');
        root.style.setProperty('--site-panel-strong', 'rgba(13, 27, 40, 0.96)');
        root.style.setProperty('--site-card', 'rgba(8, 18, 29, 0.8)');
        root.style.setProperty('--site-input', 'rgba(4, 9, 14, 0.92)');
        root.style.setProperty('--site-input-focus', 'rgba(7, 17, 27, 0.98)');
        root.style.setProperty('--site-editor-top', 'rgba(11, 22, 34, 0.95)');
        root.style.setProperty('--site-border', 'rgba(101, 232, 255, 0.18)');
        root.style.setProperty('--site-border-strong', 'rgba(101, 232, 255, 0.34)');
        root.style.setProperty('--site-border-soft', 'rgba(56, 217, 255, 0.12)');
        root.style.setProperty('--site-shadow', '0 24px 80px rgba(0, 0, 0, 0.42)');
        root.style.setProperty('--site-placeholder', '#6f8a97');
        root.style.setProperty('--site-warning', '#ffcf8a');
        root.style.setProperty('--grid-opacity', '0.035');
    }

    localStorage.setItem('authBackgroundMode', value);
};

const toggleMode = () => {
    const currentIndex = modes.indexOf(mode.value);
    const nextMode = modes[(currentIndex + 1) % modes.length];
    applyMode(nextMode);
};

onMounted(() => {
    const savedMode = localStorage.getItem('authBackgroundMode');
    applyMode(savedMode || 'dark');
});
</script>

<template>
    <button class="auth-bg-toggle" type="button" @click="toggleMode">
        {{ buttonLabel }}
    </button>
</template>

<style scoped>
.auth-bg-toggle {
    /* Remove position: fixed, top, and right */
    min-height: 32px;
    padding: 0 10px;
    border: 1px solid var(--ab-border);
    border-radius: 6px;
    background: rgba(56, 217, 255, 0.1);
    color: var(--ab-cyan);
    cursor: pointer;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    transition: all 0.2s ease;
}

.auth-bg-toggle:hover {
    transform: translateY(-2px);
    background: rgba(255, 255, 255, 0.92);
    box-shadow: 0 16px 34px rgba(6, 55, 65, 0.18);
}

@media (max-width: 560px) {
    .auth-bg-toggle {
        top: 18px;
        right: 18px;
    }
}
</style>
