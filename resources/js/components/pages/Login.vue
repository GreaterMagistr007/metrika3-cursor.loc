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
          <input
            id="phone"
            v-model="phone"
            name="phone"
            type="tel"
            required
            class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            placeholder="+7 (999) 123-45-67"
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
            class="appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            placeholder="Введите код из SMS"
          />
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
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
          <a href="#" class="font-medium text-blue-600 hover:text-blue-500">
            Обратитесь к администратору
          </a>
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const phone = ref('');
const otp = ref('');
const showOtpInput = ref(false);
const loading = ref(false);
const error = ref('');
const success = ref('');

const handleSubmit = async () => {
  loading.value = true;
  error.value = '';
  success.value = '';

  try {
    if (!showOtpInput.value) {
      // Запрос OTP
      // TODO: Реализовать API вызов
      console.log('Запрос OTP для номера:', phone.value);
      showOtpInput.value = true;
      success.value = 'Код отправлен на номер ' + phone.value;
    } else {
      // Проверка OTP
      // TODO: Реализовать API вызов
      console.log('Проверка OTP:', otp.value);
      success.value = 'Успешная авторизация!';
      
      // Перенаправление на дашборд
      setTimeout(() => {
        router.push('/');
      }, 1000);
    }
  } catch (err) {
    error.value = 'Произошла ошибка. Попробуйте еще раз.';
  } finally {
    loading.value = false;
  }
};
</script>
