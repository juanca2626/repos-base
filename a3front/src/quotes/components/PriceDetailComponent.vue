<template>
  <div class="quotes-price-detail" :class="{ pricefooter: hideTitle }">
    <h2 v-if="!hideTitle">{{ props.title }}</h2>
    <div class="quotes-detail" :class="{ detailfooter: hideTitle }">
      <QuotesTotalPay />
      <template v-if="operation == 'passengers'">
        <ButtonComponent
          v-if="viewBtn === 'table'"
          class="quotes-detail-two"
          icon="arrows-rotate"
          type="outline"
          @click="updatePrice()"
        >
          {{ t('quote.label.update_prices') }}
        </ButtonComponent>
      </template>
      <ButtonComponent class="quotes-detail-three" @click="changePage('details-price')"
        >{{ t('quote.label.see_price_details') }}
      </ButtonComponent>
    </div>
  </div>
</template>

<script lang="ts" setup>
  import QuotesTotalPay from '@/quotes/components/QuotesTotalPay.vue';
  import ButtonComponent from '@/quotes/components/global/ButtonComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import useNotification from '@/quotes/composables/useNotification';
  import { useI18n } from 'vue-i18n';
  import { storeToRefs } from 'pinia';
  import { useQuoteStore } from '@/quotes/store/quote.store';

  const { t } = useI18n();

  const { operation, page, updatePrince, verify_itinerary_errors } = useQuote();
  // const { getQuotePricePassenger,getQuotePriceRanger } = useQuotePrice()
  const { showErrorNotification } = useNotification();

  const quoteStore = useQuoteStore();
  const { quote } = storeToRefs(quoteStore);

  const changePage = async (newView: string) => {
    // if(operation.value == 'passengers'){
    //   if(Object.keys(quotePricePassenger?.value).length == 0){
    //      await getQuotePricePassenger();
    //   }
    // }else{
    //   if (Object.keys(quotePriceRanger?.value).length == 0)  {
    //      await getQuotePriceRanger()
    //   }
    // }

    if (quote.value?.passengers) {
      const hasInvalidAge = quote.value?.age_child?.some((child) => parseInt(child.age) === 0);
      if (hasInvalidAge) {
        showErrorNotification(t('quote.label.error_child_age_zero'));
        quoteStore.showPassengersForm = true;
        window.scrollTo(0, 0);
        return;
      }
    }

    if (verify_itinerary_errors()) {
      showErrorNotification(t('quote.label.observations_validation_text'));
    } else {
      page.value = newView;
      window.scrollTo(0, 0);
    }
  };

  // const changePage2 = async (newView: string) => {
  //   page.value = newView;
  // };

  const props = defineProps({
    title: String,
    viewBtn: String,
    hideTitle: Boolean,
  });

  const updatePrice = async () => {
    if (verify_itinerary_errors()) {
      showErrorNotification(t('quote.label.observations_validation_text'));
    } else {
      updatePrince();
    }
  };
</script>

<style lang="scss">
  .quotes-price-detail {
    display: flex;
    width: 100%;
    padding: 30px 0 24px;
    justify-content: space-between;
    align-items: center;
    background: #fff;

    &.pricefooter {
      padding: 0 0 50px 0;
    }

    h2 {
      display: flex;
      width: 433px;
      margin: 0;
      height: 29px;
      flex-direction: column;
      justify-content: center;
      flex-shrink: 0;
      color: #2e2e2e;
      font-size: 36px;
      font-style: normal;
      font-weight: 400;
      line-height: 50px;
      letter-spacing: -0.54px;
    }

    .quotes-detail {
      display: flex;
      align-items: flex-start;
      gap: 24px;

      &.detailfooter {
        width: 100%;

        .quotes-detail-two {
          margin-left: auto;
        }
      }

      .quotes-detail-two {
        min-width: 210px;
        font-size: 16px;
        cursor: pointer;
        justify-content: center;
        align-items: center;
      }

      .quotes-detail-three {
        width: 225px;
        gap: 10px;
        cursor: pointer;
        justify-content: center;
        align-items: center;
      }
    }
  }
</style>
