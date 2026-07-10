export function useTasks() {
  const tasksStore = useTasksStore()
  const { tasks, loading, filters, pagination } = storeToRefs(tasksStore)

  const debouncedFetch = useDebounce(() => {
    tasksStore.fetchTasks()
  }, 300)

  watch(
    filters,
    () => {
      debouncedFetch()
    },
    { deep: true },
  )

  return {
    tasks,
    loading,
    filters,
    pagination,
    fetchTasks: tasksStore.fetchTasks,
    createTask: tasksStore.createTask,
    updateTask: tasksStore.updateTask,
    deleteTask: tasksStore.deleteTask,
    updateStatus: tasksStore.updateStatus,
    setFilter: tasksStore.setFilter,
    resetFilters: tasksStore.resetFilters,
  }
}
