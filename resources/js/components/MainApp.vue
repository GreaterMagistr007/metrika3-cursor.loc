<template>
  <div id="main-app" class="main-app">
    <!-- Заголовок -->
    <header class="main-app-header bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center space-x-4">
            <h1 class="text-xl font-semibold text-gray-900">Metrika3 Cabinet</h1>
            
            <!-- Переключатель кабинетов -->
            <div v-if="isAuthenticated && hasMultipleCabinets" class="flex items-center space-x-2">
              <label class="text-sm font-medium text-gray-700">Кабинет:</label>
              <select 
                v-model="selectedCabinetId" 
                @change="switchCabinet"
                class="px-3 py-1 border border-gray-300 rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option 
                  v-for="cabinet in userCabinets" 
                  :key="cabinet.id" 
                  :value="cabinet.id"
                >
                  {{ cabinet.name }}
                </option>
              </select>
            </div>
            
            <!-- Текущий кабинет (если один) -->
            <div v-else-if="isAuthenticated && currentCabinet" class="flex items-center space-x-2">
              <span class="text-sm text-gray-500">Кабинет:</span>
              <span class="text-sm font-medium text-gray-900">{{ currentCabinet.name }}</span>
            </div>
          </div>
          
          <div class="flex items-center space-x-4">
            <!-- Центр уведомлений -->
            <div v-if="isAuthenticated" class="relative">
              <button 
                @click="toggleNotifications"
                class="relative p-2 text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-md"
              >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM4 19h6v-6H4v6zM4 5h6V1H4v4zM15 7h5l-5-5v5z"></path>
                </svg>
                <span v-if="unreadCount > 0" class="absolute -top-1 -right-1 h-4 w-4 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                  {{ unreadCount }}
                </span>
              </button>
              
              <!-- Dropdown уведомлений -->
              <div v-if="showNotifications" class="absolute right-0 mt-2 w-80 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                <div class="py-1">
                  <div class="px-4 py-2 border-b border-gray-200">
                    <h3 class="text-sm font-medium text-gray-900">Уведомления</h3>
                  </div>
                  <div class="max-h-64 overflow-y-auto">
                    <div v-if="unreadMessages.length === 0" class="px-4 py-3 text-sm text-gray-500 text-center">
                      Нет новых уведомлений
                    </div>
                    <div v-else>
                      <div 
                        v-for="message in unreadMessages.slice(0, 5)" 
                        :key="message.id"
                        class="px-4 py-3 hover:bg-gray-50 cursor-pointer"
                        @click="markAsRead(message.id)"
                      >
                        <div class="flex items-start space-x-3">
                          <div class="flex-shrink-0">
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2"></div>
                          </div>
                          <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-900">{{ message.title || message.text }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ formatTime(message.created_at) }}</p>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div v-if="unreadMessages.length > 0" class="px-4 py-2 border-t border-gray-200">
                    <button 
                      @click="goToNotifications"
                      class="text-sm text-blue-600 hover:text-blue-500"
                    >
                      Показать все уведомления
                    </button>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Информация о пользователе -->
            <div class="flex items-center space-x-3">
              <div class="text-right">
                <div class="text-sm font-medium text-gray-900">{{ user?.name || 'Пользователь' }}</div>
                <div class="text-xs text-gray-500">{{ user?.phone || 'Не авторизован' }}</div>
              </div>
              
              <div class="flex items-center space-x-2">
                <button 
                  v-if="!isAuthenticated"
                  @click="goToLogin"
                  class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-blue-700 transition-colors"
                >
                  Войти
                </button>
                <button 
                  v-else
                  @click="logout"
                  class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700 transition-colors"
                >
                  Выйти
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Основной контент -->
    <div class="flex min-h-screen bg-gray-50">
      <!-- Боковая панель -->
      <aside v-if="isAuthenticated" class="main-app-sidebar w-64 bg-white shadow-sm">
        <nav class="mt-5 px-2 space-y-1">
          <router-link 
            to="/" 
            class="group flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors"
            :class="[
              $route.name === 'dashboard' 
                ? 'bg-blue-100 text-blue-700' 
                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
            ]"
          >
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
            </svg>
            Дашборд
          </router-link>
          
          <router-link 
            to="/cabinets" 
            class="group flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors"
            :class="[
              $route.name === 'cabinets' 
                ? 'bg-blue-100 text-blue-700' 
                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
            ]"
          >
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
            </svg>
            Кабинеты
          </router-link>
          
          <router-link 
            to="/settings" 
            class="group flex items-center px-3 py-2 rounded-md text-sm font-medium transition-colors"
            :class="[
              $route.name === 'settings' 
                ? 'bg-blue-100 text-blue-700' 
                : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
            ]"
          >
            <svg class="mr-3 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Настройки
          </router-link>
        </nav>
      </aside>

      <!-- Контент -->
      <main class="main-app-content flex-1 p-6">
        <router-view />
      </main>
    </div>

    <!-- Toast уведомления -->
    <Toast />
  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../stores/useAuthStore.js';
