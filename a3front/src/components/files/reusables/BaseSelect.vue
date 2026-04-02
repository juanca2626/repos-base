<template>
  <a-form-item :label="label" :name="name" :colon="false" :class="`base-select-w${width}`">
    <a-select
      style="min-height: 45px; width: 100%"
      class="base-select"
      :placeholder="placeholder"
      :options="options"
      :value="value"
      @change="$emit('update:value', $event)"
      :loading="loading"
      :filter-option="filterOption"
      :show-search="showSearch"
      @search="$emit('search', $event)"
      v-bind="$attrs"
    >
    </a-select>
  </a-form-item>
</template>

<script setup>
  defineProps({
    showSearch: {
      type: Boolean,
      default: true,
    },
    value: {
      type: [String, Number, Array],
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
  defineEmits(['update:value', 'search']);
</script>

<style scoped lang="scss">
  .ant-form-item {
    margin-bottom: 0;
    width: 100% !important;

    // Estos estilos ahora serán sobrescritos por el grid, pero los mantenemos como fallback
    &.base-select-w210 {
      width: 100% !important;
      min-width: 100% !important;
      max-width: 100% !important;
    }
    &.base-select-w340 {
      width: 100% !important;
      min-width: 100% !important;
      max-width: 100% !important;
    }
    &.base-select-w660 {
      width: 100% !important;
      min-width: 100% !important;
      max-width: 100% !important;
    }
  }

  .base-select {
    width: 100% !important;
    min-width: 100% !important;

    & :deep(.ant-select) {
      width: 100% !important;
      min-width: 100% !important;
    }

    & :deep(.ant-select-selector) {
      height: 45px;
      line-height: 45px;
      width: 100% !important;
      min-width: 100% !important;
      max-width: 100% !important;
      overflow: hidden;
    }

    & :deep(.ant-select-selection-placeholder),
    & :deep(.ant-select-selection-item) {
      line-height: 45px;
      text-align: left;
      overflow: hidden;
      text-overflow: ellipsis;
      white-space: nowrap;
      max-width: 100%;
      width: 100%;
    }

    // Para el select multiple
    & :deep(.ant-select-selection-overflow) {
      max-width: 100%;
      overflow: hidden;
      width: 100%;
    }

    & :deep(.ant-select-selection-overflow-item) {
      max-width: 100%;
    }

    // Asegurar que el dropdown también mantenga el ancho consistente
    & :deep(.ant-select-dropdown) {
      .ant-select-item {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
    }
  }
</style>
