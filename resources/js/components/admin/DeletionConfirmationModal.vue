<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
      <div class="mt-3">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-red-100 mr-4">
              <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
              </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900">Подтверждение удаления пользователя</h3>
          </div>
          <button @click="close" class="text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
        
        <div class="space-y-4">
          <!-- User info -->
          <div class="bg-gray-50 p-4 rounded-md">
            <h4 class="font-medium text-gray-900 mb-2">Удаляемый пользователь:</h4>
            <p class="text-sm text-gray-600">
              <strong>{{ user?.name || 'Без имени' }}</strong> ({{ user?.phone }})
            </p>
          </div>

          <!-- Deletion summary -->
          <div v-if="summary" class="space-y-3">
            <h4 class="font-medium text-gray-900">Будет удалено:</h4>
            
            <div class="space-y-2">
              <div class="flex items-center text-sm text-gray-600">
                <svg class="h-4 w-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd" />
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                Пользователь и все его данные
              </div>
              
              <div v-if="summary.total_cabinets > 0" class="flex items-center text-sm text-gray-600">
                <svg class="h-4 w-4 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" clip-rule="evenodd" />
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                {{ summary.total_cabinets }} кабинет(ов), которыми он владеет
              </div>
              
              <div v-if="summary.total_affected_users > 0" class="flex items-center text-sm text-gray-600">
                <svg class="h-4 w-4 text-yellow-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                {{ summary.total_affected_users }} пользователь(ей) получат уведомление
              </div>
            </div>

            <!-- Cabinets list -->
            <div v-if="summary.owned_cabinets && summary.owned_cabinets.length > 0" class="mt-3">
              <h5 class="text-sm font-medium text-gray-700 mb-2">Кабинеты:</h5>
              <ul class="text-sm text-gray-600 space-y-1">
                <li v-for="cabinet in summary.owned_cabinets" :key="cabinet.id" class="flex items-center">
                  <span class="w-2 h-2 bg-red-400 rounded-full mr-2"></span>
                  {{ cabinet.name }}
                </li>
              </ul>
            </div>

            <!-- Affected users list -->
            <div v-if="summary.affected_users && summary.affected_users.length > 0" class="mt-3">
              <h5 class="text-sm font-medium text-gray-700 mb-2">Затронутые пользователи:</h5>
              <ul class="text-sm text-gray-600 space-y-1">
                <li v-for="affectedUser in summary.affected_users" :key="affectedUser.id" class="flex items-center">
                  <span class="w-2 h-2 bg-yellow-400 rounded-full mr-2"></span>
                  {{ affectedUser.name || 'Без имени' }} ({{ affectedUser.phone }})
                </li>
              </ul>
            </div>
          </div>

          <!-- Warning -->
          <div class="bg-red-50 border border-red-200 rounded-md p-3">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">
                  Внимание!
                </h3>
                <div class="mt-1 text-sm text-red-700">
                  Это действие нельзя отменить. Все данные будут безвозвратно удалены.
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Buttons -->
        <div class="flex justify-end space-x-3 mt-6">
          <button
            type="button"
            @click="close"
            :disabled="loading"
            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-400 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Отмена
          </button>
          <button
            type="button"
            @click="confirmDeletion"
            :disabled="loading"
            class="bg-red-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? 'Удаление...' : 'Да, удалить' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false
  },
  user: {
    type: Object,
    default: null
  },
  summary: {
    type: Object,
    default: null
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['close', 'confirm'])

const close = () => {
  if (!props.loading) {
    emit('close')
  }
}

const confirmDeletion = () => {
  emit('confirm')
}
</script>