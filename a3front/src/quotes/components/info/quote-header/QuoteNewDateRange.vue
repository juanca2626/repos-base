<script setup lang="ts">
  import BoxComponent from '@/quotes/components/info/BoxComponent.vue';
  import { computed } from 'vue';
  // import moment from 'moment';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useLanguagesStore } from '@/stores/global';

  import dayjs, { Dayjs } from 'dayjs';
  import { useI18n } from 'vue-i18n';
  import { DateTime } from 'luxon';

  const languageStore = useLanguagesStore();
  const { t } = useI18n();
  const { quoteNew } = useQuote();

  // moment.locale('es');

  // formatting dates
  const dateIn = computed(() => quoteNew.value.date_in);
  const dateInFormatted = computed(() => {
    if (dateIn.value) {
      let [y, m, d] = dateIn.value.split('-');
      return DateTime.fromObject(
        { year: y, month: m, day: d, hour: 0, minute: 0 },
        { locale: languageStore.currentLanguage }
      ).toFormat('MMM dd, yyyy');
    }
  });

  const dateEstimate = computed(() => quoteNew.value.estimated_travel_date);
  const dateEstimateFormat = computed(() => {
    if (dateEstimate.value) {
      let [y, m, d] = dateEstimate.value.split('-');
      return DateTime.fromObject(
        { year: y, month: m, day: d, hour: 0, minute: 0 },
        { locale: languageStore.currentLanguage }
      ).toFormat('MMM dd, yyyy');
    }
  });

  const changeDateIn = (data: { date: Date | Dayjs; dateString: string }) => {
    quoteNew.value.date_in = dayjs(data.dateString).format('YYYY-MM-DD');
    quoteNew.value.estimated_travel_date = DateTime.fromISO(quoteNew.value.date_in)
      .plus({ days: 1 })
      .toFormat('yyyy-MM-dd');
  };

  const changeEstimatedTravelDateFormatted = (data: { date: Date | Dayjs; dateString: string }) => {
    quoteNew.value.estimated_travel_date = dayjs(data.dateString).format('YYYY-MM-DD');
  };

  // watchEffect(() => {
  //   if (!quote.value.estimated_travel_date) {
  //     quote.value.estimated_travel_date = dayjs(quote.value.date_in)
  //       .add(quote.value.nights, 'day')
  //       .format('YYYY-MM-DD');
  //   }
  // });
</script>

<template>
  <BoxComponent
    :title="t('quote.label.day_start')"
    type="date"
    :default-date="dateIn"
    @change="changeDateIn"
  >
    <template #text>{{ dateInFormatted }}</template>
  </BoxComponent>
  <BoxComponent
    :title="t('quote.label.day_end')"
    type="date"
    :default-date="dateEstimate"
    @change="changeEstimatedTravelDateFormatted"
  >
    <template #text>{{ dateEstimateFormat }}</template>
  </BoxComponent>
</template>

<style scoped lang="scss"></style>
