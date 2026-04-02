<template>
  <div v-if="visible" class="chart-point-popup" :style="popupStyle">
    <div class="popup-header">
      <div class="popup-header-left">
        <span class="popup-date">{{ formattedDate }}</span>
      </div>
      <div class="popup-header-right">
        <span class="popup-category">
          {{ categoryName }}
        </span>
        <span class="popup-icon">
          <IconHotel />
        </span>
        <span class="popup-total">{{ hotelDetails.length }} hoteles</span>
      </div>
    </div>
    <div class="popup-body">
      <div v-for="(hotel, index) in hotelDetails" :key="index" class="popup-hotel-item">
        <span class="hotel-name" :title="hotel.hotel_name">{{ hotel.hotel_name }}</span>
        <span class="hotel-status" :class="getStatusClass(hotel.state_group)">
          <font-awesome-icon
            v-if="hotel.state_group === 'agotado'"
            :icon="['fas', 'circle-exclamation']"
            class="badge-icon"
          />
          <font-awesome-icon
            v-else-if="hotel.state_group === 'minima'"
            :icon="['fas', 'circle-exclamation']"
            class="badge-icon"
          />
          <IconCheck v-else-if="hotel.state_group === 'disponible'" class="badge-icon" />
          {{ getStatusLabel(hotel.state_group) }}
        </span>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import moment from 'moment';
  import type { HotelAvailabilityDetail } from '@/modules/negotiations/hotels/quotas/interfaces/quotas.interface';
  import IconHotel from '../icons/icon-hotel.vue';
  import IconCheck from '../icons/icon-check.vue';

  interface Props {
    visible: boolean;
    date: string;
    categoryName: string;
    categoryColor: string;
    totalHotels: number;
    hotelDetails: HotelAvailabilityDetail[];
    chartType: 'month' | 'day';
    x: number;
    y: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    visible: false,
    date: '',
    categoryName: '',
    categoryColor: '#DDCA1F',
    totalHotels: 0,
    hotelDetails: () => [],
    chartType: 'month',
    x: 0,
    y: 0,
  });

  const formattedDate = computed(() => {
    if (!props.date) return '';

    if (props.chartType === 'day') {
      // Formato: "26 Noviembre 2025"
      const dateMoment = moment(props.date, 'YYYY-MM-DD');
      return dateMoment.format('DD [de] MMMM YYYY');
    } else {
      // Formato: "Noviembre 2025"
      const dateMoment = moment(props.date, 'YYYY-MM');
      return dateMoment.format('MMMM YYYY');
    }
  });

  const popupStyle = computed(() => {
    // Calcular posición para que aparezca al lado del punto
    // Intentar colocar a la derecha primero, si no hay espacio, a la izquierda
    const popupWidth = 320; // min-width del popup
    const popupHeight = 200; // altura estimada del popup
    const offset = 10; // offset desde el punto

    let left = props.x + offset;
    let top = props.y - popupHeight / 2;

    // Verificar si hay espacio a la derecha
    if (left + popupWidth > window.innerWidth) {
      // Colocar a la izquierda del punto
      left = props.x - popupWidth - offset;
    }

    // Verificar si se sale por arriba
    if (top < 0) {
      top = props.y + offset;
    }

    // Verificar si se sale por abajo
    if (top + popupHeight > window.innerHeight) {
      top = window.innerHeight - popupHeight - 10;
    }

    return {
      left: `${left}px`,
      top: `${top}px`,
    };
  });

  const getStatusClass = (stateGroup: string): string => {
    const statusMap: Record<string, string> = {
      agotado: 'status-soldout',
      minima: 'status-minima',
      disponible: 'status-available',
    };
    return statusMap[stateGroup] || '';
  };

  const getStatusLabel = (stateGroup: string): string => {
    const labelMap: Record<string, string> = {
      agotado: 'Agotado',
      minima: 'Mínima',
      disponible: 'Disponible',
    };
    return labelMap[stateGroup] || stateGroup;
  };
</script>

<style lang="scss" scoped>
  .chart-point-popup {
    position: fixed;
    background: #ffffff;
    border: 1px solid #e5e5e5;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    min-width: 400px;
    max-width: 600px;
    max-height: 500px;
    z-index: 1000;
  }

  .popup-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    border-bottom: 1px solid #e5e5e5;
  }

  .popup-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .popup-date {
    font-size: 14px;
    font-weight: 600;
    color: #2f353a;
  }

  .popup-category {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
    background: #f9f9f9;
    color: #7e8285;
    width: fit-content;
    min-width: 80px;
    text-align: center;
  }

  .popup-header-right {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #2f353a;
  }

  .popup-icon {
    font-size: 16px;
  }

  .popup-total {
    font-weight: 500;
  }

  .popup-body {
    max-height: 300px;
    overflow-y: auto;
    padding: 8px 0;
  }

  .popup-hotel-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 16px;
    border-bottom: 1px solid #f5f5f5;

    &:last-child {
      border-bottom: none;
    }
  }

  .hotel-name {
    font-size: 13px;
    color: #2f353a;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    max-width: 200px;
  }

  .hotel-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    min-width: 78px;
    text-align: center;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 4px;
  }

  .badge-icon {
    width: 12px;
    height: 12px;
    flex-shrink: 0;
  }

  .status-soldout {
    background-color: #ffe1e1;
    color: #bd0d12;
  }

  .status-minima {
    background-color: #fffbd9;
    color: #975f04;
  }

  .status-available {
    background-color: #dfffe9;
    color: #246337;

    .badge-icon {
      :deep(path) {
        fill: currentColor;
      }
    }
  }

  // Media query para pantallas menores a 1280px
  @media (max-width: 1280px) {
    .chart-point-popup {
      min-width: 350px;
      max-width: 500px;
    }

    .popup-header {
      padding: 10px 12px;
    }

    .popup-date {
      font-size: 13px;
    }

    .popup-category {
      font-size: 11px;
      padding: 3px 6px;
      min-width: 70px;
    }

    .popup-header-right {
      font-size: 11px;
    }

    .popup-body {
      max-height: 280px;
      padding: 6px 0;
    }

    .popup-hotel-item {
      padding: 6px 12px;
    }

    .hotel-name {
      font-size: 12px;
      max-width: 180px;
    }

    .hotel-status {
      font-size: 10px;
      padding: 3px 6px;
      min-width: 70px;
    }

    .badge-icon {
      width: 11px;
      height: 11px;
    }
  }
</style>
