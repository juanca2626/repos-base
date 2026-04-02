<template>
  <a-form layout="vertical">
    <PeriodSelectionSection
      :model="model"
      :cards="variationCards"
      :selectedId="selectedRateVariationId"
      :onSelect="changeVariation"
      :isTrainService="isTrainService"
      :completedCount="completedCount"
      :inProgressCount="inProgressCount"
      :notStartedCount="notStartedCount"
    />

    <TariffStatusSection
      :model="selectedRateVariation"
      :isTrainService="isTrainService"
      :frequencyOptions="frequencyOptions"
    />

    <PoliciesSection :model="selectedRateVariation" />

    <hr class="section-divider" />

    <TariffInputModeSection :model="selectedRateVariation" />

    <UniquePricingSection
      v-if="selectedRateVariation?.typeMode === 'UNIQUE'"
      :model="selectedRateVariation?.pricing.passengers"
      :servicePercent="18"
      :igvPercent="18"
    />

    <!-- <RangeTable
      v-if="model.tariffInputMode === 'rangos'"
      :model="model"
    /> -->
  </a-form>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import PeriodSelectionSection from './sections/PeriodSelectionSection.vue';
  import TariffStatusSection from './sections/TariffStatusSection.vue';
  import PoliciesSection from './sections/PoliciesSection.vue';
  import TariffInputModeSection from './sections/TariffInputModeSection.vue';
  import UniquePricingSection from './passenger/UniquePricingSection.vue';
  import { useRateVariationSelector } from '@/modules/negotiations/products/configuration/content/pricingPlans/composables/prices/useRateVariationSelector';
  import { ServiceTypeEnum } from '@/modules/negotiations/products/configuration/enums/ServiceType.enum';
  import { usePriceStep } from './composables/usePriceStep';
  import { usePricesFlow } from '@/modules/negotiations/products/configuration/content/pricingPlans/composables/prices/usePricesFlow';

  interface Props {
    model: any;
    errors?: Record<string, string>;
    serviceType: ServiceTypeEnum;
  }

  const props = defineProps<Props>();

  const isTrainService = computed(() => props.serviceType === ServiceTypeEnum.TRAIN);

  const { variationCards, selectedRateVariationId, selectedRateVariation } =
    useRateVariationSelector();

  const { changeVariation, completedCount, inProgressCount, notStartedCount } = usePricesFlow();

  const { frequencyOptions } = usePriceStep(props.model);
</script>

<style scoped>
  @import '@/modules/negotiations/products/configuration/content/pricingPlans/ui/styles/price.scss';
</style>
