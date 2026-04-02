<template>
  <div class="box-rooming">
    <a-row :gutter="16" align="middle" justify="space-between" class="mt-5">
      <a-col :span="20">
        <div class="hotel-info">
          <div class="hotel-info-name">{{ hotel.hotel }}</div>
          <a-divider type="vertical" style="height: 25px; width: 2px; background-color: #575757" />
          <div class="hotel-info-city">{{ hotel.city }}</div>
        </div>
      </a-col>
      <a-col :span="4">
        <div class="hotel-date">
          <div class="hotel-date-label">Fecha:</div>
          <div class="hotel-date-in">{{ hotel.date_in }}</div>
        </div>
      </a-col>
    </a-row>
    <a-row :gutter="16" align="middle" justify="space-between" class="mb-5 mt-3">
      <a-col :span="20">
        <div class="file-info">
          <div>REF:</div>
          <div class="file-info-number">{{ file.fileNumber }}</div>
          <div>- {{ file.description }}</div>
        </div>
      </a-col>
    </a-row>
    <!-- Llegadas -->
    <!--    <a-row :gutter="16" align="middle" justify="space-between" class="mb-2">-->
    <!--      <a-col :span="20">-->
    <!--        <div class="file-arrival">-->
    <!--          <div>Llega</div>-->
    <!--          <div class="file-info-number">May 21/23</div>-->
    <!--          <div>-->
    <!--            <font-awesome-icon :icon="['fas', 'plane-arrival']" />-->
    <!--            H25145-->
    <!--          </div>-->
    <!--          <div>-->
    <!--            <font-awesome-icon :icon="['far', 'clock']" />-->
    <!--            19:09-->
    <!--          </div>-->
    <!--        </div>-->
    <!--      </a-col>-->
    <!--    </a-row>-->

    <!--    <a-row :gutter="16" align="middle" justify="space-between" class="mb-2">-->
    <!--      <a-col :span="20">-->
    <!--        <div class="file-arrival">-->
    <!--          <div>Llega</div>-->
    <!--          <div class="file-info-number">May 21/23</div>-->
    <!--          <div>-->
    <!--            <font-awesome-icon :icon="['fas', 'bus']" />-->
    <!--            Terrestre-->
    <!--          </div>-->
    <!--        </div>-->
    <!--      </a-col>-->
    <!--    </a-row>-->

    <!-- Notas -->
    <!--    <a-row :gutter="16" align="middle" justify="space-between" class="mb-5 mt-5">-->
    <!--      <a-col :span="24">-->
    <!--        <div class="notes-form">-->
    <!--          <a-checkbox size="large"> Agregar nota al proveedor</a-checkbox>-->
    <!--        </div>-->
    <!--      </a-col>-->
    <!--    </a-row>-->

    <HotelTableList :rooms="hotel.rooms" />
  </div>
</template>
<script setup lang="ts">
  import HotelTableList from '@/components/files/rooming/components/HotelTableList.vue';
  import { useFilesStore } from '@/stores/files';

  const fileStore = useFilesStore();
  const file = fileStore.getFile;
  defineProps({
    hotel: {
      type: Object,
      default: () => {},
    },
  });
</script>

<style scoped lang="scss">
  .hotel-info {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 25px;
    font-family: Montserrat, serif;
    font-weight: 700;
    font-size: 24px;

    &-city {
      font-weight: 500;
    }

    &-date-label,
    &-date {
      font-family: Montserrat, serif;
      font-weight: 400;
      font-size: 12px;
    }

    &-date {
      font-weight: 700;
    }
  }

  .file-info {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
    font-family: Montserrat, serif;
    font-weight: 400;
    font-size: 16px;

    &-number {
      font-weight: 700;
    }
  }

  .file-arrival {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
    font-family: Montserrat, serif;
    font-weight: 400;
    font-size: 16px;

    &-number {
      font-weight: 700;
    }
  }

  .hotel-date {
    display: flex;
    flex-direction: row;
    gap: 10px;
    font-family: Montserrat, serif;
    font-size: 12px;
    flex-wrap: nowrap;
    justify-content: flex-end;

    &-label {
      font-weight: 400;
    }

    &-in {
      font-weight: 700;
    }
  }

  .notes-form {
    label {
      font-family: Montserrat, serif;
      font-size: 16px;
      font-weight: 500;
      color: #979797;
    }
  }

  /* Estilos para el encabezado */
  .header-programacion {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 30px;
    gap: 10px;
    margin-bottom: 20px;
  }

  .info-date,
  .info-service,
  .info-time,
  .info-passengers,
  .info-status {
    display: flex;
    align-items: center;
  }

  .info-date {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    gap: 10px;
    font-family: Montserrat, serif;
    font-weight: 700;
    font-size: 12px;

    svg {
      color: #eb5757;
      font-size: 15px;
    }
  }

  .info-status {
    gap: 16px;
  }

  .status-tag {
    font-family: Montserrat, serif;
    padding: 2px 10px;
    background-color: #c63838;
    color: #ffffff;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 700;
  }

  .info-passengers {
    font-weight: 500 !important;
  }

  .info-service,
  .info-passengers {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
    font-family: Montserrat, serif;
    font-weight: 400;
    font-size: 14px;

    svg {
      font-size: 18px;
    }
  }

  .info-time {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;
    font-family: Montserrat, serif;
    font-weight: 400;
    font-size: 14px;

    svg {
      color: #c4c4c4;
      font-size: 18px;
    }
  }

  .icon-calendar,
  .icon-bus,
  .icon-clock {
    font-size: 20px;
    color: #575757;
  }

  .info-status .ant-tag {
    font-size: 14px;
    padding: 0 10px;
  }

  /* Detalles del servicio */
  .box-service-container {
    background: #fafafa;
    border-radius: 6px;
    margin-left: 56px;
    margin-right: 56px;

    .box-service {
      background: #fff;
      padding: 15px 20px;
      margin-bottom: 30px;

      &-title {
        font-family: Montserrat, serif;
        font-weight: 600;
        font-size: 14px;
      }

      &-info {
        font-family: Montserrat, serif;
        font-weight: 500;
        font-size: 16px;
        color: #979797;
      }

      &-profile {
        font-family: Montserrat, serif;
        font-weight: 600;
        color: #55a3ff;
        font-size: 12px;
        display: flex;
        gap: 8px;
        align-items: flex-start;
        flex-direction: row;
        flex-wrap: nowrap;
        cursor: pointer;
        text-decoration: underline;
        text-underline-position: under;
        text-underline-offset: 1px;

        svg {
          color: #55a3ff;
          font-size: 15px;
        }
      }

      &-view-report {
        font-family: Montserrat, serif;
        font-weight: 600;
        color: #eb5757;
        font-size: 12px;
        display: flex;
        gap: 8px;
        align-items: flex-start;
        flex-direction: row;
        flex-wrap: nowrap;
        cursor: pointer;
        text-decoration: underline;
        text-underline-position: under;
        text-underline-offset: 1px;

        svg {
          color: #55a3ff;
          font-size: 15px;
        }
      }

      &-number-phone {
        font-family: Montserrat, serif;
        font-weight: 600;
        color: #52c41a;
        font-size: 12px;
        display: flex;
        gap: 10px;
        align-items: center;
        flex-direction: row;
        flex-wrap: nowrap;
        cursor: pointer;

        svg {
          color: #55a3ff;
          font-size: 15px;
        }
      }
    }
  }
</style>
