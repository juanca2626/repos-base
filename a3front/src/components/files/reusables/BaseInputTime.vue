<template>
  <a-input
    :style="`width: ${size === 'small' ? '60' : '70'}px`"
    :value="formattedValue"
    @blur="onInput"
    @keypress="onInput"
    placeholder="00:00"
    maxlength="5"
    :size="size"
  />
</template>

<script setup>
  import { computed, ref, watch } from 'vue';
  import { debounce } from 'lodash-es';

  // Props
  const props = defineProps({
    modelValue: {
      type: String,
      default: '', // puede venir como "13:45:00"
    },
    size: {
      type: String,
      default: '',
    },
  });

  // Emits
  const emit = defineEmits(['update:modelValue']);

  // Estado local
  const rawValue = ref(extractHHmm(props.modelValue));

  watch(
    () => props.modelValue,
    (val) => {
      rawValue.value = extractHHmm(val);
    }
  );

  // Computed
  const formattedValue = computed(() => {
    return rawValue.value;
  });

  // Extrae solo los primeros 5 caracteres (HH:mm)
  function extractHHmm(str) {
    if (!str) return '';
    return str.slice(0, 5);
  }

  // Formatea a HH:mm válido con solo números
  function formatToHHmm(input) {
    const digits = input.replace(/\D/g, '').slice(0, 4);
    const hh = digits.slice(0, 2).padEnd(2, '0');
    const mm = digits.slice(2).padEnd(2, '0');

    const h = Math.min(parseInt(hh) || 0, 23);
    const m = Math.min(parseInt(mm) || 0, 59);

    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`;
  }

  // Al hacer blur o enter
  const onInput = debounce((e) => {
    const formatted = formatToHHmm(e.target.value);
    rawValue.value = formatted;
    emit('update:modelValue', formatted);
  }, 350);
</script>
