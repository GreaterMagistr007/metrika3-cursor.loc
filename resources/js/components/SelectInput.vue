<template>
  <div class="space-y-1">
    <label v-if="label" :for="id" class="block text-sm font-medium text-gray-700">
      {{ label }}
      <span v-if="required" class="text-red-500">*</span>
    </label>
    
    <div class="relative">
      <select
        :id="id"
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)"
        @change="$emit('change', $event.target.value)"
        :disabled="disabled"
        :class="selectClass"
      >
        <option v-if="placeholder" value="">{{ placeholder }}</option>
        <option 
          v-for="option in options" 
          :key="option.value" 
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
      
      <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
        <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
        </svg>
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
    required: true
  },
  modelValue: {
    type: [String, Number],
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
  options: {
    type: Array,
    default: () => []
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
  }
});

defineEmits(['update:modelValue', 'change']);

const selectClass = computed(() => {
  const baseClass = 'w-full pl-3 pr-10 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 disabled:bg-gray-100 disabled:cursor-not-allowed appearance-none'
  const errorClass = props.error ? 'border-red-300 focus:ring-red-500 focus:border-red-500' : 'border-gray-300'
  return `${baseClass} ${errorClass}`
});
</script>
