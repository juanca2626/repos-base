<template>
  <a-form-item :label="label" :name="name" :colon="false" :class="`base-select-w${width}`">
    <a-select
      :dropdownStyle="{ backgroundColor: 'white', borderRadius: 0 }"
      popup-class-name="file-multi-select-popup"
      style="min-height: 45px"
      class="base-select"
      :placeholder="placeholder"
      :size="size"
      :options="options"
      :value="value"
      @change="handleChange"
      :loading="loading"
      mode="multiple"
      v-bind="$attrs"
      :max-tag-count="maxTagCount"
      :filter-option="filterLogic"
    >
      <template #maxTagPlaceholder="omittedValues">
        <span>+ {{ omittedValues.length }} ...</span>
      </template>
      <template #option="{ value, label }">
        <div class="select-multiple-opt-file">
          <font-awesome-icon
            :class="[isSelected(value) ? 'icon-color-selected' : 'icon-color-not-selected']"
            :icon="[
              isSelected(value) ? 'fas' : 'far',
              isSelected(value) ? 'square-check' : 'square',
            ]"
            size="xl"
          />
          <span style="margin-left: 8px">{{ label }}</span>
        </div>
      </template>
      <template #tagRender="{ label, onClose, option }">
        <a-tooltip v-if="option.title" :title="option.title">
          <a-tag class="tag-selected-multiple" @close="onClose">{{ label }}</a-tag>
        </a-tooltip>
        <a-tag v-else class="tag-selected-multiple" @close="onClose">{{ label }}</a-tag>
      </template>
      <template #menuItemSelectedIcon />
    </a-select>
  </a-form-item>
</template>

<script setup lang="ts">
  import { computed, ref, watch } from 'vue';

  const props = defineProps({
    value: {
      type: Array,
      default: () => [],
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
      validator(sizeValue: any) {
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
      validator(widthValue: any) {
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
    maxTagCount: {
      type: Number,
      default: 1,
    },
  });

  const emit = defineEmits(['update:value', 'search']);

  const selectedValues = ref<(string | number)[]>([]);

  watch(
    () => props.value,
    (newValue) => {
      selectedValues.value = newValue as (string | number)[];
    },
    { immediate: true }
  );

  const isSelected = (value: string | number) => {
    return selectedValues.value.includes(value);
  };

  const handleChange = (value: (string | number)[]) => {
    selectedValues.value = value;
    emit('update:value', value);
  };

  const filterLogic = computed(() => {
    if (props.filterOption === true) {
      // Función de filtrado personalizada para buscar por etiqueta, valor y título
      return (input: string, option: any) => {
        const label = (option.label || '').toLowerCase();
        const value = String(option.value || '').toLowerCase();
        const title = (option.title || '').toLowerCase();
        const lowercasedInput = input.toLowerCase();

        return (
          label.includes(lowercasedInput) ||
          value.includes(lowercasedInput) ||
          title.includes(lowercasedInput)
        );
      };
    }
    if (typeof props.filterOption === 'function') {
      return props.filterOption;
    }
    return false;
  });
</script>

<style scoped lang="scss">
  .select-multiple-opt-file {
    display: flex;
    align-items: center;
    gap: 8px;

    .icon-color-selected {
      color: #bd0d12;
    }

    .icon-color-not-selected {
      color: #bec0c2;
    }

    .ant-select-item-option-selected {
      background-color: #ffffff !important;
    }
  }

  .base-select {
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
      line-height: 45px;
    }

    & :deep(.ant-select-selection-placeholder) {
      line-height: 45px;
      text-align: left;
    }

    & :deep(.ant-select-selection-item) {
      text-align: center;
      font-family: Montserrat, serif;
      background-color: #e9e9e9 !important;
      color: #0d0d0d;
      padding: 1px 8px;
      height: 24px;
      margin-bottom: 0;
      font-size: 14px;
    }

    & :deep(.tag-selected-multiple) {
      text-align: center;
      font-family: Montserrat, serif;
      background-color: #e9e9e9;
      color: #0d0d0d;
      padding: 1px 8px;
      height: 24px;
      font-size: 14px;
    }
  }

  :where(.file-multi-select-popup)
    .ant-select-item-option-selected:not(.ant-select-item-option-disabled) {
    color: rgba(0, 0, 0, 0.88) !important;
    font-weight: 600 !important;
    background-color: #f9f9f9 !important;
  }
</style>
