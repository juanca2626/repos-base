<template>
  <div v-if="props.show" class="price-order">
    <DropDownSelectComponent :items="price" :multi="false" />
  </div>
</template>
<script lang="ts" setup>
  import DropDownSelectComponent from '../global/DropDownSelectComponent.vue';
  // const { quote } = useQuote();

  const props = defineProps({
    show: {
      required: true,
      default: false,
    },
  });

  const price = [
    {
      label: t('quote.label.offers'),
      value: 'ofertas',
    },
    {
      label: t('quote.label.price'),
      value: 'precio',
    },
    {
      label: t('quote.label.name'),
      value: 'nombre',
    },
  ];
</script>

<style lang="scss" scoped>
  .price-order {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    border-radius: 0 0 6px 6px;
    background: #fff;
    box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
    z-index: 1;

    .dropdown-select {
      width: 120px;
    }
  }
</style>
