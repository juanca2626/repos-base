<template>
  <div class="filterable-options-wrapper">
    <a-input
      class="input-search"
      :placeholder="placeholder ?? 'Buscar'"
      allow-clear
      v-model:value="searchModel"
    />

    <div class="mt-3 options-scroll">
      <a-checkbox-group
        :value="visibleSelected"
        @change="handleCheckboxGroup"
        v-show="visibleOptions.length"
        class="custom-filter-checkbox"
      >
        <div v-for="item in visibleOptions" :key="item.value" class="mb-2 w-100">
          <a-checkbox :value="item.value">
            <span class="checkbox-label">{{ item.label }}</span>
          </a-checkbox>
        </div>
      </a-checkbox-group>

      <div v-if="canShowMore" class="mt-1">
        <span class="show-more" @click="showMore">Ver más</span>
      </div>

      <p v-if="!filteredOptions.length" class="no-results">No se encontraron resultados.</p>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { toRef } from 'vue';
  import { useFilterableCheckboxList } from '@/modules/negotiations/suppliers/staff/composables/filterable-checkbox-list.composable';
  import type {
    FilterableCheckboxListEmits,
    FilterableCheckboxListProps,
  } from '@/modules/negotiations/suppliers/staff/interfaces';

  const props = defineProps<FilterableCheckboxListProps>();

  const selectedOptionsModel = defineModel<any[]>('selectedOptions', { required: true });

  const searchModel = defineModel<string>('search', { default: '' });

  const emit = defineEmits<FilterableCheckboxListEmits>();

  const {
    visibleOptions,
    canShowMore,
    filteredOptions,
    visibleSelected,
    showMore,
    handleCheckboxGroup,
  } = useFilterableCheckboxList({
    options: toRef(props, 'options'),
    selectedOptionsModel,
    searchModel,
    chunkSize: props.chunk,
    emit,
  });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .filterable-options-wrapper {
    display: flex;
    flex-direction: column;
    flex: 1;
    min-height: 0;
  }

  .options-scroll {
    flex: 1;
    overflow-y: auto;
    padding-right: 6px;
  }

  .no-results {
    font-size: 15px;
    font-weight: 400;
    color: $color-black;
  }

  .show-more {
    font-size: 16px;
    font-weight: 500;
    color: $color-blue;
    text-decoration: underline;
    cursor: pointer;
  }

  .custom-filter-checkbox {
    .checkbox-label {
      font-size: 16px;
      font-weight: 400;
      color: $color-black;
    }
  }
</style>
