<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Административная панель</h1>
      <p class="mt-2 text-gray-600">Обзор системы и управление</p>
    </div>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Всего пользователей</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.totalUsers }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Кабинеты</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.totalCabinets }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Активные сессии</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.activeSessions }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Ошибки</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.errors }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Последние действия -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Последние действия пользователей</h3>
        </div>
        <div class="px-6 py-4">
          <div class="space-y-4">
            <div v-for="activity in recentActivities" :key="activity.id" class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900">{{ activity.description }}</p>
                <p class="text-xs text-gray-500">{{ activity.user }} • {{ activity.time }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200">
          <h3 class="text-lg font-medium text-gray-900">Системные уведомления</h3>
        </div>
        <div class="px-6 py-4">
          <div class="space-y-4">
            <div v-for="notification in notifications" :key="notification.id" class="flex items-center space-x-3">
              <div class="flex-shrink-0">
                <div :class="[
                  'w-2 h-2 rounded-full',
                  notification.type === 'error' ? 'bg-red-500' : 
                  notification.type === 'warning' ? 'bg-yellow-500' : 'bg-green-500'
                ]"></div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm text-gray-900">{{ notification.message }}</p>
                <p class="text-xs text-gray-500">{{ notification.time }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const stats = ref({
  totalUsers: 0,
  totalCabinets: 0,
  activeSessions: 0,
  errors: 0
});

const recentActivities = ref([
  {
    id: 1,
    description: 'Создан новый кабинет "Офис на Тверской"',
    user: '+7 (999) 123-45-67',
    time: '2 часа назад'
  },
  {
    id: 2,
    description: 'Пользователь добавлен в кабинет',
    user: '+7 (999) 987-65-43',
    time: '5 часов назад'
  },
  {
    id: 3,
    description: 'Изменены права доступа',
    user: '+7 (999) 555-44-33',
    time: '1 день назад'
  }
]);

const notifications = ref([
  {
    id: 1,
    type: 'info',
    message: 'Система работает стабильно',
    time: '10 минут назад'
  },
  {
    id: 2,
    type: 'warning',
    message: 'Высокая нагрузка на сервер',
    time: '1 час назад'
  },
  {
    id: 3,
    type: 'error',
    message: 'Ошибка подключения к базе данных',
    time: '3 часа назад'
  }
]);

onMounted(() => {
  // TODO: Загрузить реальные данные
  stats.value = {
    totalUsers: 156,
    totalCabinets: 23,
    activeSessions: 8,
    errors: 2
  };
});
</script>
