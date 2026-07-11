import type { ApiError as ApiErrorShape } from '~/types/task'

export class ApiRequestError extends Error {
  status: number
  details?: Record<string, string[]>

  constructor(message: string, status: number, details?: Record<string, string[]>) {
    super(message)
    this.name = 'ApiRequestError'
    this.status = status
    this.details = details
  }
}

function getBaseUrl(): string {
  try {
    const config = useRuntimeConfig()
    // Server-side (SSR): use private config (Docker internal URL)
    // Client-side: use public config (relative URL through Nginx)
    if (import.meta.server) {
      return (config.apiBaseUrl as string) || 'http://localhost:8000/api'
    }
    return (config.public.apiBaseUrl as string) || 'http://localhost:8000/api'
  } catch {
    return 'http://localhost:8000/api'
  }
}

function getAuthHeaders(): Record<string, string> {
  const headers: Record<string, string> = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  }

  if (import.meta.client) {
    const token = localStorage.getItem('auth_token')
    if (token) {
      headers['Authorization'] = `Bearer ${token}`
    }
  }

  return headers
}

async function handleResponse<T>(response: Response): Promise<T> {
  if (response.status === 401) {
    if (import.meta.client) {
      localStorage.removeItem('auth_token')
      navigateTo('/login')
    }

    throw new ApiRequestError(`Request failed with status ${response.status}`, response.status)
  }

  if (!response.ok) {
    let errorData: ApiErrorShape | null = null
    try {
      errorData = await response.json()
    } catch {
      // Response body is not valid JSON
    }

    const message = errorData?.error?.message || `Request failed with status ${response.status}`
    const details = errorData?.error?.details
    throw new ApiRequestError(message, response.status, details)
  }

  if (response.status === 204) {
    return undefined as unknown as T
  }

  return response.json() as Promise<T>
}

async function request<T>(
  method: string,
  url: string,
  data?: unknown,
  params?: Record<string, unknown>,
): Promise<T> {
  const baseUrl = getBaseUrl()
  let fullUrl = `${baseUrl}${url}`

  if (params) {
    const searchParams = new URLSearchParams()
    for (const [key, value] of Object.entries(params)) {
      if (value !== undefined && value !== null && value !== '') {
        searchParams.append(key, String(value))
      }
    }
    const queryString = searchParams.toString()
    if (queryString) {
      fullUrl += `?${queryString}`
    }
  }

  const options: RequestInit = {
    method,
    headers: getAuthHeaders(),
  }

  if (data && method !== 'GET') {
    options.body = JSON.stringify(data)
  }

  const response = await fetch(fullUrl, options)
  return handleResponse<T>(response)
}

export const api = {
  get<T>(url: string, params?: Record<string, unknown>): Promise<T> {
    return request<T>('GET', url, undefined, params)
  },

  post<T>(url: string, data?: unknown): Promise<T> {
    return request<T>('POST', url, data)
  },

  put<T>(url: string, data?: unknown): Promise<T> {
    return request<T>('PUT', url, data)
  },

  patch<T>(url: string, data?: unknown): Promise<T> {
    return request<T>('PATCH', url, data)
  },

  del<T>(url: string): Promise<T> {
    return request<T>('DELETE', url)
  },
}
