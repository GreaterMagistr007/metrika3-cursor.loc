<template>
  <div id="admin-panel" class="admin-panel">
    <!-- Заголовок -->
    <header class="admin-panel-header">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <h1 class="text-xl font-semibold text-gray-900">Metrika3 Admin Panel</h1>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-500">Администратор: {{ admin?.phone || 'Не авторизован' }}</span>
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
      <aside v-if="isAuthenticated" class="admin-panel-sidebar w-64 min-h-screen">
        <nav class="mt-5 px-2">
          <router-link 
            to="/admin" 
            class="admin-nav-item"
            :class="{ 'active': $route.name === 'admin-dashboard' }"
          >
            Дашборд
          </router-link>
          <router-link 
            to="/admin/users" 
            class="admin-nav-item"
            :class="{ 'active': $route.name === 'admin-users' }"
          >
            Пользователи
          </router-link>
          <router-link 
            to="/admin/cabinets" 
            class="admin-nav-item"
            :class="{ 'active': $route.name === 'admin-cabinets' }"
          >
            Кабинеты
          </router-link>
          <router-link 
            to="/admin/audit-logs" 
            class="admin-nav-item"
            :class="{ 'active': $route.name === 'admin-audit-logs' }"
          >
            Логи аудита
          </router-link>
          <router-link 
            to="/admin/messages" 
            class="admin-nav-item"
            :class="{ 'active': $route.name === 'admin-messages' }"
          >
            Сообщения
          </router-link>
        </nav>
      </aside>

      <!-- Контент -->
      <main class="admin-panel-content flex-1">
        <router-view />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const admin = ref(null);
const isAuthenticated = ref(false);

const goToLogin = () => {
  router.push('/admin/login');
};

const logout = () => {
  // TODO: Реализовать logout
  admin.value = null;
  isAuthenticated.value = false;
  router.push('/admin/login');
};

onMounted(() => {
  // TODO: Проверить аутентификацию
  // Пока что для демонстрации
  isAuthenticated.value = false;
});
</script>
