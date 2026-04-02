<template>
  <div class="day-day">
    <div class="table-header">
      <div class="title">PROGRAMA DÍA A DÍA</div>
      <div class="items">
        <div class="item">CANTIDAD DE PAX</div>
        <div class="item">PRECIO POR PAX SGL</div>
        <div class="item">PRECIO POR PAX DBL</div>
        <div class="item">PRECIO POR PAX TPL</div>
        <div class="item">STATUS DEL SERVICIO</div>
      </div>
    </div>
    <div class="table-body">
      <div v-for="(day, index) in data" :key="index" class="item">
        <p class="title">
          {{ day.title }} | <span>{{ day.subtitle }}</span>
        </p>
        <div class="detail-container">
          <day-day-detail v-for="(item, a) in day.items" :key="a" :item="item" />
        </div>
      </div>
    </div>
    <div class="table-total">
      <div class="title">Total por pasajero</div>
      <div class="items">
        <div class="item">&nbsp;</div>
        <div class="item">$ 493</div>
        <div class="item">$ 397</div>
        <div class="item">&nbsp;</div>
        <div class="item">&nbsp;</div>
      </div>
    </div>
    <div class="aditional-services">
      <div v-for="(day, index) in dataAditional" :key="index" class="item">
        <p class="title">
          {{ day.title }}
        </p>
        <div class="detail-container">
          <day-day-detail v-for="(item, a) in day.items" :key="a" :item="item" />
        </div>
      </div>
    </div>
  </div>
</template>
<script lang="ts" setup>
  import DayDayDetail from '@/quotes/components/quotes-detail/day-day-detail.vue';
  // const { t } = useI18n();

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
      ],
    },
  ];
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
</style>
