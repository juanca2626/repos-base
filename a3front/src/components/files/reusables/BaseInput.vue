<template>
  <a-form-item :label="label" :name="name" style="margin-bottom: 0">
    <a-input
      class="base-input"
      :class="`base-input-w${width}`"
      v-bind="$attrs"
      :placeholder="placeholder"
      :size="size"
      v-model:value="internalValue"
    />
  </a-form-item>
</template>

<script setup>
  import { computed } from 'vue';

  const props = defineProps({
    value: {
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
  });

  const emit = defineEmits(['update:value']);

  const internalValue = computed({
    get() {
      return props.value;
    },
    set(newValue) {
      emit('update:value', newValue);
    },
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

    &-w660 {
      min-width: 660px;
    }

    height: 45px;
    font-size: 14px;
  }
</style>
