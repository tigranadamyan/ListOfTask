<template>
  <div class="flex flex-wrap items-center gap-3 bg-white border border-gray-200 rounded-lg p-4">
    <div class="flex-1 min-w-[200px]">
      <AppInput
        :model-value="tasksStore.filters.search ?? ''"
        placeholder="Поиск задач..."
        @update:model-value="onSearchInput"
      />
    </div>

    <AppSelect
      :model-value="tasksStore.filters.status ?? ''"
      placeholder="Все статусы"
      :options="statusOptions"
      @update:model-value="onStatusChange"
    />

    <AppSelect
      :model-value="tasksStore.filters.sort ?? 'created_at'"
      :options="sortOptions"
      @update:model-value="onSortChange"
    />

    <button
      class="inline-flex items-center justify-center w-9 h-9 rounded-md border border-gray-300 hover:bg-gray-100 transition-colors"
      :title="directionLabel"
      @click="toggleDirection"
    >
      <svg
        class="w-4 h-4 text-gray-600 transition-transform duration-200"
        :class="tasksStore.filters.direction === 'asc' ? 'rotate-180' : ''"
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
        stroke-width="1.5"
        stroke="currentColor"
      >
        <path stroke-linecap="round" stroke-linejoin="round" d="M3 4.5h14.25M3 9h9.75M3 13.5h5.25m5.25-.75L17.25 9m0 0L21 12.75M17.25 9v12" />
      </svg>
    </button>

    <AppButton
      v-if="hasActiveFilters"
      variant="ghost"
      size="sm"
      @click="tasksStore.resetFilters(); emit('change')"
    >
      Сбросить
    </AppButton>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { useTasksStore } from '~/stores/tasks'

const tasksStore = useTasksStore()

const emit = defineEmits<{
  change: []
}>()

const statusOptions = [
  { value: '', label: 'Все статусы' },
  { value: 'pending', label: 'Ожидает' },
  { value: 'in_progress', label: 'В работе' },
  { value: 'completed', label: 'Завершена' },
]

const sortOptions = [
  { value: 'due_date', label: 'По сроку' },
  { value: 'status', label: 'По статусу' },
  { value: 'created_at', label: 'По дате создания' },
  { value: 'title', label: 'По названию' },
]

const hasActiveFilters = computed(() => {
  return (
    tasksStore.filters.status ||
    tasksStore.filters.search ||
    tasksStore.filters.sort !== 'created_at'
  )
})

const directionLabel = computed(() => {
  return tasksStore.filters.direction === 'asc' ? 'По возрастанию' : 'По убыванию'
})

function toggleDirection() {
  tasksStore.setFilter('direction', tasksStore.filters.direction === 'asc' ? 'desc' : 'asc')
  emit('change')
}

function onStatusChange(val: string) {
  tasksStore.setFilter('status', val)
  emit('change')
}

function onSortChange(val: string) {
  tasksStore.setFilter('sort', val)
  emit('change')
}

const onSearchInput = useDebounceFn((value: string) => {
  tasksStore.setFilter('search', value)
  emit('change')
}, 300)
</script>
