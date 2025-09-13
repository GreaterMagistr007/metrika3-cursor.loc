<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Регистрация
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Создайте новый аккаунт
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div class="rounded-md shadow-sm -space-y-px">
          <!-- Name field -->
          <div>
            <label for="name" class="sr-only">Имя</label>
            <input
              id="name"
              v-model="form.name"
              name="name"
              type="text"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-t-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Ваше имя"
              :disabled="authStore.loading"
            />
          </div>
          
          <!-- Phone field -->
          <div>
            <label for="phone" class="sr-only">Номер телефона</label>
            <input
              id="phone"
              v-model="form.phone"
              name="phone"
              type="tel"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="+7 (999) 123-45-67"
              :disabled="authStore.loading"
            />
          </div>
          
          <!-- Telegram ID field (optional) -->
          <div>
            <label for="telegram_id" class="sr-only">Telegram ID (необязательно)</label>
            <input
              id="telegram_id"
              v-model="form.telegram_id"
              name="telegram_id"
              type="number"
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-b-md focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm"
              placeholder="Telegram ID (необязательно)"
              :disabled="authStore.loading"
            />
          </div>
        </div>

        <!-- Error message -->
        <div v-if="error" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                {{ error }}
              </h3>
            </div>
          </div>
        </div>

        <!-- Success message -->
        <div v-if="successMessage" class="rounded-md bg-green-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <h3 class="text-sm font-medium text-green-800">
                {{ successMessage }}
              </h3>
            </div>
          </div>
        </div>

        <!-- Submit button -->
        <div>
          <button
            type="submit"
            :disabled="authStore.loading || !isFormValid"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="authStore.loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ authStore.loading ? 'Регистрация...' : 'Зарегистрироваться' }}
          </button>
        </div>

        <!-- Login link -->
        <div class="text-center">
          <p class="text-sm text-gray-600">
            Уже есть аккаунт?
            <router-link to="/login" class="font-medium text-indigo-600 hover:text-indigo-500">
              Войти
            </router-link>
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/useAuthStore.js'

const router = useRouter()
const authStore = useAuthStore()

// Form data
const form = ref({
  name: '',
  phone: '',
  telegram_id: null
})

// UI state
const error = ref('')
const successMessage = ref('')

// Computed
const isFormValid = computed(() => {
  return form.value.name.trim().length >= 2 && 
         form.value.phone.trim().length > 0
})

// Methods
const handleSubmit = async () => {
  error.value = ''
  successMessage.value = ''

  // Validate form
  if (!isFormValid.value) {
    error.value = 'Пожалуйста, заполните все обязательные поля'
    return
  }

  // Format phone number
  const phone = form.value.phone.replace(/\D/g, '')
  const formattedPhone = phone.startsWith('7') ? `+${phone}` : `+7${phone}`

  // Prepare registration data
  const registrationData = {
    name: form.value.name.trim(),
    phone: formattedPhone,
    telegram_id: form.value.telegram_id ? parseInt(form.value.telegram_id) : null
  }

  // Call registration API
  const result = await authStore.register(registrationData)

  if (result.success) {
    successMessage.value = result.message
    
    // If OTP was sent, redirect to login with phone pre-filled
    if (result.otp_sent) {
      setTimeout(() => {
        router.push({
          name: 'login',
          query: { phone: formattedPhone, message: 'Код подтверждения отправлен в Telegram' }
        })
      }, 2000)
    } else {
      // If no OTP sent, redirect to login
      setTimeout(() => {
        router.push({
          name: 'login',
          query: { phone: formattedPhone }
        })
      }, 2000)
    }
  } else {
    error.value = result.message
  }
}

// Format phone input
const formatPhone = (event) => {
  let value = event.target.value.replace(/\D/g, '')
  if (value.startsWith('7')) {
    value = value.substring(1)
  }
  if (value.length > 0) {
    value = `+7 (${value.substring(0, 3)}${value.length > 3 ? ') ' : ''}${value.substring(3, 6)}${value.length > 6 ? '-' : ''}${value.substring(6, 8)}${value.length > 8 ? '-' : ''}${value.substring(8, 10)}`
  } else {
    value = '+7 ('
  }
  form.value.phone = value
}
</script>
