<template>
  <div class="admin-messages-management">
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Управление сообщениями</h1>
          <p class="mt-2 text-gray-600">Создание и управление системными сообщениями</p>
        </div>
        <button
          @click="openCreateModal"
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
                <dd class="text-lg font-medium text-gray-900">{{ statistics.total_messages || 0 }}</dd>
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
                <dd class="text-lg font-medium text-gray-900">{{ statistics.active_messages || 0 }}</dd>
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
                <dt class="text-sm font-medium text-gray-500 truncate">Истекших</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.expired_messages || 0 }}</dd>
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
                <dt class="text-sm font-medium text-gray-500 truncate">Непрочитанных</dt>
                <dd class="text-lg font-medium text-gray-900">{{ statistics.unread_messages || 0 }}</dd>
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
            <SearchInput
              id="search"
              v-model="searchQuery"
              label="Поиск"
              placeholder="Поиск по тексту сообщения..."
              @search="handleSearch"
              @clear="handleClearSearch"
              @keyup.enter="handleSearch"
            />
          </div>
          <div>
            <SelectInput
              id="type"
              v-model="typeFilter"
              label="Тип"
              placeholder="Все типы"
              :options="typeOptions"
              @change="handleTypeChange"
            />
          </div>
          <div>
            <SelectInput
              id="status"
              v-model="statusFilter"
              label="Статус"
              placeholder="Все"
              :options="statusOptions"
              @change="handleStatusChange"
            />
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

    <!-- Модальное окно создания сообщения -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <!-- Заголовок -->
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Создать сообщение</h3>
            <button
              @click="closeCreateModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <!-- Форма -->
          <form @submit.prevent="createMessage" class="space-y-4">
            <!-- Заголовок -->
            <div>
              <TextInput
                id="create-title"
                v-model="createForm.title"
                label="Заголовок"
                placeholder="Введите заголовок сообщения"
                :error="createFormErrors.title"
              />
            </div>

            <!-- Текст сообщения -->
            <div>
              <label for="create-text" class="block text-sm font-medium text-gray-700 mb-1">
                Текст сообщения <span class="text-red-500">*</span>
              </label>
              <textarea
                id="create-text"
                v-model="createForm.text"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-300': createFormErrors.text }"
                placeholder="Введите текст сообщения"
                required
              ></textarea>
              <p v-if="createFormErrors.text" class="mt-1 text-sm text-red-600">{{ createFormErrors.text }}</p>
            </div>

            <!-- Тип сообщения -->
            <div>
              <SelectInput
                id="create-type"
                v-model="createForm.type"
                label="Тип сообщения"
                :options="createTypeOptions"
                :error="createFormErrors.type"
              />
            </div>

            <!-- Получатели -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Получатели <span class="text-red-500">*</span>
              </label>
              
              <div v-for="(recipient, index) in createForm.recipients" :key="index" class="flex items-center space-x-2 mb-2">
                <SelectInput
                  :id="`recipient-type-${index}`"
                  v-model="recipient.type"
                  :options="recipientTypeOptions"
                  class="flex-1"
                  @change="(value) => handleRecipientTypeChange(index, value)"
                />
                <div class="flex-1">
                  <input
                    v-if="recipient.type === 'cabinet' || recipient.type === 'user'"
                    v-model="recipient.id"
                    type="number"
                    :placeholder="recipient.type === 'cabinet' ? 'ID кабинета' : 'ID пользователя'"
                    :class="[
                      'w-full px-3 py-2 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                      createFormErrors[`recipients.${index}.id`] ? 'border-red-300' : 'border-gray-300'
                    ]"
                    required
                  />
                  <p v-if="createFormErrors[`recipients.${index}.id`]" class="mt-1 text-sm text-red-600">
                    {{ createFormErrors[`recipients.${index}.id`] }}
                  </p>
                </div>
                <button
                  type="button"
                  @click="removeRecipient(index)"
                  class="text-red-600 hover:text-red-800"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
              
              <button
                type="button"
                @click="addRecipient"
                :disabled="createForm.recipients.some(r => r.type === 'all')"
                :class="[
                  'text-sm font-medium',
                  createForm.recipients.some(r => r.type === 'all')
                    ? 'text-gray-400 cursor-not-allowed'
                    : 'text-indigo-600 hover:text-indigo-800'
                ]"
              >
                + Добавить получателя
              </button>
              
              <p v-if="createFormErrors.recipients" class="mt-1 text-sm text-red-600">{{ createFormErrors.recipients }}</p>
            </div>

            <!-- Дополнительные настройки -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Активность -->
              <div class="flex items-center">
                <input
                  id="create-active"
                  v-model="createForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label for="create-active" class="ml-2 block text-sm text-gray-900">
                  Активное сообщение
                </label>
              </div>

              <!-- Постоянное сообщение -->
              <div class="flex items-center">
                <input
                  id="create-persistent"
                  v-model="createForm.is_persistent"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label for="create-persistent" class="ml-2 block text-sm text-gray-900">
                  Постоянное сообщение
                </label>
              </div>
            </div>

            <!-- Дата истечения -->
            <div v-if="!createForm.is_persistent">
              <DateInput
                id="create-expires"
                v-model="createForm.expires_at"
                label="Дата истечения"
                :error="createFormErrors.expires_at"
              />
            </div>

            <!-- Кнопки -->
            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeCreateModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
              >
                Отмена
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700"
              >
                Создать сообщение
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Модальное окно редактирования сообщения -->
    <div v-if="showEditModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
      <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <div class="mt-3">
          <!-- Заголовок -->
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Редактировать сообщение</h3>
            <button
              @click="closeEditModal"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>

          <!-- Форма -->
          <form @submit.prevent="updateMessage" class="space-y-4">
            <!-- Заголовок -->
            <div>
              <TextInput
                id="edit-title"
                v-model="editForm.title"
                label="Заголовок"
                placeholder="Введите заголовок сообщения"
                :error="editFormErrors.title"
              />
            </div>

            <!-- Текст сообщения -->
            <div>
              <label for="edit-text" class="block text-sm font-medium text-gray-700 mb-1">
                Текст сообщения <span class="text-red-500">*</span>
              </label>
              <textarea
                id="edit-text"
                v-model="editForm.text"
                rows="4"
                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                :class="{ 'border-red-300': editFormErrors.text }"
                placeholder="Введите текст сообщения"
                required
              ></textarea>
              <p v-if="editFormErrors.text" class="mt-1 text-sm text-red-600">{{ editFormErrors.text }}</p>
            </div>

            <!-- Тип сообщения -->
            <div>
              <SelectInput
                id="edit-type"
                v-model="editForm.type"
                label="Тип сообщения"
                :options="createTypeOptions"
                :error="editFormErrors.type"
              />
            </div>

            <!-- Получатели -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Получатели <span class="text-red-500">*</span>
              </label>
              
              <div v-for="(recipient, index) in editForm.recipients" :key="index" class="flex items-center space-x-2 mb-2">
                <SelectInput
                  :id="`edit-recipient-type-${index}`"
                  v-model="recipient.type"
                  :options="recipientTypeOptions"
                  class="flex-1"
                  @change="(value) => handleEditRecipientTypeChange(index, value)"
                />
                <div class="flex-1">
                  <input
                    v-if="recipient.type === 'cabinet' || recipient.type === 'user'"
                    v-model="recipient.id"
                    type="number"
                    :placeholder="recipient.type === 'cabinet' ? 'ID кабинета' : 'ID пользователя'"
                    :class="[
                      'w-full px-3 py-2 border rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                      editFormErrors[`recipients.${index}.id`] ? 'border-red-300' : 'border-gray-300'
                    ]"
                    required
                  />
                  <p v-if="editFormErrors[`recipients.${index}.id`]" class="mt-1 text-sm text-red-600">
                    {{ editFormErrors[`recipients.${index}.id`] }}
                  </p>
                </div>
                <button
                  type="button"
                  @click="removeEditRecipient(index)"
                  class="text-red-600 hover:text-red-800"
                >
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                  </svg>
                </button>
              </div>
              
              <button
                type="button"
                @click="addEditRecipient"
                :disabled="editForm.recipients.some(r => r.type === 'all')"
                :class="[
                  'text-sm font-medium',
                  editForm.recipients.some(r => r.type === 'all')
                    ? 'text-gray-400 cursor-not-allowed'
                    : 'text-indigo-600 hover:text-indigo-800'
                ]"
              >
                + Добавить получателя
              </button>
              
              <p v-if="editFormErrors.recipients" class="mt-1 text-sm text-red-600">{{ editFormErrors.recipients }}</p>
            </div>

            <!-- Дополнительные настройки -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Активность -->
              <div class="flex items-center">
                <input
                  id="edit-active"
                  v-model="editForm.is_active"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label for="edit-active" class="ml-2 block text-sm text-gray-900">
                  Активное сообщение
                </label>
              </div>

              <!-- Постоянное сообщение -->
              <div class="flex items-center">
                <input
                  id="edit-persistent"
                  v-model="editForm.is_persistent"
                  type="checkbox"
                  class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
                />
                <label for="edit-persistent" class="ml-2 block text-sm text-gray-900">
                  Постоянное сообщение
                </label>
              </div>
            </div>

            <!-- Дата истечения -->
            <div v-if="!editForm.is_persistent">
              <DateInput
                id="edit-expires"
                v-model="editForm.expires_at"
                label="Дата истечения"
                :error="editFormErrors.expires_at"
              />
            </div>

            <!-- Кнопки -->
            <div class="flex justify-end space-x-3 pt-4">
              <button
                type="button"
                @click="closeEditModal"
                class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
              >
                Отмена
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700"
              >
                Сохранить изменения
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
import SelectInput from '../SelectInput.vue';
import TextInput from '../TextInput.vue';
import DateInput from '../DateInput.vue';

