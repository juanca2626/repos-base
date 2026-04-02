<template>
  <div class="summary-container">
    <ul class="summary-list">
      <li>
        <span class="item-title"> Tipo de servicio: </span>
        <span class="item-description">
          {{ formState.serviceTypeName }}
        </span>
      </li>
      <li>
        <span class="item-title"> Código genérico: </span>
        <span class="item-description">
          {{ formState.code }}
        </span>
      </li>
      <li>
        <span class="item-title"> Nombre genérico: </span>
        <span class="item-description">
          {{ formState.name }}
        </span>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
  import { useProductFormStoreFacade } from '@/modules/negotiations/products/general/composables/form/useProductFormStoreFacade';

  const { formState } = useProductFormStoreFacade();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .summary-container {
    padding: 30px 0 12px 16px;

    .summary-list {
      padding-left: 20px;
    }

    .item-title {
      font-size: 14px;
      font-weight: 600;
      color: $color-black-3;
    }

    .item-description {
      font-size: 14px;
      font-weight: 400;
      color: $color-black-3;
    }

    .summary-list li {
      margin-bottom: 16px;
    }

    .summary-list li::marker {
      color: $color-black-3;
    }
  }
</style>
