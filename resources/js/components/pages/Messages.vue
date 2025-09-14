<template>
  <div class="messages-page">
    <div class="bg-white shadow rounded-lg">
      <div class="px-6 py-4 border-b border-gray-200">
        <h1 class="text-lg font-medium text-gray-900">Уведомления</h1>
        <p class="mt-1 text-sm text-gray-500">Все ваши сообщения и уведомления</p>
      </div>

      <div v-if="loading" class="text-center py-8">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600 mx-auto"></div>
        <p class="mt-2 text-gray-500">Загрузка сообщений...</p>
      </div>

      <div v-else-if="messages.length === 0" class="text-center py-8">
        <div class="text-gray-400 mb-4">
          <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
          </svg>
        </div>
        <p class="text-gray-500">У вас пока нет сообщений</p>
      </div>

      <div v-else class="divide-y divide-gray-200">
        <div 
          v-for="message in messages" 
          :key="message.id"
          class="px-6 py-4 hover:bg-gray-50 cursor-pointer transition-colors"
          :class="{ 'bg-blue-50': !message.is_read }"
          @click="markAsRead(message.id)"
        >
          <div class="flex items-start space-x-4">
            <div class="flex-shrink-0">
              <div :class="[
                'w-3 h-3 rounded-full mt-1',
                getMessageTypeColor(message.type)
              ]"></div>
            </div>
            
            <div class="flex-1 min-w-0">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-2">
                  <h3 class="text-sm font-medium text-gray-900">
                    {{ message.title || 'Без заголовка' }}
                  </h3>
                  <span :class="[
                    'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                    getMessageTypeBadgeColor(message.type)
                  ]">
                    {{ getMessageTypeLabel(message.type) }}
                  </span>
                  <span v-if="!message.is_read" class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                    Новое
                  </span>
                </div>
                <p class="text-xs text-gray-500">{{ formatTime(message.created_at) }}</p>
              </div>
              
              <p class="mt-2 text-sm text-gray-600">{{ message.text }}</p>
              
              <div v-if="message.expires_at" class="mt-2 text-xs text-gray-400">
                Истекает: {{ formatDate(message.expires_at) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Пагинация -->
      <div v-if="pagination && pagination.last_page > 1" class="px-6 py-4 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            Показано {{ pagination.from || 0 }} - {{ pagination.to || 0 }} из {{ pagination.total || 0 }} сообщений
          </div>
          <div class="flex space-x-2">
            <button
              @click="changePage(pagination.current_page - 1)"
              :disabled="pagination.current_page <= 1"
              class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Предыдущая
            </button>
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="changePage(page)"
              :class="[
                page === pagination.current_page
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-700 hover:bg-gray-50',
                'px-3 py-1 text-sm border border-gray-300 rounded-md'
              ]"
            >
              {{ page }}
            </button>
            <button
              @click="changePage(pagination.current_page + 1)"
              :disabled="pagination.current_page >= pagination.last_page"
              class="px-3 py-1 text-sm border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Следующая
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useMessageStore } from '../../stores/useMessageStore';
import axios from '../../api/axios';

const messageStore = useMessageStore();

const messages = ref([]);
const loading = ref(false);
const pagination = ref(null);

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
    const response = await axios.get(`/messages?page=${page}&per_page=20`);
    messages.value = response.data.data || [];
    pagination.value = response.data.meta || {};
  } catch (error) {
    console.error('Ошибка загрузки сообщений:', error);
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при загрузке сообщений';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  } finally {
    loading.value = false;
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchMessages(page);
  }
};

const markAsRead = async (messageId) => {
  try {
    await messageStore.markAsRead(messageId);
    
    // Обновляем локальное состояние
    const message = messages.value.find(msg => msg.id === messageId);
    if (message) {
      message.is_read = true;
      message.read_at = new Date().toISOString();
    }
  } catch (error) {
    console.error('Ошибка отметки сообщения как прочитанного:', error);
  }
};

const getMessageTypeColor = (type) => {
  const colors = {
    success: 'bg-green-500',
    error: 'bg-red-500',
    warning: 'bg-yellow-500',
    info: 'bg-blue-500',
    system: 'bg-purple-500'
  };
  
  return colors[type] || 'bg-gray-500';
};

const getMessageTypeBadgeColor = (type) => {
  const colors = {
    success: 'bg-green-100 text-green-800',
    error: 'bg-red-100 text-red-800',
    warning: 'bg-yellow-100 text-yellow-800',
    info: 'bg-blue-100 text-blue-800',
    system: 'bg-purple-100 text-purple-800'
  };
  
  return colors[type] || 'bg-gray-100 text-gray-800';
};

const getMessageTypeLabel = (type) => {
  const labels = {
    success: 'Успех',
    error: 'Ошибка',
    warning: 'Предупреждение',
    info: 'Информация',
    system: 'Система'
  };
  
  return labels[type] || 'Сообщение';
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

const formatDate = (dateString) => {
  if (!dateString) return 'Не указано';
  
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
});
</script>
