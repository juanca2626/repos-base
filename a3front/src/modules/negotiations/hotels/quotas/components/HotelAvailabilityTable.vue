<template>
  <a-row :gutter="16" class="margin-bottom-20">
    <a-col :span="24">
      <!-- Header estático -->
      <div class="table-static-header" v-if="!isLoadingHotelsRooms && pagination.total !== 0">
        <div class="header-cell header-connection">
          <button class="header-with-sort" type="button" @click="toggleConnectionSortIcon">
            <span>Conexión</span>
            <IconArrowDownShortWide v-if="connectionSortIcon === 'short-wide'" class="sort-icon" />
            <IconArrowDownWideShort v-else class="sort-icon" />
          </button>
        </div>
        <div class="header-cell header-preferente">
          <button class="header-with-sort" type="button" @click="togglePreferenteSortIcon">
            <span>Preferente</span>
            <IconArrowDownShortWide v-if="preferenteSortIcon === 'short-wide'" class="sort-icon" />
            <IconArrowDownWideShort v-else class="sort-icon" />
          </button>
        </div>
        <div class="header-cell header-hotel-name">Nombre del Hotel</div>
        <div class="header-cell header-category">Categoría</div>
        <div class="header-cell header-chain">Cadena</div>
        <div class="header-cell header-status">
          <button class="header-with-sort" type="button" @click="toggleStatusSortIcon">
            <span>Estado</span>
            <IconArrowDownShortWide v-if="statusSortIcon === 'short-wide'" class="sort-icon" />
            <IconArrowDownWideShort v-else class="sort-icon" />
          </button>
        </div>
        <div class="header-cell header-release">Release</div>
        <div class="header-cell header-quota">Cupos</div>
        <div class="header-cell header-actions">Acciones</div>
      </div>

      <div class="hotel-availability-collapse">
        <a-empty
          v-if="tableData.length === 0 && !isLoadingHotelsRooms"
          description="No hay datos disponibles. Presiona 'Buscar' para cargar los hoteles."
          class="empty-state"
        />
        <a-collapse
          v-else
          v-model:activeKey="activeKeys"
          :bordered="false"
          :expand-icon="() => null"
          class="hotel-collapse"
        >
          <a-collapse-panel
            v-for="hotel in tableData"
            :key="hotel.id"
            :class="['hotel-panel', { 'has-error': hotel.hasError }]"
          >
            <template #header>
              <div class="hotel-header-row" @click.stop="toggleCollapse(hotel.id)">
                <div class="hotel-cell hotel-connection">
                  <div class="connection-icon-wrapper">
                    <div :class="['connection-icon', `connection-${hotel.connection}`]">
                      <IconHyperguest v-if="hotel.connection === 'hyperguest'" />
                      <IconAurora v-else-if="hotel.connection === 'aurora'" />
                      <IconBoth v-else />
                    </div>
                    <div v-if="hotel.hasError" class="error-overlay">
                      <span>E</span>
                    </div>
                  </div>
                </div>
                <div class="hotel-cell hotel-preferente">
                  <IconFlag v-if="hotel.preferente" />
                </div>
                <div class="hotel-cell hotel-hotel-name">
                  <a-tooltip
                    :title="hotel.hotelName"
                    v-if="hotel.hotelName && hotel.hotelName.length > 16"
                  >
                    <span>{{ truncateHotelName(hotel.hotelName, 16) }}</span>
                  </a-tooltip>
                  <span v-else>{{ hotel.hotelName }}</span>
                </div>
                <div class="hotel-cell hotel-category">
                  <a-tag class="category-tag">{{ hotel.category }}</a-tag>
                </div>
                <div class="hotel-cell hotel-chain">{{ hotel.chain }}</div>
                <div class="hotel-cell hotel-status">
                  <a-tag
                    :class="['status-tag', `status-${getStatusClass(hotel.status)}`]"
                    :style="{
                      backgroundColor: getStatusConfig(hotel.status).backgroundColor,
                      color: getStatusConfig(hotel.status).textColor,
                    }"
                  >
                    <span class="status-icon-wrapper">
                      <IconCheck
                        v-if="getStatusConfig(hotel.status).icon === 'check'"
                        class="status-icon"
                      />
                      <font-awesome-icon
                        v-else-if="getStatusConfig(hotel.status).icon === 'circle-minus'"
                        :icon="['fas', 'circle-minus']"
                        class="status-icon"
                      />
                      <font-awesome-icon
                        v-else-if="getStatusConfig(hotel.status).icon === 'warning'"
                        :icon="['fas', 'triangle-exclamation']"
                        class="status-icon"
                      />
                    </span>
                    {{ hotel.status_porcent }}
                  </a-tag>
                </div>
                <div class="hotel-cell hotel-release">
                  <!-- <a-tag class="release-tag">{{ hotel.release }}</a-tag> -->
                  <span class="text-center">-</span>
                </div>
                <div class="hotel-cell hotel-quota">{{ hotel.quota }}</div>
                <div class="hotel-cell hotel-actions" @click.stop>
                  <div class="action-buttons">
                    <a-button
                      type="text"
                      size="small"
                      @click.stop="openHotelInventories(hotel.id, hotel.connection)"
                    >
                      <IconArrowUpRightFromSquare />
                    </a-button>
                    <a-button type="text" size="small" @click.stop="toggleCollapse(hotel.id)">
                      <IconArrowDown v-if="!activeKeys.includes(String(hotel.id))" />
                      <IconArrowUp v-else />
                    </a-button>
                  </div>
                </div>
              </div>
            </template>
            <div class="hotel-calendar-view">
              <div class="calendar-header">
                <div class="period-picker">
                  <button
                    v-if="getAvailablePeriods(hotel.id).length > 1"
                    class="period-arrow period-arrow-left"
                    @click="previousPeriod(hotel.id)"
                    :disabled="getPeriodIndex(hotel.id) === 0"
                  >
                    <font-awesome-icon :icon="['fas', 'chevron-left']" />
                  </button>
                  <div class="period-display">{{ getCurrentMonth(hotel.id) }}</div>
                  <button
                    v-if="getAvailablePeriods(hotel.id).length > 1"
                    class="period-arrow period-arrow-right"
                    @click="nextPeriod(hotel.id)"
                    :disabled="
                      getPeriodIndex(hotel.id) === getAvailablePeriods(hotel.id).length - 1
                    "
                  >
                    <font-awesome-icon :icon="['fas', 'chevron-right']" />
                  </button>
                </div>
              </div>
              <div class="calendar-table-wrapper">
                <div class="calendar-table">
                  <div class="calendar-sidebar">
                    <div class="sidebar-header">
                      <div class="sidebar-col sidebar-col-header">Tipo de habitación</div>
                      <div class="sidebar-col sidebar-col-header">Ocupabilidad</div>
                      <div class="sidebar-col sidebar-col-header">Tarifa</div>
                    </div>
                    <div v-for="room in hotel.rooms" :key="room.id" class="sidebar-row">
                      <div class="sidebar-col">
                        <div
                          class="sidebar-col-content"
                          style="display: flex; align-items: center; gap: 4px"
                        >
                          <IconHyperguest
                            v-if="hotel.connection === 'both' && room.channel_mark === 'hyperguest'"
                          />
                          <IconAurora
                            v-else-if="
                              hotel.connection === 'both' && room.channel_mark === 'aurora'
                            "
                          />
                          <div
                            v-if="getRoomIndicatorColor(room.availability, room.lockedDays)"
                            class="room-indicator-line"
                            :style="{
                              backgroundColor: getRoomIndicatorColor(
                                room.availability,
                                room.lockedDays
                              )!,
                            }"
                          ></div>
                          <a-tooltip
                            :title="room.type"
                            v-if="
                              room.type &&
                              room.type.length >
                                (getRoomIndicatorColor(room.availability, room.lockedDays)
                                  ? 11
                                  : 13)
                            "
                          >
                            <span class="text-truncate">{{
                              truncateText(
                                room.type,
                                getRoomIndicatorColor(room.availability, room.lockedDays) ? 11 : 13
                              )
                            }}</span>
                          </a-tooltip>
                          <span v-else>{{ room.type }}</span>
                        </div>
                      </div>
                      <div class="sidebar-col text-truncate">{{ room.occupancy }}</div>
                      <div class="sidebar-col">
                        <div style="display: flex; align-items: center; gap: 8px">
                          <a-tooltip :title="room.rate" v-if="room.rate && room.rate.length > 10">
                            <span class="text-truncate">{{ truncateText(room.rate, 10) }}</span>
                          </a-tooltip>
                          <span v-else>{{ room.rate }}</span>
                          <span
                            v-if="formatPriceWithValidation(room.price, room.rates_plans_type_id)"
                            style="color: #2f353a; font-weight: 700"
                          >
                            ${{ formatPriceWithValidation(room.price, room.rates_plans_type_id) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="calendar-dates">
                    <div class="dates-header">
                      <div
                        v-for="day in getMonthDays(hotel.id)"
                        :key="day"
                        class="date-header-cell"
                      >
                        {{ formatDay(day) }}
                      </div>
                    </div>
                    <div v-for="room in hotel.rooms" :key="room.id" class="dates-row">
                      <div
                        v-for="day in getMonthDays(hotel.id)"
                        :key="day"
                        :class="[
                          'date-cell',
                          { highlighted: room.highlightedDays?.includes(parseInt(formatDay(day))) },
                        ]"
                      >
                        <span
                          :class="[
                            'date-cell-value',
                            {
                              'date-cell-blocked': isDayBlocked(room.blockedDays, day),
                            },
                            {
                              'date-cell-zero':
                                !isDayBlocked(room.blockedDays, day) &&
                                getAvailabilityForDay(room.availability, day) === 0,
                            },
                            {
                              'date-cell-low':
                                !isDayBlocked(room.blockedDays, day) &&
                                (getAvailabilityForDay(room.availability, day) === 1 ||
                                  getAvailabilityForDay(room.availability, day) === 2),
                            },
                          ]"
                        >
                          {{ getAvailabilityForDay(room.availability, day) }}
                        </span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </a-collapse-panel>
        </a-collapse>
      </div>

      <!-- Paginación -->
      <div class="pagination-wrapper" v-if="!isLoadingHotelsRooms && pagination.total !== 0">
        <a-pagination
          :current="pagination.current"
          :page-size="pagination.pageSize"
          :total="pagination.total"
          :show-size-changer="pagination.showSizeChanger"
          :show-total="pagination.showTotal"
          :disabled="isLoadingHotelsRooms || pagination.total === 0"
          @change="handlePaginationChange"
          @showSizeChange="handlePaginationChange"
        />
      </div>
    </a-col>
  </a-row>
