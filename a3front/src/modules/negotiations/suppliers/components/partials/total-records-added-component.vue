<template>
  <div class="mb-3">
    <span class="quantity-records">
      {{ description }}
    </span>
  </div>
</template>
<script setup lang="ts">
  import { computed } from 'vue';
  import { useSupplier } from '@/modules/negotiations/suppliers/composables/supplier.composable';
  import { supplierInfo } from '@/modules/negotiations/suppliers/constants/supplier-info';

  const props = defineProps({
    total: {
      type: Number,
      required: true,
    },
  });

  const { selectedClassificationId } = useSupplier();

  const description = computed(() => {
    if (selectedClassificationId.value) {
      return supplierInfo[selectedClassificationId.value]?.getRecordsAddedText(props.total);
    }

    return null;
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .quantity-records {
    font-size: 16px;
    font-weight: 500;
    color: $color-black-2;
  }
</style>
