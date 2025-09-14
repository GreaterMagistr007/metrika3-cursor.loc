<template>
  <div class="admin-messages-management">
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Управление сообщениями</h1>
          <p class="mt-2 text-gray-600">Создание и управление системными сообщениями</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
        >
          Создать сообщение
        </button>
      </div>
    </div>

    <!-- Статистика сообщений -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Всего сообщений</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.total || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Активных</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.active || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Broadcast</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.broadcast || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Persistent</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.persistent || 0 }}</dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Фильтры -->
    <div class="bg-white shadow rounded-lg mb-6">
      <div class="px-4 py-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label for="search" class="block text-sm font-medium text-gray-700">Поиск</label>
            <input
              id="search"
              v-model="searchQuery"
              type="text"
              placeholder="Поиск по тексту сообщения..."
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>
          <div>
            <label for="type" class="block text-sm font-medium text-gray-700">Тип</label>
            <select
              id="type"
              v-model="typeFilter"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">Все типы</option>
              <option value="success">Success</option>
              <option value="error">Error</option>
              <option value="warning">Warning</option>
              <option value="info">Info</option>
              <option value="system">System</option>
            </select>
          </div>
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Статус</label>
            <select
              id="status"
              v-model="statusFilter"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">Все</option>
              <option value="active">Активные</option>
              <option value="inactive">Неактивные</option>
            </select>
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button
            @click="fetchMessages"
            class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
          >
            Обновить
          </button>
        </div>
      </div>
    </div>

    <!-- Таблица сообщений -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <div v-if="loading" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
        <p class="mt-2 text-gray-500">Загрузка сообщений...</p>
      </div>
      
      <div v-else-if="messages.length === 0" class="text-center py-8">
        <p class="text-gray-500">Сообщения не найдены</p>
      </div>
      
      <ul v-else class="divide-y divide-gray-200">
        <li v-for="message in messages" :key="message.id" class="px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div :class="[
                  'h-8 w-8 rounded-md flex items-center justify-center',
                  getTypeColor(message.type)
                ]">
                  <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">
                  {{ message.title || 'Без заголовка' }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ message.text }}
                </div>
                <div class="text-xs text-gray-400">
                  Создано: {{ formatDate(message.created_at) }}
                  <span v-if="message.expires_at"> • Истекает: {{ formatDate(message.expires_at) }}</span>
                </div>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <div class="text-sm text-gray-500">
                <div class="flex items-center">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    message.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]">
                    {{ message.is_active ? 'Активно' : 'Неактивно' }}
                  </span>
                </div>
                <div class="mt-1">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                    {{ message.type }}
                  </span>
                </div>
              </div>
              <div class="flex space-x-2">
                <button
                  @click="editMessage(message)"
                  class="text-yellow-600 hover:text-yellow-900 text-sm font-medium"
                >
                  Редактировать
                </button>
                <button
                  @click="toggleMessageStatus(message)"
                  :class="[
                    'text-sm font-medium',
                    message.is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'
                  ]"
                >
                  {{ message.is_active ? 'Деактивировать' : 'Активировать' }}
                </button>
                <button
                  @click="deleteMessage(message)"
                  class="text-red-600 hover:text-red-900 text-sm font-medium"
                >
                  Удалить
                </button>
              </div>
            </div>
          </div>
        </li>
      </ul>
    </div>

    <!-- Пагинация -->
    <div v-if="pagination && pagination.last_page > 1" class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6 mt-6">
      <div class="flex-1 flex justify-between sm:hidden">
        <button
          @click="changePage(pagination.current_page - 1)"
          :disabled="pagination.current_page <= 1"
          class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Предыдущая
        </button>
        <button
          @click="changePage(pagination.current_page + 1)"
          :disabled="pagination.current_page >= pagination.last_page"
          class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Следующая
        </button>
      </div>
      <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700">
            Показано
            <span class="font-medium">{{ pagination.from || 0 }}</span>
            -
            <span class="font-medium">{{ pagination.to || 0 }}</span>
            из
            <span class="font-medium">{{ pagination.total || 0 }}</span>
            результатов
          </p>
        </div>
        <div>
          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Предыдущая
            </button>
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="changePage(page)"
              :class="[
                page === pagination.current_page
                  ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                  : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
                'relative inline-flex items-center px-4 py-2 border text-sm font-medium'
              ]"
            >
              {{ page }}
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Следующая
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '../../api/adminAxios';

const messages = ref([]);
const statistics = ref({});
const loading = ref(false);
const searchQuery = ref('');
const typeFilter = ref('');
const statusFilter = ref('');
const pagination = ref(null);
const showCreateModal = ref(false);

const visiblePages = computed(() => {
  if (!pagination.value) return [];
  
  const current = pagination.value.current_page;
  const last = pagination.value.last_page;
  const pages = [];
  
  const start = Math.max(1, current - 2);
  const end = Math.min(last, current + 2);
  
  for (let i = start; i <= end; i++) {
    pages.push(i);
  }
  
  return pages;
});

const fetchMessages = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: '15'
    });
    
    if (searchQuery.value) {
      params.append('search', searchQuery.value);
    }
    
    if (typeFilter.value) {
      params.append('type', typeFilter.value);
    }
    
    if (statusFilter.value) {
      params.append('status', statusFilter.value);
    }
    
    const response = await axios.get(`/api/admin/messages?${params}`);
    messages.value = response.data.data;
    pagination.value = response.data.meta;
  } catch (error) {
    console.error('Ошибка загрузки сообщений:', error);
  } finally {
    loading.value = false;
  }
};

const fetchStatistics = async () => {
  try {
    const response = await axios.get('/api/admin/messages-statistics');
    statistics.value = response.data;
  } catch (error) {
    console.error('Ошибка загрузки статистики:', error);
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchMessages(page);
  }
};

const getTypeColor = (type) => {
  const colors = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warning: 'bg-yellow-500',
    info: 'bg-blue-500',
    system: 'bg-purple-500'
  };
  
  return colors[type] || 'bg-gray-500';
};

const editMessage = (message) => {
  // TODO: Реализовать редактирование сообщения
  console.log('Edit message:', message);
};

const toggleMessageStatus = async (message) => {
  try {
    await axios.patch(`/api/admin/messages/${message.id}/toggle-active`);
    await fetchMessages(pagination.value.current_page);
  } catch (error) {
    console.error('Ошибка изменения статуса сообщения:', error);
  }
};

const deleteMessage = async (message) => {
  if (confirm(`Вы уверены, что хотите удалить сообщение "${message.title || message.text}"?`)) {
    try {
      await axios.delete(`/api/admin/messages/${message.id}`);
      await fetchMessages(pagination.value.current_page);
    } catch (error) {
      console.error('Ошибка удаления сообщения:', error);
    }
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'Неизвестно';
  
  const date = new Date(dateString);
  if (isNaN(date.getTime())) return 'Неверная дата';
  return date.toLocaleString('ru-RU', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
};

onMounted(() => {
  fetchMessages();
  fetchStatistics();
});
</script>