</template>

<script setup lang="ts">
  import moment from 'moment';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { useHotelAvailabilityTable } from '@/modules/negotiations/hotels/quotas/composables/useHotelAvailabilityTable';
  import IconHyperguest from '@/modules/negotiations/hotels/quotas/icons/icon-hyperguest.vue';
  import IconAurora from '@/modules/negotiations/hotels/quotas/icons/icon-aurora.vue';
  import IconBoth from '@/modules/negotiations/hotels/quotas/icons/icon-both.vue';
  import IconArrowDownShortWide from '@/modules/negotiations/hotels/quotas/icons/icon-arrow-down-short-wide.vue';
  import IconArrowDownWideShort from '@/modules/negotiations/hotels/quotas/icons/icon-arrow-down-wide-short.vue';
  import IconFlag from '@/modules/negotiations/hotels/quotas/icons/icon-flag.vue';
  import IconCheck from '@/modules/negotiations/hotels/quotas/icons/icon-check.vue';
  import IconArrowUpRightFromSquare from '@/modules/negotiations/hotels/quotas/icons/icon-arrow-up-right-from-square.vue';
  import IconArrowUp from '@/modules/negotiations/hotels/quotas/icons/icon-arrow-up.vue';
  import IconArrowDown from '@/modules/negotiations/hotels/quotas/icons/icon-arrow-down.vue';

  // Usar el composable específico de la tabla
  const {
    connectionSortIcon,
    preferenteSortIcon,
    statusSortIcon,
    activeKeys,
    getAvailablePeriods,
    pagination,
    tableData,
    handlePaginationChange,
    toggleConnectionSortIcon,
    togglePreferenteSortIcon,
    toggleStatusSortIcon,
    toggleCollapse,
    getStatusClass,
    getStatusConfig,
    getAvailabilityForDay,
    getRoomIndicatorColor,
    getCurrentMonth,
    getMonthDays,
    getPeriodIndex,
    previousPeriod,
    nextPeriod,
    isLoadingHotelsRooms,
  } = useHotelAvailabilityTable();

  // Función para truncar el nombre del hotel
  const truncateHotelName = (name: string, maxLength: number): string => {
    if (!name) return '';
    if (name.length <= maxLength) return name;
    return name.substring(0, maxLength) + '...';
  };

  // Función para formatear el precio con validación de rates_plans_type_id
  const formatPriceWithValidation = (
    price: number | string | null,
    ratesPlansTypeId?: number
  ): string => {
    // Si rates_plans_type_id es 3, no mostrar el precio
    if (ratesPlansTypeId === 3) return '';

    // Si no hay precio o es null, retornar cadena vacía
    if (!price || price === null) return '';

    // Convertir a número si es string
    const numPrice = typeof price === 'string' ? parseFloat(price) : price;

    // Si no es un número válido, retornar cadena vacía
    if (isNaN(numPrice)) return '';

    // Si el precio es 0, retornar "0.00"
    if (numPrice === 0) return '0.00';

    // Formatear con 2 decimales y separador de miles
    return new Intl.NumberFormat('es-PE', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2,
    }).format(numPrice);
  };

  // Función para abrir la página de inventarios del hotel en una nueva ventana
  const openHotelInventories = (hotelId: number, connection: string) => {
    const backendUrl = import.meta.env.VITE_APP_BACKEND_URL;
    if (!backendUrl) {
      console.error('VITE_APP_BACKEND_URL no está definida en las variables de entorno');
      return;
    }
    let url = '';
    if (connection === 'hyperguest') {
      url = `${backendUrl}/#/hotels/${hotelId}/manage_hotel/inventories/general/channels/6`;
    } else {
      url = `${backendUrl}/#/hotels/${hotelId}/manage_hotel/inventories/free_sale`;
    }
    window.open(url, '_blank');
  };

  // Función para truncar texto genérico
  const truncateText = (text: string, maxLength: number): string => {
    if (!text) return '';
    if (text.length <= maxLength) return text;
    return text.substring(0, maxLength) + '...';
  };

  // Función para formatear el día desde una fecha en formato YYYY-MM-DD
  const formatDay = (dateStr: string): string => {
    return moment(dateStr).format('DD');
  };

  // Función para verificar si un día está bloqueado
  const isDayBlocked = (
    blockedDays: Record<string, boolean> | undefined,
    dayStr: string
  ): boolean => {
    if (!blockedDays || !dayStr) {
      return false;
    }

    // dayStr viene en formato 'YYYY-MM-DD' de getMonthDays
    // Asegurarse de que la fecha esté en el formato correcto
    const dateKey = moment(dayStr).format('YYYY-MM-DD');

    // Buscar en blockedDays usando la clave formateada
    const isBlocked = blockedDays[dateKey];

    // Si no se encuentra, intentar buscar con la fecha original
    if (isBlocked === undefined && blockedDays[dayStr] !== undefined) {
      return blockedDays[dayStr] === true;
    }

    return isBlocked === true;
  };
