<template>
  <div id="main-app" class="main-app">
    <!-- Заголовок -->
    <header class="main-app-header">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900">Metrika3 Cabinet</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-500">Пользователь: {{ user?.phone || 'Не авторизован' }}</span>
            <button 
              v-if="!isAuthenticated"
              @click="goToLogin"
              class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700"
            >
              Войти
            </button>
            <button 
              v-else
              @click="logout"
              class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700"
            >
              Выйти
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Основной контент -->
    <div class="flex">
      <!-- Боковая панель -->
      <aside v-if="isAuthenticated" class="main-app-sidebar w-64 min-h-screen">
        <nav class="mt-5 px-2">
          <router-link 
            to="/" 
            class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100"
            :class="{ 'bg-gray-100': $route.name === 'dashboard' }"
          >
            Дашборд
          </router-link>
          <router-link 
            to="/settings" 
            class="block px-3 py-2 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-100"
            :class="{ 'bg-gray-100': $route.name === 'settings' }"
          >
            Настройки
          </router-link>
        </nav>
      </aside>

      <!-- Контент -->
      <main class="main-app-content flex-1">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/useAuthStore.js';

const router = useRouter();
const authStore = useAuthStore();

const user = computed(() => authStore.user);
const isAuthenticated = computed(() => authStore.isAuthenticated);

const goToLogin = () => {
  router.push('/login');
};

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};

onMounted(() => {
  // Initialize authentication state
  authStore.initAuth();
});
</script>
