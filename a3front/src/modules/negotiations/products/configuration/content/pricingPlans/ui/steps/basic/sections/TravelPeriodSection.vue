<template>
  <div v-if="formState.tariffType !== TariffType.PERIODS && formState.tariffType !== null">
    <div class="row section-header-row">
      <div class="col-12">
        <label class="field-section-label">
          Periodo de viaje:
          <span class="required mr-2">*</span>
          <a-tooltip
            title="Fechas en las que viajará el pasajero"
            placement="topLeft"
            overlay-class-name="tooltip-nowrap"
          >
            <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
          </a-tooltip>
        </label>
      </div>
    </div>

    <div class="row mt-2">
      <div class="col-5">
        <div class="row">
          <div class="col-6">
            <a-form-item label="Desde:">
              <a-date-picker
                :value="formState.travelFrom"
                format="DD/MM/YYYY"
                size="large"
                class="full-width"
                :disabled-date="disablePastDates"
                @change="onChangeFrom"
              />
            </a-form-item>
          </div>

          <div class="col-6">
            <a-form-item label="Hasta:">
              <a-date-picker
                :value="formState.travelTo"
                format="DD/MM/YYYY"
                size="large"
                class="full-width"
                :disabled-date="disablePastDates"
                @change="onChangeTo"
              />
            </a-form-item>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { TariffType } from '@/modules/negotiations/products/configuration/enums/tariffType.enum';
  import dayjs, { Dayjs } from 'dayjs';

  const props = defineProps<{
    model: any;
    errors: any;
  }>();

  const emit = defineEmits<{
    (e: 'change', payload: { from: Dayjs | null; to: Dayjs | null }): void;
  }>();

  const formState = props.model;

  const disablePastDates = (current: Dayjs) => {
    return current < dayjs().startOf('day');
  };

  function onChangeFrom(value: Dayjs | null) {
    emit('change', {
      from: value,
      to: formState.travelTo,
    });
  }

  function onChangeTo(value: Dayjs | null) {
    emit('change', {
      from: formState.travelFrom,
      to: value,
    });
  }
</script>

<style scoped>
  .row {
    display: flex;
    gap: 24px;
  }

  .col-12 {
    flex: 0 0 100%;
    width: 100%;
  }

  .col-6 {
    flex: 0 0 50%;
  }

  .col-5 {
    flex: 0 0 41.66%;
  }

  .full-width {
    width: 100%;
  }

  .required {
    color: #ff4d4f;
    margin-left: 4px;
  }

  .flex-center {
    display: flex;
    align-items: center;
  }

  .label-text {
    font-weight: 500;
  }

  .field-section-label {
    font-weight: 500;
  }

  .mr-2 {
    margin-right: 8px;
  }

  .mr-3 {
    margin-right: 12px;
  }
</style>
