<template>
  <div class="consideration-alert" v-if="considerationDescription">
    <a-alert v-if="visibleAlert">
      <template #description>
        <div class="container-alert-description">
          <div class="alert-description">
            <CustomInfoCircleIcon :width="18" :height="18" fill="#5C5AB4" class="info-icon" />
            <span class="consideration-text">
              {{ considerationDescription }}
            </span>
          </div>
          <font-awesome-icon
            :icon="['fas', 'xmark']"
            class="close-icon"
            @click="handleCloseAlert"
          />
        </div>
      </template>
    </a-alert>
  </div>
</template>
<script setup lang="ts">
  import { ref, computed, watch } from 'vue';
  import { driverDocumentInfo } from '@/modules/negotiations/supplier/register/configuration-module/constants/type-driver-document';
  import { vehicleDocumentInfo } from '@/modules/negotiations/supplier/register/configuration-module/constants/type-vehicle-document';
  import { TypeVehicleDriverDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-driver-document.enum';
  import { TypeVehicleDocumentEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-vehicle-document.enum';
  import CustomInfoCircleIcon from '@/modules/negotiations/supplier/components/icons/CustomInfoCircleIcon.vue';

  const props = defineProps<{
    typeDocumentId: TypeVehicleDriverDocumentEnum | TypeVehicleDocumentEnum;
    isTransportVehicle: boolean;
    isDrawerOpen: boolean;
  }>();

  const visibleAlert = ref<boolean>(true);

  const handleCloseAlert = () => {
    visibleAlert.value = false;
  };

  const considerationDescription = computed(() => {
    return props.isTransportVehicle
      ? vehicleDocumentInfo[props.typeDocumentId as TypeVehicleDocumentEnum] || ''
      : driverDocumentInfo[props.typeDocumentId as TypeVehicleDriverDocumentEnum] || '';
  });

  watch(
    () => props.isDrawerOpen,
    (newVal) => {
      if (newVal) {
        visibleAlert.value = true;
      }
    }
  );
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .consideration-alert {
    .close-icon {
      color: $color-black-5 !important;
      cursor: pointer;
      width: 18px;
      height: 18px;
    }

    .alert-description {
      display: flex;
      align-items: center;
      gap: 10px;

      > .info-icon {
        flex-shrink: 0;
        margin-top: 2px;
      }
    }

    .container-alert-description {
      display: flex;
      align-items: center;
      justify-content: space-between;
      min-height: 30px;
    }

    .consideration-text {
      font-size: 14px;
      font-weight: 400;
      margin-right: 15px;
    }
  }
</style>
