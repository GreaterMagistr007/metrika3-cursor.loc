<template>
  <div>
    <!-- Заголовок с кнопкой создания -->
    <div class="mb-8 flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Управление кабинетами</h1>
        <p class="mt-2 text-gray-600">Создавайте и управляйте вашими кабинетами</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
      >
        Создать кабинет
      </button>
    </div>

    <!-- Список кабинетов -->
    <div v-if="loading" class="flex justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
    </div>

    <div v-else-if="cabinets.length === 0" class="text-center py-12">
      <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">Нет кабинетов</h3>
      <p class="mt-1 text-sm text-gray-500">Начните с создания нового кабинета.</p>
      <div class="mt-6">
        <button
          @click="showCreateModal = true"
          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors"
        >
          Создать кабинет
        </button>
      </div>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="cabinet in cabinets"
        :key="cabinet.id"
        class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow"
      >
        <div class="p-6">
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <h3 class="text-lg font-medium text-gray-900">{{ cabinet.name }}</h3>
              <p v-if="cabinet.description" class="mt-1 text-sm text-gray-500">
                {{ cabinet.description }}
              </p>
              <div class="mt-2 flex items-center text-sm text-gray-500">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                </svg>
                {{ cabinet.users_count }} пользователей
              </div>
              <div class="mt-1 text-xs text-gray-400">
                Создан {{ formatDate(cabinet.created_at) }}
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <button
                @click="selectCabinet(cabinet)"
                class="text-blue-600 hover:text-blue-700 text-sm font-medium"
              >
                Выбрать
              </button>
              <div class="relative">
                <button
                  @click="toggleCabinetMenu(cabinet.id)"
                  class="text-gray-400 hover:text-gray-600 p-1"
                >
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"></path>
                  </svg>
                </button>
                <div
                  v-if="showCabinetMenu === cabinet.id"
                  class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10 border border-gray-200"
                >
                  <div class="py-1">
                    <button
                      @click="editCabinet(cabinet)"
                      class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                    >
                      Редактировать
                    </button>
                    <button
                      @click="deleteCabinet(cabinet)"
                      class="block w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100"
                    >
                      Удалить
                    </button>
                  </div>
                </div>
              </div>
            </div>
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

    <!-- Модальное окно редактирования кабинета -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeEditModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Редактировать кабинет</h3>
          <form @submit.prevent="updateCabinet">
            <div class="mb-4">
              <label for="edit-cabinet-name" class="block text-sm font-medium text-gray-700 mb-2">
                Название кабинета
              </label>
              <input
                id="edit-cabinet-name"
                v-model="editCabinetName"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Введите название кабинета"
              />
            </div>
            <div class="mb-4">
              <label for="edit-cabinet-description" class="block text-sm font-medium text-gray-700 mb-2">
                Описание (необязательно)
              </label>
              <textarea
                id="edit-cabinet-description"
                v-model="editCabinetDescription"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Введите описание кабинета"
              ></textarea>
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="closeEditModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200"
              >
                Отмена
              </button>
              <button
                type="submit"
                :disabled="updating"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                {{ updating ? 'Сохранение...' : 'Сохранить' }}
              </button>
            </div>
          </form>
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
const cabinets = ref([]);
const loading = ref(false);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const showCabinetMenu = ref(null);
const creating = ref(false);
const updating = ref(false);

// Form data
const newCabinetName = ref('');
const newCabinetDescription = ref('');
const editCabinetName = ref('');
const editCabinetDescription = ref('');
const editingCabinet = ref(null);

// Computed properties
const user = computed(() => authStore.user);

// Methods
const loadCabinets = async () => {
  loading.value = true;
  try {
    const response = await api.get('/cabinets');
    cabinets.value = response.data.data || [];
  } catch (error) {
    console.error('Failed to load cabinets:', error);
    messageStore.showToast('Ошибка загрузки кабинетов', 'error');
  } finally {
    loading.value = false;
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
    
    // Обновляем список кабинетов
    await loadCabinets();
    
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

const editCabinet = (cabinet) => {
  editingCabinet.value = cabinet;
  editCabinetName.value = cabinet.name;
  editCabinetDescription.value = cabinet.description || '';
  showEditModal.value = true;
  showCabinetMenu.value = null;
};

const updateCabinet = async () => {
  if (!editCabinetName.value.trim() || !editingCabinet.value) return;
  
  updating.value = true;
  try {
    await api.put(`/cabinets/${editingCabinet.value.id}`, {
      name: editCabinetName.value.trim(),
      description: editCabinetDescription.value.trim() || null
    });
    
    // Обновляем список кабинетов
    await loadCabinets();
    
    // Закрываем модальное окно
    closeEditModal();
    
    // Показываем уведомление об успехе
    messageStore.showToast('Кабинет успешно обновлен!', 'success');
    
  } catch (error) {
    console.error('Ошибка при обновлении кабинета:', error);
    messageStore.showToast('Ошибка при обновлении кабинета. Попробуйте еще раз.', 'error');
  } finally {
    updating.value = false;
  }
};

const deleteCabinet = async (cabinet) => {
  if (!confirm(`Вы уверены, что хотите удалить кабинет "${cabinet.name}"?`)) {
    return;
  }
  
  try {
    await api.delete(`/cabinets/${cabinet.id}`);
    
    // Обновляем список кабинетов
    await loadCabinets();
    
    // Показываем уведомление об успехе
    messageStore.showToast('Кабинет успешно удален!', 'success');
    
  } catch (error) {
    console.error('Ошибка при удалении кабинета:', error);
    messageStore.showToast('Ошибка при удалении кабинета. Попробуйте еще раз.', 'error');
  } finally {
    showCabinetMenu.value = null;
  }
};

const selectCabinet = (cabinet) => {
  authStore.setCurrentCabinet(cabinet);
  messageStore.showToast(`Выбран кабинет: ${cabinet.name}`, 'success');
  router.push('/');
};

const toggleCabinetMenu = (cabinetId) => {
  showCabinetMenu.value = showCabinetMenu.value === cabinetId ? null : cabinetId;
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  newCabinetName.value = '';
  newCabinetDescription.value = '';
};

const closeEditModal = () => {
  showEditModal.value = false;
  editingCabinet.value = null;
  editCabinetName.value = '';
  editCabinetDescription.value = '';
};

const formatDate = (dateString) => {
  if (!dateString) return 'Неизвестно';
  
  const date = new Date(dateString);
  return date.toLocaleDateString('ru-RU', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  });
};

// Close menu when clicking outside
const handleClickOutside = (event) => {
  if (!event.target.closest('.relative')) {
    showCabinetMenu.value = null;
  }
};

onMounted(() => {
  loadCabinets();
  document.addEventListener('click', handleClickOutside);
});
</script>
