export function useAuth() {
  const authStore = useAuthStore()
  const { user, isAuthenticated, loading } = storeToRefs(authStore)

  return {
    user,
    isAuthenticated,
    loading,
    login: authStore.login,
    logout: authStore.logout,
  }
}
