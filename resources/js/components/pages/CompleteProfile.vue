<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Завершение регистрации
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Для завершения регистрации укажите ваше имя и номер телефона
        </p>
      </div>

      <form @submit.prevent="handleSubmit" class="mt-8 space-y-6">
        <div class="space-y-4">
          <NameInput
            id="name"
            v-model="name"
            label="Имя"
            placeholder="Введите ваше имя"
            :required="true"
            :error="nameError"
            @blur="validateName"
          />
          
          <PhoneInput
            id="phone"
            v-model="phone"
            label="Номер телефона"
            placeholder="+7 (999) 999-99-99"
            :required="true"
            :error="phoneError"
            @blur="validatePhone"
          />
        </div>

        <div>
          <button
            type="submit"
            :disabled="!isFormValid || loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? 'Сохранение...' : 'Завершить регистрацию' }}
          </button>
        </div>

        <div v-if="error" class="text-red-600 text-sm text-center">
          {{ error }}
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/useAuthStore'
import { useMessageStore } from '@/stores/useMessageStore'
import NameInput from '@/components/NameInput.vue'
import PhoneInput from '@/components/PhoneInput.vue'
import api from '@/api/axios'

const router = useRouter()
const authStore = useAuthStore()
const messageStore = useMessageStore()

// Состояние
const loading = ref(false)
const error = ref('')
const name = ref('')
const phone = ref('')
const nameError = ref('')
const phoneError = ref('')

// Валидация
const isFormValid = computed(() => {
  return name.value.trim().length >= 2 && 
         name.value.trim().length <= 30 && 
         phone.value.length === 12 && 
         phone.value.startsWith('+7') &&
         !nameError.value && 
         !phoneError.value
})

// Валидация имени
const validateName = () => {
  const trimmedName = name.value.trim()
  
  if (!trimmedName) {
    nameError.value = 'Имя обязательно для заполнения'
    return false
  }
  
  if (trimmedName.length < 2) {
    nameError.value = 'Имя должно содержать минимум 2 символа'
    return false
  }
  
  if (trimmedName.length > 30) {
    nameError.value = 'Имя должно содержать максимум 30 символов'
    return false
  }
  
  // Проверяем, что содержит только русские/английские буквы и пробелы
  const namePattern = /^[а-яёА-ЯЁa-zA-Z\s]+$/
  if (!namePattern.test(trimmedName)) {
    nameError.value = 'Имя может содержать только русские и английские буквы'
    return false
  }
  
  nameError.value = ''
  return true
}

// Валидация телефона
const validatePhone = () => {
  if (!phone.value) {
    phoneError.value = 'Номер телефона обязателен'
    return false
  }
  
  if (phone.value.length !== 12 || !phone.value.startsWith('+7')) {
    phoneError.value = 'Введите корректный номер телефона в формате +7XXXXXXXXXX'
    return false
  }
  
  phoneError.value = ''
  return true
}

// Обработка отправки формы
const handleSubmit = async () => {
  if (!validateName() || !validatePhone()) {
    return
  }
  
  loading.value = true
  error.value = ''
  
  try {
    const response = await api.post('/auth/update-profile', {
      name: name.value.trim(),
      phone: phone.value
    })
    
    // Обновляем данные пользователя в store
    authStore.user.name = response.data.user.name
    authStore.user.phone = response.data.user.phone
    localStorage.setItem('user_data', JSON.stringify(authStore.user))
    
    messageStore.showToast('Профиль успешно обновлен!', 'success')
    
    // Перенаправляем на главную страницу
    window.location.href = '/'
  } catch (err) {
    console.error('Profile update error:', err)
    
    if (err.response?.data?.error_code === 'PHONE_EXISTS') {
      phoneError.value = 'Пользователь с таким номером телефона уже существует'
    } else if (err.response?.data?.errors) {
      // Обработка ошибок валидации
      const errors = err.response.data.errors
      if (errors.name) {
        nameError.value = errors.name[0]
      }
      if (errors.phone) {
        phoneError.value = errors.phone[0]
      }
    } else {
      error.value = err.response?.data?.message || 'Ошибка обновления профиля. Попробуйте позже.'
    }
  } finally {
    loading.value = false
  }
}

// Инициализация - заполняем поля если они есть
onMounted(() => {
  if (authStore.user) {
    name.value = authStore.user.name || ''
    phone.value = authStore.user.phone || ''
  }
})
</script>
