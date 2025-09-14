<template>
  <div class="space-y-1">
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative">
      <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
      
      <input
        :id="id"
        :value="modelValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="inputClass"
        @input="handleInput"
        @keydown.enter="handleSearch"
        @blur="handleBlur"
      />
      
      <div v-if="modelValue" class="absolute inset-y-0 right-0 pr-3 flex items-center">
        <button
          @click="clearSearch"
          class="text-gray-400 hover:text-gray-600 focus:outline-none"
          type="button"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>
    
    <p v-if="error" class="text-sm text-red-600">{{ error }}</p>
    <p v-if="help" class="text-sm text-gray-500">{{ help }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  id: {
    type: String,
    default: 'search'
  },
  modelValue: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: 'Поиск...'
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  error: {
    type: String,
    default: ''
  },
  help: {
    type: String,
    default: ''
  },
  inputClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'search', 'clear', 'blur'])

const inputClass = computed(() => {
  const baseClass = 'block w-full pl-10 pr-10 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed'
  const customClass = props.inputClass || ''
  return `${baseClass} ${customClass}`
})

// Обработка ввода
const handleInput = (event) => {
  emit('update:modelValue', event.target.value)
}

// Обработка поиска (Enter)
const handleSearch = () => {
  emit('search', props.modelValue)
}

// Очистка поиска
const clearSearch = () => {
  emit('update:modelValue', '')
  emit('clear')
}

// Обработка потери фокуса
const handleBlur = (event) => {
  emit('blur', event.target.value)
}
</script>
