<template>
  <div class="day-day-detail" v-if="service">
    <div class="detail-name">
      <span v-if="optional">{{ optional }}</span>
      <span v-if="service.type == 'service'">{{ service.descriptions }}</span>
      <span v-if="service.type == 'hotel'"
        >{{ service.descriptions }} - {{ service.room_type }} - {{ service.meal }}</span
      >
      <!-- <div v-if="props.item.private" class="tag tag-on">Privado</div> -->
    </div>
    <div class="items">
      <div v-for="(a, b) in imports" :key="b" class="item">
        {{ displayPrice(a) }}
      </div>
      <!-- {{ imports }}  {{ range }} {{ accomodation }} -->
    </div>
  </div>
</template>
<script lang="ts" setup>
  import { computed, toRef } from 'vue';
  import type { ServicePassenger } from '@/quotes/interfaces';
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
  interface Props {
    service: ServicePassenger;
    range?: number;
    accomodation?: number;
    optional?: string;
  }

  const props = defineProps<Props>();
  const service = toRef(props, 'service');
  const range = toRef(props, 'range');
  const accomodation = toRef(props, 'accomodation');
  const optional = toRef(props, 'optional');

  const imports = computed(() => {
    if (range.value !== -1) {
      let j = 0;
      let data = [];
      for (let i = 0; i < range.value + 1; i++) {
        data = [];
        for (let k = 0; k < accomodation.value; k++) {
          data.push(service.value.columns[j]);
          j++;
        }
      }
      return data;
    } else {
      return service.value.columns;
    }
  });
</script>

<style lang="sass">
  .day-day-detail
    border-radius: 6px
    background: #F6F6F6
    display: flex
    flex-direction: row
    padding-left: 20px
    justify-content: space-between
    margin-bottom: 10px

    .detail-name
      display: flex
      flex: 0 0 530px
      gap: 15px
      align-items: center
      position: relative
      padding: 8px 0

      .move
        font-size: 15px
        color: #EB5757

      span
        font-size: 14px

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
        margin-right: 20px
        position: absolute
        right: 0

        &.tag-on
          background-color: #FF9494
          width: auto
          padding: 0 15px
          font-size: 12px

    .items
      display: flex
      flex-direction: row
      align-items: center

      .item
        width: 140px
        text-align: center
</style>
