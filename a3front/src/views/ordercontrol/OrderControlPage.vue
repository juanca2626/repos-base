<template>
  <!-- orderStore.isLoading -->
  <VerticalPageWrapper :breadcrumbTitle="'Control de Pedidos'" :isLoading="false">
    <router-view />
  </VerticalPageWrapper>
</template>

<script setup>
  import VerticalPageWrapper from '@views/layouts/VerticalPageWrapper.vue';
  // import { useOrderStore } from '@ordercontrol/store/order.store';
  // const orderStore = useOrderStore();
</script>
