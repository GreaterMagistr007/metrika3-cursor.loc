<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Настройки</h1>
      <p class="mt-2 text-gray-600">Управление пользователями и правами доступа</p>
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
            <p>Для управления пользователями необходимо выбрать кабинет.</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Вкладки -->
    <div v-if="currentCabinet" class="border-b border-gray-200 mb-6">
      <nav class="-mb-px flex space-x-8">
        <button
          @click="activeTab = 'users'"
          :class="[
            activeTab === 'users'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          Пользователи
        </button>
        <button
          @click="activeTab = 'permissions'"
          :class="[
            activeTab === 'permissions'
              ? 'border-blue-500 text-blue-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          Права доступа
        </button>
      </nav>
    </div>

    <!-- Контент вкладок -->
    <div v-if="currentCabinet && activeTab === 'users'">
      <!-- Список пользователей -->
      <div class="bg-white shadow-sm rounded-lg border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-medium text-gray-900">Пользователи кабинета</h3>
          <button
            @click="showInviteModal = true"
            class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
          >
            Пригласить пользователя
          </button>
        </div>
        
        <div v-if="loading" class="flex justify-center py-8">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500"></div>
        </div>
        
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Пользователь
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Роль
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Права
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Действия
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                        <span class="text-sm font-medium text-gray-700">
                          {{ user.phone?.charAt(0) || 'U' }}
                        </span>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ user.phone }}</div>
                      <div class="text-sm text-gray-500">{{ user.name || 'Без имени' }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                        :class="user.is_owner ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                    {{ user.is_owner ? 'Владелец' : 'Пользователь' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex flex-wrap gap-1">
                    <span 
                      v-for="permission in user.permissions?.slice(0, 3)" 
                      :key="permission.id"
                      class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800"
                    >
                      {{ permission.name }}
                    </span>
                    <span 
                      v-if="user.permissions?.length > 3"
                      class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800"
                    >
                      +{{ user.permissions.length - 3 }}
                    </span>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="managePermissions(user)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Права
                    </button>
                    <button
                      v-if="!user.is_owner"
                      @click="removeUser(user)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Удалить
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="currentCabinet && activeTab === 'permissions'">
      <div class="bg-white shadow-sm rounded-lg border border-gray-200 p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Права доступа</h3>
        <p class="text-gray-600">Управление правами доступа будет реализовано в следующих версиях.</p>
      </div>
    </div>

    <!-- Модальное окно приглашения -->
    <div v-if="showInviteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Пригласить пользователя</h3>
          <form @submit.prevent="inviteUser">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Номер телефона
              </label>
              <input
                v-model="invitePhone"
                type="tel"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="+7 (999) 123-45-67"
              />
            </div>
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Роль
              </label>
              <select
                v-model="inviteRole"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="manager">Менеджер</option>
                <option value="operator">Оператор</option>
              </select>
            </div>
            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="showInviteModal = false"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
              >
                Отмена
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
              >
                Пригласить
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Модальное окно управления правами -->
    <div v-if="showPermissionsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Права пользователя: {{ selectedUser?.phone }}
          </h3>
          <div class="space-y-2 max-h-64 overflow-y-auto">
            <label 
              v-for="permission in availablePermissions" 
              :key="permission.id"
              class="flex items-center space-x-2"
            >
              <input
                type="checkbox"
                :value="permission.id"
                v-model="selectedPermissions"
                class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
              />
              <span class="text-sm text-gray-700">{{ permission.name }}</span>
            </label>
          </div>
          <div class="flex justify-end space-x-3 mt-4">
            <button
              @click="showPermissionsModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300"
            >
              Отмена
            </button>
            <button
              @click="savePermissions"
              :disabled="loading"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              Сохранить
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import { useAuthStore } from '../../stores/useAuthStore.js';
import { useMessageStore } from '../../stores/useMessageStore.js';
import api from '../../api/axios.js';

const authStore = useAuthStore();
const messageStore = useMessageStore();

// Reactive data
const activeTab = ref('users');
const showInviteModal = ref(false);
const showPermissionsModal = ref(false);
const invitePhone = ref('');
const inviteRole = ref('manager');
const loading = ref(false);
const users = ref([]);
const availablePermissions = ref([]);
const selectedUser = ref(null);
const selectedPermissions = ref([]);

// Computed properties
const currentCabinet = computed(() => authStore.currentCabinet);

// Methods
const loadUsers = async () => {
  if (!currentCabinet.value) return;
  
  loading.value = true;
  try {
    const response = await api.get(`/cabinets/${currentCabinet.value.id}`);
    const cabinet = response.data.data;
    users.value = cabinet.users || [];
  } catch (error) {
    console.error('Failed to load users:', error);
    messageStore.showToast('Ошибка загрузки пользователей', 'error');
  } finally {
    loading.value = false;
  }
};

const loadPermissions = async () => {
  try {
    const response = await api.get('/permissions');
    availablePermissions.value = response.data.data || [];
  } catch (error) {
    console.error('Failed to load permissions:', error);
  }
};

const inviteUser = async () => {
  if (!currentCabinet.value) return;
  
  loading.value = true;
  try {
    await api.post(`/cabinets/${currentCabinet.value.id}/invite`, {
      phone: invitePhone.value,
      role: inviteRole.value
    });
    
    messageStore.showToast('Пользователь приглашен', 'success');
    showInviteModal.value = false;
    invitePhone.value = '';
    inviteRole.value = 'manager';
    
    // Reload users
    await loadUsers();
  } catch (error) {
    console.error('Failed to invite user:', error);
    messageStore.showToast('Ошибка при приглашении пользователя', 'error');
  } finally {
    loading.value = false;
  }
};

const removeUser = async (user) => {
  if (!currentCabinet.value) return;
  
  if (confirm(`Удалить пользователя ${user.phone}?`)) {
    loading.value = true;
    try {
      await api.delete(`/cabinets/${currentCabinet.value.id}/users/${user.id}`);
      
      messageStore.showToast('Пользователь удален', 'success');
      
      // Reload users
      await loadUsers();
    } catch (error) {
      console.error('Failed to remove user:', error);
      messageStore.showToast('Ошибка при удалении пользователя', 'error');
    } finally {
      loading.value = false;
    }
  }
};

const managePermissions = async (user) => {
  selectedUser.value = user;
  selectedPermissions.value = user.permissions?.map(p => p.id) || [];
  showPermissionsModal.value = true;
};

const savePermissions = async () => {
  if (!currentCabinet.value || !selectedUser.value) return;
  
  loading.value = true;
  try {
    await api.post(`/cabinets/${currentCabinet.value.id}/users/${selectedUser.value.id}/permissions`, {
      permission_ids: selectedPermissions.value
    });
    
    messageStore.showToast('Права обновлены', 'success');
    showPermissionsModal.value = false;
    
    // Reload users
    await loadUsers();
  } catch (error) {
    console.error('Failed to save permissions:', error);
    messageStore.showToast('Ошибка при сохранении прав', 'error');
  } finally {
    loading.value = false;
  }
};

// Watchers
watch(() => currentCabinet.value, (newCabinet) => {
  if (newCabinet) {
    loadUsers();
  }
}, { immediate: true });

onMounted(() => {
  loadPermissions();
});
</script>
