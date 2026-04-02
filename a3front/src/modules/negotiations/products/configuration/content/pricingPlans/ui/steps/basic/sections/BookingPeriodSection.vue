<template>
  <div>
    <div class="row section-header-row">
      <div class="col-12 flex-center">
        <label class="field-section-label mr-2"> Periodo de reserva </label>

        <a-tooltip
          title="Fechas en las que se puede reservar el servicio"
          placement="topLeft"
          overlay-class-name="tooltip-nowrap"
          class="mr-2"
        >
          <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline q-mr-md" />
        </a-tooltip>

        <a-checkbox
          v-model:checked="formState.modifyBookingPeriod"
          @change="handleModifyBookingPeriodChange"
        >
          Modificar periodo de reserva
        </a-checkbox>
      </div>
    </div>

    <div class="row mt-3">
      <div class="col-5">
        <div class="row">
          <div class="col-6">
            <a-form-item label="Desde:">
              <a-date-picker
                v-model:value="formState.bookingFrom"
                format="DD/MM/YYYY"
                size="large"
                :disabled="!formState.modifyBookingPeriod"
                class="full-width"
              />
            </a-form-item>
          </div>

          <div class="col-6">
            <a-form-item label="Hasta:">
              <a-date-picker
                v-model:value="formState.bookingTo"
                format="DD/MM/YYYY"
                size="large"
                :disabled="!formState.modifyBookingPeriod"
                class="full-width"
              />
            </a-form-item>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { resetBasicStateAfterModifyBookingPeriodChange } from '@/modules/negotiations/products/configuration/content/pricingPlans/state/princingPlanStateActions';

  interface Props {
    model: any;
    errors: any;
  }

  const props = defineProps<Props>();

  const formState = props.model;

  const handleModifyBookingPeriodChange = async () => {
    resetBasicStateAfterModifyBookingPeriodChange(formState);
  };
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
