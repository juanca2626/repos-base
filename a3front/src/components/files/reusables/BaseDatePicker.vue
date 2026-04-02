<template>
  <a-form-item :label="label" :name="name">
    <a-date-picker
      class="base-range-date-picker"
      :class="`base-range-date-picker-w${width}`"
      :placeholder="placeholder"
      :size="size"
      :format="format"
      :value="modelValue"
      @update:value="handleDateChange"
      @input="$emit('update:modelValue', $event.target.value)"
      v-bind="$attrs"
      :disabled-date="disabledDate"
    >
      <template #suffixIcon> <font-awesome-icon :icon="['far', 'calendar-days']" /> </template>
    </a-date-picker>
  </a-form-item>
</template>

<script setup>
  import { computed } from 'vue';
  import dayjs from 'dayjs';

  const props = defineProps({
    modelValue: {
      type: [String, Number],
      default: '',
    },
    label: {
      type: String,
      default: '',
    },
    name: {
      type: String,
      default: '',
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
        return ['210'].includes(widthValue);
      },
    },
    placeholder: {
      type: Array,
      default: () => ['dd  /  mm  /  aa'],
    },
    format: {
      type: String,
      default: 'DD/MM/YYYY',
    },
    disabledDate: {
      type: Function,
      default: () => false,
    },
  });

  const emit = defineEmits(['update:modelValue']);

  const handleDateChange = (date) => {
    emit('update:modelValue', date ? date.format(props.format) : null);
  };

  computed(() => {
    if (props.modelValue) {
      return dayjs(props.modelValue);
    }
    return null;
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
