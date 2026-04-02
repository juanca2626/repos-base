<template>
  <div class="datos-section">
    <div class="section-title">Datos</div>
    <div class="section-divider" />
    <div class="fields-row">
      <!-- Fecha de cotización -->
      <div class="field-group">
        <label class="field-label">Fecha de cotización</label>
        <a-date-picker
          v-model:value="formState.fechaCotizacion"
          format="DD/MM/YYYY"
          placeholder="DD/MM/YYYY"
          :allow-clear="false"
          class="date-picker"
          style="width: 100%"
        >
          <template #suffixIcon>
            <font-awesome-icon :icon="['fas', 'calendar']" class="calendar-icon" />
          </template>
        </a-date-picker>
      </div>

      <!-- Mercado / cliente -->
      <div class="field-group">
        <label class="field-label">Mercado / cliente</label>
        <a-select
          v-model:value="formState.mercado"
          placeholder="Buscar..."
          show-search
          allow-clear
          :options="mercadoOptions"
          :filter-option="
            (input: string, option: any) => option.label.toLowerCase().includes(input.toLowerCase())
          "
          class="transport-select"
          style="width: 100%"
        />
      </div>

      <!-- Configuración de transporte -->
      <div class="field-group field-group--wide">
        <label class="field-label">Configuración de transporte</label>
        <a-select
          v-model:value="formState.configuracionTransporte"
          placeholder="Seleccionar"
          :options="transportOptions"
          class="transport-select"
          :allow-clear="true"
          style="width: 100%"
        />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import { useCompoundsComposable } from '../../../composables/use-compounds.composable';

  defineOptions({ name: 'DatosSection' });

  const { formState } = useCompoundsComposable();

  const mercadoOptions = ref([
    { value: 'asia', label: 'Asia' },
    { value: 'asia-cod-riviera', label: 'Asia / COD Riviera / Riviera Travel' },
    { value: 'eeuu-cod-amazon', label: 'EE.UU / COD Amazon / Amazon River' },
  ]);

  const transportOptions = ref([
    { value: 'aut-auto-tour-pasajero', label: 'AUT / Auto / Tour / Pasajero' },
    {
      value: 'spc-sprinter-corta-traslado-pasajero',
      label: 'SPC /Sprinter corta / Traslado / Pasajero',
    },
    {
      value: 'spc-sprinter-corta-traslado-maletero',
      label: 'SPC /Sprinter corta / Traslado / Maletero',
    },
  ]);
</script>

<style lang="scss" scoped>
  .datos-section {
    background: #fff;
    border: 1px solid #e7e7e7;
    border-radius: 8px;
    padding: 20px 24px 24px;
    width: 100%;
    box-sizing: border-box;

    .section-title {
      font-size: 16px;
      font-weight: 700;
      color: #2f353a;
      margin-bottom: 12px;
    }

    .section-divider {
      height: 1px;
      background: #e7e7e7;
      margin-bottom: 20px;
    }

    .fields-row {
      display: flex;
      gap: 16px;
      align-items: flex-start;
    }

    .field-group {
      display: flex;
      flex-direction: column;
      gap: 6px;
      width: 212px;
      flex-shrink: 0;

      &:nth-child(2) {
        width: 310px;
      }

      &--wide {
        width: 327px;
      }
    }

    .field-label {
      font-size: 13px;
      font-weight: 500;
      color: #575b5f;
      line-height: 1.4;
    }

    .date-picker {
      width: 100%;
      height: 38px;

      :deep(.ant-picker) {
        width: 100%;
        height: 38px;
        border-radius: 6px;
        border: 1px solid #d9d9d9;
      }
    }

    .calendar-icon {
      color: #595959;
      font-size: 14px;
    }

    .search-input {
      height: 38px;
      border-radius: 6px;

      :deep(.ant-input) {
        font-size: 14px;
      }
    }

    .search-icon {
      color: #8c8c8c;
      font-size: 14px;
    }

    .transport-select {
      width: 100%;
      height: 38px;

      :deep(.ant-select) {
        width: 100% !important;
      }

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
  }
</style>
