<template>
  <form class="space-y-4" @submit.prevent="handleSubmit">
    <AppInput
      v-model="form.title"
      label="Название"
      required
      :error="errors.title"
      placeholder="Введите название задачи"
    />

    <AppTextarea
      v-model="form.description"
      label="Описание"
      placeholder="Введите описание задачи (необязательно)"
    />

    <AppInput
      v-model="form.due_date"
      label="Срок"
      type="date"
      :error="errors.due_date"
    />

    <AppSelect
      v-if="isEditMode"
      v-model="form.status"
      label="Статус"
      :options="statusOptions"
    />

    <div class="flex items-center gap-3 pt-2">
      <AppButton type="submit" :loading="loading" class="flex-1">
        {{ isEditMode ? 'Сохранить' : 'Создать задачу' }}
      </AppButton>
      <AppButton
        v-if="!loading"
        variant="secondary"
        type="button"
        class="flex-1"
        @click="$emit('cancel')"
      >
        Отмена
      </AppButton>
    </div>
  </form>
</template>

<script setup lang="ts">
import { reactive, computed } from 'vue'
import type { Task, TaskStatus, TaskFormData } from '~/types/task'

const props = defineProps<{
  task: Task | null
  loading: boolean
}>()

const emit = defineEmits<{
  submit: [data: TaskFormData]
  cancel: []
}>()

const isEditMode = computed(() => props.task !== null)

const statusOptions = [
  { value: 'pending', label: 'Ожидает' },
  { value: 'in_progress', label: 'В работе' },
  { value: 'completed', label: 'Завершена' },
]

const form = reactive({
  title: props.task?.title ?? '',
  description: props.task?.description ?? '',
  due_date: props.task?.due_date ?? '',
  status: props.task?.status.value ?? 'pending',
})

const errors = reactive({
  title: '',
  due_date: '',
})

function validate(): boolean {
  let valid = true
  errors.title = ''
  errors.due_date = ''

  if (!form.title.trim()) {
    errors.title = 'Введите название'
    valid = false
  }

  if (form.due_date) {
    const selected = new Date(form.due_date)
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    selected.setHours(0, 0, 0, 0)
    if (selected < today) {
      errors.due_date = 'Срок не может быть в прошлом'
      valid = false
    }
  }

  return valid
}

function handleSubmit() {
  if (!validate()) return

  const data: TaskFormData = {
    title: form.title.trim(),
  }

  if (form.description.trim()) {
    data.description = form.description.trim()
  }

  if (form.due_date) {
    data.due_date = form.due_date
  }

  if (isEditMode.value) {
    data.status = form.status as TaskStatus
  }

  emit('submit', data)
}
</script>
