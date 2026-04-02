<template>
  <a-form-item :label="label">
    <a-range-picker
      class="base-range-date-picker"
      :class="`base-range-date-picker-w${width}`"
      :placeholder="placeholder"
      :size="size"
      :format="format"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      v-bind="$attrs"
    >
      <template #separator>-</template>
    </a-range-picker>
  </a-form-item>
</template>

<script setup>
  defineProps({
    modelValue: {
      type: [String, Number],
      default: '',
    },
    label: {
      type: String,
      default: '',
    },
    size: {
      type: String,
      default: 'large',
      validator(sizeValue) {
        return ['large', 'small'].includes(sizeValue);
      },
    },
    width: {
      type: String,
      default: '210',
      validator(widthValue) {
        return ['210'].includes(widthValue);
      },
    },
    placeholder: {
      type: Array,
      default: () => ['dd/mm/aa', 'dd/mm/aa'],
    },
    format: {
      type: String,
      default: 'DD/MM/YYYY',
    },
  });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables';
  .base-range-date-picker {
    font-size: 16px;
    height: 45px;
    &-w210 {
      min-width: 210px;
    }
  }
  .ant-picker-suffix {
    color: $color-primary !important;
  }
</style>
