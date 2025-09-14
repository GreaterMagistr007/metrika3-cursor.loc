<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Вход в панель администратора
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Введите номер телефона для входа в систему
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div>
          <label for="phone" class="sr-only">Номер телефона</label>
          <PhoneInput
            id="phone"
            v-model="phone"
            :error="phoneError"
            :input-class="'appearance-none rounded-md relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 focus:z-10 sm:text-sm'"
            :disabled="loading"
          />
        </div>

        <div v-if="error" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="ml-3">
              <h3 class="text-sm font-medium text-red-800">
                Ошибка входа
              </h3>
              <div class="mt-2 text-sm text-red-700">
                {{ error }}
              </div>
            </div>
          </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading || !isValidPhone"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? 'Вход...' : 'Войти' }}
          </button>
        </div>

        <div class="text-center">
          <router-link
            to="/"
            class="font-medium text-indigo-600 hover:text-indigo-500"
          >
            ← Вернуться на главную
          </router-link>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAdminAuthStore } from '../../stores/useAdminAuthStore';
import PhoneInput from '../PhoneInput.vue';

const router = useRouter();
const adminAuthStore = useAdminAuthStore();

const phone = ref('');
const loading = ref(false);
const error = ref('');
const phoneError = ref('');

// Валидация номера телефона
const isValidPhone = computed(() => {
  const cleanPhone = phone.value.replace(/[^\d+]/g, '');
  return cleanPhone.length === 12 && cleanPhone.startsWith('+7');
});

const handleLogin = async () => {
  // Очищаем предыдущие ошибки
  error.value = '';
  phoneError.value = '';
  
  // Валидация номера телефона
  if (!phone.value) {
    phoneError.value = 'Введите номер телефона';
    return;
  }
  
  if (!isValidPhone.value) {
    phoneError.value = 'Введите корректный номер телефона';
    return;
  }
  
  loading.value = true;
  
  try {
    const result = await adminAuthStore.login(phone.value);
    
    if (result.success) {
      // Показываем уведомление об успехе
      if (window.showSuccessToast) {
        window.showSuccessToast('Успешно!', 'Вы успешно авторизованы');
      }
      
      // Успешный вход - перенаправляем на дашборд
      router.push('/');
    } else {
      error.value = result.error;
      
      // Показываем уведомление об ошибке
      if (window.showErrorToast) {
        window.showErrorToast('Ошибка авторизации!', result.error);
      }
    }
  } catch (err) {
    error.value = 'Произошла ошибка при входе в систему';
    console.error('Login error:', err);
    
    // Показываем уведомление об ошибке
    if (window.showErrorToast) {
      window.showErrorToast('Ошибка!', 'Произошла ошибка при входе в систему');
    }
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  // Если уже авторизован, перенаправляем на дашборд
  if (adminAuthStore.isLoggedIn) {
    router.push('/');
  }
});
</script>