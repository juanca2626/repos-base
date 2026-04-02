<template>
  <div class="quotes-detail-page">
    <div class="top-bg">
      <img alt="top-bg" src="../../images/quotes/quotes-details-bg.png" />
    </div>
    <div class="body">
      <h1>1. {{ t('quote.label.details_quote') }}</h1>
      <div class="title">
        <span>{{ quoteName }}</span
        ><span class="dates text-lowercase"
          >{{ quote.nights + 1 }} {{ t('quote.label.days') }} / {{ quote.nights }}
          {{ t('quote.label.nights') }}</span
        >
        <ButtonOutlineContainer
          icon="arrow-left"
          :text="t('quote.label.go_back')"
          @click="changePage('details')"
        />
      </div>
      <p class="quote-number">
        {{ t('quote.label.number_quote') }}: <b>{{ quoteId }}</b>
      </p>
      <div class="header">
        <!-- <p class="route">Lima, Cusco , Valle Sagrado & Machu Picchu</p> -->
        <div class="detail">
          <span class="label">{{ t('quote.label.arrival_departure') }}</span>
          <span class="text">{{ dateInFormatted }} - {{ dateEstimateFormat }}</span>
          <span class="label">{{ t('quote.label.number_passagers') }}</span>
          <span class="text">
            <template v-if="operation == 'passengers'"
              >{{ people?.adults }} {{ t('quote.label.adults') }} {{ people?.child }}
              {{ t('quote.label.child') }}</template
            >

            <template v-if="operation == 'ranges'">
              <span
                class="tag"
                :class="rangeSelected == index ? 'tag-on' : 'tag-off'"
                v-for="(range, index) of ranges"
                :key="`range-from-${range}`"
                @click="selected_range(index)"
              >
                {{ range.from }} - {{ range.to }}
              </span>
            </template>
          </span>

          <span class="label">{{ t('quote.label.rooms') }}</span>
          <span class="text">
            <span v-if="single > 0">{{ singleF }} SGL </span>
            <span v-if="double > 0">{{ doubleF }} DBL </span>
            <span v-if="triple > 0">{{ tripleF }} TPL </span>
          </span>
          <span class="label">{{ t('quote.label.add_categories') }}</span>
          <span class="text">
            <span
              class="tag"
              :class="catSelected == quoteCategory.type_class_id ? 'tag-on' : 'tag-off'"
              v-for="(quoteCategory, i) in quoteCategories"
              :key="i"
              @click="selected_category(quoteCategory.type_class_id)"
            >
              {{ quoteCategory.type_class.translations[0].value }}
            </span>
          </span>

          <span class="label">{{ t('quote.label.type_services') }}</span>
          <span class="text">{{ services }}</span>
        </div>
      </div>
      <hr />
      <InfoButtonsComponent :style="{ float: 'right' }" />

      <DayPassenger v-if="operation == 'passengers'" :category="catSelected" />
      <DayRanger v-if="operation == 'ranges'" :category="catSelected" :range="rangeSelected" />

      <hr />
      <AlertComponent
        icon="triangle-exclamation"
        :text="t('quote.label.prices_vary_based')"
        type="alert"
      />
      <DetailFooterComponent :category="catSelected" />
    </div>
    <div class="footer">
      <div class="centerFooter">
        <QuoteDetailsPackages />
      </div>
    </div>
  </div>

  <!-- Delete category modal is handled in DetailComponent.vue -->
</template>

<script lang="ts" setup>
  import { computed, ref } from 'vue';
  import { useQuote } from '@/quotes/composables/useQuote';

  import QuoteDetailsPackages from '@/quotes/pages/QuoteDetailsPackages.vue';
  import ButtonOutlineContainer from '@/quotes/components/global/ButtonOutlineContainer.vue';
  import InfoButtonsComponent from '@/quotes/components/InfoButtonsComponent.vue';
  import DayPassenger from '@/quotes/components/quotes-detail/day-passenger.vue';
  import DayRanger from '@/quotes/components/quotes-detail/day-ranger.vue';
  import AlertComponent from '@/quotes/components/global/AlertComponent.vue';
  import DetailFooterComponent from '@/quotes/components/global/DetailFooterComponent.vue';
  import moment from 'moment';
  import { useQuoteServices } from '@/quotes/composables/useQuoteServices';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const { servicesTypes } = useQuoteServices();
  const {
    quote,
    page,
    operation,
    people,
    quoteId,
    quoteCategories,
    accommodation,
    quoteServiceTypeId,
    ranges,
  } = useQuote();
  const catSelected = ref<number>();
  const rangeSelected = ref<number>();

  const changePage = (newView: string) => {
    page.value = newView;
  };
  const quoteName = computed(() => quote.value.name);

  if (!catSelected.value) {
    catSelected.value = quoteCategories.value[0].type_class_id;
  }

  if (!rangeSelected.value) {
    rangeSelected.value = 0;
  }

  const single = computed(() => accommodation.value.single);
  const double = computed(() => accommodation.value.double);
  const triple = computed(() => accommodation.value.triple);

  const singleF = computed(() => accommodation.value.single.toString().padStart(2, '0'));
  const doubleF = computed(() => accommodation.value.double.toString().padStart(2, '0'));
  const tripleF = computed(() => accommodation.value.triple.toString().padStart(2, '0'));

  const servicesTypesFiltered = computed(() => servicesTypes.value.filter((t) => t.code !== 'NA'));
  const services = computed(() => {
    if (quoteServiceTypeId.value) {
      const result = servicesTypesFiltered.value.find(({ id }) => id === quoteServiceTypeId.value);
      return result?.label;
    } else {
      return '';
    }
  });

  const dateIn = computed(() => quote.value.date_in);
  const dateInFormatted = computed(() => moment(dateIn.value).format('DD MMM  y'));

  const dateEstimate = computed(() => quote.value.estimated_travel_date);
  const dateEstimateFormat = computed(() => moment(dateEstimate.value).format('DD MMM  y'));

  const selected_category = (c: number) => {
    catSelected.value = c;
  };

  const selected_range = (c: number) => {
    rangeSelected.value = c;
  };

  // Delete category logic is handled in DetailComponent.vue

  // const children = reactive([
  //   {
  //     text: '7-9',
  //     class: 'tag-off',
  //   },
  //   {
  //     text: '9-12',
  //     class: 'tag-off',
  //   },
  //   {
  //     text: '13-14',
  //     class: 'tag-on',
  //   },
  // ]);

  // const delete_children = (index: number) => {
  //   children.splice(index, 1);
  // };
</script>

<style lang="sass" scoped>
  .quotes-detail-page
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
        font-size: 36px !important
        font-style: normal
        font-weight: 400
        line-height: 44px
        letter-spacing: -0.36px

      .title
        color: #000
        font-size: 28px
        font-style: normal
        font-weight: 400
        line-height: 40px
        letter-spacing: -0.42px
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
