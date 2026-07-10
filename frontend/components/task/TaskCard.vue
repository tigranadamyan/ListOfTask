<template>
  <div
    class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow duration-200 cursor-pointer group"
    @click="$emit('edit', task)"
  >
    <div class="flex items-start justify-between gap-3 mb-3">
      <h3 class="font-semibold text-gray-900 truncate flex-1">{{ task.title }}</h3>
      <span
        :class="[
          'shrink-0 inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium',
          statusClasses,
        ]"
      >
        {{ task.status.label }}
      </span>
    </div>

    <p
      v-if="task.description"
      class="text-sm text-gray-500 mb-3 line-clamp-2"
    >
      {{ task.description }}
    </p>

    <div class="flex items-center justify-between">
      <div class="flex items-center gap-1.5 text-xs text-gray-400">
        <svg class="w-3.5 h-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
        </svg>
        <span>{{ dueDateText }}</span>
      </div>

      <div v-if="canEdit" class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
        <select
          :value="task.status.value"
          class="text-xs border border-gray-300 rounded-md px-1.5 py-1 bg-white focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 outline-none"
          @click.stop
          @change="$emit('statusChange', { id: task.id, status: ($event.target as HTMLSelectElement).value as TaskStatus })"
        >
          <option v-for="opt in statusOptions" :key="opt.value" :value="opt.value">
            {{ opt.label }}
          </option>
        </select>
        <AppButton
          variant="ghost"
          size="sm"
          @click.stop="$emit('edit', task)"
        >
          Изменить
        </AppButton>
        <AppButton
          variant="ghost"
          size="sm"
          @click.stop="$emit('delete', task.id)"
        >
          <span class="text-red-500">Удалить</span>
        </AppButton>
      </div>
      <div v-else-if="showOwnerBadge" class="text-xs text-gray-400">
        {{ task.user?.name ?? 'Другой пользователь' }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { Task, TaskStatus } from '~/types/task'

const props = defineProps<{
  task: Task
}>()

defineEmits<{
  edit: [task: Task]
  delete: [id: number]
  statusChange: [payload: { id: number; status: TaskStatus }]
}>()

const authStore = useAuthStore()

const canEdit = computed(() => authStore.user?.id === props.task.user_id)
const showOwnerBadge = computed(() => authStore.isAdmin && props.task.user_id !== authStore.user?.id)

const statusOptions = [
  { value: 'pending', label: 'Ожидает' },
  { value: 'in_progress', label: 'В работе' },
  { value: 'completed', label: 'Завершена' },
]

const statusClasses = computed(() => {
  switch (props.task.status.value) {
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'in_progress':
      return 'bg-blue-100 text-blue-800'
    case 'completed':
      return 'bg-green-100 text-green-800'
  }
})

const dueDateText = computed(() => {
  if (!props.task.due_date) return 'Без срока'
  return new Date(props.task.due_date).toLocaleDateString('ru-RU', {
    month: 'short',
    day: 'numeric',
    year: 'numeric',
  })
})
</script>
