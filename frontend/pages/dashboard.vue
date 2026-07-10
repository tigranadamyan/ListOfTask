<template>
  <div>
    <div class="flex items-center justify-between mb-6">
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-bold text-gray-900">Мои задачи</h1>
        <span
          v-if="tasksStore.pagination.total > 0"
          class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800"
        >
          {{ tasksStore.pagination.total }}
        </span>
      </div>
      <AppButton @click="openCreateModal">
        <svg class="w-4 h-4 mr-1.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
        </svg>
Новая задача
       </AppButton>
    </div>

    <TaskFilters class="mb-6" @change="onFilterChange" />

    <div v-if="tasksStore.loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <div v-for="i in 6" :key="i" class="bg-white border border-gray-200 rounded-lg p-4">
        <AppSkeleton type="card" />
      </div>
    </div>

    <EmptyState
      v-else-if="tasksStore.tasks.length === 0"
      title="Задач пока нет"
      description="Создайте первую задачу, чтобы начать. Нажмите кнопку «Новая задача» выше."
    >
      <AppButton class="mt-4" @click="openCreateModal">
        Создать первую задачу
      </AppButton>
    </EmptyState>

    <template v-else>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <TaskCard
          v-for="task in tasksStore.tasks"
          :key="task.id"
          :task="task"
          @edit="openEditModal"
          @delete="handleDelete"
          @status-change="handleStatusChange"
        />
      </div>

      <div
        v-if="tasksStore.pagination.last_page > 1"
        class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200"
      >
        <p class="text-sm text-gray-500">
          Всего: <span class="font-medium text-gray-900">{{ tasksStore.pagination.total }}</span> задач
        </p>
        <div class="flex items-center gap-2">
          <AppButton
            variant="secondary"
            size="sm"
            :disabled="tasksStore.pagination.current_page <= 1"
            @click="goToPage(tasksStore.pagination.current_page - 1)"
          >
Назад
           </AppButton>
          <span class="text-sm text-gray-600 px-2">
            {{ tasksStore.pagination.current_page }} / {{ tasksStore.pagination.last_page }}
          </span>
          <AppButton
            variant="secondary"
            size="sm"
            :disabled="tasksStore.pagination.current_page >= tasksStore.pagination.last_page"
            @click="goToPage(tasksStore.pagination.current_page + 1)"
          >
Вперёд
           </AppButton>
        </div>
      </div>
    </template>

    <TaskModal
      :open="modalOpen"
      :task="editingTask"
      @close="closeModal"
    />
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useDebounceFn } from '@vueuse/core'
import { useTasksStore } from '~/stores/tasks'
import type { Task, TaskStatus } from '~/types/task'

definePageMeta({
  layout: 'default',
  middleware: 'auth',
})

const tasksStore = useTasksStore()
const route = useRoute()
const router = useRouter()

const modalOpen = ref(false)
const editingTask = ref<Task | null>(null)

function restoreFiltersFromUrl() {
  if (route.query.search) tasksStore.filters.search = String(route.query.search)
  if (route.query.status) tasksStore.filters.status = route.query.status as typeof tasksStore.filters.status
  if (route.query.sort) tasksStore.filters.sort = route.query.sort as typeof tasksStore.filters.sort
  if (route.query.direction) tasksStore.filters.direction = route.query.direction as 'asc' | 'desc'
  if (route.query.page) tasksStore.filters.page = Number(route.query.page)
}

function syncUrl() {
  const query: Record<string, string> = {}

  if (tasksStore.filters.search) query.search = tasksStore.filters.search
  if (tasksStore.filters.status) query.status = tasksStore.filters.status
  if (tasksStore.filters.sort && tasksStore.filters.sort !== 'created_at') query.sort = tasksStore.filters.sort
  if (tasksStore.filters.direction && tasksStore.filters.direction !== 'desc') query.direction = tasksStore.filters.direction
  if (tasksStore.filters.page && tasksStore.filters.page !== 1) query.page = String(tasksStore.filters.page)

  router.replace({ query })
}

const onFilterChange = useDebounceFn(() => {
  syncUrl()
  tasksStore.fetchTasks()
}, 100)

function openCreateModal() {
  editingTask.value = null
  modalOpen.value = true
}

function openEditModal(task: Task) {
  editingTask.value = task
  modalOpen.value = true
}

function closeModal() {
  modalOpen.value = false
  editingTask.value = null
}

function handleDelete(taskId: number) {
  tasksStore.deleteTask(taskId)
}

function handleStatusChange(payload: { id: number; status: TaskStatus }) {
  tasksStore.updateStatus(payload.id, payload.status)
}

function goToPage(page: number) {
  tasksStore.setFilter('page', String(page))
  onFilterChange()
}

onMounted(() => {
  restoreFiltersFromUrl()
  tasksStore.fetchTasks()
})
</script>
