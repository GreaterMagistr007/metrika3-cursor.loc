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
    default: 'text-input'
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
    default: null
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

const emit = defineEmits(['update:modelValue', 'blur'])

const inputClass = computed(() => {
  const baseClass = 'w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed'
  const errorClass = props.error ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'
  const customClass = props.inputClass || ''
  return `${baseClass} ${errorClass} ${customClass}`
})

// Обработка ввода
const handleInput = (event) => {
  emit('update:modelValue', event.target.value)
}

// Обработка клавиатуры
const handleKeydown = (event) => {
  // Разрешаем все служебные клавиши
  const allowedKeys = [
    'Backspace', 'Delete', 'Tab', 'Enter', 'Escape',
    'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
    'Home', 'End', 'PageUp', 'PageDown'
  ];
  
  if (allowedKeys.includes(event.key)) {
    return;
  }
  
  // Разрешаем Ctrl комбинации
  if (event.ctrlKey && ['a', 'c', 'v', 'x', 'z'].includes(event.key.toLowerCase())) {
    return;
  }
  
  // Для типа 'number' разрешаем только цифры и некоторые символы
  if (props.type === 'number') {
    const allowedPattern = /^[0-9+\-.,]$/;
    if (!allowedPattern.test(event.key)) {
      event.preventDefault();
    }
  }
  
  // Для типа 'email' разрешаем email символы
  if (props.type === 'email') {
    const allowedPattern = /^[a-zA-Z0-9@._-]$/;
    if (!allowedPattern.test(event.key)) {
      event.preventDefault();
    }
  }
};

// Обработка вставки
const handlePaste = (event) => {
  // Для типа 'number' очищаем вставленный текст
  if (props.type === 'number') {
    event.preventDefault();
    const pastedText = event.clipboardData.getData('text');
    const cleanedText = pastedText.replace(/[^0-9+\-.,]/g, '');
    
    const input = event.target;
    const start = input.selectionStart;
    const end = input.selectionEnd;
    const currentValue = props.modelValue;
    const newValue = currentValue.substring(0, start) + cleanedText + currentValue.substring(end);
    
    emit('update:modelValue', newValue);
    
    setTimeout(() => {
      input.setSelectionRange(start + cleanedText.length, start + cleanedText.length);
    }, 0);
  }
};

// Обработка потери фокуса
const handleBlur = (event) => {
  const trimmedValue = props.modelValue.trim();
  if (trimmedValue !== props.modelValue) {
    emit('update:modelValue', trimmedValue);
  }
  emit('blur', trimmedValue);
};
</script>
