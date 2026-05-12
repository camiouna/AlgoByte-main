<script setup>
import { onMounted, ref } from 'vue';

const model = defineModel({
    type: String,
    required: true,
});

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <input
        class="rounded-lg border-[#38d9ff]/20 bg-[#050a10] text-[#e8f7ff] shadow-sm transition placeholder:text-[#89a9b8] focus:border-[#38d9ff] focus:ring-[#38d9ff]"
        v-model="model"
        ref="input"
    />
</template>
