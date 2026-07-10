<template>
  <div>
    <template v-if="type === 'card'">
      <div class="w-full h-32 rounded-lg bg-gray-200 animate-pulse" />
    </template>

    <template v-else-if="type === 'text'">
      <div class="space-y-2">
        <div
          v-for="i in 4"
          :key="i"
          class="h-4 rounded bg-gray-200 animate-pulse"
          :class="i === 4 ? 'w-2/3' : 'w-full'"
        />
      </div>
    </template>

    <template v-else-if="type === 'title'">
      <div class="h-6 w-3/4 rounded bg-gray-200 animate-pulse" />
    </template>
  </div>
</template>

<script setup lang="ts">
defineProps<{
  type: 'card' | 'text' | 'title'
}>()
</script>
