<template>
  <div class="space-y-1">
    <label 
      :for="id" 
      class="block text-sm font-medium text-gray-700"
    >
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <input
      :id="id"
      :type="type"
      :value="modelValue"
      :placeholder="placeholder"
      :disabled="disabled"
      :maxlength="maxLength"
      :class="inputClass"
      @input="handleInput"
      @blur="handleBlur"
      @keydown="handleKeydown"
      @paste="handlePaste"
    />
    
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
    default: 'name'
  },
  modelValue: {
    type: String,
    default: ''
  },
  label: {
    type: String,
    default: 'Имя'
  },
  placeholder: {
    type: String,
    default: 'Введите ваше имя'
  },
  type: {
    type: String,
    default: 'text'
  },
  required: {
    type: Boolean,
    default: false
  },
  disabled: {
    type: Boolean,
    default: false
  },
  maxLength: {
    type: Number,
    default: 30
  },
  error: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['update:modelValue', 'blur'])

const inputClass = computed(() => {
  const baseClass = 'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed'
  const errorClass = props.error ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'
  return `${baseClass} ${errorClass}`
})

// Очистка имени от недопустимых символов
const cleanName = (value) => {
  // Разрешаем только русские и английские буквы, пробелы
  return value.replace(/[^а-яёА-ЯЁa-zA-Z\s]/g, '')
}

// Обработка ввода
const handleInput = (event) => {
  const cleanedValue = cleanName(event.target.value)
  emit('update:modelValue', cleanedValue)
}

// Обработка клавиатуры - блокируем недопустимые символы
const handleKeydown = (event) => {
  const allowedKeys = [
    'Backspace', 'Delete', 'Tab', 'Enter', 'Escape',
    'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
    'Home', 'End', 'PageUp', 'PageDown'
  ]
  
  // Разрешаем служебные клавиши
  if (allowedKeys.includes(event.key)) {
    return
  }
  
  // Разрешаем Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, Ctrl+Z
  if (event.ctrlKey && ['a', 'c', 'v', 'x', 'z'].includes(event.key.toLowerCase())) {
    return
  }
  
  // Разрешаем только русские и английские буквы, пробелы
  const allowedPattern = /^[а-яёА-ЯЁa-zA-Z\s]$/
  if (!allowedPattern.test(event.key)) {
    event.preventDefault()
  }
}

// Обработка вставки
const handlePaste = (event) => {
  event.preventDefault()
  const pastedText = event.clipboardData.getData('text')
  const cleanedText = cleanName(pastedText)
  
  // Вставляем очищенный текст
  const input = event.target
  const start = input.selectionStart
  const end = input.selectionEnd
  const currentValue = props.modelValue
  const newValue = currentValue.substring(0, start) + cleanedText + currentValue.substring(end)
  
  emit('update:modelValue', newValue)
  
  // Устанавливаем курсор в конец вставленного текста
  setTimeout(() => {
    input.setSelectionRange(start + cleanedText.length, start + cleanedText.length)
  }, 0)
}

// Обработка потери фокуса
const handleBlur = (event) => {
  // Обрезаем пробелы по краям
  const trimmedValue = props.modelValue.trim()
  if (trimmedValue !== props.modelValue) {
    emit('update:modelValue', trimmedValue)
  }
  emit('blur', trimmedValue)
}
</script>
