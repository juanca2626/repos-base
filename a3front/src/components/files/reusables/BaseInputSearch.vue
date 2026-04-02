<template>
  <a-form-item :label="label" :name="name" style="margin-bottom: 0">
    <a-input
      v-model="inputValue"
      class="base-input"
      :class="`base-input-w${width}`"
      v-bind="$attrs"
      :placeholder="placeholder"
      :size="size"
    >
    </a-input>
  </a-form-item>
</template>

<script setup>
  import { ref, watch, onBeforeUnmount } from 'vue';

  const props = defineProps({
    modelValue: {
      type: [String, Number],
      default: '',
    },
    name: {
      type: String,
      default: '',
    },
    label: {
      type: String,
      default: '',
    },
    placeholder: {
      type: String,
      default: 'Placeholder',
    },
    size: {
      type: String,
      default: '',
      validator(sizeValue) {
        return ['large', 'small'].includes(sizeValue);
      },
    },
    width: {
      type: String,
      default: '210',
      validator(widthValue) {
        return ['210', '340', '660'].includes(widthValue);
      },
    },
    debounceTime: {
      type: Number,
      default: 500, // tiempo en milisegundos
    },
    minChars: {
      type: Number,
      default: 3,
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
  });

  const emit = defineEmits(['update:modelValue', 'search']);

  const inputValue = ref(props.modelValue);
  let debounceTimeout = null;

  // Actualiza visualmente cuando el padre cambia formFilter.filter
  watch(
    () => props.modelValue,
    (newVal) => {
      inputValue.value = newVal;
    }
  );

  // Debounce al escribir
  watch(inputValue, (val) => {
    if (debounceTimeout) clearTimeout(debounceTimeout);

    if (val?.length >= props.minChars || val === '') {
      debounceTimeout = setTimeout(() => {
        emit('update:modelValue', val);
        emit('search', val);
      }, props.debounceTime);
    }
  });

  onBeforeUnmount(() => {
    if (debounceTimeout) clearTimeout(debounceTimeout);
  });
</script>

<style scoped lang="scss">
  .base-input {
    &-w210 {
      min-width: 210px;
    }

    &-w340 {
      min-width: 340px;
    }

    &-w400 {
      min-width: 400px;
    }

    &-w660 {
      min-width: 660px;
    }

    height: 45px;
    font-size: 14px;
  }
</style>
