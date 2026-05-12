<script setup>
import { computed } from 'vue'

const emit = defineEmits(['update:checked'])

const props = defineProps({
  checked: {
    type: [Array, Boolean],
    required: true,
  },
  value: {
    default: null,
  },
})

const proxyChecked = computed({
  get() {
    return props.checked
  },
  set(val) {
    emit('update:checked', val)
  },
})
</script>

<template>
  <input
    type="checkbox"
    :value="value"
    v-model="proxyChecked"
    class="rounded border-[#38d9ff]/30 bg-[#050a10] text-[#18f2c3] shadow-sm focus:ring-[#38d9ff]"
  />
</template>
