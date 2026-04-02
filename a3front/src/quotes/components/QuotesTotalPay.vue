<script lang="ts" setup>
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import { computed, reactive, onMounted } from 'vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';
  import { getPriceWithCommission, hasCommission } from '@/utils/price';
  import { getUserType } from '@/utils/auth';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import { useReports } from '@/quotes/composables/useReports';
  import { storeToRefs } from 'pinia';
  const quoteStore = useQuoteStore();
  const { markets } = useReports();
  const { marketList } = storeToRefs(quoteStore);
  const user_type_id = parseInt(getUserType(), 10);

  // ✅ usar directamente el cliente guardado en el store
  const client = computed(() => marketList?.value.data || null);

  // ✅ Función para mostrar el precio con comisión
  const displayPrice = (price: number) => getPriceWithCommission(price, client.value, user_type_id);

  onMounted(async () => {
    if (!marketList.value || Object.keys(marketList.value).length === 0) {
      await markets(true); // 🔁 esto fuerza la carga del cliente en el store
    }
  });

  // ✅ Mostrar badge solo si aplica
  const showCommissionBadge = computed(() => hasCommission(client.value, user_type_id));

  const { t } = useI18n();

  const {
    accommodation,
    quoteCategories,
    selectedCategory,
    operation,
    quotePricePassenger,
    quotePriceRanger,
    page,
  } = useQuote();

  const single = computed(() => accommodation.value.single);
  const double = computed(() => accommodation.value.double);
  const triple = computed(() => accommodation.value.triple);

  const singleF = computed(() => accommodation.value.single.toString().padStart(2, '0'));
  const doubleF = computed(() => accommodation.value.double.toString().padStart(2, '0'));
  const tripleF = computed(() => accommodation.value.triple.toString().padStart(2, '0'));

  const state = reactive({
    showModalDetail: false,
  });

  // const priceCategorySelected = ref();

  const priceCategorySelected = computed(() => {
    if (quotePricePassenger.value.length > 0) {
      if (operation.value == 'passengers') {
        return quotePricePassenger?.value.find((c) => c.category_id === selectedCategory.value);
      } else {
        return quotePriceRanger?.value.find((c) => c.category_id === selectedCategory.value);
      }
    } else {
      return false;
    }
  });

  const toggleModalDetail = async () => {
    if (operation.value == 'passengers') {
      //  if (Object.keys(quotePricePassenger?.value).length == 0)  {
      //    await getQuotePricePassenger();
      //  }
      //  priceCategorySelected.value = quotePricePassenger?.value.find((c) => c.category_id === selectedCategory.value)

      if (priceCategorySelected.value.data.type_report == 'summarized') {
        state.showModalDetail = !state.showModalDetail;
      } else {
        page.value = 'details-price';
      }
    }

    if (operation.value == 'ranges') {
      // if (Object.keys(quotePriceRanger?.value).length == 0)  {
      //    await getQuotePriceRanger();
      // }
      page.value = 'details-price';
    }
  };

  // prices
  // const services = computed(() => {
  //   return (
  //     quoteCategories.value.find((c) => c.type_class_id === selectedCategory.value)?.services ?? []
  //   );
  // });

  interface QuoteTotalPrices {
    total: number;
    optional: number;
  }

  // prices
  const priceTotal = computed<QuoteTotalPrices>(() => {
    const prices = {
      total: 0,
      optional: 0,
    };

    // services.value.forEach((ser) => {
    //   if ((ser as GroupedServices).service.optional) {
    //     if ((ser as GroupedServices).type === 'service') {
    //       prices.optional += ((ser as GroupedServices).service.import as QuoteServiceServiceImport)?.total_amount ?? 0
    //     } else {
    //       prices.optional += (ser as GroupedServices).group.map(s => s.import_amount?.price_ADL ?? 0).reduce((a, b) => Number(a) + Number(b), 0)
    //     }
    //   } else {
    //     if ((ser as GroupedServices).type === 'service') {
    //       prices.total += ((ser as GroupedServices).service.import as QuoteServiceServiceImport)?.total_amount ?? 0
    //     } else {
    //       prices.total += (ser as GroupedServices).group.map(s => s.import_amount?.price_ADL ?? 0).reduce((a, b) => Number(a) + Number(b), 0)
    //     }
    //   }
    // })

    prices.total = priceCategorySelected.value ? priceCategorySelected.value.data.sum_total : 0;

    // let total = 0
    // if(priceCategorySelected.value){
    //     priceCategorySelected.value.data.headers.forEach((row, index) => {
    //        if(row == 'Single'){
    //           total = total + (priceCategorySelected.value.data.services_totals[index] * 1);
    //        }
    //        if(row == 'double'){
    //           total = total + (priceCategorySelected.value.data.services_totals[index] * 1);
    //        }
    //     });
    // }
    // prices.total = total

    return prices;
  });

  const selected_category = (c: number) => {
    selectedCategory.value = c;
  };
