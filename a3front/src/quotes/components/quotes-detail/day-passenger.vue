<template>
  <div class="day-day">
    <div class="table-header">
      <div class="title">{{ t('quote.label.day_by_day_program') }}</div>
      <div class="items" :style="'flex: 0 0 ' + quotePrice.headers.length * 140 + 'px'">
        <div class="item" v-for="headers in quotePrice.headers">
          {{ headers }}
        </div>
      </div>
    </div>
    <div class="table-body">
      <div v-for="(day, index) in quotePrice.services" :key="index" class="item">
        <p class="title mb-2">
          {{ day.date }}
        </p>
        <div class="detail-container">
          <day-day-detail v-for="(item, a) in day.services" :key="a" :service="item" :range="-1" />
        </div>
      </div>
    </div>
    <div class="table-total">
      <div class="title">{{ t('quote.label.total_per_passenger') }}</div>
      <div class="items">
        <div class="item" v-for="total in quotePrice.services_totals">
          {{ displayPrice(total) }}
        </div>
      </div>
    </div>
    <div class="aditional-services" v-if="quotePrice.services_optionals.length > 0">
      <div class="item">
        <p class="title mb-2">
          {{ t('quote.label.optional_services_not') }}
        </p>
        <div
          class="detail-container"
          v-for="(day, index) in quotePrice.services_optionals"
          :key="index"
        >
          <div class="day-day-detail" v-for="(service, a) in day.services" :key="a">
            <div class="detail-name">
              <span>{{ day.date }}</span>
              <span v-if="service.type == 'service'">{{ service.descriptions }}</span>
              <span v-if="service.type == 'hotel'"
                >{{ service.descriptions }} - {{ service.room_type }} - {{ service.meal }}</span
              >
              <!-- <div v-if="props.item.private" class="tag tag-on">Privado</div> -->
            </div>
            <div class="items">
              <div v-for="(a, b) in service.columns" :key="b" class="item">
                {{ displayPrice(a) }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script lang="ts" setup>
  import { computed, toRef } from 'vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import type { QuotePricePassengersResponse } from '@/quotes/interfaces';

  import { useI18n } from 'vue-i18n';
  import DayDayDetail from '@/quotes/components/quotes-detail/day-day-detail.vue';
  import { getPriceWithCommission } from '@/utils/price';
  import { getUserType } from '@/utils/auth';
  import { useQuoteStore } from '@/quotes/store/quote.store';
  import { storeToRefs } from 'pinia';
  const quoteStore = useQuoteStore();
  const { marketList } = storeToRefs(quoteStore);
  const user_type_id = parseInt(getUserType(), 10);

  // ✅ usar directamente el cliente guardado en el store
  const client = computed(() => marketList?.value.data || null);

  // ✅ Función para mostrar el precio con comisión
  const displayPrice = (price: number) => getPriceWithCommission(price, client.value, user_type_id);

  const { t } = useI18n();

  const { quotePricePassenger } = useQuote();

  interface Props {
    category: number;
  }

  const props = defineProps<Props>();
  const category = toRef(props, 'category');

  const quotePrice = computed<QuotePricePassengersResponse[]>(() => {
    return quotePricePassenger.value.find(({ category_id }) => category_id === category.value).data;
  });

  const data = [
    {
      title: 'Día 01',
      subtitle: 'Cusco',
      items: [
        {
          title: 'Traslado aeropuerto -hotel',
          private: true,
          items: ['03', '$ 8', '$ 8', '$ 0'],
          status: 'ok',
        },
        {
          title: 'Hotel Munay Wasi',
          private: false,
          items: ['03', '$ 67', '$ 35', '$ 0'],
          status: 'alert',
        },
      ],
    },
    {
      title: 'Día 02',
      subtitle: 'Cusco',
      items: [
        {
          title: 'Hotel Munay Wasi',
          private: false,
          items: ['03', '$ 67', '$ 35', '$ 0'],
          status: 'alert',
        },
      ],
    },
    {
      title: 'Día 03',
      subtitle: 'Machu Picchu',
      items: [
        {
          title: 'Tour de día completo a Machu Picchu',
          private: true,
          items: ['02', '$ 256', '$ 256', '$ 0'],
          status: 'alert',
        },
        {
          title: 'Almuerzo en Machu Picchu',
          private: true,
          items: ['02', '$ 16', '$ 16', '$ 0'],
          status: 'alert',
        },
        {
          title: 'Hotel Munay Wasi',
          private: false,
          items: ['03', '$ 67', '$ 35', '$ 0'],
          status: 'alert',
        },
      ],
    },
    {
      title: 'Día 04',
      subtitle: 'Cusco',
      items: [
        {
          title: 'Traslado aeropuerto -hotel',
          private: true,
          items: ['03', '$ 8', '$ 8', '$ 0'],
          status: 'ok',
        },
      ],
    },
  ];
  console.log(data);
  const dataAditional = [
    {
      title: 'Servicios opcionales no incluidos',
      subtitle: '',
      items: [
        {
          title: 'City Tour y sitios arqueológicos cercanos',
          private: false,
          items: ['03', '$ 50', '$ 50', '$ 50'],
          status: 'ok',
        },
        {
          title: 'City Tour y sitios arqueológicos cercanos',
          private: false,
          items: ['03', '$ 50', '$ 50', '$ 50'],
          status: 'ok',
        },
        {
          title: 'City Tour y sitios arqueológicos cercanos',
          private: false,
          items: ['03', '$ 50', '$ 50', '$ 50'],
          status: 'ok',
        },
      ],
    },
  ];
  console.log(dataAditional);
</script>

<style lang="sass" scoped>
  .day-day
    padding: 15px 0
    margin-top: 100px

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
        flex-direction: row

      .item
        text-align: center
        font-size: 18px
        letter-spacing: -0.27px
        flex: 1 1 140px
        line-height: 1

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
        padding: 0 !important

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
        border: 2px dashed #eb5757
</style>
