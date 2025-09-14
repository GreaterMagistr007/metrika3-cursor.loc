<template>
  <div class="admin-cabinets-management">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Управление кабинетами</h1>
      <p class="mt-2 text-gray-600">Просмотр и редактирование кабинетов системы</p>
    </div>

    <!-- Фильтры и поиск -->
    <div class="bg-white shadow rounded-lg mb-6">
      <div class="px-4 py-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <SearchInput
              id="search"
              v-model="searchQuery"
              placeholder="Поиск по названию кабинета..."
              @search="handleSearch"
              @clear="handleClearSearch"
            />
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
          <div class="flex items-end">
            <button
              @click="fetchCabinets"
              class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
            >
              Обновить
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Таблица кабинетов -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <div v-if="loading" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
        <p class="mt-2 text-gray-500">Загрузка кабинетов...</p>
      </div>
      
      <div v-else-if="cabinets.length === 0" class="text-center py-8">
        <p class="text-gray-500">Кабинеты не найдены</p>
      </div>
      
      <ul v-else class="divide-y divide-gray-200">
        <li v-for="cabinet in cabinets" :key="cabinet.id" class="px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div class="h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center">
                  <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">
                  {{ cabinet.name }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ cabinet.description || 'Без описания' }}
                </div>
                <div class="text-xs text-gray-400">
                  Создан: {{ formatDate(cabinet.created_at) }}
                </div>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <div class="text-sm text-gray-500">
                <div>Владелец: {{ cabinet.owner?.name || 'Неизвестно' }}</div>
                <div>Пользователей: {{ cabinet.users_count || 0 }}</div>
                <div class="flex items-center">
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    cabinet.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                  ]">
                    {{ cabinet.is_active ? 'Активен' : 'Неактивен' }}
                  </span>
                </div>
              </div>
              <div class="flex space-x-2">
                <button
                  @click="viewCabinet(cabinet)"
                  class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                >
                  Просмотр
                </button>
                <button
                  @click="editCabinet(cabinet)"
                  class="text-yellow-600 hover:text-yellow-900 text-sm font-medium"
                >
                  Редактировать
                </button>
                <button
                  @click="toggleCabinetStatus(cabinet)"
                  :class="[
                    'text-sm font-medium',
                    cabinet.is_active ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900'
                  ]"
                >
                  {{ cabinet.is_active ? 'Деактивировать' : 'Активировать' }}
                </button>
                <button
                  @click="deleteCabinet(cabinet)"
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

    <!-- Модальное окно просмотра кабинета -->
    <div v-if="showViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Информация о кабинете</h3>
            <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div v-if="selectedCabinet" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Название</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedCabinet.name }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Описание</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedCabinet.description || 'Не указано' }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Владелец</label>
              <p class="mt-1 text-sm text-gray-900">
                {{ selectedCabinet.owner?.name || 'Неизвестно' }} 
                ({{ selectedCabinet.owner?.phone || 'Нет телефона' }})
              </p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Статус</label>
              <span :class="[
                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                selectedCabinet.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
              ]">
                {{ selectedCabinet.is_active ? 'Активен' : 'Неактивен' }}
              </span>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Пользователей</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedCabinet.users_count || 0 }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Создан</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(selectedCabinet.created_at) }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Модальное окно редактирования кабинета -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Редактирование кабинета</h3>
            <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="saveCabinet" class="space-y-4">
            <div>
              <label for="edit-name" class="block text-sm font-medium text-gray-700">Название</label>
              <input
                id="edit-name"
                v-model="editForm.name"
                type="text"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              />
            </div>
            
            <div>
              <label for="edit-description" class="block text-sm font-medium text-gray-700">Описание</label>
              <textarea
                id="edit-description"
                v-model="editForm.description"
                rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              ></textarea>
            </div>
            
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeModals"
                class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400"
              >
                Отмена
              </button>
              <button
                type="submit"
                class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
              >
                Сохранить
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '../../api/adminAxios';
import SearchInput from '../SearchInput.vue';

const cabinets = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const statusFilter = ref('');
const pagination = ref(null);

// Модальные окна
const showViewModal = ref(false);
const showEditModal = ref(false);
const selectedCabinet = ref(null);
const editForm = ref({
  name: '',
  description: ''
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

const fetchCabinets = async (page = 1) => {
  loading.value = true;
  try {
    const params = new URLSearchParams({
      page: page.toString(),
      per_page: '15'
    });
    
    if (searchQuery.value) {
      params.append('search', searchQuery.value);
    }
    
    if (statusFilter.value) {
      params.append('status', statusFilter.value);
    }
    
    const response = await axios.get(`/cabinets?${params}`);
    cabinets.value = response.data.cabinets || [];
    pagination.value = response.data.pagination || {};
  } catch (error) {
    console.error('Ошибка загрузки кабинетов:', error);
    
    // Показываем уведомление об ошибке
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при загрузке кабинетов';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchCabinets(page);
  }
};

const handleSearch = () => {
  fetchCabinets(1);
};

const handleClearSearch = () => {
  searchQuery.value = '';
  fetchCabinets(1);
};

const viewCabinet = (cabinet) => {
  selectedCabinet.value = cabinet;
  showViewModal.value = true;
};

const editCabinet = (cabinet) => {
  selectedCabinet.value = cabinet;
  editForm.value = {
    name: cabinet.name,
    description: cabinet.description || ''
  };
  showEditModal.value = true;
};

const closeModals = () => {
  showViewModal.value = false;
  showEditModal.value = false;
  selectedCabinet.value = null;
  editForm.value = {
    name: '',
    description: ''
  };
};

const saveCabinet = async () => {
  if (!selectedCabinet.value) return;
  
  try {
    await axios.put(`/cabinets/${selectedCabinet.value.id}`, editForm.value);
    await fetchCabinets(pagination.value.current_page);
    closeModals();
    
    // Показываем уведомление об успехе
    if (window.showSuccessToast) {
      window.showSuccessToast('Успешно!', 'Кабинет успешно обновлен');
    }
  } catch (error) {
    console.error('Ошибка сохранения кабинета:', error);
    
    // Показываем уведомление об ошибке
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при обновлении кабинета';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  }
};

const toggleCabinetStatus = async (cabinet) => {
  try {
    await axios.patch(`/cabinets/${cabinet.id}/toggle-status`);
    await fetchCabinets(pagination.value.current_page);
    
    // Показываем уведомление об успехе
    if (window.showSuccessToast) {
      window.showSuccessToast('Успешно!', 'Статус кабинета изменен');
    }
  } catch (error) {
    console.error('Ошибка изменения статуса кабинета:', error);
    
    // Показываем уведомление об ошибке
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при изменении статуса кабинета';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  }
};

const deleteCabinet = async (cabinet) => {
  if (confirm(`Вы уверены, что хотите удалить кабинет "${cabinet.name}"?`)) {
    try {
      await axios.delete(`/cabinets/${cabinet.id}`);
      await fetchCabinets(pagination.value.current_page);
      
      // Показываем уведомление об успехе
      if (window.showSuccessToast) {
        window.showSuccessToast('Успешно!', 'Кабинет успешно удален');
      }
    } catch (error) {
      console.error('Ошибка удаления кабинета:', error);
      
      // Показываем уведомление об ошибке
      if (window.showErrorToast) {
        const errorMessage = error.response?.data?.message || 'Произошла ошибка при удалении кабинета';
        window.showErrorToast('Ошибка!', errorMessage);
      }
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
  fetchCabinets();
});
</script>