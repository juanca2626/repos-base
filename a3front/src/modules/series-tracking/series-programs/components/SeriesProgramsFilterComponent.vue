<template>
  <a-form layout="inline" class="mb-4">
    <a-form-item label="Nombre">
      <a-input v-model:value="localFilters.name" placeholder="Buscar programa" allow-clear />
    </a-form-item>

    <a-form-item label="Estado">
      <a-select v-model:value="localFilters.status" allow-clear style="width: 160px">
        <a-select-option value="ACTIVE">Activo</a-select-option>
        <a-select-option value="INACTIVE">Inactivo</a-select-option>
      </a-select>
    </a-form-item>
  </a-form>
</template>

<script setup lang="ts">
  import { reactive, watch } from 'vue';
  import type { FiltersInputsInterface } from '../interfaces/filters-inputs.interface';

  const emit = defineEmits(['updateFilters']);

  const localFilters = reactive<FiltersInputsInterface>({
    name: '',
    status: null,
  });

  watch(
    () => ({ ...localFilters }),
    (val) => emit('updateFilters', val),
    { deep: true }
  );
</script>
