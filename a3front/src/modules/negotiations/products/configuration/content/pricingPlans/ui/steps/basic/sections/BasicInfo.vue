<template>
  <div class="row">
    <div class="col-5">
      <a-form-item required>
        <template #label>
          <span class="label-with-tooltip">
            Nombre de la tarifa:
            <span class="required">*</span>

            <a-tooltip
              title="Nombre automático completado con selección realizada"
              placement="topLeft"
              overlay-class-name="tooltip-nowrap"
            >
              <font-awesome-icon :icon="['fas', 'circle-info']" class="info-icon-inline" />
            </a-tooltip>
          </span>
        </template>

        <a-input
          v-model:value="formState.name"
          placeholder="Tarifa Estándar 2025"
          size="large"
          disabled
        />
      </a-form-item>
    </div>
  </div>

  <div class="row">
    <div class="col-12 flex-center">
      <span class="label-text q-mr-md mr-2"> Requiere código de reserva: </span>

      <a-radio-group v-model:value="formState.requiresBookingCode">
        <a-radio :value="true">Sí</a-radio>
        <a-radio :value="false">No</a-radio>
      </a-radio-group>
    </div>
  </div>

  <div v-if="formState.requiresBookingCode" class="row">
    <div class="col-5 mt-3">
      <a-input v-model:value="formState.bookingCode" placeholder="Ingresa" size="large" />
    </div>
  </div>
</template>

<script setup lang="ts">
  interface Props {
    model: any;
    errors: any;
  }

  const props = defineProps<Props>();
  const formState = props.model;
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