</script>

<template>
  <div class="quotes-total-pay" v-if="operation == 'passengers'">
    <div class="total">{{ t('quote.label.total_pay') }}</div>
    <div class="total-value">${{ displayPrice(priceTotal.total) }}</div>
    <span v-if="showCommissionBadge" class="badge-warning ml-2">
      {{ t('global.label.with_commission') }}
    </span>
    <!-- <div class="optional">Servicios opcionales</div>
    <div class="total-optional">${{ priceTotal.optional }}</div> -->
    <div class="link">
      <a href="#" @click="toggleModalDetail">{{ t('quote.label.view_detail') }}</a>
    </div>
    <ModalComponent
      :modal-active="state.showModalDetail"
      class="quotes-total-pay-modal"
      @close="toggleModalDetail"
    >
      <template #body>
        <h3 class="title">{{ t('quote.label.detail_prices') }}:</h3>
        <div class="body">
          <span
            class="tag"
            :class="selectedCategory == quoteCategory.type_class_id ? 'tag-on' : 'tag-off'"
            v-for="(quoteCategory, i) in quoteCategories"
            :key="i"
            @click="selected_category(quoteCategory.type_class_id)"
          >
            {{ quoteCategory.type_class.translations[0].value }}
          </span>

          <div class="item">
            <span class="big">
              {{ t('quote.label.rooms') }}
              <span v-if="single > 0">{{ singleF }} SGL</span>
              <span v-if="double > 0">{{ doubleF }} DBL</span>
              <span v-if="triple > 0">{{ tripleF }} TPL</span>
            </span>
          </div>
          <hr />
          <template v-if="priceCategorySelected">
            <div
              class="item"
              v-for="(price, index) in priceCategorySelected.data.headers"
              :key="index"
            >
              <span>x ADL {{ price }}</span>
              <span>$ {{ displayPrice(priceCategorySelected.data.services_totals[index]) }}</span>
            </div>
            <!-- {{ priceCategorySelected }} -->
          </template>

          <div class="spacer"></div>

          <hr />
          <div class="item total" v-if="priceCategorySelected">
            <span class="pri">{{ t('quote.label.total') }}</span>
            <span>$ {{ displayPrice(priceCategorySelected.data.sum_total) }}</span>
          </div>

          <span v-if="showCommissionBadge" class="badge-warning ml-2">
            {{ t('global.label.with_commission') }}
          </span>
        </div>
      </template>
    </ModalComponent>
  </div>
</template>

<style lang="scss">
  .quotes-total-pay {
    width: 268px;
    height: 82px;
    display: flex;
    flex-wrap: wrap;

    .total {
      display: flex;
      width: 60%;
      height: 29px;
      flex-direction: column;
      justify-content: center;
      flex-shrink: 0;
      font-size: 18px;
      font-style: normal;
      font-weight: 700;
      line-height: 30px;
      letter-spacing: -0.27px;
    }

    .optional {
      display: flex;
      width: 60%;
      height: 29px;
      flex-direction: column;
      justify-content: center;
      flex-shrink: 0;
      font-size: 14px;
      font-style: normal;
      font-weight: 400;
      line-height: 22px;
      letter-spacing: -0.21px;
    }

    .link {
      width: 179px;
      height: 29px;
      flex-shrink: 0;

      a {
        display: flex;
        width: 179px;
        height: 29px;
        flex-direction: column;
        justify-content: center;
        flex-shrink: 0;
        color: #eb5757;
        font-size: 18px;
        font-style: normal;
        font-weight: 400;
        line-height: 30px; /* 166.667% */
        letter-spacing: -0.27px;
        text-decoration-line: underline;
      }
    }

    .total-value {
      display: flex;
      width: 40%;
      height: 29px;
      flex-direction: column;
      justify-content: center;
      flex-shrink: 0;
      color: #eb5757;
      font-size: 24px;
      font-style: normal;
      font-weight: 700;
      line-height: 36px; /* 150% */
      letter-spacing: -0.36px;
    }

    .total-optional {
      display: flex;
      width: 40%;
      height: 29px;
      flex-direction: column;
      justify-content: center;
      flex-shrink: 0;
      color: #eb5757;
      font-size: 14px;
      font-style: normal;
      font-weight: 400;
      line-height: 22px;
      letter-spacing: -0.21px;
    }

    .quotes-total-pay-modal {
      .modal-inner {
        width: 480px;
      }

      .title {
        text-align: left !important;
        margin-bottom: 15px !important;
      }

      .body {
        flex-shrink: 0;
        margin: 0 15px;

        .item {
          display: flex;
          height: 34px;
          flex-direction: row;
          justify-content: center;
          flex-shrink: 0;
          color: #000;
          font-size: 14px;
          font-style: normal;
          font-weight: 400;
          line-height: 22px;
          letter-spacing: -0.21px;

          span {
            flex: 0 0 200px;
            font-size: 18px;

            &:nth-last-of-type(odd) {
              flex: 1;
              text-align: right;
              font-weight: 600;
            }
          }

          .big {
            font-size: 18px;
            line-height: 30px;
            letter-spacing: -0.27px;
            text-align: left !important;
            font-weight: normal !important;

            span {
              margin: 0 6px;
              border: 1px solid #e9e9e9;
              border-radius: 6px;
              font-size: 12px;
              font-weight: normal !important;
              padding: 3px 5px;
            }
          }

          .spacer {
            margin-bottom: 10px;
          }

          hr {
            border-top: 1px solid #c4c4c4;
          }

          &.total {
            width: auto;
            color: #eb5757;
            font-size: 24px;
            font-style: normal;
            font-weight: 700;
            line-height: 36px;
            letter-spacing: -0.36px;
            margin-bottom: 15px;
            font-size: 36px;

            span {
              font-size: 36px;

              &.pri {
                font-size: 24px;
              }
            }

            &:nth-last-of-type(odd) {
              font-size: 36px;
            }
          }
        }
      }

      p {
        color: #3a3a3c;
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 22px;
        letter-spacing: -0.21px;
        margin-left: 10px;
      }
    }
  }
