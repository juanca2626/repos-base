<template>
  <a-form layout="vertical" class="my-3">
    <a-row justify="space-between" class="py-1 px-5" style="display: flex; flex-wrap: nowrap">
      <div style="flex: 1; margin-right: 10px">
        <a-form-item label="Filtrar por:">
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
        <a-form-item label="Tipo de servicio:">
          <a-select v-model:value="formSearch.category">
            <a-select-option value="all">Todos</a-select-option>
            <a-select-option value="sim">Compartido</a-select-option>
            <a-select-option value="pri">Privado</a-select-option>
          </a-select>
        </a-form-item>
      </div>

      <div style="flex: 1; margin-right: 10px">
        <a-form-item label="Idioma:">
          <a-select v-model:value="formSearch.language">
            <a-select-option value="all">Todos</a-select-option>
            <a-select-option value="EN">Inglés</a-select-option>
            <a-select-option value="ES">Español</a-select-option>
            <a-select-option value="PT">Portugués</a-select-option>
          </a-select>
        </a-form-item>
      </div>

      <div style="flex: 1">
        <a-form-item label="Ingresar término:">
          <a-input
            v-model:value="formSearch.search_text"
            placeholder="Ingresa una palabra..."
            @keyup.enter=""
          >
            <template #prefix>
              <SearchOutlined style="color: #bdbdbd" />
            </template>
          </a-input>
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
  import { useFormStore } from '@operations/modules/service-management/store/form.store';
  import { watch } from 'vue';
  import { SearchOutlined } from '@ant-design/icons-vue';
  const formStore = useFormStore();
  const { formSearch, isLoading, error } = storeToRefs(formStore);

  // Observa cambios en el formulario para disparar fetchServicesWithParams
  watch(
    () => formSearch,
    () => {
      console.log('Formulario actualizado, llamando a fetchServicesWithParams');
      formStore.fetchServicesWithParams();
    },
    { deep: true } // Observa cambios profundos en formSearch
  );
</script>

<style scoped>
  .mt-4 {
    margin-top: 1rem;
  }

  .box-filter {
    border: 1px solid #e7e7e7;
    padding: 1rem;
    border-radius: 4px;
  }
</style>
