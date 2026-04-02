<template>
  <a-row>
    <a-col span="17" class="space-align-block">
      <font-awesome-icon :icon="['fas', 'bus']" /> Transporte terrestre
    </a-col>
    <a-col span="7" class="p-r-2">
      <a-tabs
        class="transport-tabs-menu"
        centered
        v-model:activeKey="activeKey"
        @change="handleTabChange"
      >
        <a-tab-pane key="suppliersTouristTransportList">
          <template #tab>
            <span>Transportes turísticos</span>
          </template>
        </a-tab-pane>
        <a-tab-pane key="suppliersTouristTrainList">
          <template #tab>
            <span>Trenes turísticos</span>
          </template>
        </a-tab-pane>
      </a-tabs>
    </a-col>
  </a-row>
</template>
<script setup lang="ts">
  import { ref, onMounted, watch } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { SupplierSubClassifications } from '@/modules/negotiations/constants'; // Ajusta esta ruta si es necesario
  import { useSupplierClassificationStore } from '@/modules/negotiations/supplier/store/supplier-classification.store';

  const route = useRoute();
  const router = useRouter();
  const supplierClassificationStore = useSupplierClassificationStore();
  const activeKey = ref('');

  const updateActiveKeyAndClassification = () => {
    const currentRouteName = route.name as string;
    if (currentRouteName === 'supplierTouristTransportList') {
      activeKey.value = currentRouteName;
      supplierClassificationStore.setSupplierSubClassificationId(
        SupplierSubClassifications.TOURIST_TRANSPORT
      );
    } else if (currentRouteName === 'suppliersTouristTrainList') {
      activeKey.value = currentRouteName;
      supplierClassificationStore.setSupplierSubClassificationId(
        SupplierSubClassifications.TOURIST_TRAINS
      );
    }
  };

  onMounted(() => {
    updateActiveKeyAndClassification();
  });

  // Observar cambios en la ruta y actualizar
  watch(() => route.name, updateActiveKeyAndClassification);

  // Manejar cambios de tab
  const handleTabChange = (key: string) => {
    router.push({ name: key });
  };
</script>
<style scoped lang="scss">
  .space-align-block {
    display: flex;
    align-items: center;
    font-size: 16px;
    font-weight: 600;
    padding-left: 2rem;
    svg {
      margin-right: 10px;
      font-size: 16px;
    }
  }

  .transport-tabs-menu :deep(.ant-tabs-nav-wrap) {
    justify-content: right !important;
    padding-right: 45px;
  }

  .transport-tabs-menu :deep(.ant-tabs-nav) {
    &::before {
      border-bottom: none;
    }
  }

  .transport-tabs-menu :deep(.ant-tabs-tab-active) {
    border-bottom: 2px solid #cf1322;
    font-weight: 600;
    font-size: 14px;
  }

  .transport-tabs-menu :deep(.ant-tabs-tab-active::after) {
    border-bottom: none;
  }

  .transport-tabs-menu :deep(.ant-tabs-tab) {
    color: #7e8285;
  }
</style>
