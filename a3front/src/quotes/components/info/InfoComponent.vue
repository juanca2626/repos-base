<script lang="ts" setup>
  import QuoteNameComponent from '@/quotes/components/info/quote-header/QuoteName.vue';
  import QuoteNewNameComponent from '@/quotes/components/info/quote-header/QuoteNewName.vue';

  import QuoteDateRange from '@/quotes/components/info/quote-header/QuoteDateRange.vue';
  import QuoteNewDateRange from '@/quotes/components/info/quote-header/QuoteNewDateRange.vue';

  import QuotePassengers from '@/quotes/components/info/quote-header/QuotePassengers.vue';
  import QuoteNewPassengers from '@/quotes/components/info/quote-header/QuoteNewPassengers.vue';

  import QuoteRange from '@/quotes/components/info/quote-header/QuoteRange.vue';

  import QuoteOccupation from '@/quotes/components/info/quote-header/QuoteOccupation.vue';
  import QuoteHotelCategories from '@/quotes/components/info/quote-header/QuoteHotelCategories.vue';
  import QuoteNewHotelCategories from '@/quotes/components/info/quote-header/QuoteNewHotelCategories.vue';
  import QuoteServiceCategories from '@/quotes/components/info/quote-header/QuoteServiceCategories.vue';
  import QuoteNewServiceCategories from '@/quotes/components/info/quote-header/QuoteNewServiceCategories.vue';
  import QuoteLanguages from '@/quotes/components/info/quote-header/QuoteLanguages.vue';

  import IconView from '@/quotes/components/icons/IconView.vue';
  import IconCalendars from '@/quotes/components/icons/IconCalendars.vue';

  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';

  import { provide, ref } from 'vue';

  const sw = ref<boolean>(false);
  provide('show-occupations', sw);
  provide('show-range', sw);

  const { view, operation, quote } = useQuote();
  const { t } = useI18n();

  const changeView = (newView: string) => {
    view.value = newView;
  };
</script>

<template>
  <div class="quotes-info">
    <h3 class="title-red" v-if="quote?.id">
      {{ t('quote.label.modify_package') }}
      <span v-if="quote.file.file_code" style="float: right"
        >{{ t('quote.label.file') }}: {{ quote.file.file_code }}</span
      >
    </h3>
    <h3 class="title-red" v-else>
      {{ t('quote.label.new_quote') }}
    </h3>
    <div class="frame-two">
      <div class="frame-two-left">
        <quote-name-component v-if="quote?.id" />
        <quote-new-name-component v-else />
      </div>
      <div class="frame-two-right" v-if="quote?.id">
        <span class="text">{{ t('quote.label.view') }}:</span>
        <span class="table-icon" @click="changeView('table')">
          <icon-view :class="{ active: view === 'table' }" />
          <!--<font-awesome-icon
            :class="{ active: view === 'table' }"
            icon="table-list"
          />-->
        </span>
        <span class="calendar-icon" @click="changeView('calendar')">
          <icon-calendars :class="{ active: view === 'calendar' }" />
          <!--<font-awesome-icon
            :class="{ active: view === 'calendar' }"
            icon="calendar-days"
          />-->
        </span>
      </div>
    </div>
    <div class="frame-three">
      <div class="container" v-if="quote?.id">
        <quote-date-range />

        <quote-passengers />

        <quote-occupation />

        <quote-hotel-categories />

        <quote-service-categories />

        <quote-languages />
      </div>

      <div class="container" v-else>
        <quote-new-date-range />
        <quote-new-passengers />
        <quote-new-service-categories />
        <quote-new-hotel-categories />
      </div>
    </div>
    <div v-if="operation == 'ranges'" class="frame-three range">
      <quote-range />
    </div>
  </div>
</template>

<style lang="scss">
  @import '@/scss/_variables.scss';
</style>
