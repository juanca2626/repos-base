<script setup lang="ts">
  import BoxComponent from '@/quotes/components/info/BoxComponent.vue';
  import { computed, watchEffect } from 'vue';
  // import moment from 'moment';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useLanguagesStore } from '@/stores/global';

  import dayjs, { Dayjs } from 'dayjs';
  import { useI18n } from 'vue-i18n';
  import { DateTime } from 'luxon';

  const languageStore = useLanguagesStore();
  const { t } = useI18n();
  const { quote, updateDateIn, processing } = useQuote();

  // moment.locale('es');

  // formatting dates
  const dateIn = computed(() => quote.value.date_in);
  const dateInFormatted = computed(() => {
    let [y, m, d] = dateIn.value.split('-');
    return DateTime.fromObject(
      { year: y, month: m, day: d, hour: 0, minute: 0 },
      { locale: languageStore.currentLanguage }
    ).toFormat('MMM dd, yyyy');
  });
  const dateEstimate = computed(() => quote.value.estimated_travel_date);
  // const dateEstimateFormat = computed(() => moment(dateEstimate.value).format("MMM DD, y"))

  const dateEstimateFormat = computed(() => {
    let [y, m, d] = dateEstimate.value.split('-');
    return DateTime.fromObject(
      { year: y, month: m, day: d, hour: 0, minute: 0 },
      { locale: languageStore.currentLanguage }
    ).toFormat('MMM dd, yyyy');
  });

  const changeDateIn = (data: { date: Date | Dayjs; dateString: string }) => {
    quote.value.date_in = dayjs(data.dateString).format('YYYY-MM-DD');
    updateDateIn(quote.value.date_in);
  };

  const changeEstimatedTravelDateFormatted = (data: { date: Date | Dayjs; dateString: string }) => {
    quote.value.estimated_travel_date = dayjs(data.dateString).format('YYYY-MM-DD');
  };

  watchEffect(() => {
    if (!quote.value.estimated_travel_date) {
      quote.value.estimated_travel_date = dayjs(quote.value.date_in)
        .add(quote.value.nights, 'day')
        .format('YYYY-MM-DD');
    }
  });
</script>

<template>
  <BoxComponent
    :title="t('quote.label.day_start')"
    type="date"
    :default-date="dateIn"
    :disabled="processing"
    @change="changeDateIn"
  >
    <template #text>{{ dateInFormatted }}</template>
  </BoxComponent>
  <BoxComponent
    :title="t('quote.label.day_end')"
    type="date"
    :default-date="dateEstimate"
    :disabled="processing"
    @change="changeEstimatedTravelDateFormatted"
  >
    <template #text>{{ dateEstimateFormat }}</template>
  </BoxComponent>
</template>

<style scoped lang="scss"></style>
