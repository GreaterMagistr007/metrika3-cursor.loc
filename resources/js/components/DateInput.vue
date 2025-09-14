<template>
  <div class="space-y-1">
    <label 
      v-if="label"
      :for="id" 
      class="block text-sm font-medium text-gray-700"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative">
      <input
        :id="id"
        :type="type"
        :value="formattedValue"
        :placeholder="placeholder"
        :disabled="disabled"
        :min="min"
        :max="max"
        :class="inputClass"
        @input="handleInput"
        @blur="handleBlur"
        @change="handleChange"
      />
      
      <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
      </div>
    </div>
    
    <p v-if="error" class="text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  id: {
    type: String,
    default: 'date-input'
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
    default: ''
  },
  type: {
    type: String,
    default: 'date'
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  min: {
    type: String,
    default: ''
  },
  max: {
    type: String,
    default: ''
  },
  error: {
    type: String,
    default: ''
  },
  inputClass: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'blur', 'change'])

const inputClass = computed(() => {
  const baseClass = 'w-full pl-3 pr-10 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed'
  const errorClass = props.error ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'
  const customClass = props.inputClass || ''
  return `${baseClass} ${errorClass} ${customClass}`
})

// Форматируем значение для отображения
const formattedValue = computed(() => {
  if (!props.modelValue) return ''
  
  // Если это дата в формате YYYY-MM-DD, возвращаем как есть
  if (props.type === 'date' && /^\d{4}-\d{2}-\d{2}$/.test(props.modelValue)) {
    return props.modelValue
  }
  
  // Если это datetime в формате YYYY-MM-DDTHH:MM, возвращаем как есть
  if (props.type === 'datetime-local' && /^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/.test(props.modelValue)) {
    return props.modelValue
  }
  
  // Если это время в формате HH:MM, возвращаем как есть
  if (props.type === 'time' && /^\d{2}:\d{2}$/.test(props.modelValue)) {
    return props.modelValue
  }
  
  return props.modelValue
})

// Обработка ввода
const handleInput = (event) => {
  emit('update:modelValue', event.target.value)
}

// Обработка изменения
const handleChange = (event) => {
  emit('change', event.target.value)
}

// Обработка потери фокуса
const handleBlur = (event) => {
  emit('blur', event.target.value)
}
</script>
