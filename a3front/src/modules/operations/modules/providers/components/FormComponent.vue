<template>
  <a-form layout="vertical" class="my-3">
    <a-row justify="space-between" class="py-1 px-5" style="display: flex; flex-wrap: nowrap">
      <div style="flex: 1; margin-right: 10px" v-if="providerStore.getContract === 'F'">
        <a-form-item label="Ver servicios:">
          <a-select v-model:value="formSearch.confirmation" size="middle">
            <a-select-option value="2">Por confirmar</a-select-option>
            <a-select-option value="1">Solo confirmados</a-select-option>
            <a-select-option value="0">Solo cancelados</a-select-option>
            <a-select-option value="3">Confirmados y cancelados</a-select-option>
            <a-select-option value="4">Todos</a-select-option>
          </a-select>
        </a-form-item>
      </div>

      <div style="flex: 1; margin-right: 10px">
        <a-form-item label="Filtrar por:" size="middle">
          <a-select v-model:value="formSearch.option">
            <a-select-option value="date">Fecha</a-select-option>
            <a-select-option value="file">File</a-select-option>
          </a-select>
        </a-form-item>
      </div>

      <div style="flex: 1; margin-right: 10px">
        <a-form-item label="Selecciona un rango de fecha:" v-if="formSearch.option === 'date'">
          <a-range-picker
            style="width: 100%; height: 32px"
            v-model:value="formSearch.dateRange"
            format="DD/MM/YYYY"
            :placeholder="['Fecha Inicio', 'Fecha Fin']"
          />
        </a-form-item>

        <a-form-item label="Número de file(s):" v-if="formSearch.option === 'file'">
          <a-input v-model:value="formSearch.file_numbers" placeholder="Ingrese números de file" />
        </a-form-item>
      </div>

      <div style="flex: 1; margin-right: 10px">
        <a-form-item label="Ingresar término:">
          <a-input v-model:value="formSearch.search_text" placeholder="Ingresa una palabra...">
            <template #prefix>
              <SearchOutlined />
            </template>
          </a-input>
        </a-form-item>
      </div>

      <div style="flex: 1; margin-right: 10px; text-align: right">
        <a-form-item label="&nbsp;">
          <a-button @click="resetFilters" type="link" justify="center" align="center">
            <font-awesome-icon :icon="['fas', 'wand-magic-sparkles']" style="padding-top: 5px" />
            Limpiar filtros
          </a-button>
        </a-form-item>
      </div>
    </a-row>
    <!-- Mostrar indicador de carga o error -->
    <a-spin v-if="isLoading" />
    <p v-if="error" class="text-red-500">{{ error }}</p>
  </a-form>
</template>

<script setup lang="ts">
  import { storeToRefs } from 'pinia';
  import { watch } from 'vue';

  import { useFormStore } from '@operations/modules/providers/store/form.store';
  import { useProviderStore } from '../store/providerStore';
  import { SearchOutlined } from '@ant-design/icons-vue';

  const formStore = useFormStore();
  const providerStore = useProviderStore();

  const { formSearch, isLoading, error } = storeToRefs(formStore);

  // Llamada al método de búsqueda
  // const searchServices = formStore.searchServices;
  // Método para resetear filtros
  // const resetFilters = () => {
  //   formStore.resetFilters(); // Llamar al método del store
  //   formStore.fetchServicesWithParams(); // Actualizar la tabla después de reiniciar filtros
  // };

  const resetFilters = () => {
    formStore.resetFilters();
  };

  watch(
    () => formSearch,
    () => {
      console.log('Formulario actualizado, llamando a fetchServicesWithParams');
      formStore.fetchServicesWithParams();
    },
    { deep: true } // Observa cambios profundos en formSearch
  );
</script>
