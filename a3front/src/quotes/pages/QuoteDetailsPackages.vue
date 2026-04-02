<script lang="ts" setup>
  import { ref } from 'vue';
  import ButtonComponent from '@/quotes/components/ButtonComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { useI18n } from 'vue-i18n';
  import { getPackagesRecommendations } from '@/quotes/helpers/get-packages-recommendations';
  import type { Package } from '@/quotes/interfaces';
  import moment from 'moment';
  import { getUserClientId } from '@/utils/auth';

  const { t } = useI18n();
  // const { showIsLoading, closeIsLoading } = useLoader();
  const { quote } = useQuote();

  const footerItems = [
    {
      img: 'img01.png',
      name: t('quote.label.hacienda_concepcion'),
      days: '3D/2N',
      place: t('quote.label.puerto_maldonado'),
      description: t('quote.label.biodiversity_peruvian'),
      price: '280',
    },
    {
      img: 'img02.png',
      name: t('quote.label.posada_amazonas'),
      days: '3D/2N',
      place: t('quote.label.puerto_maldonado'),
      description: t('quote.label.biodiversity_peruvian'),
      price: '280',
    },
    {
      img: 'img03.png',
      name: t('quote.label.amazon_reserve'),
      days: '3D/2N',
      place: t('quote.label.puerto_maldonado'),
      description: t('quote.label.biodiversity_peruvian'),
      price: '440',
    },
  ];

  console.log(footerItems);

  const packages = ref<Package[]>([]);

  const getPackages = async () => {
    packages.value = await getPackagesRecommendations({
      lang: 'en',
      client_id: getUserClientId(),
      type_service: 'all',
      limit: 3,
      date: moment().add(1, 'days').format('YYYY-MM-DD'),
      quantity_persons: {
        adults: 2,
        child_with_bed: 0,
        child_without_bed: 0,
        age_child: [
          {
            age: 1,
          },
        ],
      },
      only_recommended: 1,
      rooms: {
        quantity_sgl: 0,
        quantity_dbl: 1,
        quantity_tpl: 0,
      },
    });
  };

  getPackages();

  const goToPackageDetails = (pack) => {
    let data = {
      lang: localStorage.getItem('lang'),
      date: moment().add(1, 'days').format('YYYY-MM-DD'),
      quantity_persons: {
        adults: 2,
        child_with_bed: 0,
        child_without_bed: 0,
        age_child: [
          {
            age: 1,
          },
        ],
      },
      type_service: quote.value.service_type_id,
      rooms: {
        quantity_sgl: 0,
        quantity_dbl: 1,
        quantity_tpl: 0,
      },
      date_to_days: pack.nights + 1,
      package_ids: [pack.id],
    };

    document.cookie =
      'parameters_packages_details=' + JSON.stringify(data) + ';domain=' + window.DOMAIN + ';';

    window.location = window.url_front_a2 + 'package-details';
  };

  const truncate = (text, length, clamp) => {
    clamp = clamp || '...';
    var node = document.createElement('div');
    node.innerHTML = text;
    var content = node.textContent;
    return content.length > length ? content.slice(0, length) + clamp : content;
  };
</script>

<template>
  <h2>{{ t('quote.label.keep_quoting') }}</h2>
  <div class="items">
    <div v-for="(item, index) in packages" :key="index" class="item">
      <img :src="item.galleries[0].url" alt="quotes-detail-footer" />
      <div class="top">
        <div class="name">
          {{ item.descriptions.name }}
        </div>
        <div class="days">{{ item.nights + 1 }}/{{ item.nights }}N</div>
      </div>
      <div class="place">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
        >
          <path
            fill-rule="evenodd"
            clip-rule="evenodd"
            d="M5 9.26489C5 5.39489 8.13 2.26489 12 2.26489C15.87 2.26489 19 5.39489 19 9.26489C19 13.4349 14.58 19.1849 12.77 21.3749C12.37 21.8549 11.64 21.8549 11.24 21.3749C9.42 19.1849 5 13.4349 5 9.26489ZM9.5 9.26489C9.5 10.6449 10.62 11.7649 12 11.7649C13.38 11.7649 14.5 10.6449 14.5 9.26489C14.5 7.88489 13.38 6.76489 12 6.76489C10.62 6.76489 9.5 7.88489 9.5 9.26489Z"
            fill="#333333"
          />
        </svg>
        {{ item.destinations.destinations_display }}
      </div>
      <div class="description">
        {{ truncate(item.descriptions.description, 200, '...') }}
      </div>
      <div class="buttons">
        <div class="detail-button">
          <ButtonComponent type="outline" @click="goToPackageDetails(item)"
            >{{ t('quote.label.view_detail') }}
          </ButtonComponent>
        </div>
        <div class="price">
          <p>
            {{ t('quote.label.from') }} ${{ item.amounts.price_per_person }}<br /><span>{{
              t('quote.label.per_passenger')
            }}</span>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

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
<style>
  .quotes-detail-page .footer {
    background-color: #f5f5f5;
    margin-top: 80px;
    padding: 90px 0 110px;

    .centerFooter {
      width: 80vw;
      margin: 0 auto;

      h2 {
        color: #000;
        font-size: 48px;
        font-style: normal;
        font-weight: 400;
        line-height: 72px;
        letter-spacing: -0.72px;
        margin-bottom: 30px;
      }

      .items {
        display: flex;
        flex-direction: row;
        justify-content: space-between;
        gap: 70px;

        .item {
          max-width: 410px;
          display: flex;
          flex-direction: column;
          gap: 10px;

          img {
            width: 100%;
            height: 280px;
            margin-bottom: 15px;
          }

          .top {
            display: flex;
            color: #000;
            font-size: 24px;
            font-style: normal;
            font-weight: 700;
            line-height: 36px;
            letter-spacing: -0.36px;
            justify-content: space-between;
          }

          .place {
            color: #333;
            font-size: 18px;
            font-style: normal;
            font-weight: 700;
            line-height: 30px;
            letter-spacing: -0.27px;
            display: flex;
            align-items: center;
          }

          .description {
            font-size: 18px;
            color: #2e2e2e;
            margin-bottom: 40px;
          }

          .buttons {
            display: flex;
            flex-direction: row;
            justify-content: space-between;

            .button-component.btn-md {
              height: 40px;
              line-height: 40px;
              min-width: 148px;
              padding: 0;
            }
          }

          .price {
            span {
              font-size: 18px;
            }
          }
        }
      }
    }
  }
</style>
