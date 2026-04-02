<template>
  <div v-if="resolvedCommercialInformationComponent">
    <component :is="resolvedCommercialInformationComponent" />
  </div>
</template>

<script setup lang="ts">
  import { useSupplierNegotiationComponentResolver } from '@/modules/negotiations/supplier-new/composables/form/supplier-negotiation-component-resolver.composable';

  defineOptions({
    name: 'CommercialInformationComponent',
  });

  const { resolvedCommercialInformationComponent } = useSupplierNegotiationComponentResolver();
</script>
