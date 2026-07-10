export type TaskStatus = 'pending' | 'in_progress' | 'completed'

export interface TaskStatusInfo {
  value: TaskStatus
  label: string
}

export interface Task {
  id: number
  title: string
  description: string | null
  status: TaskStatusInfo
  due_date: string | null
  created_at: string
  updated_at: string
  user_id: number
  user?: UserInfo
}

export interface UserInfo {
  id: number
  name: string
  email: string
  is_admin: boolean
}

export interface TaskFilters {
  status?: TaskStatus
  search?: string
  sort?: 'due_date' | 'status' | 'created_at' | 'title'
  direction?: 'asc' | 'desc'
  page?: number
  per_page?: number
}

export interface PaginatedResponse<T> {
  data: T[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
    from: number | null
    to: number | null
  }
}

export interface TaskFormData {
  title: string
  description?: string
  due_date?: string
  status?: TaskStatus
}

export interface LoginCredentials {
  email: string
  password: string
  device_name?: string
}

export interface AuthResponse {
  data: {
    token: string
    user: UserInfo
  }
  message: string
}

export interface ApiError {
  error: {
    code: number
    message: string
    details?: Record<string, string[]>
  }
}