import { useMessageStore } from '../stores/useMessageStore.js';
import Toast from './Toast.vue';

const router = useRouter();
const authStore = useAuthStore();
const messageStore = useMessageStore();

// Reactive data
const selectedCabinetId = ref(null);
const showNotifications = ref(false);

// Computed properties
const user = computed(() => authStore.user);
const isAuthenticated = computed(() => authStore.isAuthenticated);
const currentCabinet = computed(() => authStore.currentCabinet);
const userCabinets = computed(() => authStore.userCabinets);
const hasMultipleCabinets = computed(() => authStore.hasMultipleCabinets);
const unreadMessages = computed(() => messageStore.unreadMessages);
const unreadCount = computed(() => messageStore.unreadCount);

// Methods
const goToLogin = () => {
  router.push('/login');
};

const logout = async () => {
  await authStore.logout();
  messageStore.clearMessages();
  router.push('/login');
};

const switchCabinet = () => {
  if (selectedCabinetId.value) {
    const cabinet = userCabinets.value.find(c => c.id === selectedCabinetId.value);
    if (cabinet) {
      authStore.setCurrentCabinet(cabinet);
    }
  }
};

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
};

const markAsRead = async (messageId) => {
  await messageStore.markAsRead(messageId);
  showNotifications.value = false;
};

const goToNotifications = () => {
  showNotifications.value = false;
  // TODO: Navigate to notifications page when implemented
  console.log('Navigate to notifications page');
};

const formatTime = (dateString) => {
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
  } else {
    return date.toLocaleDateString('ru-RU');
  }
};

// Watchers
watch(() => authStore.userCabinets, (newCabinets) => {
  if (newCabinets && newCabinets.length > 0) {
    // Set first cabinet as current if none selected
    if (!authStore.currentCabinet) {
      authStore.setCurrentCabinet(newCabinets[0]);
    }
    // Set selected cabinet ID
    selectedCabinetId.value = authStore.currentCabinet?.id || newCabinets[0].id;
  }
}, { immediate: true });

watch(() => authStore.currentCabinet, (newCabinet) => {
  if (newCabinet) {
    selectedCabinetId.value = newCabinet.id;
  }
});

// Close notifications when clicking outside
const handleClickOutside = (event) => {
  if (showNotifications.value && !event.target.closest('.relative')) {
    showNotifications.value = false;
  }
};

onMounted(async () => {
  // Initialize authentication state
  authStore.initAuth();
  
  // Load user data if authenticated
  if (authStore.isAuthenticated) {
    await authStore.fetchUser();
  }
  
  // Add click outside listener
  document.addEventListener('click', handleClickOutside);
});

// Cleanup
import { onUnmounted } from 'vue';
onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>
