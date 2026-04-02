<template>
  <div class="passenger-cards">
    <PassengerCard
      title="Adulto"
      subtitle="Mayor de 12 años"
      icon="user-group"
      :servicePercent="servicePercent"
      :igvPercent="igvPercent"
      v-model:rate="model.adult.rate"
    />

    <PassengerCard
      title="Niño"
      subtitle="Mayor de 12 años"
      icon="user"
      :servicePercent="servicePercent"
      :igvPercent="igvPercent"
      allowDiscount
      v-model:rate="model.child.rate"
      v-model:discountEnabled="model.child.discountEnabled"
      v-model:discountPercent="model.child.discountPercent"
    />

    <PassengerCard
      title="Infante"
      subtitle="2 - 12 años"
      icon="child"
      :servicePercent="servicePercent"
      :igvPercent="igvPercent"
      allowDiscount
      v-model:rate="model.infant.rate"
      v-model:discountEnabled="model.infant.discountEnabled"
      v-model:discountPercent="model.infant.discountPercent"
    />
  </div>
</template>

<script setup lang="ts">
  import PassengerCard from './PassengerCard.vue';

  defineProps<{
    model: any;
    servicePercent: number;
    igvPercent: number;
  }>();
</script>

<style scoped>
  .passenger-cards {
    display: flex;
    gap: 16px;
    width: 100%;
    align-items: stretch;
  }

  :deep(.passenger-card) {
    flex: 1 1 0;
    min-width: 0;
  }
</style>
