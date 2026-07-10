export function useDebounce<T extends (...args: never[]) => void>(
  callback: T,
  delay = 300,
): (...args: Parameters<T>) => void {
  let timeout: ReturnType<typeof setTimeout> | null = null

  const debounced = (...args: Parameters<T>) => {
    if (timeout) {
      clearTimeout(timeout)
    }
    timeout = setTimeout(() => {
      callback(...args)
      timeout = null
    }, delay)
  }

  onUnmounted(() => {
    if (timeout) {
      clearTimeout(timeout)
    }
  })

  return debounced
}
