<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Дашборд</h1>
      <p class="mt-2 text-gray-600">Добро пожаловать в личный кабинет Metrika3</p>
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
            <p class="text-sm font-medium text-gray-500">Кабинеты</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.cabinets }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Аппараты</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.machines }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Доход за месяц</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.revenue }} ₽</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Активные сессии</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.activeSessions }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Последние действия -->
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Последние действия</h3>
      </div>
      <div class="px-6 py-4">
        <div class="space-y-4">
          <div v-for="activity in recentActivities" :key="activity.id" class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-900">{{ activity.description }}</p>
              <p class="text-xs text-gray-500">{{ activity.time }}</p>
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
  cabinets: 0,
  machines: 0,
  revenue: 0,
  activeSessions: 0
});

const recentActivities = ref([
  {
    id: 1,
    description: 'Создан новый кабинет "Офис на Тверской"',
    time: '2 часа назад'
  },
  {
    id: 2,
    description: 'Добавлен пользователь в кабинет',
    time: '5 часов назад'
  },
  {
    id: 3,
    description: 'Настроены права доступа',
    time: '1 день назад'
  }
]);

onMounted(() => {
  // TODO: Загрузить реальные данные
  // Пока что демонстрационные данные
  stats.value = {
    cabinets: 3,
    machines: 12,
    revenue: 45600,
    activeSessions: 2
  };
});
</script>
