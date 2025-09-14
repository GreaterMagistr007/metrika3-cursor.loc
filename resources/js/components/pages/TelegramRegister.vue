<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Регистрация через Telegram
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Проверяем ваш аккаунт Telegram...
        </p>
      </div>

      <!-- Форма регистрации - этап 1: данные -->
      <form v-if="showRegistrationForm && !showOtpInput" @submit.prevent="handleSubmit" class="mt-8 space-y-6">
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
            {{ loading ? 'Отправка кода...' : 'Получить код' }}
          </button>
        </div>

        <div class="text-center">
          <button
            type="button"
            @click="goToLogin"
            class="text-sm text-blue-600 hover:text-blue-500"
          >
            Уже есть аккаунт? Войти
          </button>
        </div>
      </form>

      <!-- Форма регистрации - этап 2: OTP -->
      <form v-if="showRegistrationForm && showOtpInput" @submit.prevent="handleOtpSubmit" class="mt-8 space-y-6">
        <div>
          <h3 class="text-lg font-medium text-gray-900 text-center mb-4">
            Введите код подтверждения
          </h3>
          <p class="text-sm text-gray-600 text-center mb-6">
            Код отправлен в Telegram на номер {{ phone }}
          </p>
          
          <div>
            <label for="otp" class="sr-only">Код подтверждения</label>
            <input
              id="otp"
              v-model="otp"
              name="otp"
              type="text"
              required
              maxlength="6"
              class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm text-center text-lg tracking-widest"
              placeholder="000000"
            />
          </div>
          
          <div v-if="timeLeft > 0" class="mt-2 text-sm text-gray-600 text-center">
            Код действителен еще: {{ formatTime(timeLeft) }}
          </div>
          <div v-else class="mt-2 text-center">
            <button
              type="button"
              @click="resendOtp"
              class="text-sm text-blue-600 hover:text-blue-500"
            >
              Отправить код повторно
            </button>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="!otp || otp.length !== 6 || loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? 'Завершение регистрации...' : 'Завершить регистрацию' }}
          </button>
        </div>

        <div class="text-center">
          <button
            type="button"
            @click="backToForm"
            class="text-sm text-gray-600 hover:text-gray-500"
          >
            ← Назад к форме
          </button>
        </div>
      </form>

      <!-- Загрузка -->
      <div v-else-if="loading" class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Проверяем ваш аккаунт...</p>
      </div>

      <!-- Ошибка -->
      <div v-else-if="error" class="text-center">
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
          <p class="text-red-800">{{ error }}</p>
          <button
            @click="retry"
            class="mt-2 text-sm text-red-600 hover:text-red-500"
          >
            Попробовать снова
          </button>
        </div>
      </div>
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
const showRegistrationForm = ref(false)
const showOtpInput = ref(false)
const error = ref('')
const name = ref('')
const phone = ref('')
const otp = ref('')
const nameError = ref('')
const phoneError = ref('')
const otpTimer = ref(null)
const timeLeft = ref(0)

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

// Получение Telegram ID из URL параметров или Telegram Web App
const getTelegramId = () => {
  // Сначала пытаемся получить из Telegram Web App
  if (window.Telegram && window.Telegram.WebApp) {
    const user = window.Telegram.WebApp.initDataUnsafe?.user
    if (user && user.id) {
      return user.id.toString()
    }
  }
  
  // Если не получилось, берем из URL параметров
  const urlParams = new URLSearchParams(window.location.search)
  return urlParams.get('telegram_id')
}

