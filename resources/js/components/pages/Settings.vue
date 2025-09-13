<template>
  <div>
    <div class="mb-8">
      <h1 class="text-2xl font-bold text-gray-900">Настройки</h1>
      <p class="mt-2 text-gray-600">Управление пользователями и правами доступа</p>
    </div>

    <!-- Вкладки -->
    <div class="border-b border-gray-200 mb-6">
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
    <div v-if="activeTab === 'users'">
      <!-- Список пользователей -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
          <h3 class="text-lg font-medium text-gray-900">Пользователи кабинета</h3>
          <button
            @click="showInviteModal = true"
            class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700"
          >
            Пригласить пользователя
          </button>
        </div>
        
        <div class="overflow-x-auto">
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
                  Статус
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
                          {{ user.phone.charAt(0) }}
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
                        :class="user.role === 'owner' ? 'bg-green-100 text-green-800' : 'bg-blue-100 text-blue-800'">
                    {{ user.role === 'owner' ? 'Владелец' : 'Пользователь' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                    Активен
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    v-if="user.role !== 'owner'"
                    @click="removeUser(user)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Удалить
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div v-if="activeTab === 'permissions'">
      <div class="bg-white shadow rounded-lg p-6">
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const activeTab = ref('users');
const showInviteModal = ref(false);
const invitePhone = ref('');
const loading = ref(false);

const users = ref([
  {
    id: 1,
    phone: '+7 (999) 123-45-67',
    name: 'Иван Иванов',
    role: 'owner'
  },
  {
    id: 2,
    phone: '+7 (999) 987-65-43',
    name: 'Петр Петров',
    role: 'user'
  }
]);

const inviteUser = async () => {
  loading.value = true;
  try {
    // TODO: Реализовать API вызов
    console.log('Приглашение пользователя:', invitePhone.value);
    users.value.push({
      id: Date.now(),
      phone: invitePhone.value,
      name: 'Новый пользователь',
      role: 'user'
    });
    showInviteModal.value = false;
    invitePhone.value = '';
  } catch (error) {
    console.error('Ошибка при приглашении:', error);
  } finally {
    loading.value = false;
  }
};

const removeUser = (user) => {
  if (confirm(`Удалить пользователя ${user.phone}?`)) {
    const index = users.value.findIndex(u => u.id === user.id);
    if (index > -1) {
      users.value.splice(index, 1);
    }
  }
};

onMounted(() => {
  // TODO: Загрузить реальные данные
});
</script>