const messages = ref([]);
const statistics = ref({});
const loading = ref(false);
const searchQuery = ref('');
const typeFilter = ref('');
const statusFilter = ref('');
const pagination = ref(null);
const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingMessage = ref(null);

// Форма создания сообщения
const createForm = ref({
  title: '',
  text: '',
  type: 'info',
  is_active: true,
  is_persistent: false,
  expires_at: '',
  recipients: []
});

// Форма редактирования сообщения
const editForm = ref({
  title: '',
  text: '',
  type: 'info',
  is_active: true,
  is_persistent: false,
  expires_at: '',
  recipients: []
});

// Ошибки валидации
const createFormErrors = ref({});
const editFormErrors = ref({});

// Опции для селектов
const typeOptions = ref([
  { value: '', label: 'Все типы' },
  { value: 'success', label: 'Success' },
  { value: 'error', label: 'Error' },
  { value: 'warning', label: 'Warning' },
  { value: 'info', label: 'Info' },
  { value: 'system', label: 'System' }
]);

const statusOptions = ref([
  { value: '', label: 'Все' },
  { value: 'active', label: 'Активные' },
  { value: 'inactive', label: 'Неактивные' }
]);

// Опции для создания сообщения
const createTypeOptions = ref([
  { value: 'success', label: 'Success' },
  { value: 'error', label: 'Error' },
  { value: 'warning', label: 'Warning' },
  { value: 'info', label: 'Info' },
  { value: 'system', label: 'System' }
]);

