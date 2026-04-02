<template>
  <div>
    <div class="main-header">
      <div class="container-header">
        <span class="title-form"> Producto genérico </span>

        <a-button type="primary" class="button-new-service" @click="handleNewService">
          <font-awesome-icon :icon="['fas', 'plus']" />
          Crear nuevo producto
        </a-button>
      </div>
    </div>

    <div class="container-body">
      <ProductFilterComponent />
      <ProductListComponent />
    </div>
  </div>
</template>
<script lang="ts" setup>
  import ProductFilterComponent from '@/modules/negotiations/products/general/components/ProductFilterComponent.vue';
  import ProductListComponent from '@/modules/negotiations/products/general/components/ProductListComponent.vue';
  import { useRouter } from 'vue-router';

  const router = useRouter();

  const handleNewService = async () => {
    router.push({ name: 'productRegister' });
  };
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .container-body {
    padding: 24px 32px;
  }

  .main-header {
    .container-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 98px;
      padding: 24px 32px;
      border-bottom: 1px solid $color-gray-ligth-4;
    }

    .button-new-service {
      font-size: 16px !important;
      height: 50px;
      width: 245px;
      gap: 8px;
      background: $color-white;
      color: $color-black;
      border: 1px solid $color-black;
      box-shadow: none !important;

      &:hover {
        background: $color-white-2;
        border: 1px solid $color-black;
      }
    }
  }

  .title-form {
    font-weight: 700;
    font-size: 24px;
    color: $color-black;
  }
</style>
