<template>
  <div class="w-full">
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
    </label>
    <select
      :value="modelValue"
      :class="[
        'block w-full rounded-md px-3 py-2 text-sm transition-colors duration-200 border outline-none bg-white',
        'placeholder:text-gray-400',
        error
          ? 'border-red-500 focus:ring-2 focus:ring-red-500/20 focus:border-red-500'
          : 'border-gray-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500',
      ]"
      @change="$emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
      <option v-if="placeholder" value="" disabled>{{ placeholder }}</option>
      <option
        v-for="option in options"
        :key="option.value"
        :value="option.value"
      >
        {{ option.label }}
      </option>
    </select>
    <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  modelValue: string
  label?: string
  options: { value: string; label: string }[]
  placeholder?: string
  error?: string
}>()

defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>
