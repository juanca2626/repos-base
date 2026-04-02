<template>
  <div class="mt-2">
    <a-row>
      <a-col span="12">
        <p class="text-title-filter">{{ textTitle }}</p>
      </a-col>
      <a-col span="12" class="text-right">
        <a-typography-link class="link-title-download" @click="emit('onDownload')">
          <CustomDownloadIcon :width="18" :height="18" stroke="#1284ed" :strokeWidth="2.5" />
          <span class="text-download"> Descargar resultados </span>
        </a-typography-link>
      </a-col>
    </a-row>
  </div>
</template>
<script setup lang="ts">
  import { storeToRefs } from 'pinia';
  import { computed } from 'vue';
  import { useTechnicalSheetStore } from '@/modules/negotiations/supplier/register/configuration-module/store/useTechnicalSheetStore';
  import CustomDownloadIcon from '@/modules/negotiations/supplier/components/icons/CustomDownloadIcon.vue';

  const technicalSheetStore = useTechnicalSheetStore();
  const { isTransportVehicleActive } = storeToRefs(technicalSheetStore);

  const textTitle = computed(
    () => `Filtra tus ${isTransportVehicleActive.value ? 'unidades' : 'conductores'}`
  );

  const emit = defineEmits(['onDownload']);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .text-title-filter {
    font-size: 16px;
    font-weight: 600;
    color: $color-black;
  }

  .link-title-download {
    display: flex;
    align-items: center;
    justify-content: end;
    gap: 6px;
    margin-right: 8px;

    font-size: 16px;
    font-weight: 600;
    color: $color-blue;
    margin-left: 10px;

    &:hover {
      color: $color-blue;
    }
  }
</style>
