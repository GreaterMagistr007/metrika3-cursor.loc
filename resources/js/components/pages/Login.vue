<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Вход в систему
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Введите номер телефона для получения кода подтверждения
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleSubmit">
        <div>
          <label for="phone" class="sr-only">Номер телефона</label>
          <PhoneInput
            id="phone"
            v-model="phone"
            :input-class="'appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm'"
            :error="phoneError"
          />
        </div>

        <div v-if="showOtpInput">
          <label for="otp" class="sr-only">Код подтверждения</label>
          <input
            id="otp"
            v-model="otp"
            name="otp"
            type="text"
            required
            maxlength="6"
            class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            placeholder="Введите код из Telegram"
          />
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
            :disabled="authStore.loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="authStore.loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ showOtpInput ? 'Подтвердить код' : 'Получить код' }}
          </button>
        </div>

        <div v-if="error" class="text-red-600 text-sm text-center">
          {{ error }}
        </div>

        <div v-if="success" class="text-green-600 text-sm text-center">
          {{ success }}
        </div>
      </form>

      <div class="text-center">
        <p class="text-sm text-gray-600">
          Нет аккаунта? 
          <a 
            href="https://t.me/M_150_site_bot" 
            target="_blank" 
            class="font-medium text-blue-600 hover:text-blue-500"
          >
            Зарегистрироваться через Telegram
          </a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/useAuthStore.js';
import PhoneInput from '../PhoneInput.vue';

const router = useRouter();
const authStore = useAuthStore();

const phone = ref('');
const otp = ref('');
const showOtpInput = ref(false);
const error = ref('');
const success = ref('');
const phoneError = ref('');
const otpTimer = ref(null);
const timeLeft = ref(0);

// Check if we're in Telegram Mini App
const isTelegramApp = ref(false);

onMounted(() => {
  // Check if we're in Telegram Mini App
  if (window.Telegram && window.Telegram.WebApp) {
    isTelegramApp.value = true;
    handleTelegramAuth();
  }
});

const handleTelegramAuth = async () => {
  try {
    const initData = window.Telegram.WebApp.initData;
    if (initData) {
      const result = await authStore.loginWithTelegram(initData);
      if (result.success) {
        router.push('/');
      } else {
        error.value = result.message;
        // If phone is required, show phone input
        if (result.message.includes('номер телефона')) {
          showOtpInput.value = false;
        }
      }
    }
  } catch (err) {
    console.error('Telegram auth error:', err);
    error.value = 'Ошибка авторизации через Telegram';
  }
};

const handleSubmit = async () => {
  error.value = '';
  success.value = '';
  phoneError.value = '';

  if (!showOtpInput.value) {
    // Validate phone number
    if (phone.value.length !== 12 || !phone.value.startsWith('+7')) {
      phoneError.value = 'Номер телефона должен быть в формате +7XXXXXXXXXX';
      return;
    }

    // Request OTP
    const result = await authStore.loginWithPhone(phone.value);
    
    if (result.success) {
      showOtpInput.value = true;
      success.value = result.message;
      startOtpTimer();
    } else {
      error.value = result.message;
    }
  } else {
    // Verify OTP
    const result = await authStore.verifyOtp(phone.value, otp.value);
    
    if (result.success) {
      success.value = result.message;
      router.push('/');
    } else {
      error.value = result.message;
    }
  }
};

const startOtpTimer = () => {
  timeLeft.value = 300; // 5 minutes
  otpTimer.value = setInterval(() => {
    timeLeft.value--;
    if (timeLeft.value <= 0) {
      clearInterval(otpTimer.value);
      otpTimer.value = null;
    }
  }, 1000);
};

const formatTime = (seconds) => {
  const minutes = Math.floor(seconds / 60);
  const remainingSeconds = seconds % 60;
  return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
};

const resendOtp = async () => {
  if (timeLeft.value > 0) return;
  
  const result = await authStore.loginWithPhone(phone.value);
  if (result.success) {
    success.value = result.message;
    startOtpTimer();
  } else {
    error.value = result.message;
  }
};
</script>
