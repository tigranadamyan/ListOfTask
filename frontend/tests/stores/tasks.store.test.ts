import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useTasksStore } from '../../stores/tasks'
import { api } from '../../services/api'

vi.mock('../../services/api', () => ({
  api: {
    post: vi.fn(),
    get: vi.fn(),
    del: vi.fn(),
    put: vi.fn(),
    patch: vi.fn(),
  },
  ApiRequestError: class extends Error {
    constructor(message: string) {
      super(message)
      this.name = 'ApiRequestError'
    }
  }
}))

vi.mock('vue-sonner', () => ({
  toast: {
    success: vi.fn(),
    error: vi.fn(),
  }
}))

describe('TasksStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('initial state', () => {
    const store = useTasksStore()
    expect(store.tasks).toEqual([])
    expect(store.loading).toBe(false)
    expect(store.filters).toEqual({
      status: undefined,
      search: undefined,
      sort: 'created_at',
      direction: 'desc',
      page: 1,
      per_page: 15,
    })
  })

  it('fetchTasks populates tasks and pagination', async () => {
    const store = useTasksStore()
    const mockTasks = [{ id: 1, title: 'Task 1' }]
    const mockMeta = { current_page: 1, last_page: 2, per_page: 15, total: 20 }
    
    vi.mocked(api.get).mockResolvedValueOnce({
      data: mockTasks,
      meta: mockMeta
    })

    await store.fetchTasks()

    expect(store.tasks).toEqual(mockTasks)
    expect(store.pagination).toEqual({
      current_page: 1,
      last_page: 2,
      per_page: 15,
      total: 20,
    })
  })

  it('createTask adds task to list', async () => {
    const store = useTasksStore()
    const newTask = { id: 2, title: 'New Task' }
    
    vi.mocked(api.post).mockResolvedValueOnce({
      data: newTask
    })

    await store.createTask({ title: 'New Task' })

    expect(store.tasks[0]).toEqual(newTask)
  })

  it('deleteTask removes task by id', async () => {
    const store = useTasksStore()
    store.tasks = [
      { id: 1, title: 'Task 1', description: null, status: { value: 'pending', label: 'Pending' }, due_date: null, created_at: '', updated_at: '', user_id: 1 },
      { id: 2, title: 'Task 2', description: null, status: { value: 'pending', label: 'Pending' }, due_date: null, created_at: '', updated_at: '', user_id: 1 }
    ]
    
    vi.mocked(api.del).mockResolvedValueOnce({})

    await store.deleteTask(1)

    expect(store.tasks).toHaveLength(1)
    expect(store.tasks[0].id).toBe(2)
  })

  it('updateStatus changes task status in list', async () => {
    const store = useTasksStore()
    store.tasks = [
      { id: 1, title: 'Task 1', description: null, status: { value: 'pending', label: 'Pending' }, due_date: null, created_at: '', updated_at: '', user_id: 1 }
    ]
    
    const updatedTask = { id: 1, title: 'Task 1', status: { value: 'completed', label: 'Completed' } }
    vi.mocked(api.patch).mockResolvedValueOnce({
      data: updatedTask
    })

    await store.updateStatus(1, 'completed')

    expect(store.tasks[0].status.value).toBe('completed')
  })
})
