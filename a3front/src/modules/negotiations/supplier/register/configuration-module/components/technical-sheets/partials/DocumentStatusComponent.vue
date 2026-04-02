<template>
  <div
    :class="['main-container', { clickable: isClickable }]"
    @click="emit('onDocumentStatus', status)"
  >
    <div :class="['container-status', containerStatusClass]">
      <span :class="['text-status', textStatusClass]">
        <template v-if="showUploadIcon">
          <font-awesome-icon :icon="['fas', 'arrow-up-from-bracket']" />
        </template>
        {{ statusText }}

        <template v-if="showObservations">
          <a-popover placement="bottom">
            <template #content>
              <div class="popover-content">
                <span class="title-observation"> Observaciones </span>
                <span class="popover-content-text d-block">
                  {{ observations }}
                </span>
              </div>
            </template>
            <font-awesome-icon
              :icon="['fas', 'circle-exclamation']"
              :style="{ color: '#FF3B3B', width: '12px', height: '12px' }"
            />
          </a-popover>
        </template>
      </span>
    </div>

    <div class="container-date mt-1">
      <span :class="['container-date', 'date-status', dateStatusClass]" v-if="showDate">
        <template v-if="showVehicleExtension && extension">
          {{ extension.dateTo }}
          <font-awesome-icon
            :icon="['fas', 'calendar-check']"
            class="icon-calendar-check"
            @click.stop="emit('onExtensionSuccessNotify', extension.id)"
          />
        </template>
        <template v-else>
          {{ date }}
        </template>
      </span>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { useDocumentStatus } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/useDocumentStatus';
  import type { DocumentStatusProps } from '@/modules/negotiations/supplier/register/configuration-module/types';

  const props = defineProps<DocumentStatusProps>();
  const emit = defineEmits(['onDocumentStatus', 'onExtensionSuccessNotify']);

  const {
    statusText,
    containerStatusClass,
    textStatusClass,
    dateStatusClass,
    showUploadIcon,
    showDate,
    showObservations,
    isClickable,
    showVehicleExtension,
    extension,
  } = useDocumentStatus(props);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .container-date {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
  }

  .icon-calendar-check {
    font-size: 16px;
    color: $color-blue-dark;
  }

  .main-container {
    display: inline-block;
    align-items: center;
    justify-content: center;
  }

  .clickable {
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

  .date-status {
    font-size: 12px;
    font-weight: 500;

    &-expired {
      color: $color-error-dark;
    }
  }

  .container-status {
    min-width: 100px;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 2px 6px;

    &-to-be-reviewed {
      background-color: $color-warning-light;
    }

    &-approved {
      background-color: $color-info-light;
    }

    &-rejected {
      background-color: $color-state-ligth;
    }

    &-to-expire {
      background-color: $color-warning-faded;
    }

    &-expired {
      background-color: $color-state-ligth;
    }

    &-not-applicable {
      background-color: $color-white-2;
    }
  }

  .text-status {
    font-size: 12px;
    font-weight: 600;

    &-no-documents {
      text-decoration: underline;
      color: $color-blue;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 4px;
    }

    &-to-be-reviewed {
      color: $color-gold-dark;
    }

    &-approved {
      color: $color-info-dark;
    }

    &-rejected {
      color: $color-error-dark;
    }

    &-to-expire {
      color: $color-warning-strong;
    }

    &-expired {
      color: $color-error-dark;
    }

    &-not-applicable {
      color: $color-black-2;
    }
  }
</style>