const recipientTypeOptions = ref([
  { value: 'all', label: 'Все пользователи' },
  { value: 'cabinet', label: 'Пользователи кабинета' },
  { value: 'user', label: 'Конкретный пользователь' }
]);

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
      params.append('is_active', statusFilter.value === 'active' ? '1' : '0');
    }
    
    const response = await axios.get(`/messages?${params}`);
    messages.value = response.data.messages || [];
    pagination.value = response.data.pagination || {};
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

const fetchStatistics = async () => {
  try {
    const response = await axios.get('/messages-statistics');
    statistics.value = response.data.statistics || {};
  } catch (error) {
    console.error('Ошибка загрузки статистики:', error);
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при загрузке статистики';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  }
};

const changePage = (page) => {
  if (page >= 1 && page <= pagination.value.last_page) {
    fetchMessages(page);
  }
};

const handleSearch = () => {
  fetchMessages(1);
};

const handleClearSearch = () => {
  searchQuery.value = '';
  fetchMessages(1);
};

const handleTypeChange = () => {
  fetchMessages(1);
};

const handleStatusChange = () => {
  fetchMessages(1);
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

const editMessage = async (message) => {
  try {
    // Загружаем полную информацию о сообщении
    const response = await axios.get(`/messages/${message.id}`);
    const fullMessage = response.data.message;
    
    editingMessage.value = fullMessage;
    showEditModal.value = true;
    editFormErrors.value = {};
    
    // Заполняем форму данными сообщения
    editForm.value = {
      title: fullMessage.title || '',
      text: fullMessage.text || '',
      type: fullMessage.type || 'info',
      is_active: fullMessage.is_active || false,
      is_persistent: fullMessage.is_persistent || false,
      expires_at: fullMessage.expires_at || '',
      recipients: fullMessage.recipients ? fullMessage.recipients.map(r => ({
        type: r.recipient_type,
        id: r.recipient_id
      })) : []
    };
  } catch (error) {
    console.error('Ошибка загрузки сообщения для редактирования:', error);
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при загрузке сообщения';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  }
};

const toggleMessageStatus = async (message) => {
  try {
    await axios.patch(`/messages/${message.id}/toggle-active`);
    await fetchMessages(pagination.value.current_page);
    
    // Показываем уведомление об успехе
    if (window.showSuccessToast) {
      window.showSuccessToast('Успешно!', 'Статус сообщения изменен');
    }
  } catch (error) {
    console.error('Ошибка изменения статуса сообщения:', error);
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при изменении статуса сообщения';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  }
};

const deleteMessage = async (message) => {
  if (confirm(`Вы уверены, что хотите удалить сообщение "${message.title || message.text}"?`)) {
    try {
      await axios.delete(`/messages/${message.id}`);
      await fetchMessages(pagination.value.current_page);
      
      // Показываем уведомление об успехе
      if (window.showSuccessToast) {
        window.showSuccessToast('Успешно!', 'Сообщение удалено');
      }
    } catch (error) {
      console.error('Ошибка удаления сообщения:', error);
      if (window.showErrorToast) {
        const errorMessage = error.response?.data?.message || 'Произошла ошибка при удалении сообщения';
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

// Методы для создания сообщения
const openCreateModal = () => {
  showCreateModal.value = true;
  createFormErrors.value = {};
  createForm.value = {
    title: '',
    text: '',
    type: 'info',
    is_active: true,
    is_persistent: false,
    expires_at: '',
    recipients: []
  };
};

const closeCreateModal = () => {
  showCreateModal.value = false;
  createFormErrors.value = {};
  createForm.value = {
    title: '',
    text: '',
    type: 'info',
    is_active: true,
    is_persistent: false,
    expires_at: '',
    recipients: []
  };
};

const addRecipient = () => {
  // Если уже есть "Все пользователи", не добавляем больше получателей
  if (createForm.value.recipients.some(r => r.type === 'all')) {
    return;
  }
  
  createForm.value.recipients.push({
    type: 'all',
    id: null
  });
};

const removeRecipient = (index) => {
  createForm.value.recipients.splice(index, 1);
};

const handleRecipientTypeChange = (index, newType) => {
  const recipient = createForm.value.recipients[index];
  recipient.type = newType;
  
  // Если выбран "Все пользователи", очищаем ID и удаляем все остальные получатели
  if (newType === 'all') {
    recipient.id = null;
    // Оставляем только этого получателя
    createForm.value.recipients = [recipient];
  } else {
    // Если выбран другой тип, очищаем ID
    recipient.id = null;
  }
};

const createMessage = async () => {
  createFormErrors.value = {};
  
  // Валидация
  if (!createForm.value.text.trim()) {
    createFormErrors.value.text = 'Текст сообщения обязателен';
    return;
  }
  
  if (createForm.value.recipients.length === 0) {
    createFormErrors.value.recipients = 'Необходимо указать получателей';
    return;
  }
  
  // Валидация получателей
  for (let i = 0; i < createForm.value.recipients.length; i++) {
    const recipient = createForm.value.recipients[i];
    
    if (recipient.type === 'cabinet' && (!recipient.id || recipient.id <= 0)) {
      createFormErrors.value[`recipients.${i}.id`] = 'ID кабинета обязателен';
      return;
    }
    
    if (recipient.type === 'user' && (!recipient.id || recipient.id <= 0)) {
      createFormErrors.value[`recipients.${i}.id`] = 'ID пользователя обязателен';
      return;
    }
  }
  
  try {
    const response = await axios.post('/messages', createForm.value);
    
    if (window.showSuccessToast) {
      window.showSuccessToast('Успешно!', 'Сообщение создано');
    }
    
    closeCreateModal();
    await fetchMessages(1);
    await fetchStatistics();
  } catch (error) {
    console.error('Ошибка создания сообщения:', error);
    
    if (error.response?.data?.errors) {
      createFormErrors.value = error.response.data.errors;
    }
    
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при создании сообщения';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  }
};

// Методы для редактирования сообщения
const openEditModal = () => {
  showEditModal.value = true;
  editFormErrors.value = {};
};

const closeEditModal = () => {
  showEditModal.value = false;
  editFormErrors.value = {};
  editingMessage.value = null;
  editForm.value = {
    title: '',
    text: '',
    type: 'info',
    is_active: true,
    is_persistent: false,
    expires_at: '',
    recipients: []
  };
};

const addEditRecipient = () => {
  // Если уже есть "Все пользователи", не добавляем больше получателей
  if (editForm.value.recipients.some(r => r.type === 'all')) {
    return;
  }
  
  editForm.value.recipients.push({
    type: 'all',
    id: null
  });
};

const removeEditRecipient = (index) => {
  editForm.value.recipients.splice(index, 1);
};

const handleEditRecipientTypeChange = (index, newType) => {
  const recipient = editForm.value.recipients[index];
  recipient.type = newType;
  
  // Если выбран "Все пользователи", очищаем ID и удаляем все остальные получатели
  if (newType === 'all') {
    recipient.id = null;
    // Оставляем только этого получателя
    editForm.value.recipients = [recipient];
  } else {
    // Если выбран другой тип, очищаем ID
    recipient.id = null;
  }
};

const updateMessage = async () => {
  editFormErrors.value = {};
  
  // Валидация
  if (!editForm.value.text.trim()) {
    editFormErrors.value.text = 'Текст сообщения обязателен';
    return;
  }
  
  if (editForm.value.recipients.length === 0) {
    editFormErrors.value.recipients = 'Необходимо указать получателей';
    return;
  }
  
  // Валидация получателей
  for (let i = 0; i < editForm.value.recipients.length; i++) {
    const recipient = editForm.value.recipients[i];
    
    if (recipient.type === 'cabinet' && (!recipient.id || recipient.id <= 0)) {
      editFormErrors.value[`recipients.${i}.id`] = 'ID кабинета обязателен';
      return;
    }
    
    if (recipient.type === 'user' && (!recipient.id || recipient.id <= 0)) {
      editFormErrors.value[`recipients.${i}.id`] = 'ID пользователя обязателен';
      return;
    }
  }
  
  try {
    const response = await axios.put(`/messages/${editingMessage.value.id}`, editForm.value);
    
    if (window.showSuccessToast) {
      window.showSuccessToast('Успешно!', 'Сообщение обновлено');
    }
    
    closeEditModal();
    await fetchMessages(pagination.value.current_page);
    await fetchStatistics();
  } catch (error) {
    console.error('Ошибка обновления сообщения:', error);
    
    if (error.response?.data?.errors) {
      editFormErrors.value = error.response.data.errors;
    }
    
    if (window.showErrorToast) {
      const errorMessage = error.response?.data?.message || 'Произошла ошибка при обновлении сообщения';
      window.showErrorToast('Ошибка!', errorMessage);
    }
  }
};

onMounted(() => {
  fetchMessages();
  fetchStatistics();
});
</script>