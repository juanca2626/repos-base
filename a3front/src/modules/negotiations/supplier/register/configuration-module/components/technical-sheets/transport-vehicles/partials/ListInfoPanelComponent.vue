<template>
  <a-row class="main-container">
    <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="8" class="col-container-info">
      <div class="container-document-info" v-if="isTransportVehicleActive">
        <font-awesome-icon :icon="['fas', 'circle-info']" class="icon-document-info" />
        <span class="document-info"> El certificado de inspección y la TUC son opcionales </span>
      </div>
    </a-col>
    <a-col :xs="24" :sm="24" :md="24" :lg="24" :xl="16" class="col-container-legends">
      <div class="container-legends">
        <div v-for="legend in legendItems" :key="legend.key" class="div-legend">
          <span class="custom-circle" :class="'custom-circle-' + legend.key"></span>
          <span class="legend-title" :class="'legend-title-' + legend.key">
            {{ legend.text }}
          </span>
        </div>
        <div class="container-extension">
          <font-awesome-icon :icon="['fas', 'calendar-check']" class="icon-extension-activated" />
          <span class="legend-title legend-title-extension-activated"> Prórroga activada </span>
        </div>
      </div>
    </a-col>
  </a-row>
</template>
<script setup lang="ts">
  import { computed } from 'vue';

  interface LegendItem {
    key: string;
    text: string;
  }

  const props = defineProps({
    isTransportVehicleActive: {
      type: Boolean,
      required: true,
    },
  });

  const baseLegendItems: LegendItem[] = [
    { key: 'to-be-reviewed', text: 'Por revisar' },
    { key: 'rejected', text: 'Rechazado' },
    { key: 'approved', text: 'Aprobado' },
    { key: 'to-expire', text: 'Por vencer' },
    { key: 'expired', text: 'Vencido' },
  ];

  const vehicleLegendItems: LegendItem[] = [
    { key: 'not-apply', text: 'No se aplica' },
    ...baseLegendItems,
  ];

  const driverLegendItems: LegendItem[] = [...baseLegendItems];

  const legendItems = computed(() => {
    return props.isTransportVehicleActive ? vehicleLegendItems : driverLegendItems;
  });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .main-container {
    margin-bottom: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .col-container-info {
    display: flex;
    flex-grow: 1;
  }

  .col-container-legends {
    display: flex;
    flex-grow: 1;
    justify-content: flex-end;
  }

  .container-legends {
    display: flex;
    gap: 16px;
  }

  .div-legend {
    display: flex;
    align-items: center;
    gap: 3px;
  }

  .legend-title {
    font-size: 14px;
    font-weight: 500;

    &-not-apply {
      color: $color-black-2;
    }

    &-to-be-reviewed {
      color: $color-gold-dark;
    }

    &-rejected {
      color: $color-error-dark;
    }

    &-approved {
      color: $color-info-dark;
    }

    &-to-expire {
      color: $color-warning-strong;
    }

    &-expired {
      color: $color-error-dark;
    }

    &-extension-activated {
      color: $color-blue-dark;
    }
  }

  .container-extension {
    display: flex;
    align-items: center;
    gap: 3px;
  }

  .icon-extension-activated {
    color: $color-blue-dark;
  }

  .custom-circle {
    display: inline-block;
    width: 10px;
    height: 10px;
    border-radius: 50%;

    &-not-apply {
      background-color: $color-black-2;
    }

    &-to-be-reviewed {
      background-color: $color-warning;
    }

    &-rejected {
      background-color: $color-error-dark;
    }

    &-approved {
      background-color: $color-success;
    }

    &-to-expire {
      background-color: $color-warning-soft;
    }

    &-expired {
      background-color: $color-error-dark;
    }

    &-extension-activated {
      background-color: $color-blue-dark;
    }
  }

  .container-document-info {
    min-height: 36px;
    display: flex;
    align-items: center;
    border-radius: 4px;
    padding: 10px;
    background-color: $color-tag-ligth;
    gap: 6px;
  }

  .document-info {
    font-size: 14px;
    font-weight: 500;
    color: $color-tag-dark;
  }

  .icon-document-info {
    width: 14px;
    height: 14px;
    color: $color-info;
  }
</style>