// Проверка существования пользователя
const checkUser = async () => {
  const telegramId = getTelegramId()
  
  if (!telegramId) {
    error.value = 'Telegram ID не найден в параметрах URL'
    return
  }
  
  loading.value = true
  error.value = ''
  
  try {
    const response = await api.post('/auth/check-user-by-telegram', {
      telegram_id: parseInt(telegramId)
    })
    
    // Пользователь найден или создан - авторизуем
    await authStore.setAuthData(response.data.user, response.data.token)
    
    if (response.data.needs_profile_completion) {
      // Нужно завершить профиль - перенаправляем на страницу завершения
      messageStore.showToast('Завершите регистрацию, указав имя и телефон', 'info')
      router.push('/complete-profile')
    } else {
      // Профиль полный - переходим на главную
      messageStore.showToast('Добро пожаловать!', 'success')
      router.push('/')
    }
  } catch (err) {
    console.error('Error checking user:', err)
    error.value = 'Ошибка проверки аккаунта. Попробуйте позже.'
  } finally {
    loading.value = false
  }
}

// Обработка регистрации - этап 1: отправка OTP
const handleSubmit = async () => {
  if (!validateName() || !validatePhone()) {
    return
  }
  
  loading.value = true
  error.value = ''
  
  try {
    const telegramId = getTelegramId()
    
    const result = await authStore.register({
      name: name.value.trim(),
      phone: phone.value,
      telegram_id: telegramId ? parseInt(telegramId) : null
    })
    
    if (result.success) {
      // Показываем форму ввода OTP
      showOtpInput.value = true
      startOtpTimer()
      messageStore.showToast('Код подтверждения отправлен в Telegram', 'success')
    } else {
      error.value = result.message
    }
  } catch (err) {
    console.error('Registration error:', err)
    
    if (err.response?.data?.error_code === 'USER_EXISTS') {
      phoneError.value = 'Пользователь с таким номером телефона уже существует'
    } else if (err.response?.data?.error_code === 'TELEGRAM_ID_EXISTS') {
      error.value = 'Пользователь с таким Telegram ID уже существует'
    } else {
      error.value = err.response?.data?.message || 'Ошибка регистрации. Попробуйте позже.'
    }
  } finally {
    loading.value = false
  }
}

// Обработка регистрации - этап 2: подтверждение OTP
const handleOtpSubmit = async () => {
  if (!otp.value || otp.value.length !== 6) {
    return
  }
  
  loading.value = true
  error.value = ''
  
  try {
    const result = await authStore.completeRegistration(phone.value, otp.value)
    
    if (result.success) {
      messageStore.showToast('Регистрация успешно завершена!', 'success')
      router.push('/')
    } else {
      error.value = result.message
    }
  } catch (err) {
    console.error('OTP verification error:', err)
    error.value = err.response?.data?.message || 'Ошибка подтверждения кода. Попробуйте позже.'
  } finally {
    loading.value = false
  }
}

// Таймер OTP
const startOtpTimer = () => {
  timeLeft.value = 300 // 5 minutes
  otpTimer.value = setInterval(() => {
    timeLeft.value--
    if (timeLeft.value <= 0) {
      clearInterval(otpTimer.value)
      otpTimer.value = null
    }
  }, 1000)
}

const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60)
  const remainingSeconds = seconds % 60
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`
}

// Повторная отправка OTP
const resendOtp = async () => {
  if (timeLeft.value > 0) return
  
  loading.value = true
  error.value = ''
  
  try {
    const telegramId = getTelegramId()
    
    const result = await authStore.register({
      name: name.value.trim(),
      phone: phone.value,
      telegram_id: telegramId ? parseInt(telegramId) : null
    })
    
    if (result.success) {
      startOtpTimer()
      messageStore.showToast('Код подтверждения отправлен повторно', 'success')
    } else {
      error.value = result.message
    }
  } catch (err) {
    console.error('Resend OTP error:', err)
    error.value = 'Ошибка повторной отправки кода. Попробуйте позже.'
  } finally {
    loading.value = false
  }
}

// Возврат к форме
const backToForm = () => {
  showOtpInput.value = false
  otp.value = ''
  if (otpTimer.value) {
    clearInterval(otpTimer.value)
    otpTimer.value = null
  }
  timeLeft.value = 0
}

// Переход к странице входа
const goToLogin = () => {
  router.push('/login')
}

// Повторная попытка
const retry = () => {
  error.value = ''
  checkUser()
}

// Инициализация
onMounted(() => {
  checkUser()
})
</script>
