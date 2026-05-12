<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-lg border border-[#38d9ff]/20 bg-[#0b1622] px-4 py-2 text-xs font-semibold uppercase text-[#e8f7ff] shadow-sm transition duration-150 ease-in-out hover:border-[#38d9ff]/40 hover:bg-[#102434] focus:outline-none focus:ring-2 focus:ring-[#38d9ff] focus:ring-offset-2 focus:ring-offset-[#09141f] disabled:opacity-25"
    >
        <slot />
    </button>
</template>
