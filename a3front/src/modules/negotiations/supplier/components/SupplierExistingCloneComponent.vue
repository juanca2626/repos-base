<template>
  <a-form :model="formModel" @submit.prevent="handleSubmit">
    <!-- Selector de proveedor -->
    <a-form-item label="Selecciona proveedor:" name="provider">
      <a-select v-model="selectedProvider" placeholder="Elige un proveedor">
        <a-select-option
          v-for="provider in supplierOptions"
          :key="provider.value"
          :value="provider.value"
        >
          {{ provider.label }}
        </a-select-option>
      </a-select>
    </a-form-item>

    <div>
      <span
        >Recuerda elegir los formularios del registro y configuración que deseas clonar de este
        proveedor.</span
      >
    </div>

    <a-divider />

    <!-- Registro del proveedor -->
    <span>Registro del proveedor:</span>
    <div>
      <span v-for="module in supplierRegistration" :key="module.id">
        <a-checkbox :value="module.module_name">{{ module.module_name }}</a-checkbox>
      </span>
    </div>

    <!-- Configuración de módulos -->
    <span>Configuración de módulos:</span>
    <div>
      <span v-for="config in moduleConfiguration" :key="config.supplier_sub_classification_id">
        <strong>{{ config.name }}</strong>
        <div v-for="module in config.modules" :key="module.id">
          <a-checkbox :value="module.module_name">{{ module.module_name }}</a-checkbox>
        </div>
      </span>
    </div>

    <a-form-item>
      <a-button type="primary" html-type="submit">Continuar</a-button>
      <a-button @click="handleClose">Cancelar</a-button>
    </a-form-item>
  </a-form>
</template>

<script setup lang="ts">
  import { ref, onMounted, watch } from 'vue';
  import { useCloneSupplierStore } from '@/modules/negotiations/supplier/store/supplier-clone.store';
  import { useSearchTouristTransportFiltersStore } from '@/modules/negotiations/supplier/store/search-tourist-transport-filters.store';
  import type { CloneSupplierData } from '@/modules/negotiations/supplier/interfaces/supplier-clone-response.interface';

  // Emitir eventos
  const emit = defineEmits(['close', 'cloneProvider']);

  // Estado reactivo
  const selectedProvider = ref<string | null>(null);
  const formModel = ref<CloneSupplierData>({
    provider: null,
    fields: [],
    modules: [],
  });

  // Store de filtros
  const filtersStore = useSearchTouristTransportFiltersStore();
  const { operationLocation } = filtersStore;

  // Store de proveedores
  const cloneStore = useCloneSupplierStore();
  const { transportSuppliers, fetchTransportSuppliersByLocation } = cloneStore;

  // Opciones para el selector de proveedores
  const supplierOptions = ref([]);

  // Cerrar el formulario
  const handleClose = () => {
    emit('close');
  };

  // Enviar el formulario
  const handleSubmit = () => {
    if (!selectedProvider.value) {
      console.error('No se seleccionó ningún proveedor.');
      return;
    }
    emit('cloneProvider', {
      provider: selectedProvider.value,
      fields: formModel.value.fields,
      modules: formModel.value.modules,
    });
  };

  // Escuchar cambios en el tab activo y actualizar los proveedores
  watch(
    () => filtersStore.operationLocation,
    async (newLocation) => {
      if (newLocation.country_id && newLocation.state_id) {
        // Llama a la API para obtener los proveedores filtrados
        await fetchTransportSuppliersByLocation(newLocation.country_id, newLocation.state_id);
        supplierOptions.value = transportSuppliers.value.map((supplier) => ({
          value: supplier.supplier_id,
          label: supplier.supplier_name,
        }));
      }
    },
    { immediate: true }
  );

  // Cargar los datos iniciales
  onMounted(async () => {
    try {
      // Asegúrate de que los datos iniciales estén sincronizados
      if (operationLocation.country_id && operationLocation.state_id) {
        await fetchTransportSuppliersByLocation(
          operationLocation.country_id,
          operationLocation.state_id
        );
        supplierOptions.value = transportSuppliers.value.map((supplier) => ({
          value: supplier.supplier_id,
          label: supplier.supplier_name,
        }));
      }
    } catch (error) {
      console.error('Error al cargar datos iniciales:', error);
    }
  });
</script>

<style scoped>
  /* Agrega estilos personalizados si es necesario */
</style>
