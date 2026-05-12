<script setup>
defineProps({
    message: {
        type: String,
    },
});
</script>

<template>
    <div v-show="message">
        <p class="text-sm text-[#ff8aa0]">
            {{ message }}
        </p>
    </div>
</template>
