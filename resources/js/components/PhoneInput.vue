<template>
  <div>
    <input
      :id="id"
      :value="formattedValue"
      @input="handleInput"
      @keydown="handleKeydown"
      @paste="handlePaste"
      @blur="handleBlur"
      :placeholder="placeholder"
      :class="inputClass"
      :maxlength="maxLength"
      :disabled="disabled"
      type="tel"
    />
    <div v-if="error" class="mt-1 text-sm text-red-600">{{ error }}</div>
  </div>
</template>

<script setup>
import { ref, computed, watch, nextTick } from 'vue';

const props = defineProps({
  id: {
    type: String,
    default: 'phone'
  },
  modelValue: {
    type: String,
    default: ''
  },
  placeholder: {
    type: String,
    default: '+7 (XXX) XXX-XX-XX'
  },
  inputClass: {
    type: String,
    default: 'w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500'
  },
  error: {
    type: String,
    default: ''
  },
  disabled: {
    type: Boolean,
    default: false
  }
});

const emit = defineEmits(['update:modelValue', 'blur']);

const maxLength = 18; // +7 (XXX) XXX-XX-XX

// Очищаем номер от всех символов кроме цифр и +
const cleanPhone = (value) => {
  // Убираем все символы кроме цифр и +
  let cleaned = value.replace(/[^\d+]/g, '');
  
  // Если есть несколько +, оставляем только первый
  if (cleaned.includes('+')) {
    const plusIndex = cleaned.indexOf('+');
    cleaned = '+' + cleaned.substring(plusIndex + 1).replace(/\+/g, '');
  }
  
  return cleaned;
};

// Форматируем номер в красивый вид
const formatPhone = (value) => {
  const cleaned = cleanPhone(value);
  
  if (!cleaned) return '';
  
  // Если начинается не с +7, добавляем +7
  let phone = cleaned;
  if (!phone.startsWith('+7')) {
    if (phone.startsWith('7')) {
      phone = '+' + phone;
    } else if (phone.startsWith('8')) {
      phone = '+7' + phone.substring(1);
    } else {
      phone = '+7' + phone;
    }
  }
  
  // Ограничиваем длину (максимум 11 цифр после +7)
  if (phone.length > 12) { // +7 + 10 цифр
    phone = phone.substring(0, 12);
  }
  
  // Если только +7, возвращаем как есть
  if (phone === '+7') {
    return '+7';
  }
  
  // Форматируем в красивый вид
  if (phone.length <= 2) {
    return phone; // +7
  } else if (phone.length <= 5) {
    return `+7 (${phone.substring(2)})`;
  } else if (phone.length <= 8) {
    return `+7 (${phone.substring(2, 5)}) ${phone.substring(5)}`;
  } else if (phone.length <= 10) {
    return `+7 (${phone.substring(2, 5)}) ${phone.substring(5, 8)}-${phone.substring(8)}`;
  } else {
    return `+7 (${phone.substring(2, 5)}) ${phone.substring(5, 8)}-${phone.substring(8, 10)}-${phone.substring(10)}`;
  }
};

// Получаем чистый номер для отправки на сервер
const getCleanPhone = (value) => {
  const cleaned = cleanPhone(value);
  if (cleaned.startsWith('+7') && cleaned.length === 12) {
    return cleaned;
  }
  return '';
};

const formattedValue = computed(() => {
  return formatPhone(props.modelValue);
});

const handleKeydown = (event) => {
  // Разрешенные символы: цифры, +, -, (, ), пробел, Backspace, Delete, Tab, Enter, стрелки
  const allowedKeys = [
    'Backspace', 'Delete', 'Tab', 'Enter', 'Escape',
    'ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown',
    'Home', 'End'
  ];
  
  // Разрешенные символы для ввода
  const allowedChars = /[0-9+\-() ]/;
  
  // Если это служебная клавиша, разрешаем
  if (allowedKeys.includes(event.key)) {
    return;
  }
  
  // Если это разрешенный символ, разрешаем
  if (allowedChars.test(event.key)) {
    return;
  }
  
  // Если это Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X, разрешаем
  if (event.ctrlKey && ['a', 'c', 'v', 'x'].includes(event.key.toLowerCase())) {
    return;
  }
  
  // Во всех остальных случаях блокируем
  event.preventDefault();
};

const handleInput = (event) => {
  const input = event.target;
  
  // Получаем новое значение
  const newValue = event.target.value;
  
  // Очищаем и форматируем
  const cleaned = cleanPhone(newValue);
  const formatted = formatPhone(cleaned);
  
  // Обновляем значение
  emit('update:modelValue', cleaned);
  
  // Устанавливаем курсор в конец строки
  nextTick(() => {
    input.setSelectionRange(formatted.length, formatted.length);
  });
};

const handlePaste = (event) => {
  // Получаем данные из буфера обмена
  const pastedData = (event.clipboardData || window.clipboardData).getData('text');
  
  // Очищаем вставленные данные от недопустимых символов
  const cleanedData = pastedData.replace(/[^0-9+\-() ]/g, '');
  
  // Если есть недопустимые символы, заменяем вставку
  if (cleanedData !== pastedData) {
    event.preventDefault();
    
    // Вставляем очищенные данные
    const input = event.target;
    const start = input.selectionStart;
    const end = input.selectionEnd;
    const currentValue = input.value;
    
    const newValue = currentValue.substring(0, start) + cleanedData + currentValue.substring(end);
    const cleaned = cleanPhone(newValue);
    const formatted = formatPhone(cleaned);
    
    emit('update:modelValue', cleaned);
    
    // Устанавливаем курсор в конец строки
    nextTick(() => {
      input.setSelectionRange(formatted.length, formatted.length);
    });
  }
};

const handleBlur = (event) => {
  const cleanPhone = getCleanPhone(props.modelValue);
  emit('blur', cleanPhone);
};

// Валидация номера
const isValidPhone = computed(() => {
  const clean = getCleanPhone(props.modelValue);
  return clean.length === 12 && clean.startsWith('+7');
});

// Экспортируем валидацию для родительского компонента
defineExpose({
  isValidPhone
});
</script>
