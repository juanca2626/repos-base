<template>
  <div class="clickable-status" @click="emit('onPhotoClick')">
    <template v-if="status === VehiclePhotoStatusEnum.NO_DOCUMENTS">
      <div class="container-icon-photo container-icon-photo-no-documents">
        <CustomImagePlusIcon :width="18" :height="18" stroke="#1284ED" />
      </div>
    </template>
    <template v-else-if="status === VehiclePhotoStatusEnum.TO_BE_REVIEWED">
      <div class="container-icon-photo container-icon-photo-to-be-reviewed">
        <CustomImageSquareIcon :width="18" :height="18" stroke="#E4B804" />
      </div>
    </template>
    <template v-else-if="status === VehiclePhotoStatusEnum.APPROVED">
      <div class="container-icon-photo container-icon-photo-approved">
        <CustomImageSquareCheckIcon :width="18" :height="18" stroke="#00A15B" />
      </div>
    </template>
    <template v-else-if="status === VehiclePhotoStatusEnum.REJECTED">
      <div class="container-icon-photo container-icon-photo-rejected">
        <a-badge :offset="[-2.5, 2.5]">
          <template #count>
            <span>
              <a-popover placement="bottom" class="custom-popover">
                <template #content>
                  <div class="popover-content">
                    <span class="title-observation"> Observaciones </span>
                    <span class="popover-content-text d-block">
                      {{ observation }}
                    </span>
                  </div>
                </template>
                <CustomExclamationCircleIcon :width="14" :height="14" fillCircle="#FF3B3B" />
              </a-popover>
            </span>
          </template>
          <CustomImageIcon :width="18" :height="18" fill="#D80404" />
        </a-badge>
      </div>
    </template>
  </div>
</template>

<script setup lang="ts">
  import type { PropType } from 'vue';
  import { VehiclePhotoStatusEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/vehicle-photo-status.enum';
  import CustomImagePlusIcon from '@/modules/negotiations/supplier/components/icons/CustomImagePlusIcon.vue';
  import CustomImageSquareIcon from '@/modules/negotiations/supplier/components/icons/CustomImageSquareIcon.vue';
  import CustomImageSquareCheckIcon from '@/modules/negotiations/supplier/components/icons/CustomImageSquareCheckIcon.vue';
  import CustomImageIcon from '@/modules/negotiations/supplier/components/icons/CustomImageIcon.vue';
  import CustomExclamationCircleIcon from '@/modules/negotiations/supplier/components/icons/CustomExclamationCircleIcon.vue';

  defineProps({
    status: {
      type: Number as PropType<VehiclePhotoStatusEnum>,
      required: true,
    },
    observation: {
      type: String,
      default: null,
    },
  });

  const emit = defineEmits(['onPhotoClick']);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .clickable-status {
    cursor: pointer;
  }

  .popover-content {
    max-width: 350px;

    &-text {
      font-size: 12px;
      font-weight: 400;
      text-align: justify;
      margin-top: 4px;
    }
  }

  .title-observation {
    font-size: 12px;
    font-weight: 500;
  }

  .container-icon-photo {
    border-radius: 4px;
    padding: 5px;
    gap: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 26px;
    min-height: 26px;

    &-no-documents {
      background-color: $color-blue-light;
    }

    &-to-be-reviewed {
      background-color: $color-warning-light;
    }

    &-approved {
      background-color: $color-info-light;
    }

    &-rejected {
      background-color: $color-state-ligth;
    }
  }
</style>
