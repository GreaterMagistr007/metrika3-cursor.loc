<template>
  <div class="admin-users-management">
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Управление пользователями</h1>
      <p class="mt-2 text-gray-600">Просмотр и редактирование пользователей системы</p>
    </div>

    <!-- Фильтры и поиск -->
    <div class="bg-white shadow rounded-lg mb-6">
      <div class="px-4 py-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <SearchInput
              id="search"
              v-model="searchQuery"
              placeholder="Поиск по имени или телефону..."
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
              @click="fetchUsers"
              class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700"
            >
              Обновить
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Таблица пользователей -->
    <div class="bg-white shadow overflow-hidden sm:rounded-md">
      <div v-if="loading" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
        <p class="mt-2 text-gray-500">Загрузка пользователей...</p>
      </div>
      
      <div v-else-if="users.length === 0" class="text-center py-8">
        <p class="text-gray-500">Пользователи не найдены</p>
      </div>
      
      <ul v-else class="divide-y divide-gray-200">
        <li v-for="user in users" :key="user.id" class="px-6 py-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <div class="flex-shrink-0 h-10 w-10">
                <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                  <span class="text-sm font-medium text-gray-700">
                    {{ user.name ? user.name.charAt(0).toUpperCase() : 'U' }}
                  </span>
                </div>
              </div>
              <div class="ml-4">
                <div class="text-sm font-medium text-gray-900">
                  {{ user.name || 'Без имени' }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ user.phone }}
                </div>
                <div v-if="user.telegram_id" class="text-xs text-gray-400">
                  Telegram ID: {{ user.telegram_id }}
                </div>
              </div>
            </div>
            <div class="flex items-center space-x-4">
              <div class="text-sm text-gray-500">
                <div>Кабинетов: {{ user.cabinets_count || 0 }}</div>
                <div>Последний вход: {{ formatDate(user.last_login_at) }}</div>
              </div>
              <div class="flex space-x-2">
                <button
                  @click="viewUser(user)"
                  class="text-indigo-600 hover:text-indigo-900 text-sm font-medium"
                >
                  Просмотр
                </button>
                <button
                  @click="editUser(user)"
                  class="text-yellow-600 hover:text-yellow-900 text-sm font-medium"
                >
                  Редактировать
                </button>
                <button
                  @click="deleteUser(user)"
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

    <!-- Модальное окно просмотра пользователя -->
    <div v-if="showViewModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Информация о пользователе</h3>
            <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <div v-if="selectedUser" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700">Имя</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedUser.name || 'Не указано' }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Телефон</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedUser.phone }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Telegram ID</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedUser.telegram_id || 'Не указан' }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Последний вход</label>
              <p class="mt-1 text-sm text-gray-900">{{ formatDate(selectedUser.last_login_at) }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700">Количество кабинетов</label>
              <p class="mt-1 text-sm text-gray-900">{{ selectedUser.cabinets_count || 0 }}</p>
            </div>
          </div>
          
          <div class="mt-6 flex justify-end">
            <button @click="closeModals" class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400">
              Закрыть
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Модальное окно редактирования пользователя -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Редактирование пользователя</h3>
            <button @click="closeModals" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="saveUser" class="space-y-4">
            <div>
              <NameInput
                id="edit-name"
                v-model="editForm.name"
                label="Имя"
                placeholder="Введите имя"
                required
              />
            </div>
            
            <div>
              <PhoneInput
                id="edit-phone"
                v-model="editForm.phone"
                placeholder="+7 (XXX) XXX-XX-XX"
              />
            </div>
            
            <div>
              <TextInput
                id="edit-telegram"
                v-model="editForm.telegram_id"
                label="Telegram ID"
                placeholder="Введите Telegram ID"
              />
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
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
    
    <!-- Модальное окно подтверждения удаления -->
    <DeletionConfirmationModal
      :show="showDeletionModal"
      :user="selectedUser"
      :summary="deletionSummary"
      :loading="deletionLoading"
      @close="closeModals"
      @confirm="confirmDeletion"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from '../../api/adminAxios';
import SearchInput from '../SearchInput.vue';
import NameInput from '../NameInput.vue';
import PhoneInput from '../PhoneInput.vue';
import TextInput from '../TextInput.vue';
import DeletionConfirmationModal from './DeletionConfirmationModal.vue';

const users = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const statusFilter = ref('');
const pagination = ref(null);

// Модальные окна
const showViewModal = ref(false);
const showEditModal = ref(false);
const showDeletionModal = ref(false);
const selectedUser = ref(null);
const deletionSummary = ref(null);
const deletionLoading = ref(false);
const editForm = ref({
  name: '',
  phone: '',
  telegram_id: ''
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

const fetchUsers = async (page = 1) => {
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
    
    const response = await axios.get(`/users?${params}`);
    users.value = response.data.data;
    pagination.value = response.data.meta;
  } catch (error) {
    console.error('Ошибка загрузки пользователей:', error);
    
    // Показываем уведомление об ошибке
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при загрузке пользователей';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchUsers(page);
  }
};

const handleSearch = () => {
  fetchUsers(1);
};

const handleClearSearch = () => {
  searchQuery.value = '';
  fetchUsers(1);
};

const viewUser = (user) => {
  selectedUser.value = user;
  showViewModal.value = true;
};

const editUser = (user) => {
  selectedUser.value = user;
  editForm.value = {
    name: user.name || '',
    phone: user.phone || '',
    telegram_id: user.telegram_id || ''
  };
  showEditModal.value = true;
};

const closeModals = () => {
  showViewModal.value = false;
  showEditModal.value = false;
  showDeletionModal.value = false;
  selectedUser.value = null;
  deletionSummary.value = null;
  deletionLoading.value = false;
  editForm.value = {
    name: '',
    phone: '',
    telegram_id: ''
  };
};

const saveUser = async () => {
  if (!selectedUser.value) return;
  
  try {
    await axios.put(`/users/${selectedUser.value.id}`, editForm.value);
    await fetchUsers(pagination.value.current_page);
    closeModals();
    
    // Показываем уведомление об успехе
    if (window.showSuccessToast) {
      window.showSuccessToast('Успешно!', 'Пользователь успешно обновлен');
    }
  } catch (error) {
    console.error('Ошибка сохранения пользователя:', error);
    
    // Показываем уведомление об ошибке
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при обновлении пользователя';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  }
};

const deleteUser = async (user) => {
  try {
    // Получаем информацию об удалении
    const summaryResponse = await axios.get(`/users/${user.id}/deletion-summary`);
    deletionSummary.value = summaryResponse.data.summary;
    selectedUser.value = user;
    showDeletionModal.value = true;
  } catch (error) {
    console.error('Ошибка получения информации об удалении:', error);
    
    // Показываем уведомление об ошибке
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при получении информации об удалении';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  }
};

const confirmDeletion = async () => {
  if (!selectedUser.value) return;
  
  deletionLoading.value = true;
  
  try {
    await axios.delete(`/users/${selectedUser.value.id}`);
    await fetchUsers(pagination.value.current_page);
    
    // Показываем уведомление об успехе с подробностями
    if (window.showSuccessToast) {
      let successMessage = 'Пользователь и все связанные данные успешно удалены';
      if (deletionSummary.value?.total_cabinets > 0) {
        successMessage += ` (удалено ${deletionSummary.value.total_cabinets} кабинетов)`;
      }
      if (deletionSummary.value?.total_affected_users > 0) {
        successMessage += ` (уведомлено ${deletionSummary.value.total_affected_users} пользователей)`;
      }
      window.showSuccessToast('Успешно!', successMessage);
    }
    
    closeModals();
  } catch (error) {
    console.error('Ошибка удаления пользователя:', error);
    
    // Показываем уведомление об ошибке
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при удалении пользователя';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  } finally {
    deletionLoading.value = false;
  }
};

const formatDate = (dateString) => {
  if (!dateString) return 'Никогда';
  
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
  fetchUsers();
});
</script>