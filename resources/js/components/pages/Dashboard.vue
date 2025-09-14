<template>
  <div>
    <!-- Заголовок с приветствием -->
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">
        Добро пожаловать, {{ user?.name || 'Пользователь' }}!
      </h1>
      <p class="mt-2 text-gray-600">
        {{ currentCabinet ? `Кабинет: ${currentCabinet.name}` : 'Выберите кабинет для работы' }}
      </p>
    </div>

    <!-- Сообщение о выборе кабинета -->
    <div v-if="!currentCabinet" class="mb-8 bg-yellow-50 border border-yellow-200 rounded-md p-4">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800">Выберите кабинет</h3>
          <div class="mt-2 text-sm text-yellow-700">
            <p>Для работы с системой необходимо выбрать кабинет. Если у вас нет кабинетов, создайте новый.</p>
          </div>
          <div class="mt-4">
            <button
              @click="showCreateModal = true"
              class="bg-yellow-100 text-yellow-800 px-3 py-2 rounded-md text-sm font-medium hover:bg-yellow-200"
            >
              Создать кабинет
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Модальное окно создания кабинета -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeCreateModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Создать новый кабинет</h3>
          <form @submit.prevent="createCabinet">
            <div class="mb-4">
              <label for="cabinet-name" class="block text-sm font-medium text-gray-700 mb-2">
                Название кабинета
              </label>
              <input
                id="cabinet-name"
                v-model="newCabinetName"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Введите название кабинета"
              />
            </div>
            <div class="mb-4">
              <label for="cabinet-description" class="block text-sm font-medium text-gray-700 mb-2">
                Описание (необязательно)
              </label>
              <textarea
                id="cabinet-description"
                v-model="newCabinetDescription"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Введите описание кабинета"
              ></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeCreateModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
              >
                Отмена
              </button>
              <button
                type="submit"
                :disabled="creating"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                {{ creating ? 'Создание...' : 'Создать' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Статистика -->
    <div v-if="currentCabinet" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Кабинеты</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.cabinets }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Пользователи</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.users }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Активные права</p>
            <p class="text-2xl font-semibold text-gray-900">{{ stats.permissions }}</p>
          </div>
        </div>
      </div>

      <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-red-500 rounded-md flex items-center justify-center">
              <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Последний вход</p>
            <p class="text-sm font-semibold text-gray-900">{{ formatLastLogin(user?.last_login_at) }}</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Быстрые действия -->
    <div v-if="currentCabinet" class="mb-8">
      <h2 class="text-lg font-medium text-gray-900 mb-4">Быстрые действия</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <button
          @click="goToSettings"
          class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow text-left"
        >
          <div class="flex items-center">
            <svg class="w-6 h-6 text-blue-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
            </svg>
            <div>
              <h3 class="font-medium text-gray-900">Управление пользователями</h3>
              <p class="text-sm text-gray-500">Пригласить и настроить права</p>
            </div>
          </div>
        </button>

        <button
          @click="viewAuditLogs"
          class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow text-left"
        >
          <div class="flex items-center">
            <svg class="w-6 h-6 text-green-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            <div>
              <h3 class="font-medium text-gray-900">Журнал действий</h3>
              <p class="text-sm text-gray-500">Просмотр активности в кабинете</p>
            </div>
          </div>
        </button>

        <button
          @click="viewMessages"
          class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow text-left"
        >
          <div class="flex items-center">
            <svg class="w-6 h-6 text-yellow-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
            </svg>
            <div>
              <h3 class="font-medium text-gray-900">Сообщения</h3>
              <p class="text-sm text-gray-500">Просмотр уведомлений</p>
            </div>
          </div>
        </button>
      </div>
    </div>

    <!-- Последние действия -->
    <div v-if="currentCabinet" class="bg-white shadow-sm rounded-lg border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-medium text-gray-900">Последние действия</h3>
      </div>
      <div class="px-6 py-4">
        <div v-if="loading" class="flex justify-center py-4">
          <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
        </div>
        <div v-else-if="recentActivities.length === 0" class="text-center py-4 text-gray-500">
          Нет недавних действий
        </div>
        <div v-else class="space-y-4">
          <div v-for="activity in recentActivities" :key="activity.id" class="flex items-center space-x-3">
            <div class="flex-shrink-0">
              <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm text-gray-900">{{ activity.description }}</p>
              <p class="text-xs text-gray-500">{{ formatTime(activity.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Toast notifications -->
    <Toast />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/useAuthStore.js';
import { useMessageStore } from '../../stores/useMessageStore.js';
import Toast from '../Toast.vue';
import api from '../../api/axios.js';

const router = useRouter();
const authStore = useAuthStore();
const messageStore = useMessageStore();

// Reactive data
const stats = ref({
  cabinets: 0,
  users: 0,
  permissions: 0
});

const recentActivities = ref([]);
const loading = ref(false);

// Modal state
const showCreateModal = ref(false);
const newCabinetName = ref('');
const newCabinetDescription = ref('');
const creating = ref(false);

// Computed properties
const user = computed(() => authStore.user);
const currentCabinet = computed(() => authStore.currentCabinet);

// Methods
const loadDashboardData = async () => {
  if (!currentCabinet.value) return;
  
  loading.value = true;
  try {
    // Load cabinets count
    console.log('Загружаем кабинеты...');
    const cabinetsResponse = await api.get('/cabinets');
    console.log('Ответ API кабинетов:', cabinetsResponse.data);
    stats.value.cabinets = cabinetsResponse.data.data?.length || 0;
    
    // Load current cabinet details
    console.log('Загружаем детали кабинета...', currentCabinet.value.id);
    const cabinetResponse = await api.get(`/cabinets/${currentCabinet.value.id}`);
    console.log('Ответ API деталей кабинета:', cabinetResponse.data);
    const cabinet = cabinetResponse.data.data;
    
    // Load users count for current cabinet
    stats.value.users = cabinet.users?.length || 0;
    
    // Load permissions count (simplified - count all permissions for current user in cabinet)
    stats.value.permissions = cabinet.permissions?.length || 0;
    
    // Load recent activities (audit logs)
    await loadRecentActivities();
    
  } catch (error) {
    console.error('Failed to load dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

const loadRecentActivities = async () => {
  try {
    // Пока что используем демо-данные, так как API для audit-logs не реализован
    recentActivities.value = [
      {
        id: 1,
        description: 'Создан новый кабинет',
        created_at: new Date().toISOString()
      },
      {
        id: 2,
        description: 'Пользователь авторизовался',
        created_at: new Date(Date.now() - 300000).toISOString() // 5 минут назад
      }
    ];
  } catch (error) {
    console.error('Failed to load recent activities:', error);
    // Fallback to demo data
    recentActivities.value = [
      {
        id: 1,
        description: 'Создан новый кабинет',
        created_at: new Date().toISOString()
      }
    ];
  }
};

const createCabinet = async () => {
  if (!newCabinetName.value.trim()) return;
  
  creating.value = true;
  try {
    const response = await api.post('/cabinets', {
      name: newCabinetName.value.trim(),
      description: newCabinetDescription.value.trim() || null
    });
    
    // Обновляем список кабинетов пользователя
    await authStore.fetchUserCabinets();
    
    // Устанавливаем новый кабинет как текущий
    authStore.setCurrentCabinet(response.data.cabinet);
    
    // Закрываем модальное окно и очищаем поля
    closeCreateModal();
    
    // Показываем уведомление об успехе
    messageStore.showToast('Кабинет успешно создан!', 'success');
    
  } catch (error) {
    console.error('Ошибка при создании кабинета:', error);
    messageStore.showToast('Ошибка при создании кабинета. Попробуйте еще раз.', 'error');
  } finally {
    creating.value = false;
  }
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  newCabinetName.value = '';
  newCabinetDescription.value = '';
};

const goToSettings = () => {
  router.push('/settings');
};

const viewAuditLogs = () => {
  // TODO: Implement audit logs page
  console.log('View audit logs');
};

const viewMessages = () => {
  // TODO: Implement messages page
  console.log('View messages');
};

const formatTime = (dateString) => {
  if (!dateString) return 'Неизвестно';
  
  const date = new Date(dateString);
  const now = new Date();
  const diff = now - date;
  
  if (diff < 60000) { // Less than 1 minute
    return 'Только что';
  } else if (diff < 3600000) { // Less than 1 hour
    const minutes = Math.floor(diff / 60000);
    return `${minutes} мин назад`;
  } else if (diff < 86400000) { // Less than 1 day
    const hours = Math.floor(diff / 3600000);
    return `${hours} ч назад`;
  } else if (diff < 604800000) { // Less than 1 week
    const days = Math.floor(diff / 86400000);
    return `${days} дн назад`;
  } else {
    return date.toLocaleDateString('ru-RU');
  }
};

const formatLastLogin = (dateString) => {
  if (!dateString) return 'Первый вход';
  
  const date = new Date(dateString);
  const now = new Date();
  const diff = now - date;
  
  if (diff < 86400000) { // Less than 1 day
    return 'Сегодня';
  } else if (diff < 604800000) { // Less than 1 week
    const days = Math.floor(diff / 86400000);
    return `${days} дн назад`;
  } else {
    return date.toLocaleDateString('ru-RU');
  }
};

// Watchers
import { watch } from 'vue';
watch(() => currentCabinet.value, (newCabinet) => {
  if (newCabinet) {
    loadDashboardData();
  }
}, { immediate: true });

onMounted(() => {
  if (currentCabinet.value) {
    loadDashboardData();
  }
});
</script>
