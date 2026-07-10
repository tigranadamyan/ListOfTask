import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { createPinia, setActivePinia } from 'pinia'
import TaskCard from '../../components/task/TaskCard.vue'
import { useAuthStore } from '../../stores/auth'
import type { Task } from '../../types/task'

describe('TaskCard', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  const mockTask: Task = {
    id: 1,
    title: 'Test Task',
    description: 'Test Description',
    status: { value: 'pending', label: 'Ожидает' },
    due_date: null,
    created_at: '2023-01-01T00:00:00.000Z',
    updated_at: '2023-01-01T00:00:00.000Z',
    user_id: 1,
    user: { id: 1, name: 'Owner', email: 'owner@test.com', is_admin: false }
  }

  it('shows edit/delete buttons for owner', async () => {
    const authStore = useAuthStore()
    authStore.user = { id: 1, name: 'Owner', email: 'owner@test.com', is_admin: false }

    const wrapper = mount(TaskCard, {
      props: { task: mockTask },
      global: {
        stubs: {
          AppButton: true
        }
      }
    })

    expect(wrapper.text()).toContain('Изменить')
    expect(wrapper.text()).toContain('Удалить')
  })

  it('hides edit/delete buttons for non-owner regular user', () => {
    const authStore = useAuthStore()
    authStore.user = { id: 2, name: 'Other', email: 'other@test.com', is_admin: false }

    const wrapper = mount(TaskCard, {
      props: { task: mockTask },
      global: {
        stubs: {
          AppButton: true
        }
      }
    })

    expect(wrapper.text()).not.toContain('Изменить')
    expect(wrapper.text()).not.toContain('Удалить')
  })

  it('shows owner name for admin viewing another\'s task', () => {
    const authStore = useAuthStore()
    authStore.user = { id: 2, name: 'Admin', email: 'admin@test.com', is_admin: true }

    const wrapper = mount(TaskCard, {
      props: { task: mockTask },
      global: {
        stubs: {
          AppButton: true
        }
      }
    })

    expect(wrapper.text()).toContain('Owner')
    expect(wrapper.text()).not.toContain('Изменить')
    expect(wrapper.text()).not.toContain('Удалить')
  })
})
