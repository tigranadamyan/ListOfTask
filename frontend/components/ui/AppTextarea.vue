<template>
  <div class="w-full">
    <label v-if="label" class="block text-sm font-medium text-gray-700 mb-1">
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>
    <textarea
      :value="modelValue"
      :placeholder="placeholder"
      :required="required"
      :rows="rows"
      :class="[
        'block w-full rounded-md px-3 py-2 text-sm transition-colors duration-200 border outline-none resize-y',
        'placeholder:text-gray-400',
        error
          ? 'border-red-500 focus:ring-2 focus:ring-red-500/20 focus:border-red-500'
          : 'border-gray-300 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500',
      ]"
      @input="$emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    />
    <p v-if="error" class="mt-1 text-sm text-red-500">{{ error }}</p>
  </div>
</template>

<script setup lang="ts">
withDefaults(
  defineProps<{
    modelValue: string
    label?: string
    placeholder?: string
    error?: string
    required?: boolean
    rows?: number
  }>(),
  {
    rows: 3,
  },
)

defineEmits<{
  'update:modelValue': [value: string]
}>()
</script>
