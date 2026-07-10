import type { Task, TaskFilters, TaskFormData, TaskStatus, PaginatedResponse } from '~/types/task'
import { api, ApiRequestError } from '~/services/api'
import { defineStore } from 'pinia'
import { toast } from 'vue-sonner'

export const useTasksStore = defineStore('tasks', () => {
  const tasks = ref<Task[]>([])
  const loading = ref(false)
  const error = ref<string | null>(null)
  const filters = ref<TaskFilters>({
    status: undefined,
    search: undefined,
    sort: 'created_at',
    direction: 'desc',
    page: 1,
    per_page: 15,
  })
  const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 15,
    total: 0,
  })

  function buildParams(): Record<string, unknown> {
    const params: Record<string, unknown> = {}
    if (filters.value.status) params.status = filters.value.status
    if (filters.value.search) params.search = filters.value.search
    if (filters.value.sort) params.sort = filters.value.sort
    if (filters.value.direction) params.direction = filters.value.direction
    if (filters.value.page) params.page = filters.value.page
    if (filters.value.per_page) params.per_page = filters.value.per_page
    return params
  }

  async function fetchTasks(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<PaginatedResponse<Task>>('/tasks', buildParams())
      tasks.value = response.data
      pagination.value = {
        current_page: response.meta.current_page,
        last_page: response.meta.last_page,
        per_page: response.meta.per_page,
        total: response.meta.total,
      }
    } catch (e) {
      const message = e instanceof ApiRequestError ? e.message : 'Не удалось загрузить задачи'
      error.value = message
      toast.error(message)
    } finally {
      loading.value = false
    }
  }

  async function createTask(data: TaskFormData): Promise<Task | undefined> {
    loading.value = true
    error.value = null

    try {
      const response = await api.post<{ data: Task }>('/tasks', data)
      tasks.value.unshift(response.data)
      toast.success('Задача создана')
      return response.data
    } catch (e) {
      const message = e instanceof ApiRequestError ? e.message : 'Не удалось создать задачу'
      error.value = message
      toast.error(message)
    } finally {
      loading.value = false
    }
  }

  async function updateTask(id: number, data: Partial<TaskFormData>): Promise<Task | undefined> {
    loading.value = true
    error.value = null

    try {
      const response = await api.put<{ data: Task }>(`/tasks/${id}`, data)
      const index = tasks.value.findIndex((t) => t.id === id)
      if (index !== -1) {
        tasks.value[index] = response.data
      }
      return response.data
    } catch (e) {
      const message = e instanceof ApiRequestError ? e.message : 'Не удалось обновить задачу'
      error.value = message
      toast.error(message)
    } finally {
      loading.value = false
    }
  }

  async function updateStatus(id: number, status: TaskStatus): Promise<void> {
    error.value = null

    try {
      const response = await api.patch<{ data: Task }>(`/tasks/${id}/status`, { status })
      const index = tasks.value.findIndex((t) => t.id === id)
      if (index !== -1) {
        tasks.value[index] = response.data
      }
    } catch (e) {
      const message = e instanceof ApiRequestError ? e.message : 'Не удалось изменить статус'
      error.value = message
      toast.error(message)
    }
  }

  async function deleteTask(id: number): Promise<void> {
    error.value = null

    try {
      await api.del(`/tasks/${id}`)
      tasks.value = tasks.value.filter((t) => t.id !== id)
      toast.success('Задача удалена')
    } catch (e) {
      const message = e instanceof ApiRequestError ? e.message : 'Не удалось удалить задачу'
      error.value = message
      toast.error(message)
    }
  }

  function setFilter(key: string, value: unknown): void {
    const updated = { ...filters.value, [key]: value }
    if (key !== 'page') {
      updated.page = 1
    }
    filters.value = updated
  }

  function resetFilters(): void {
    filters.value = {
      status: undefined,
      search: undefined,
      sort: 'created_at',
      direction: 'desc',
      page: 1,
      per_page: 15,
    }
  }

  return {
    tasks,
    loading,
    error,
    filters,
    pagination,
    fetchTasks,
    createTask,
    updateTask,
    updateStatus,
    deleteTask,
    setFilter,
    resetFilters,
  }
})
