<template>
  <div class="admin-audit-logs">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Логи аудита</h1>
      <p class="mt-2 text-gray-600">История действий пользователей в системе</p>
    </div>

    <!-- Фильтры -->
    <div class="bg-white shadow rounded-lg mb-6">
      <div class="px-4 py-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label for="user" class="block text-sm font-medium text-gray-700">Пользователь</label>
            <input
              id="user"
              v-model="filters.user"
              type="text"
              placeholder="Поиск по пользователю..."
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>
          <div>
            <label for="event" class="block text-sm font-medium text-gray-700">Событие</label>
            <select
              id="event"
              v-model="filters.event"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            >
              <option value="">Все события</option>
              <option value="user.created">Создание пользователя</option>
              <option value="user.updated">Обновление пользователя</option>
              <option value="cabinet.created">Создание кабинета</option>
              <option value="cabinet.updated">Обновление кабинета</option>
              <option value="cabinet.deleted">Удаление кабинета</option>
              <option value="permission.assigned">Назначение прав</option>
              <option value="permission.revoked">Отзыв прав</option>
            </select>
          </div>
          <div>
            <label for="date_from" class="block text-sm font-medium text-gray-700">Дата с</label>
            <input
              id="date_from"
              v-model="filters.date_from"
              type="date"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>
          <div>
            <label for="date_to" class="block text-sm font-medium text-gray-700">Дата до</label>
            <input
              id="date_to"
              v-model="filters.date_to"
              type="date"
              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
          </div>
        </div>
        <div class="mt-4 flex justify-end">
          <button
            @click="applyFilters"
            class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
          >
            Применить фильтры
          </button>
        </div>
      </div>
    </div>

    <!-- Таблица логов -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <div v-if="loading" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
        <p class="mt-2 text-gray-500">Загрузка логов...</p>
      </div>
      
      <div v-else-if="logs.length === 0" class="text-center py-8">
        <p class="text-gray-500">Логи не найдены</p>
      </div>
      
      <ul v-else class="divide-y divide-gray-200">
        <li v-for="log in logs" :key="log.id" class="px-6 py-4">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center">
                    <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                  </div>
                </div>
                <div class="ml-4 flex-1">
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-sm font-medium text-gray-900">
                        {{ getEventDescription(log.event) }}
                      </p>
                      <p class="text-sm text-gray-500">
                        {{ log.description }}
                      </p>
                    </div>
                    <div class="text-right">
                      <p class="text-sm text-gray-500">
                        {{ formatDate(log.created_at) }}
                      </p>
                      <p v-if="log.user" class="text-xs text-gray-400">
                        {{ log.user.name || log.user.phone }}
                      </p>
                    </div>
                  </div>
                  <div v-if="log.metadata" class="mt-2">
                    <details class="text-xs text-gray-500">
                      <summary class="cursor-pointer hover:text-gray-700">Детали</summary>
                      <pre class="mt-1 bg-gray-50 p-2 rounded text-xs overflow-x-auto">{{ JSON.stringify(log.metadata, null, 2) }}</pre>
                    </details>
                  </div>
                </div>
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

const logs = ref([]);
const loading = ref(false);
const pagination = ref(null);

const filters = ref({
  user: '',
  event: '',
  date_from: '',
  date_to: ''
});

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

const fetchLogs = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: '20'
    });
    
    if (filters.value.user) {
      params.append('user', filters.value.user);
    }
    
    if (filters.value.event) {
      params.append('event', filters.value.event);
    }
    
    if (filters.value.date_from) {
      params.append('date_from', filters.value.date_from);
    }
    
    if (filters.value.date_to) {
      params.append('date_to', filters.value.date_to);
    }
    
    const response = await axios.get(`/api/admin/audit-logs?${params}`);
    logs.value = response.data.data;
    pagination.value = response.data.meta;
  } catch (error) {
    console.error('Ошибка загрузки логов:', error);
  } finally {
    loading.value = false;
  }
};

const applyFilters = () => {
  fetchLogs(1);
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchLogs(page);
  }
};

const getEventDescription = (event) => {
  const descriptions = {
    'user.created': 'Создание пользователя',
    'user.updated': 'Обновление пользователя',
    'cabinet.created': 'Создание кабинета',
    'cabinet.updated': 'Обновление кабинета',
    'cabinet.deleted': 'Удаление кабинета',
    'permission.assigned': 'Назначение прав',
    'permission.revoked': 'Отзыв прав',
    'user.invited': 'Приглашение пользователя',
    'user.removed': 'Удаление пользователя',
    'ownership.transferred': 'Передача прав владения'
  };
  
  return descriptions[event] || event;
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
  fetchLogs();
});
</script>