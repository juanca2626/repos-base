<template>
  <div v-if="visible" class="calendar-day-popup" :style="popupStyle">
    <div class="popup-header">
      <div class="popup-header-left">
        <span class="popup-date">{{ formattedDate }}</span>
      </div>
      <div class="popup-header-right">
        <span class="popup-icon">
          <IconHotel />
        </span>
        <span class="popup-total">{{ hotelDetails.length }} hoteles</span>
      </div>
    </div>
    <div class="popup-body">
      <div v-if="hotelDetails.length === 0" class="empty-state">
        No hay hoteles disponibles para esta fecha
      </div>
      <div v-for="(hotel, index) in hotelDetails" :key="index" class="popup-hotel-item">
        <span class="hotel-name" :title="hotel.hotel_name">{{ hotel.hotel_name }}</span>
        <div class="hotel-info">
          <span class="hotel-status-badge" :class="getStateClass(hotel.state_group)">
            <font-awesome-icon
              v-if="hotel.state_group === 'agotado'"
              :icon="['fas', 'circle-exclamation']"
              class="badge-icon"
            />
            <IconCheck v-else-if="hotel.state_group === 'disponible'" class="badge-icon" />
            <font-awesome-icon
              v-else-if="hotel.state_group === 'bloqueado'"
              :icon="['fas', 'circle-xmark']"
              class="badge-icon"
            />
            {{ getStateLabel(hotel.state_group, hotel.sold_out_percent, hotel.available_percent) }}
          </span>
          <!-- <span class="hotel-rooms">
              <span v-if="hotel.state_group === 'disponible'">
                {{ hotel.available_rooms }} disponibles
              </span>
              <span v-else-if="hotel.state_group === 'agotado'">
                {{ hotel.sold_out_rooms }} agotados
              </span>
              <span v-else-if="hotel.state_group === 'bloqueado'">
                {{ hotel.blocked_rooms }} bloqueados
              </span>
            </span> -->
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed, onMounted, onUnmounted, watch } from 'vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import moment from 'moment';
  import type { CalendarDetailHotel } from '@/modules/negotiations/hotels/quotas/interfaces/quotas.interface';
  import IconHotel from '../icons/icon-hotel.vue';
  import IconCheck from '../icons/icon-check.vue';

  interface Props {
    visible: boolean;
    date: string;
    hotelDetails: CalendarDetailHotel[];
    totals?: {
      available_hotels: number;
      sold_out_hotels: number;
      blocked_hotels: number;
      total_hotels: number;
    };
    x: number;
    y: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    visible: false,
    date: '',
    hotelDetails: () => [],
    totals: undefined,
    x: 0,
    y: 0,
  });

  const emit = defineEmits<{
    'update:visible': [value: boolean];
    close: [];
  }>();

  const formattedDate = computed(() => {
    if (!props.date) return '';
    const dateMoment = moment(props.date, 'YYYY-MM-DD');
    return dateMoment.format('DD [de] MMMM YYYY');
  });

  const popupStyle = computed(() => {
    // Calcular posición para que aparezca al lado del día clickeado
    const popupWidth = 400; // min-width del popup
    const popupMaxHeight = 500; // max-height del popup
    const offset = 10; // offset desde el punto

    let left = props.x + offset;
    // Inicialmente centrar verticalmente
    let top = props.y - popupMaxHeight / 2;

    // Verificar si hay espacio a la derecha
    if (left + popupWidth > window.innerWidth) {
      // Colocar a la izquierda del punto
      left = props.x - popupWidth - offset;
    }

    // Asegurar que no se salga por la izquierda
    if (left < 0) {
      left = 10;
    }

    // Verificar si se sale por arriba
    if (top < 0) {
      // Si se sale por arriba, alinear desde arriba con un pequeño margen
      top = offset;
    }

    // Verificar si se sale por abajo
    const bottomPosition = top + popupMaxHeight;
    if (bottomPosition > window.innerHeight) {
      // Si se sale por abajo, ajustar para que quepa completamente
      top = window.innerHeight - popupMaxHeight - offset;

      // Si aún se sale por arriba después del ajuste, alinear desde arriba
      if (top < 0) {
        top = offset;
      }
    }

    return {
      left: `${left}px`,
      top: `${top}px`,
    };
  });

  const getStateClass = (state: string): string => {
    const statusMap: Record<string, string> = {
      agotado: 'status-agotado',
      bloqueado: 'status-bloqueado',
      disponible: 'status-disponible',
    };
    return statusMap[state] || '';
  };

  const getStateLabel = (
    state: string,
    sold_out_percent: string,
    available_percent: string
  ): string => {
    const soldOut = Number(sold_out_percent); // conversión explícita

    if (!isNaN(soldOut) && soldOut < 100 && state == 'agotado') {
      return `${soldOut} %`;
    }

    if (state == 'disponible') {
      return `${available_percent} %`;
    }

    return '100 %';
  };

  const handleClickOutside = (e: MouseEvent) => {
    const t = e.target as HTMLElement;
    // Cerrar solo si se hace click fuera del popup y fuera de cualquier día del calendario
    if (!t.closest('.calendar-day-popup') && !t.closest('.calendar-day')) {
      emit('update:visible', false);
      emit('close');
    }
  };

  // Agregar listener cuando el popup se muestra
  const watchVisible = () => {
    if (props.visible) {
      // Usar nextTick para asegurar que el DOM esté actualizado
      setTimeout(() => {
        document.addEventListener('click', handleClickOutside);
      }, 0);
    } else {
      document.removeEventListener('click', handleClickOutside);
    }
  };

  onMounted(() => {
    watchVisible();
  });

  onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
  });

  // Watch para agregar/remover listener cuando cambia visible
  watch(() => props.visible, watchVisible);

  // Watch para agregar/remover listener cuando cambia visible
  watch(() => props.visible, watchVisible);
</script>

<style lang="scss" scoped>
  .calendar-day-popup {
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
    padding: 8px 16px;
    border-bottom: 1px solid #babcbd;
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

  .empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #7e8285;
    font-size: 14px;
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

  .hotel-info {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .hotel-status-badge {
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

  .status-agotado {
    background-color: #ffe1e1;
    color: #bd0d12;
  }

  .status-bloqueado {
    background-color: #f5f5f5;
    color: #2f353a;
  }

  .status-disponible {
    background-color: #dfffe9;
    color: #246337;

    .badge-icon {
      :deep(path) {
        fill: currentColor;
      }
    }
  }

  .hotel-rooms {
    font-size: 12px;
    color: #7e8285;
    font-weight: 500;
  }

  // Media query para pantallas menores a 1280px
  @media (max-width: 1280px) {
    .calendar-day-popup {
      min-width: 350px;
      max-width: 500px;
    }

    .popup-header {
      padding: 6px 12px;
    }

    .popup-date {
      font-size: 13px;
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

    .hotel-status-badge {
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
