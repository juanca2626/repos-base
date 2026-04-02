<template>
  <a-form layout="vertical" :model="formState">
    <ModalConfirmComponent
      :visible="showConfirmModal"
      title="Cambiar tipo de tarifa"
      text="Se perderán los feriados configurados. ¿Deseas continuar?"
      actionButtonText="Continuar"
      @confirm="confirmChange"
      @cancel="cancelChange"
    />

    <!-- Section 1: Basic Info -->
    <div class="content-card">
      <BasicInfo :model="formState" :errors="errors" />
    </div>

    <div class="content-card">
      <TariffTypeSection :model="formState" :errors="errors" @change="requestTariffTypeChange" />

      <!-- Promocional -->
      <PromotionalSection :model="formState" :errors="errors" />

      <!-- Específica -->
      <SpecificSection
        :model="formState"
        :errors="errors"
        :segmentationOptions="segmentationOptions"
        :marketsOptions="marketsOptions"
        :clientsOptions="clientsOptions"
      />

      <!-- Cuando sea diferenciada, se debe mostrar el periodo de viaje -->
      <TravelPeriodSection :model="formState" :errors="errors" @change="requestTravelDatesChange" />

      <!-- Periodos -->
      <PeriodTariffSection :model="formState" :errors="errors" />

      <BookingPeriodSection :model="formState" :errors="errors" />

      <DifferentiatedTariffSection :model="formState" :errors="errors" :daysOptions="daysOptions" />

      <HolidayTariffSection :model="formState" :errors="errors" :ratePlanId="ratePlanId" />
    </div>

    <div class="content-card">
      <BaseCurrencySection :model="formState" :errors="errors" />
    </div>
  </a-form>
</template>

<script setup lang="ts">
  import BasicInfo from './sections/BasicInfo.vue';
  import TariffTypeSection from './sections/TariffTypeSection.vue';
  import TravelPeriodSection from './sections/TravelPeriodSection.vue';
  import BookingPeriodSection from './sections/BookingPeriodSection.vue';
  import DifferentiatedTariffSection from './sections/DifferentiatedTariffSection.vue';
  import HolidayTariffSection from './sections/holiday/HolidayTariffSection.vue';
  import PeriodTariffSection from './sections/periodTariff/PeriodTariffSection.vue';
  import PromotionalSection from './sections/PromotionalSection.vue';
  import SpecificSection from './sections/SpecificSection.vue';
  import BaseCurrencySection from './sections/BaseCurrencySection.vue';
  import ModalConfirmComponent from '@/modules/negotiations/products/configuration/components/ModalConfirmComponent.vue';
  import { useBasicStepEffects } from '@/modules/negotiations/products/configuration/content/pricingPlans/composables/useBasicStepEffects';
  import { useBasicStep } from './composables/useBasicStep';
  import { useBasicTariffGuards } from '@/modules/negotiations/products/configuration/content/pricingPlans/composables/useBasicTariffGuards';

  defineOptions({
    name: 'BasicStep',
  });

  const props = defineProps<{
    model: any;
    errors: any;
    ratePlanId: string | null;
  }>();

  const formState = props.model;
  const errors = props.errors;

  useBasicStepEffects(formState);

  const { segmentationOptions, marketsOptions, clientsOptions, daysOptions } = useBasicStep();

  const {
    showConfirmModal,
    requestTariffTypeChange,
    requestTravelDatesChange,
    confirmChange,
    cancelChange,
  } = useBasicTariffGuards(formState);

  // comentario de prueba
</script>

<style scoped>
  .content-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 24px;
    margin-bottom: 24px;
  }
</style>
