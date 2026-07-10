<template>
  <AppModal :open="open" :title="modalTitle" @close="handleClose">
    <TaskForm
      :task="task"
      :loading="submitting"
      @submit="handleSubmit"
      @cancel="handleClose"
    />
  </AppModal>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { useTasksStore } from '~/stores/tasks'
import type { Task, TaskFormData } from '~/types/task'

const props = defineProps<{
  open: boolean
  task: Task | null
}>()

const emit = defineEmits<{
  close: []
}>()

const tasksStore = useTasksStore()
const submitting = ref(false)

const modalTitle = computed(() => (props.task ? 'Редактировать задачу' : 'Новая задача'))

async function handleSubmit(data: TaskFormData) {
  submitting.value = true
  try {
    if (props.task) {
      await tasksStore.updateTask(props.task.id, data)
    } else {
      await tasksStore.createTask(data)
    }
    emit('close')
  } finally {
    submitting.value = false
  }
}

function handleClose() {
  emit('close')
}
</script>
