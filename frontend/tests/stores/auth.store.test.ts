import { describe, it, expect, beforeEach, vi } from 'vitest'
import { setActivePinia, createPinia } from 'pinia'
import { useAuthStore } from '../../stores/auth'
import { api, ApiRequestError } from '../../services/api'
import { mockNuxtImport } from '@nuxt/test-utils/runtime'

mockNuxtImport('navigateTo', () => {
  return vi.fn()
})

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

describe('AuthStore', () => {
  beforeEach(() => {
    setActivePinia(createPinia())
    vi.clearAllMocks()
  })

  it('initial state', () => {
    const store = useAuthStore()
    expect(store.user).toBeNull()
    expect(store.token).toBeNull()
    expect(store.isAuthenticated).toBe(false)
    expect(store.isAdmin).toBe(false)
  })

  it('login stores token and user', async () => {
    const store = useAuthStore()
    const mockUser = { id: 1, name: 'Admin', email: 'a@b.com', is_admin: true }
    const mockToken = 'abc'
    
    vi.mocked(api.post).mockResolvedValueOnce({
      data: { token: mockToken, user: mockUser }
    })

    await store.login({ email: 'a@b.com', password: 'password' })

    expect(store.token).toBe(mockToken)
    expect(store.user).toEqual(mockUser)
    expect(store.isAdmin).toBe(true)
    expect(store.isAuthenticated).toBe(true)
  })

  it('login clears token on error', async () => {
    const store = useAuthStore()
    
    vi.mocked(api.post).mockRejectedValueOnce(new ApiRequestError('Login failed'))

    await expect(store.login({ email: 'a@b.com', password: 'wrong' })).rejects.toThrow('Login failed')

    expect(store.token).toBeNull()
    expect(store.user).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })

  it('logout clears state', async () => {
    const store = useAuthStore()
    store.token = 'abc'
    store.user = { id: 1, name: 'Admin', email: 'a@b.com', is_admin: true }
    
    vi.mocked(api.post).mockResolvedValueOnce({})
    
    // Mock navigateTo which is auto-imported by Nuxt
    vi.stubGlobal('navigateTo', vi.fn())

    await store.logout()

    expect(store.token).toBeNull()
    expect(store.user).toBeNull()
    expect(store.isAuthenticated).toBe(false)
  })

  it('isAdmin computed', () => {
    const store = useAuthStore()
    
    store.user = { id: 1, name: 'Admin', email: 'a@b.com', is_admin: true }
    expect(store.isAdmin).toBe(true)

    store.user = { id: 2, name: 'User', email: 'u@b.com', is_admin: false }
    expect(store.isAdmin).toBe(false)
  })
})