</style>
<style lang="sass" scoped>
  .body
    .tag
      display: inline-block
      width: 114px
      height: 24px
      flex-shrink: 0
      border-radius: 6px
      color: #FEFEFE
      text-align: center
      font-size: 14px
      line-height: 22px
      letter-spacing: -0.21px
      margin-right: 16px
      position: relative
      cursor: default

      a
        position: absolute
        left: -5px
        top: -5px
        border-radius: 10px
        background: #575757
        width: 20px
        height: 20px
        display: none
        line-height: 20px

      &.tag-on
        background: #EB5757

        &:hover
          background: #C63838

      &.tag-off
        background: #CFCFCF

        &:hover
          background: #BBBDBF

      &:hover a
        display: block

      &.left-tag
        margin-left: 0

    .top-bg
      img
        width: 100%
        height: 355px

    .body
      width: 80vw
      margin: 0 auto
      padding: 90px 0 110px 0

      h1
        color: #EB5757
        font-size: 48px
        font-style: normal
        font-weight: 400
        line-height: 72px
        letter-spacing: -0.72px

      .title
        color: #000
        font-size: 36px
        font-style: normal
        font-weight: 400
        line-height: 50px
        letter-spacing: -0.54px
        display: flex
        gap: 30px
        align-items: center
        margin-bottom: 30px
        padding: 0

        :deep(.button-outline-container)
          height: auto
          padding: 14px 16px

          .text
            font-size: 16px


      .dates
        font-size: 24px
        font-weight: 700
        line-height: 36px

    .quote-number
      color: #EB5757
      font-size: 18px
      margin-bottom: 30px


    .header
      font-size: 18px
      font-style: normal
      font-weight: 400
      line-height: 30px
      letter-spacing: -0.27px


      .route
        color: #000
        margin-bottom: 40px

      .detail
        display: grid
        grid-template-columns: 430px 1fr

        .label
          text-transform: uppercase
          flex: 1 1 300px
          color: #000
          font-weight: 700
          margin-bottom: 20px

        .text
          flex: 1 1 0

    hr
      stroke-width: 1px
      stroke: #C4C4C4
      margin-bottom: 38px

    .footer
      background-color: #F5F5F5
      padding: 90px 0 110px

      .centerFooter
        width: 80vw
        margin: 0 auto

        h2
          color: #000
          font-size: 48px
          font-style: normal
          font-weight: 400
          line-height: 72px
          letter-spacing: -0.72px
          margin-bottom: 30px

        .items
          display: flex
          flex-direction: row
          justify-content: space-between
          gap: 70px

          .item
            max-width: 410px
            display: flex
            flex-direction: column
            gap: 10px

            img
              width: 100%
              height: 280px
              margin-bottom: 15px

            .top
              display: flex
              color: #000
              font-size: 24px
              font-style: normal
              font-weight: 700
              line-height: 36px
              letter-spacing: -0.36px
              justify-content: space-between

            .place
              color: #333
              font-size: 18px
              font-style: normal
              font-weight: 700
              line-height: 30px
              letter-spacing: -0.27px
              display: flex
              align-items: center

            .description
              font-size: 18px
              color: #2E2E2E
              margin-bottom: 40px


            .buttons
              display: flex
              flex-direction: row
              justify-content: space-between

              .button-component.btn-md
                height: 40px
                line-height: 40px
                min-width: 148px
                padding: 0

            .price
              span
                font-size: 18px
</style>
