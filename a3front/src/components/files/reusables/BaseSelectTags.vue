<template>
  <a-form-item :label="label" :name="name" :colon="false" :class="`base-select-w${width}`">
    <a-select
      style="min-height: 45px"
      class="base-select-tags"
      mode="tags"
      :placeholder="placeholder"
      :options="options"
      :value="modelValue"
      @change="$emit('update:modelValue', $event)"
      :loading="loading"
      :filter-option="filterOption"
      showSearch
      @search="$emit('search', $event)"
      v-bind="$attrs"
    >
    </a-select>
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
    name: {
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
    options: {
      type: Array,
      default: () => [],
    },
    width: {
      type: String,
      default: '',
      validator(widthValue) {
        return ['210', '340', '660'].includes(widthValue);
      },
    },
    loading: {
      type: Boolean,
      default: false,
    },
    filterOption: {
      type: [Function, Boolean],
      default: true,
    },
  });
  defineEmits(['update:modelValue', 'search']);
</script>

<style scoped lang="scss">
  .ant-form-item {
    margin-bottom: 0;
  }

  .base-select-tags {
    &-w210 {
      width: 210px;
    }

    &-w340 {
      width: 340px;
    }

    &-w660 {
      width: 660px;
    }

    & :deep(.ant-select-selector) {
      height: 45px;
    }

    & :deep(.ant-select-selection-placeholder) {
      text-align: left;
    }

    & :deep(.ant-select-selection-item) {
      text-align: left;
    }
  }
</style>
