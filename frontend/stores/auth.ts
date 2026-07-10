import type { UserInfo, LoginCredentials, AuthResponse } from '~/types/task'
import { api, ApiRequestError } from '~/services/api'
import { defineStore } from 'pinia'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<UserInfo | null>(null)
  const token = ref<string | null>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)

  const isAuthenticated = computed(() => !!token.value)
  const isAdmin = computed(() => user.value?.is_admin === true)

  async function login(credentials: LoginCredentials): Promise<void> {
    loading.value = true
    error.value = null

    try {
      const response = await api.post<AuthResponse>('/login', credentials)
      token.value = response.data.token
      user.value = response.data.user

      if (import.meta.client) {
        localStorage.setItem('auth_token', response.data.token)
      }
    } catch (e) {
      const message = e instanceof ApiRequestError ? e.message : 'Login failed'
      error.value = message
      throw e
    } finally {
      loading.value = false
    }
  }

  async function logout(): Promise<void> {
    loading.value = true

    try {
      await api.post('/logout')
    } catch {
      // Ignore logout API errors — clear local state regardless
    }

    token.value = null
    user.value = null
    error.value = null

    if (import.meta.client) {
      localStorage.removeItem('auth_token')
    }

    loading.value = false
    await navigateTo('/login')
  }

  async function fetchUser(): Promise<void> {
    loading.value = true
    error.value = null

    try {
      const response = await api.get<{ data: UserInfo }>('/user')
      user.value = response.data
    } catch (e) {
      const message = e instanceof ApiRequestError ? e.message : 'Failed to fetch user'
      error.value = message
      user.value = null
      token.value = null

      if (import.meta.client) {
        localStorage.removeItem('auth_token')
      }
    } finally {
      loading.value = false
    }
  }

  async function init(): Promise<void> {
    if (import.meta.client) {
      const storedToken = localStorage.getItem('auth_token')
      if (storedToken) {
        token.value = storedToken
        await fetchUser()
      }
    }
  }

  return {
    user,
    token,
    loading,
    error,
    isAuthenticated,
    isAdmin,
    login,
    logout,
    fetchUser,
    init,
  }
})
