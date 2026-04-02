<template>
  <div class="day-day">
    <div class="table-header">
      <div class="title">{{ t('quote.label.day_by_day_program') }}</div>

      <div class="items items_header">
        <div class="item itemMin">{{ quotePrice.headers[range].head }}</div>
        <div class="listItem">
          <div class="item" v-for="(range, index) in quotePrice.headers[range].ranges" :key="index">
            {{ range }}
          </div>
        </div>
      </div>
    </div>
    <div class="table-body">
      <div v-for="(day, index) in quotePrice.services" :key="index" class="item">
        <p class="title">
          {{ day.date }}
        </p>
        <div class="detail-container">
          <day-day-detail
            v-for="(item, a) in day.services"
            :key="a"
            :service="item"
            :range="range"
            :accomodation="quotePrice.headers[range].ranges.length"
            :optional="false"
          />
        </div>
      </div>
    </div>
    <div class="table-total">
      <div class="title">{{ t('quote.label.total_per_passenger') }}</div>
      <div class="items">
        <div class="item" v-for="total in import_totals">{{ total }}</div>
      </div>
    </div>
    <div class="aditional-services" v-if="quotePrice.services_optionals.length > 0">
      <div class="item">
        <p class="title">
          {{ t('quote.label.optional_services_not') }}
        </p>
        <div
          class="detail-container"
          v-for="(day, index) in quotePrice.services_optionals"
          :key="index"
        >
          <day-day-detail
            v-for="(item, a) in day.services"
            :key="a"
            :service="item"
            :range="range"
            :accomodation="quotePrice.headers[range].ranges.length"
            :optional="day.date"
          />
        </div>
      </div>
    </div>
  </div>
</template>
<script lang="ts" setup>
  import { computed, toRef } from 'vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import type { QuotePriceRangesResponse } from '@/quotes/interfaces';
  import { useI18n } from 'vue-i18n';
  import DayDayDetail from '@/quotes/components/quotes-detail/day-day-detail.vue';

  const { t } = useI18n();

  const { quotePriceRanger } = useQuote();

  interface Props {
    category: number;
    range: number;
  }

  const props = defineProps<Props>();
  const category = toRef(props, 'category');
  const range = toRef(props, 'range');

  const quotePrice = computed<QuotePriceRangesResponse[]>(() => {
    return quotePriceRanger.value.find(({ category_id }) => category_id === category.value).data;
  });

  const import_totals = computed(() => {
    if (range.value !== null) {
      let j = 0;
      let data = [];
      for (let i = 0; i < range.value + 1; i++) {
        data = [];
        for (let k = 0; k < quotePrice.value.headers[range.value].ranges.length; k++) {
          data.push(quotePrice.value.services_totals[j]);
          j++;
        }
      }
      return data;
    } else {
      return quotePrice.value.services_totals;
    }
  });
</script>

<style lang="sass" scoped>
  .items_header
    .listItem
      display: flex
      flex-direction: row
      align-items: center
      padding: 15px 0 0 0

      .item
        width: 140px


  .day-day
    padding: 15px 0
    margin-top: 100px
    overflow: auto

    .table-header
      color: #2E2E2E
      font-size: 24px
      font-style: normal
      font-weight: 700
      line-height: 36px
      letter-spacing: -0.36px
      display: flex
      flex-direction: row
      align-items: center
      margin-bottom: 15px
      justify-content: space-between

      .title
        font-size: 24px
        font-style: normal
        font-weight: 700
        flex: 0 0 550px
        margin: 0
        gap: 20px
        padding: 0

      .items
        display: flex
        justify-content: space-between
        flex-direction: column

      .item
        text-align: center
        font-size: 18px
        letter-spacing: -0.27px
        //flex: 1 1 170px
        line-height: 1
        flex: 1

        &.itemMin
          width: 100%
          flex: 100%

    .table-body
      margin-bottom: 30px

      .item
        margin-bottom: 25px

        .title
          color: #EB5757
          font-size: 18px
          line-height: 30px
          letter-spacing: -0.27px
          display: block
          margin-bottom: 0

          span
            color: #2E2E2E
            font-weight: 400

    .table-total
      display: flex
      flex-direction: row
      align-items: center
      margin-bottom: 40px
      justify-content: space-between

      .title
        font-size: 24px
        font-weight: 700
        flex: 0 0 550px
        margin: 0
        gap: 20px
        padding: 0

      .items
        display: flex
        flex-direction: row

      .item
        text-align: center
        font-size: 18px
        letter-spacing: -0.27px
        width: 140px
        line-height: 1
        font-weight: 700

    .aditional-services
      .title
        color: #7E7E7E

      .day-day-detail
        background: #FCECEC

  .day-day-detail
    .items
      background: #F6F6F6
      border-radius: 6px
</style>
