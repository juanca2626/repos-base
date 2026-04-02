<script setup lang="ts">
  import { computed } from 'vue';
  import BoxComponent from '@/quotes/components/info/BoxComponent.vue';
  import { usePopup } from '@/quotes/composables/usePopup';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import QuoteServiceCategoriesForm from '@/quotes/components/info/quote-header/QuoteServiceCategoriesForm.vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const { servicesTypes } = useQuoteServices();
  const { quoteNew } = useQuote();
  const { showForm, toggleForm } = usePopup();

  const servicesTypesFiltered = computed(() => servicesTypes.value.filter((t) => t.code !== 'NA'));
  const services = computed(() => {
    if (quoteNew.value.quoteServiceTypeId) {
      const result = servicesTypesFiltered.value.find(
        ({ id }) => id === quoteNew.value.quoteServiceTypeId
      );
      return result?.label;
    } else {
      return 'Seleccione';
    }
  });

  const changeServices = (
    args: [
      {
        label: string;
        value: number;
      },
    ]
  ) => {
    showForm.value = false;
    quoteNew.value.quoteServiceTypeId = args[0].value;
  };
</script>

<template>
  <BoxComponent :title="t('quote.label.type_services')" @edit="toggleForm()">
    <template #text>{{ services }} <br /></template>
    <template #form>
      <quote-service-categories-form
        :servicesTypes="servicesTypesFiltered"
        :show="showForm"
        @selected="changeServices"
      />
    </template>
  </BoxComponent>
</template>

<style scoped lang="scss"></style>
