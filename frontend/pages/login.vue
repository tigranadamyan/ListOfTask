<template>
  <div>
    <div class="text-center mb-6">
      <h2 class="text-xl font-semibold text-gray-900">Вход в аккаунт</h2>
      <p class="mt-1 text-sm text-gray-500">Управляйте задачами эффективно</p>
    </div>

    <form class="space-y-4" @submit.prevent="handleLogin">
      <AppInput
        v-model="form.email"
        label="Email"
        type="email"
        required
        placeholder="you@example.ru"
        :error="errors.email"
      />

      <AppInput
        v-model="form.password"
        label="Password"
        type="password"
        required
        placeholder="Введите пароль"
        :error="errors.password"
      />

      <p v-if="errorMessage" class="text-sm text-red-500 text-center">{{ errorMessage }}</p>

      <AppButton
        type="submit"
        :loading="auth.loading"
        class="w-full"
      >
        Войти
      </AppButton>
    </form>
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'
import { useAuthStore } from '~/stores/auth'

definePageMeta({
  layout: 'auth',
  middleware: 'guest',
})

const auth = useAuthStore()

const form = reactive({
  email: '',
  password: '',
})

const errors = reactive({
  email: '',
  password: '',
})

const errorMessage = ref('')

function validate(): boolean {
  let valid = true
  errors.email = ''
  errors.password = ''

  if (!form.email.trim()) {
    errors.email = 'Введите email'
    valid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Некорректный email'
    valid = false
  }

  if (!form.password) {
    errors.password = 'Введите пароль'
    valid = false
  }

  return valid
}

async function handleLogin() {
  errorMessage.value = ''
  if (!validate()) return

  try {
    await auth.login({ email: form.email, password: form.password })
    await navigateTo('/dashboard')
  } catch (err: unknown) {
    errorMessage.value = err instanceof Error ? err.message : 'Неверный email или пароль'
  }
}
</script>
