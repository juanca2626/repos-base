<template>
  <div class="buscar-servicios-section">
    <div class="section-title">Buscar servicios</div>
    <div class="fields-row">
      <div class="field-group type-group">
        <a-select
          v-model:value="tipoServicio"
          placeholder="Tipo de servicios"
          :options="tipoServicioOptions"
          class="custom-select"
        />
      </div>

      <div class="field-group search-group">
        <a-select
          v-model:value="busquedaServicio"
          show-search
          placeholder="Buscar por código, servicio o proveedor"
          :options="searchResults"
          class="custom-select search-input"
          @search="handleSearch"
        >
          <template #suffixIcon>
            <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="search-icon" />
          </template>

          <template #option="{ value, label }">
            <div class="search-option-item">
              <a-checkbox :value="value" @click.stop>{{ label }}</a-checkbox>
            </div>
          </template>
        </a-select>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed, watch } from 'vue';
  import { useCompoundsComposable } from '../../../composables/use-compounds.composable';

  defineOptions({ name: 'BuscarServicios' });

  const { formState } = useCompoundsComposable();

  const tipoServicio = computed({
    get: () => formState.value.tipoServicio,
    set: (val) => (formState.value.tipoServicio = val),
  });

  // Cuando se selecciona un tipo de servicio, llenamos el Día 1 con 10 items de prueba
  watch(tipoServicio, (newVal) => {
    if (newVal && formState.value.dias.length > 0) {
      const typeLabel =
        tipoServicioOptions.value.find((o) => o.value === newVal)?.label || 'Tipo de servicio';
      const dummyServices = Array.from({ length: 10 }).map((_, i) => ({
        id: `dummy-${newVal}-${Date.now()}-${i}`,
        codigo: 'Código',
        nombre: 'Nombre del servicio',
        proveedor: 'Nombre del proveedor',
        tipoServicio: typeLabel,
        tieneEscudo: i === 0 || i === 2 || i === 4, // 1er, 3er y 5to item como en la imagen
      }));

      formState.value.dias[0].servicios = dummyServices;
    }
  });

  const busquedaServicio = ref<string[]>([]);

  const tipoServicioOptions = ref([
    { value: 'alimentacion', label: 'Alimentación' },
    { value: 'asistencia', label: 'Asistencia' },
    { value: 'actividades', label: 'Actividades' },
    { value: 'excursiones', label: 'Excursiones' },
    { value: 'traslados', label: 'Traslados' },
  ]);

  const allServices = [
    { value: '123', label: 'Cód 123 alm' },
    { value: '124', label: 'Cód 124 alm' },
    { value: '125', label: 'Cód 125 alm' },
    { value: '126', label: 'Cód 126 alm' },
    { value: '127', label: 'Cód 127 alm' },
  ];

  const searchResults = ref([...allServices]);

  const handleSearch = (val: string) => {
    if (!val) {
      searchResults.value = [...allServices];
    } else {
      searchResults.value = allServices.filter(
        (s) => s.label.toLowerCase().includes(val.toLowerCase()) || s.value.includes(val)
      );
    }
  };
</script>

<style lang="scss" scoped>
  .buscar-servicios-section {
    width: 100%;
    box-sizing: border-box;

    .section-title {
      font-size: 16px;
      font-weight: 700;
      color: #2f353a;
      margin-bottom: 16px;
    }

    .fields-row {
      display: flex;
      gap: 16px;
      align-items: flex-start;
    }

    .field-group {
      display: flex;
      flex-direction: column;
      flex-shrink: 0;
    }

    .type-group {
      width: 238px;
    }

    .search-group {
      width: 665px;
    }

    .custom-select {
      width: 100%;
      height: 38px;

      :deep(.ant-select-selector) {
        height: 38px !important;
        border-radius: 6px !important;
        border: 1px solid #d9d9d9 !important;

        .ant-select-selection-item,
        .ant-select-selection-placeholder {
          line-height: 36px;
          font-size: 14px;
        }
      }
    }

    .search-icon {
      color: #bfbfbf;
      font-size: 14px;
    }
  }

  .search-option-item {
    padding: 4px 0;
    width: 100%;
  }
</style>