</script>

<style lang="scss" scoped>
  @import '@/scss/_variables.scss';

  .margin-bottom-20 {
    margin-bottom: 20px;
  }

  .table-static-header {
    display: flex;
    background: transparent;
    border-radius: 8px 8px 0 0;
    padding: 12px 16px;
    font-weight: 400;
    font-size: 14px;
    color: #7e8285;
    margin-bottom: 8px;

    .header-cell {
      display: flex;
      align-items: center;
      padding: 0 8px;
    }

    .header-connection {
      width: 8%;
      min-width: 80px;
    }

    .header-preferente {
      width: 8%;
      min-width: 80px;
    }

    .header-hotel-name {
      width: 20%;
      min-width: 150px;
    }

    .header-category {
      width: 12%;
      min-width: 120px;
    }

    .header-chain {
      width: 15%;
      min-width: 130px;
    }

    .header-status {
      width: 10%;
      min-width: 100px;
    }

    .header-release {
      width: 10%;
      min-width: 100px;
    }

    .header-quota {
      width: 10%;
      min-width: 100px;
    }

    .header-actions {
      width: 7%;
      min-width: 80px;
      justify-content: center;
    }

    .header-with-filter {
      display: flex;
      align-items: center;
      gap: 8px;
      width: 100%;

      .filter-icon {
        cursor: pointer;
        color: rgba(0, 0, 0, 0.45);
        font-size: 12px;

        &:hover {
          color: #4096ff;
        }
      }
    }

    .header-with-sort {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      background: transparent;
      border: none;
      cursor: pointer;
      font-weight: 400;
      // font-size: 14px;
      color: #7e8285;
      padding: 0;
      margin: 0;

      .sort-icon {
        width: 14px;
        height: 14px;
      }
    }
  }

  .hotel-availability-collapse {
    .hotel-collapse {
      :deep(.ant-collapse-item) {
        border: 1px solid #d9d9d9;
        border-radius: 8px;
        margin-bottom: 12px;
        background: #fff;
        overflow: hidden;
      }

      :deep(.ant-collapse-header) {
        padding: 12px 16px !important;
        background: #fff;
        border-radius: 8px;
      }

      :deep(.ant-collapse-item-active .ant-collapse-header) {
        border-radius: 8px 8px 0 0;
      }

      :deep(.ant-collapse-expand-icon) {
        display: none !important;
      }

      :deep(.ant-collapse-arrow) {
        display: none !important;
      }

      :deep(.ant-collapse-content) {
        border-top: 1px solid #d9d9d9;
      }

      :deep(.ant-collapse-content-box) {
        padding: 0 !important;
      }
    }

    .hotel-panel {
      &.has-error {
        :deep(.ant-collapse-header) {
          border-left: 3px solid #f44336;
        }
      }
    }

    .hotel-header-row {
      display: flex;
      align-items: center;
      width: 100%;
      padding: 0 8px;
    }

    .hotel-cell {
      display: flex;
      align-items: center;
      padding: 0 8px;
      font-size: 14px;
      color: #333;
    }

    .hotel-connection {
      width: 8%;
      min-width: 80px;
      // justify-content: left;
    }

    .hotel-preferente {
      width: 8%;
      min-width: 80px;
      // justify-content: center;
    }

    .hotel-hotel-name {
      width: 20%;
      min-width: 150px;
      font-weight: 600;
      font-size: 20px;
      color: #2f353a;
      line-height: 28px;
    }

    .hotel-category {
      width: 12%;
      min-width: 120px;
    }

    .hotel-chain {
      width: 15%;
      min-width: 130px;
    }

    .hotel-status {
      width: 10%;
      min-width: 100px;
    }

    .hotel-release {
      width: 10%;
      min-width: 100px;
    }

    .hotel-quota {
      width: 10%;
      min-width: 100px;
    }

    .hotel-actions {
      width: 7%;
      min-width: 80px;
      justify-content: center;
    }

    .connection-icon-wrapper {
      position: relative;
      display: inline-block;
    }

    .connection-icon {
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      position: relative;
    }

    .error-overlay {
      position: absolute;
      top: -4px;
      right: -4px;
      width: 16px;
      height: 16px;
      background-color: #f44336;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      font-size: 10px;
      font-weight: 600;
      border: 2px solid white;
    }

    .preferente-icon {
      color: #999;
      font-size: 16px;
    }

    .hotel-name,
    .hotel-category,
    .hotel-chain {
      font-size: 14px;
      color: #333;
    }

    .status-tag {
      font-size: 12px;
      border: 1px solid transparent;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      width: 100%;
      padding: 4px 8px;

      &.status-disponible {
        border-color: #dfffe9;
      }

      &.status-agotado {
        border-color: #ffe1e1;
      }

      &.status-minima {
        border: none;
      }

      &.status-default {
        background-color: #f5f5f5;
        color: #333;
      }

      .status-icon-wrapper {
        display: flex;
        align-items: center;
        justify-content: center;
      }

      .status-icon {
        width: 14px;
        height: 14px;
        flex-shrink: 0;

        :deep(path) {
          fill: currentColor;
        }
      }
    }

    .category-tag {
      background: #f9f9f9 !important;
      border: none;
      color: #7e8285 !important;
      font-size: 12px;
      font-weight: 600;
    }

    .release-tag {
      background: #ffffff !important;
      border: 0.5px solid #575b5f !important;
      color: #7e8285 !important;
      font-size: 12px;
    }

    .action-buttons {
      display: flex;
      gap: 8px;
    }

    .hotel-calendar-view {
      padding: 16px;
      background: #fff;
    }

    .calendar-header {
      margin-bottom: 16px;
      display: flex;
      justify-content: flex-end;
      align-items: center;
    }

    .period-picker {
      width: 60%;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f9f9f9;
      border-radius: 4px;
      padding: 2px 6px;
      gap: 16px;
    }

    .period-display {
      flex: 1;
      text-align: center;
      color: #babcbd;
      font-size: 14px;
      font-weight: 400;
    }

    .period-arrow {
      background-color: #ffffff;
      border: 1px solid #e0e0e0;
      border-radius: 4px;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: #babcbd;
      transition: all 0.2s;
      padding: 0;

      &:hover:not(:disabled) {
        background-color: #f5f5f5;
        border-color: #babcbd;
      }

      &:disabled {
        opacity: 0.5;
        cursor: not-allowed;
      }

      svg {
        width: 12px;
        height: 12px;
      }
    }

    .calendar-table-wrapper {
      overflow-x: auto;
    }

    .calendar-table {
      display: flex;
      position: relative;
      min-width: 100%;
    }

    .calendar-sidebar {
      width: 40%;
      background: transparent;
      display: flex;
      flex-direction: column;
    }

    .sidebar-header,
    .sidebar-row {
      display: flex;
      min-height: 25px;
    }

    .sidebar-header {
      background: transparent;
      font-weight: 600;
      display: flex !important;
      height: 30px;
    }

    .sidebar-row {
      background: #fff;

      &:last-child {
        border-bottom: none;
      }
    }

    .sidebar-col {
      // flex: 1;
      font-size: 12px;
      color: #2f353a;
      display: flex;
      align-items: center;
      height: 30px;
      width: 30%;
    }

    .sidebar-col-header {
      color: #babcbd !important;
      font-size: 10px !important;
      font-weight: 400 !important;
    }

    .room-indicator-line {
      border-radius: 4px;
      width: 6px;
      height: 18px;
      flex-shrink: 0;
      margin-right: 4px;
      margin-left: 4px;
    }

    .calendar-dates {
      flex: 1;
      overflow-x: auto;
      display: flex;
      flex-direction: column;
    }

    .dates-header {
      // display: grid;
      // grid-auto-flow: column;
      // grid-auto-columns: 1fr;
      display: flex;
      flex-direction: row;
      background: transparent;
      height: 30px;
    }

    .date-header-cell {
      padding: auto;
      text-align: center;
      font-size: 12px;
      font-weight: 600;
      color: #babcbd;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 8px 0;
      width: 32px;
      padding: 0px 12px;
    }

    .dates-row {
      // display: grid;
      // grid-auto-flow: column;
      // grid-auto-columns: 1fr;
      display: flex;
      flex-direction: row;
      // gap: 4px;
    }

    .date-cell {
      padding: 3px 4px;
      text-align: center;
      // display: flex;
      // align-items: center;
      // justify-content: center;
      height: 30px;
    }

    .date-cell-value {
      display: flex;
      justify-content: center;
      align-items: center;
      font-size: 12px;
      font-weight: 600;
      color: #7e8285;
      background-color: #f9f9f9;
      padding: 4px 0px;
      width: 24px;
      border-radius: 4px;

      &.date-cell-blocked {
        background-color: #dcdcdc;
        color: #babcbd;
      }

      &.date-cell-zero {
        background-color: #a71216;
        color: #ffffff;
      }

      &.date-cell-low {
        background-color: #e4b804;
        color: #ffffff;
      }
    }

    .empty-state {
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 300px;
      width: 100%;
    }

    .pagination-wrapper {
      margin-top: 20px;
      width: 100%;
      display: flex !important;
      justify-content: center !important;
      align-items: center !important;

      :deep(.ant-pagination) {
        display: flex !important;
        justify-content: center !important;
        align-items: center !important;
        flex-wrap: wrap;
        margin: 0 auto !important;
      }

      :deep(.ant-pagination-total-text) {
        margin-right: 16px;
      }

      :deep(.ant-pagination-options) {
        margin-left: 16px;
      }
    }
    .text-center {
      width: 60%;
      text-align: center;
    }
  }

  // Media query para pantallas menores a 1280px
  @media (max-width: 1290px) {
    .table-static-header {
      padding: 10px 12px;
      font-size: 11px;

      .header-cell {
        padding: 0 4px;
      }

      // Tamaños fijos en porcentaje para que no se muevan
      .header-connection {
        width: 8%;
        min-width: 0;
        flex: 0 0 8%;
      }

      .header-preferente {
        width: 8%;
        min-width: 0;
        flex: 0 0 8%;
      }

      .header-hotel-name {
        width: 20%;
        min-width: 0;
        flex: 0 0 20%;
      }

      .header-category {
        width: 12%;
        min-width: 0;
        flex: 0 0 12%;
      }

      .header-chain {
        width: 15%;
        min-width: 0;
        flex: 0 0 15%;
      }

      .header-status {
        width: 10%;
        min-width: 0;
        flex: 0 0 10%;
      }

      .header-release {
        width: 10%;
        min-width: 0;
        flex: 0 0 10%;
      }

      .header-quota {
        width: 10%;
        min-width: 0;
        flex: 0 0 10%;
      }

      .header-actions {
        width: 7%;
        min-width: 0;
        flex: 0 0 7%;
      }

      .header-with-sort {
        .sort-icon {
          width: 12px;
          height: 12px;
        }
      }
    }

    .hotel-header-row {
      padding: 0 4px;
    }

    .hotel-cell {
      padding: 0 4px;
      font-size: 12px;

      // Tamaños fijos en porcentaje para que coincidan con el header
      &.hotel-connection {
        width: 8%;
        min-width: 0;
        flex: 0 0 8%;
        font-size: 12px;

        .connection-icon {
          width: 20px;
          height: 20px;

          :deep(svg) {
            width: 20px;
            height: 20px;
          }
        }
      }

      &.hotel-preferente {
        width: 8%;
        min-width: 0;
        flex: 0 0 8%;
        font-size: 12px;

        :deep(svg) {
          width: 20px;
          height: 20px;
        }
      }

      &.hotel-hotel-name {
        width: 20%;
        min-width: 0;
        flex: 0 0 20%;
        font-size: 16px; // Más pequeño que el original de 20px
      }

      &.hotel-category {
        width: 12%;
        min-width: 0;
        flex: 0 0 12%;
        font-size: 12px;
      }

      &.hotel-chain {
        width: 15%;
        min-width: 0;
        flex: 0 0 15%;
        font-size: 12px;
      }

      &.hotel-status {
        width: 10%;
        min-width: 0;
        flex: 0 0 10%;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
      }

      &.hotel-release {
        width: 10%;
        min-width: 0;
        flex: 0 0 10%;
      }

      &.hotel-quota {
        width: 10%;
        min-width: 0;
        flex: 0 0 10%;
        font-size: 12px;
      }

      &.hotel-actions {
        width: 7%;
        min-width: 0;
        flex: 0 0 7%;
      }
    }

    .category-tag {
      font-size: 11px !important;
    }

    .status-tag {
      font-size: 11px;
      padding: 4px 8px;
      min-height: 24px;
      display: inline-flex;
      align-items: center;
      justify-content: center;

      .status-icon {
        width: 12px;
        height: 12px;
      }
    }

    .calendar-sidebar {
      width: 35%;
    }

    .sidebar-col {
      font-size: 11px;
      padding: 6px 4px;
    }

    .date-header-cell {
      font-size: 11px;
      padding: 6px 2px;
    }

    .date-cell-value {
      font-size: 11px;
      padding: 3px 0px;
      width: 22px;
    }
  }

  .text-truncate {
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
  }
</style>
