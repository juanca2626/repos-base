<template>
  <div v-if="formState.tariffType === TariffType.PERIODS" class="periods-section">
    <!-- HEADER -->
    <div class="periods-header">
      <label class="field-section-label">
        Periodo de viaje: <span class="required mr-2">*</span>
        <a-tooltip
          title="Fechas en las que viajará el pasajero"
          placement="topLeft"
          overlay-class-name="tooltip-nowrap"
        >
          <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
        </a-tooltip>
      </label>

      <span class="add-period-link" @click="addPeriodGroup">
        <font-awesome-icon :icon="['fas', 'plus']" style="margin-right: 8px" />
        Periodo adicional
      </span>
    </div>

    <!-- GROUPS -->
    <PeriodGroupCard
      v-for="(group, index) in formState.periods"
      :key="group.id"
      :group="group"
      :groupIndex="Number(index)"
      :totalGroups="formState.periods.length"
      :periodTypeOptions="periodTypeOptions"
      @removeGroup="removePeriodGroup"
      :getDisabledDate="getDisabledDate"
      :addRange="addRange"
      :removeRange="removeRange"
    />
  </div>
</template>

<script setup lang="ts">
  import { TariffType } from '@/modules/negotiations/products/configuration/enums/tariffType.enum';
  import PeriodGroupCard from './components/PeriodGroupCard.vue';
  import { usePeriodTariffComposable } from './composables/usePeriodTariffComposable';

  const props = defineProps<{
    model: any;
  }>();

  const formState = props.model;

  const {
    periodTypeOptions,
    addPeriodGroup,
    removePeriodGroup,
    addRange,
    removeRange,
    getDisabledDate,
  } = usePeriodTariffComposable(formState);
</script>

<style lang="scss" scoped>
  .periods-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
  }

  .add-period-link {
    color: #1284ed;
    cursor: pointer;
    font-weight: 500;
    font-size: 16px;
  }

  .field-section-label {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
    font-weight: 500;
    color: #2f353a;
    .required {
      color: #ff4d4f;
    }
  }
</style>
