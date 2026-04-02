<template>
  <div class="calendar-detail-view">
    <!-- Header -->
    <div class="calendar-header">
      <div class="header-left">
        <h1 class="calendar-title" v-if="countryName">Calendario {{ countryName }}</h1>
        <div v-if="yearFrom && yearTo" class="validity-badge">
          Periodo de validez: {{ yearFrom }} - {{ yearTo }}
        </div>
      </div>
      <div class="header-right">
        <a-button
          v-if="!isInitialLoading"
          type="default"
          class="add-holiday-button"
          @click="handleAddHoliday"
        >
          <font-awesome-icon :icon="['fas', 'plus']" />
          Añadir festivo
        </a-button>
      </div>
    </div>

    <!-- Body Content Wrapper -->
    <div class="view-body">
      <!-- City Tabs (only shown when there are ciudad-type holidays) -->
      <div v-if="hasCityHolidays" class="city-tabs-container">
        <button v-if="hasOverflow" class="tab-nav-arrow tab-nav-left" @click="scrollTabs('left')">
          <font-awesome-icon :icon="['fas', 'chevron-left']" />
        </button>
        <div ref="tabsRef" class="city-tabs">
          <button
            v-for="tab in cityTabs"
            :key="tab"
            class="city-tab"
            :class="{ active: activeTab === tab.toLowerCase() }"
            @click="activeTab = tab.toLowerCase()"
          >
            {{ tab }}
          </button>
        </div>
        <button v-if="hasOverflow" class="tab-nav-arrow tab-nav-right">
          <font-awesome-icon :icon="['fas', 'ellipsis']" />
        </button>
        <button v-if="hasOverflow" class="tab-nav-arrow tab-nav-right" @click="scrollTabs('right')">
          <font-awesome-icon :icon="['fas', 'chevron-right']" />
        </button>
      </div>

      <!-- Content -->
      <div class="calendar-content">
        <!-- Sidebar -->
        <div class="sidebar">
          <div class="search-section">
            <a-input v-model:value="searchText" placeholder="Buscar festivo" class="search-input">
              <template #suffix>
                <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="search-icon" />
              </template>
            </a-input>
          </div>

          <div class="holidays-summary">
            <div class="summary-header">
              <a-select
                v-model:value="selectedYear"
                class="year-select-sidebar"
                :options="yearOptions"
              />
              <div class="switch-row">
                <span class="switch-label">Ver deshabilitados</span>
                <a-switch v-model:checked="showDisabled" class="disabled-switch" />
              </div>
            </div>
            <a-spin :spinning="isInitialLoading || isHolidaysLoading">
              <div v-if="holidays.length === 0 && !isInitialLoading" class="summary-empty">
                Agrega días festivos al calendario
              </div>
              <template v-else>
                <!-- Active Holidays -->
                <div v-if="activeHolidays.length > 0" class="activos-label">Activos</div>
                <div class="holiday-cards">
                  <div v-for="holiday in activeHolidays" :key="holiday.id" class="holiday-card">
                    <div class="card-left">
                      <span
                        class="card-dot"
                        :style="{ backgroundColor: getHolidayColor(holiday.holidayType) }"
                      ></span>
                      <div class="card-info">
                        <span class="card-date">{{
                          formatHolidayDate(holiday.date, holiday.dateTo)
                        }}</span>
                        <a-tooltip
                          :title="holiday.name"
                          placement="topLeft"
                          overlay-class-name="holiday-name-tooltip"
                        >
                          <span class="card-name">{{ holiday.name }}</span>
                        </a-tooltip>
                      </div>
                    </div>
                    <div class="card-right">
                      <span
                        v-if="holiday.holidayType === 'ciudad' && holiday.ciudad"
                        class="city-badge"
                      >
                        <svg
                          width="12"
                          height="12"
                          viewBox="0 0 14 15"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                          class="city-icon"
                        >
                          <path
                            d="M12.25 6.25C12.25 10.3333 7 13.8333 7 13.8333C7 13.8333 1.75 10.3333 1.75 6.25C1.75 4.85761 2.30312 3.52226 3.28769 2.53769C4.27226 1.55312 5.60761 1 7 1C8.39239 1 9.72774 1.55312 10.7123 2.53769C11.6969 3.52226 12.25 4.85761 12.25 6.25Z"
                            stroke="#7E8285"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M7 8C7.9665 8 8.75 7.2165 8.75 6.25C8.75 5.2835 7.9665 4.5 7 4.5C6.0335 4.5 5.25 5.2835 5.25 6.25C5.25 7.2165 6.0335 8 7 8Z"
                            stroke="#7E8285"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                        </svg>
                        {{ holiday.ciudad }}
                      </span>
                      <a-dropdown :trigger="['click']">
                        <button class="card-menu">
                          <font-awesome-icon :icon="['fas', 'ellipsis-vertical']" />
                        </button>
                        <template #overlay>
                          <a-menu>
                            <a-menu-item key="modify" @click="handleMenuClick(holiday, 'modify')">
                              Modificar festivo
                            </a-menu-item>
                            <a-menu-item
                              key="deactivate"
                              @click="handleMenuClick(holiday, 'deactivate')"
                            >
                              Desactivar festivo
                            </a-menu-item>
                          </a-menu>
                        </template>
                      </a-dropdown>
                    </div>
                  </div>
                </div>

                <!-- Disabled Holidays -->
                <template v-if="showDisabled && disabledHolidays.length > 0">
                  <div class="desactivados-label">Desactivados</div>
                  <div class="holiday-cards">
                    <div
                      v-for="holiday in disabledHolidays"
                      :key="holiday.id"
                      class="holiday-card disabled"
                    >
                      <div class="card-left">
                        <span
                          class="card-dot"
                          :style="{ backgroundColor: getHolidayColor(holiday.holidayType) }"
                        ></span>
                        <div class="card-info">
                          <span class="card-date">{{
                            formatHolidayDate(holiday.date, holiday.dateTo)
                          }}</span>
                          <a-tooltip
                            :title="holiday.name"
                            placement="topLeft"
                            overlay-class-name="holiday-name-tooltip"
                          >
                            <span class="card-name">{{ holiday.name }}</span>
                          </a-tooltip>
                        </div>
                      </div>
                      <div class="card-right">
                        <span v-if="holiday.ciudad" class="city-badge">
                          <svg
                            width="12"
                            height="12"
                            viewBox="0 0 14 15"
                            fill="none"
                            xmlns="http://www.w3.org/2000/svg"
                            class="city-icon"
                          >
                            <path
                              d="M12.25 6.25C12.25 10.3333 7 13.8333 7 13.8333C7 13.8333 1.75 10.3333 1.75 6.25C1.75 4.85761 2.30312 3.52226 3.28769 2.53769C4.27226 1.55312 5.60761 1 7 1C8.39239 1 9.72774 1.55312 10.7123 2.53769C11.6969 3.52226 12.25 4.85761 12.25 6.25Z"
                              stroke="#7E8285"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                            <path
                              d="M7 8C7.9665 8 8.75 7.2165 8.75 6.25C8.75 5.2835 7.9665 4.5 7 4.5C6.0335 4.5 5.25 5.2835 5.25 6.25C5.25 7.2165 6.0335 8 7 8Z"
                              stroke="#7E8285"
                              stroke-width="2"
                              stroke-linecap="round"
                              stroke-linejoin="round"
                            />
                          </svg>
                          {{ holiday.ciudad }}
                        </span>
                        <a-dropdown :trigger="['click']">
                          <button class="card-menu">
                            <font-awesome-icon :icon="['fas', 'ellipsis-vertical']" />
                          </button>
                          <template #overlay>
                            <a-menu>
                              <a-menu-item key="activate" @click="handleActivateClick(holiday)">
                                Activar festivo
                              </a-menu-item>
                            </a-menu>
                          </template>
                        </a-dropdown>
                      </div>
                    </div>
                  </div>
                </template>
              </template>
            </a-spin>
          </div>
        </div>

        <!-- Main calendar area -->
        <div class="calendar-main">
          <!-- Legend and filter -->
          <div class="calendar-toolbar">
            <div class="legend">
              <span
                class="legend-item"
                style="color: #2f353a; display: flex; align-items: center; gap: 8px"
              >
                <span
                  style="
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background-color: #2f353a;
                    display: inline-block;
                  "
                ></span>
                Festivo general
              </span>
              <span
                class="legend-item"
                style="color: #d80404; display: flex; align-items: center; gap: 8px"
              >
                <span
                  style="
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background-color: #d80404;
                    display: inline-block;
                  "
                ></span>
                Festivo turístico
              </span>
              <span
                class="legend-item"
                style="color: #1284ed; display: flex; align-items: center; gap: 8px"
              >
                <span
                  style="
                    width: 10px;
                    height: 10px;
                    border-radius: 50%;
                    background-color: #1284ed;
                    display: inline-block;
                  "
                ></span>
                Festivo en ciudad
              </span>
            </div>
            <div class="filter-section">
              <span class="filter-label">Ver:</span>
              <a-select
                v-model:value="filterValue"
                class="filter-select"
                placeholder=""
                :options="filterOptions"
              />
            </div>
          </div>

          <!-- Calendar navigation -->
          <div class="calendar-navigation">
            <div class="month-year-selectors">
              <a-select
                v-model:value="selectedMonth"
                class="month-select"
                :options="monthOptions"
              />
            </div>
            <div class="nav-arrows">
              <button class="nav-arrow" @click="previousMonth">
                <font-awesome-icon :icon="['fas', 'chevron-left']" />
              </button>
              <button class="nav-arrow" @click="nextMonth">
                <font-awesome-icon :icon="['fas', 'chevron-right']" />
              </button>
            </div>
          </div>

          <!-- Calendar grid -->
          <div class="calendar-grid">
            <!-- Header row -->
            <div class="calendar-row header-row">
              <div class="calendar-cell header-cell">Dom</div>
              <div class="calendar-cell header-cell">Lun</div>
              <div class="calendar-cell header-cell">Mar</div>
              <div class="calendar-cell header-cell">Mier</div>
              <div class="calendar-cell header-cell">Jue</div>
              <div class="calendar-cell header-cell">Vier</div>
              <div class="calendar-cell header-cell">Sab</div>
            </div>

            <!-- Calendar days -->
            <div v-for="(week, weekIndex) in calendarWeeks" :key="weekIndex" class="calendar-row">
              <div
                v-for="(day, dayIndex) in week"
                :key="dayIndex"
                class="calendar-cell day-cell"
                :class="{
                  'other-month': !day.currentMonth,
                  'has-holiday': day.holidays && day.holidays.length > 0,
                }"
              >
                <span class="day-number">{{ day.day }}</span>
                <div
                  v-if="day.holidays && day.holidays.length > 0"
                  class="day-holidays"
                  :class="{ expanded: isDayExpanded(day.id) }"
                >
                  <div
                    v-for="(event, idx) in isDayExpanded(day.id)
                      ? day.holidays
                      : day.holidays.slice(0, 2)"
                    :key="idx"
                    class="holiday-event"
                    :class="{ 'single-event': day.holidays.length === 1 }"
                    :style="{ borderLeftColor: getHolidayColor(event.holidayType) }"
                  >
                    <span class="event-name">{{ event.name }}</span>
                    <span v-if="event.holidayType === 'ciudad' && event.ciudad" class="event-city">
                      <svg
                        width="10"
                        height="10"
                        viewBox="0 0 14 15"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        class="event-city-icon"
                        style="margin-right: 4px"
                      >
                        <path
                          d="M12.25 6.25C12.25 10.3333 7 13.8333 7 13.8333C7 13.8333 1.75 10.3333 1.75 6.25C1.75 4.85761 2.30312 3.52226 3.28769 2.53769C4.27226 1.55312 5.60761 1 7 1C8.39239 1 9.72774 1.55312 10.7123 2.53769C11.6969 3.52226 12.25 4.85761 12.25 6.25Z"
                          stroke="#7E8285"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                        <path
                          d="M7 8C7.9665 8 8.75 7.2165 8.75 6.25C8.75 5.2835 7.9665 4.5 7 4.5C6.0335 4.5 5.25 5.2835 5.25 6.25C5.25 7.2165 6.0335 8 7 8Z"
                          stroke="#7E8285"
                          stroke-width="2"
                          stroke-linecap="round"
                          stroke-linejoin="round"
                        />
                      </svg>
                      {{ event.ciudad }}
                    </span>
                  </div>
                  <!-- Toggle Button -->
                  <div
                    v-if="day.holidays.length > 2"
                    class="more-events"
                    @click.stop="toggleDayExpand(day.id)"
                  >
                    <template v-if="!isDayExpanded(day.id)">
                      + {{ day.holidays.length - 2 }} evento{{
                        day.holidays.length - 2 > 1 ? 's' : ''
                      }}
                      más
                    </template>
                    <template v-else>
                      <span class="hide-text">Ocultar</span>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Drawer para nuevo festivo -->
    <NewHolidayDrawerComponent
      v-model:open="holidayDrawerOpen"
      :loading="isActionLoading"
      :year-from="yearFrom"
      :year-to="yearTo"
      @submit="handleSubmitNewHoliday"
    />

    <!-- Drawer para desactivar festivo -->
    <DeactivateHolidayDrawer
      v-model:open="deactivateDrawerOpen"
      :holiday="selectedHoliday"
      :logs="holidayLogs"
      :loading="isActionLoading"
      :loading-logs="isLogsLoading"
      destroy-on-close
      @submit="handleDeactivateHoliday"
    />

    <!-- Drawer para modificar festivo -->
    <ModifyHolidayDrawer
      v-model:open="modifyDrawerOpen"
      :holiday="selectedHoliday"
      :logs="holidayLogs"
      :loading="isActionLoading"
      destroy-on-close
      @submit="handleModifyHoliday"
    />

    <!-- Drawer para activar festivo -->
    <ActivateHolidayDrawer
      v-model:open="activateDrawerOpen"
      :holiday="selectedHoliday"
      :logs="holidayLogs"
      :loading="isActionLoading"
      :loading-logs="isLogsLoading"
      destroy-on-close
      @submit="handleActivateHoliday"
    />
  </div>
</template>

<script lang="ts" setup>
  import { ref, computed, onMounted, onUnmounted, watch, nextTick } from 'vue';
  import { useRoute } from 'vue-router';
  import dayjs from 'dayjs';
  import {
    countryCalendarService,
    type HolidayItem,
    type CreateHolidayPayload,
  } from '../services/countryCalendarService';
  import NewHolidayDrawerComponent from './NewHolidayDrawerComponent.vue';
  import DeactivateHolidayDrawer from './DeactivateHolidayDrawer.vue';
  import ModifyHolidayDrawer from './ModifyHolidayDrawer.vue';
  import ActivateHolidayDrawer from './ActivateHolidayDrawer.vue';
  import { notification } from 'ant-design-vue';
  // import type { CountryLocation } from '../services/countryCalendarService'; // Removed

  // Holiday type definition
  // Holiday type definition
  // Holiday type definition
  interface Holiday {
    id: number;
    name: string;
    date: string; // YYYY-MM-DD
    dateTo?: string; // YYYY-MM-DD
    holidayType: 'general' | 'turistico' | 'ciudad';
    ciudad?: string;
    zonaTuristica?: string;
    cityId?: number;
    zoneId?: number;
    enabled: boolean;
    createdAt?: string;
    deactivationReason?: string;
  }

  // Color map for holiday types
  const holidayColors: Record<string, string> = {
    turistico: '#d80404',
    general: '#2f353a',
    ciudad: '#1284ed',
  };

  // Props from route or parent
  const route = useRoute();
  const calendarId = Number(route.params.calendarId);

  // Calendar data
  const countryName = ref('');
  const yearFrom = ref<number | null>(null);
  const yearTo = ref<number | null>(null);
  const searchText = ref('');
  const filterValue = ref('all');
  const selectedMonth = ref(new Date().getMonth());
  const selectedYear = ref(new Date().getFullYear());
  const holidayDrawerOpen = ref(false);
  const activeTab = ref('todos');
  const showDisabled = ref(false); // User requested default off
  const selectedHoliday = ref<Holiday | null>(null);
  const tabsRef = ref<HTMLElement | null>(null);
  const hasOverflow = ref(false);

  const checkOverflow = () => {
    if (tabsRef.value) {
      hasOverflow.value = tabsRef.value.scrollWidth > tabsRef.value.clientWidth;
    }
  };

  const scrollTabs = (direction: 'left' | 'right') => {
    if (tabsRef.value) {
      const scrollAmount = 200;
      tabsRef.value.scrollBy({
        left: direction === 'left' ? -scrollAmount : scrollAmount,
        behavior: 'smooth',
      });
    }
  };

  let resizeObserver: ResizeObserver | null = null;
  const deactivateDrawerOpen = ref(false);
  const modifyDrawerOpen = ref(false);
  const activateDrawerOpen = ref(false);

  // Holidays state separated to avoid mixing during updates
  const activeHolidaysList = ref<Holiday[]>([]);
  const deactivatedHolidaysList = ref<Holiday[]>([]);
  const isHolidaysLoading = ref(false);
  const isInitialLoading = ref(true);
  const isActionLoading = ref(false);
  const isLogsLoading = ref(false);

  // Computed holidays combining both lists
  const holidays = computed(() => [...activeHolidaysList.value, ...deactivatedHolidaysList.value]);

  import { useSupportStore } from '../store/useSupportStore';
  import { storeToRefs } from 'pinia';

  const supportStore = useSupportStore();
  const { cities, zones } = storeToRefs(supportStore);

  onMounted(async () => {
    // Start calendar request first
    const calendarPromise = countryCalendarService.getCalendar(calendarId);

    try {
      if (calendarId) {
        // Fetch calendar details to get countryId, name and years
        const calendar = await calendarPromise;
        if (calendar) {
          if (calendar.country && calendar.country !== 'Unknown') {
            countryName.value = calendar.country;
          }
          if (calendar.yearFrom) yearFrom.value = calendar.yearFrom;
          if (calendar.yearTo) yearTo.value = calendar.yearTo;

          // Set initial year based on validity period
          if (calendar.yearFrom && calendar.yearTo) {
            const currentYear = new Date().getFullYear();

            // Si la vigencia es futura (yearFrom > currentYear): usar yearFrom
            if (calendar.yearFrom > currentYear) {
              selectedYear.value = calendar.yearFrom;
            }
            // Si la vigencia es pasada (yearTo < currentYear): usar yearTo
            else if (calendar.yearTo < currentYear) {
              selectedYear.value = calendar.yearTo;
            }
            // Si la vigencia incluye el año actual: usar año actual
            else {
              selectedYear.value = currentYear;
            }
          }

          if (calendar.countryId) {
            // Fetch cities and zones for this country (background)
            supportStore.fetchCitiesAndZones(calendar.countryId);
          }
        }
      }
    } catch (error) {
      console.error('Error initializing calendar view:', error);
    }

    // Fetch holidays AFTER setting the correct year
    await fetchHolidays();
    isInitialLoading.value = false;

    // Auto-open drawer only if no holidays exist
    if (activeHolidaysList.value.length === 0) {
      holidayDrawerOpen.value = true;
    }

    // Overflow detection for tabs
    await nextTick();
    checkOverflow();
    if (tabsRef.value) {
      resizeObserver = new ResizeObserver(checkOverflow);
      resizeObserver.observe(tabsRef.value);
    }
  });

  onUnmounted(() => {
    if (resizeObserver) {
      resizeObserver.disconnect();
    }
  });

  // Create lookup maps
  const cityMapById = computed(() => {
    const map: Record<number, string> = {};
    cities.value.forEach((c) => {
      map[c.id] = c.name;
    });
    return map;
  });

  const zoneMapById = computed(() => {
    const map: Record<number, string> = {};
    zones.value.forEach((z) => {
      map[z.id] = z.name;
    });
    return map;
  });

  const mapBackendToFrontend = (item: HolidayItem): Holiday => {
    let holidayType: 'general' | 'turistico' | 'ciudad' = 'general';
    let ciudad: string | undefined = undefined;
    let zonaTuristica: string | undefined = undefined;
    let cityId: number | undefined = undefined;
    let zoneId: number | undefined = undefined;

    if (item.type_calendar === 'CITY') {
      holidayType = 'ciudad';
      if (item.location?.location_name) {
        ciudad = item.location.location_name;
      } else if (item.location?.city_id) {
        cityId = item.location.city_id;
        ciudad = cityMapById.value[cityId] || `Ciudad ${cityId}`;
      } else {
        ciudad = 'Ubicación no disponible';
      }
    } else if (item.type_calendar === 'TOURIST') {
      holidayType = 'turistico';
      if (item.location?.location_name) {
        zonaTuristica = item.location.location_name;
      } else if (item.location?.zone_id) {
        zoneId = item.location.zone_id;
        zonaTuristica = zoneMapById.value[zoneId] || `Zona ${zoneId}`;
      } else {
        zonaTuristica = 'Ubicación no disponible';
      }
    }

    return {
      id: item.id,
      name: item.name || item.name_holiday || '',
      date: item.date_from,
      dateTo: item.date_to,
      holidayType,
      ciudad,
      zonaTuristica,
      cityId,
      zoneId,
      enabled: item.is_active ?? true,
      createdAt: item.created_at,
    };
  };

  const hasCityHolidays = computed(() => holidays.value.some((h) => h.holidayType === 'ciudad'));

  const cityTabs = computed(() => {
    const cities = holidays.value
      .filter((h) => h.holidayType === 'ciudad' && h.ciudad)
      .map((h) => h.ciudad!)
      .filter((value, index, self) => self.indexOf(value) === index);
    return ['Todos', ...cities];
  });

  // Watch for changes in cityTabs to re-check overflow
  watch(cityTabs, async () => {
    await nextTick();
    checkOverflow();
  });

  const filteredHolidays = computed(() => {
    let filtered = holidays.value;

    // Filter by name
    if (searchText.value) {
      const lower = searchText.value.toLowerCase();
      filtered = filtered.filter((h) => h.name.toLowerCase().includes(lower));
    }

    // Filter by city tab
    if (activeTab.value !== 'todos') {
      filtered = filtered.filter((h) => h.ciudad?.toLowerCase() === activeTab.value);
    }

    // Filter by type (select input)
    if (filterValue.value && filterValue.value !== 'all') {
      const typeMap: Record<string, string> = {
        tourist: 'turistico',
        general: 'general',
        city: 'ciudad',
      };
      const targetType = typeMap[filterValue.value as string];
      if (targetType) {
        filtered = filtered.filter((h) => h.holidayType === targetType);
      }
    }

    return filtered;
  });

  const activeHolidays = computed(() => filteredHolidays.value.filter((h) => h.enabled));
  const disabledHolidays = computed(() => filteredHolidays.value.filter((h) => !h.enabled));

  watch(showDisabled, async (newValue) => {
    isHolidaysLoading.value = true;
    try {
      if (newValue && calendarId) {
        await fetchDeactivatedHolidays();
      } else {
        // Simular un pequeño tiempo de carga para feedback visual al desactivar
        await new Promise((resolve) => setTimeout(resolve, 300));
        // Clear deactivated holidays when switch is off
        deactivatedHolidaysList.value = [];
      }
    } finally {
      isHolidaysLoading.value = false;
    }
  });

  // Watch for year changes to reload holidays
  watch(selectedYear, async () => {
    if (calendarId) {
      await fetchHolidays();
    }
  });

  const fetchDeactivatedHolidays = async () => {
    try {
      const deactivated = await countryCalendarService.getDeactivatedHolidays(calendarId, {
        year: selectedYear.value,
      });
      deactivatedHolidaysList.value = deactivated.map((item) => {
        const mapped = mapBackendToFrontend(item);
        // Ensure enabled is false for consistency from this endpoint
        mapped.enabled = false;
        return mapped;
      });
    } catch (error) {
      console.error('Error fetching deactivated holidays', error);
      deactivatedHolidaysList.value = [];
    }
  };

  const getHolidayColor = (type: string) => holidayColors[type] || '#2f353a';

  const formatHolidayDate = (dateStr: string, dateTo?: string) => {
    const formatDate = (d: string) => {
      const date = new Date(d + 'T12:00:00');
      const days = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
      const dayName = days[date.getDay()];
      const dayNum = String(date.getDate()).padStart(2, '0');
      const monthNum = String(date.getMonth() + 1).padStart(2, '0');

      return `${dayName} ${dayNum}/${monthNum}`;
    };

    if (dateTo && dateTo !== dateStr) {
      const start = formatDate(dateStr);
      const end = formatDate(dateTo);
      return `${start} - ${end}`;
    }
    return formatDate(dateStr);
  };

  const getHolidaysForDate = (year: number, month: number, day: number) => {
    const calendarDate = dayjs(`${year}-${month + 1}-${day}`, 'YYYY-M-D');

    return holidays.value.filter((h) => {
      if (!h.enabled) return false;
      const startDate = dayjs(h.date);
      const endDate = dayjs(h.dateTo || h.date);

      return (
        (calendarDate.isSame(startDate, 'day') || calendarDate.isAfter(startDate, 'day')) &&
        (calendarDate.isSame(endDate, 'day') || calendarDate.isBefore(endDate, 'day'))
      );
    });
  };

  // Expanded days state
  const expandedDays = ref<Set<string>>(new Set());

  const toggleDayExpand = (dayId: string) => {
    const newSet = new Set(expandedDays.value);
    if (newSet.has(dayId)) {
      newSet.delete(dayId);
    } else {
      newSet.add(dayId);
    }
    expandedDays.value = newSet;
  };

  const isDayExpanded = (dayId: string) => expandedDays.value.has(dayId);

  // Holiday logs state
  import type { HolidayLog } from '../services/countryCalendarService';
  const holidayLogs = ref<HolidayLog[]>([]);

  /* eslint-disable no-async-promise-executor */
  /* eslint-disable @typescript-eslint/no-misused-promises */
  const handleMenuClick = async (holiday: Holiday, action: string) => {
    selectedHoliday.value = holiday;
    holidayLogs.value = []; // Reset logs

    if (action === 'modify') {
      modifyDrawerOpen.value = true;
      // No logs fetching for modify
    } else if (action === 'deactivate') {
      deactivateDrawerOpen.value = true;
      // Fetch logs in background for deactivate
      isLogsLoading.value = true;
      try {
        // Using a new promise to avoid awaiting directly here if needed, but we can just not await it
        countryCalendarService
          .getHolidayLogs(holiday.id)
          .then((logs) => {
            holidayLogs.value = logs;
            isLogsLoading.value = false;
          })
          .catch((err) => {
            console.error('Error fetching holiday logs', err);
            isLogsLoading.value = false;
          });
      } catch (error) {
        console.error('Error initiating fetch holiday logs', error);
        isLogsLoading.value = false;
      }
    }
  };

  const handleDeactivateHoliday = async (reason: string) => {
    if (selectedHoliday.value) {
      isActionLoading.value = true;
      try {
        await countryCalendarService.changeHolidayStatus(selectedHoliday.value.id, {
          is_active: false,
          deactivation_reason: reason,
        });
        // Do not remove locally, refetch instead to get updated status
        await fetchHolidays();
        notification.success({ message: 'Festivo desactivado correctamente' });
        deactivateDrawerOpen.value = false;
        selectedHoliday.value = null;
        holidayLogs.value = [];
      } catch (error) {
        console.error('Error deleting holiday', error);
        notification.error({ message: 'Error al desactivar el festivo' });
      } finally {
        isActionLoading.value = false;
      }
    }
  };

  const handleActivateClick = async (holiday: Holiday) => {
    selectedHoliday.value = holiday;
    holidayLogs.value = [];
    activateDrawerOpen.value = true;

    // Fetch logs in background
    isLogsLoading.value = true;
    try {
      countryCalendarService
        .getHolidayLogs(holiday.id)
        .then((logs) => {
          holidayLogs.value = logs;
          isLogsLoading.value = false;
        })
        .catch((err) => {
          console.error('Error fetching holiday logs', err);
          isLogsLoading.value = false;
        });
    } catch (error) {
      console.error('Error initiating fetch holiday logs', error);
      isLogsLoading.value = false;
    }
  };

  const handleActivateHoliday = async (reason: string) => {
    if (selectedHoliday.value) {
      isActionLoading.value = true;
      try {
        await countryCalendarService.changeHolidayStatus(selectedHoliday.value.id, {
          is_active: true,
          reason,
        });
        await fetchHolidays();
        notification.success({ message: 'Festivo activado correctamente' });
        activateDrawerOpen.value = false;
        selectedHoliday.value = null;
      } catch (error) {
        console.error('Error activating holiday', error);
        notification.error({ message: 'Error al activar el festivo' });
      } finally {
        isActionLoading.value = false;
      }
    }
  };

  const filterOptions = [
    { value: 'all', label: 'Todos' },
    { value: 'general', label: 'Festivo general' },
    { value: 'tourist', label: 'Festivo turístico' },
    { value: 'city', label: 'Festivo en ciudad' },
  ];

  const monthOptions = [
    { value: 0, label: 'ENERO' },
    { value: 1, label: 'FEBRERO' },
    { value: 2, label: 'MARZO' },
    { value: 3, label: 'ABRIL' },
    { value: 4, label: 'MAYO' },
    { value: 5, label: 'JUNIO' },
    { value: 6, label: 'JULIO' },
    { value: 7, label: 'AGOSTO' },
    { value: 8, label: 'SEPTIEMBRE' },
    { value: 9, label: 'OCTUBRE' },
    { value: 10, label: 'NOVIEMBRE' },
    { value: 11, label: 'DICIEMBRE' },
  ];

  const yearOptions = computed(() => {
    if (yearFrom.value === null || yearTo.value === null) return [];
    const years = [];
    for (let y = yearFrom.value; y <= yearTo.value; y++) {
      years.push({ value: y, label: String(y) });
    }
    return years;
  });

  const handleModifyHoliday = async (newName: string, reason: string) => {
    if (selectedHoliday.value) {
      isActionLoading.value = true;
      try {
        const item = selectedHoliday.value;

        let location: any = undefined;
        let type_calendar: 'GENERAL' | 'CITY' | 'TOURIST' = 'GENERAL';

        if (item.holidayType === 'ciudad') {
          type_calendar = 'CITY';
          const cityId = item.cityId || 99;
          const selectedCity = cities.value.find((c) => c.id === cityId);

          if (selectedCity && selectedCity.state_id) {
            location = { state_id: selectedCity.state_id };
          } else {
            location = { city_id: cityId };
          }
        } else if (item.holidayType === 'turistico') {
          type_calendar = 'TOURIST';
          location = {
            zone_id: item.zoneId || 99,
          };
        }

        const payload: CreateHolidayPayload = {
          name_holiday: newName,
          date_from: item.date,
          date_to: item.dateTo || item.date,
          type_calendar,
          location,
          reason,
        };

        await countryCalendarService.updateHoliday(item.id, payload);
        await fetchHolidays();
        notification.success({ message: 'Festivo modificado exitosamente' });
        modifyDrawerOpen.value = false;
        selectedHoliday.value = null;
      } catch (error) {
        console.error('Error modify holiday', error);
        notification.error({ message: 'Error al modificar el festivo' });
      } finally {
        isActionLoading.value = false;
      }
    }
  };

  // Generate calendar weeks
  const calendarWeeks = computed(() => {
    const weeks: { id: string; day: number; currentMonth: boolean; holidays: Holiday[] }[][] = [];
    const year = selectedYear.value;
    const month = selectedMonth.value;

    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDayOfWeek = firstDay.getDay();
    const daysInMonth = lastDay.getDate();

    // Previous month days
    const prevMonthLastDay = new Date(year, month, 0).getDate();
    let currentWeek: { id: string; day: number; currentMonth: boolean; holidays: Holiday[] }[] = [];

    // Fill previous month days
    for (let i = startDayOfWeek - 1; i >= 0; i--) {
      currentWeek.push({
        id: `prev-${prevMonthLastDay - i}`,
        day: prevMonthLastDay - i,
        currentMonth: false,
        holidays: [],
      });
    }

    // Fill current month days
    for (let day = 1; day <= daysInMonth; day++) {
      const dayHolidays = getHolidaysForDate(year, month, day);

      currentWeek.push({
        id: `curr-${day}`,
        day,
        currentMonth: true,
        holidays: dayHolidays,
      });

      if (currentWeek.length === 7) {
        weeks.push(currentWeek);
        currentWeek = [];
      }
    }

    // Fill next month days
    if (currentWeek.length > 0) {
      let nextDay = 1;
      while (currentWeek.length < 7) {
        currentWeek.push({
          id: `next-${nextDay}`,
          day: nextDay,
          currentMonth: false,
          holidays: [],
        });
        nextDay++;
      }
      weeks.push(currentWeek);
    }

    return weeks;
  });

  const previousMonth = () => {
    if (selectedMonth.value === 0) {
      selectedMonth.value = 11;
      selectedYear.value--;
    } else {
      selectedMonth.value--;
    }
  };

  const nextMonth = () => {
    if (selectedMonth.value === 11) {
      selectedMonth.value = 0;
      selectedYear.value++;
    } else {
      selectedMonth.value++;
    }
  };

  const fetchHolidays = async () => {
    isHolidaysLoading.value = true;
    try {
      if (calendarId) {
        // Fetch active holidays with year parameter
        const backendHolidays = await countryCalendarService.getHolidays(calendarId, {
          year: selectedYear.value,
        });
        activeHolidaysList.value = backendHolidays.map(mapBackendToFrontend);

        // If showDisabled is true, also refresh deactivated list
        if (showDisabled.value) {
          await fetchDeactivatedHolidays();
        }
      }
    } catch (error) {
      console.error('Error fetching holidays', error);
    } finally {
      isHolidaysLoading.value = false;
    }
  };

  const handleAddHoliday = () => {
    holidayDrawerOpen.value = true;
  };

  const handleSubmitNewHoliday = async (data: any) => {
    isActionLoading.value = true;
    try {
      let type_calendar: 'GENERAL' | 'CITY' | 'TOURIST' = 'GENERAL';
      let location: any = undefined;

      // data.ciudad and data.zonaTuristica are now IDs (numbers) from the drawer
      if (data.holidayType === 'ciudad') {
        type_calendar = 'CITY';
        const selectedCity = cities.value.find((c) => c.id === data.ciudad);
        // Use state_id if available (per backend requirement), otherwise fallback to city_id?
        // User example explicitly showed state_id.
        // If state_id is missing on the resource, we might have a problem, but let's try to send it if present.
        if (selectedCity && selectedCity.state_id) {
          location = { state_id: selectedCity.state_id };
        } else {
          // Fallback or if the resource is actually a state?
          location = { city_id: data.ciudad };
        }
      } else if (data.holidayType === 'turistico') {
        type_calendar = 'TOURIST';
        location = { zone_id: data.zonaTuristica };
      }

      const payload: CreateHolidayPayload = {
        name_holiday: data.name,
        date_from:
          data.dateType === 'dia'
            ? data.date.format('YYYY-MM-DD')
            : data.dateRange[0].format('YYYY-MM-DD'),
        date_to:
          data.dateType === 'dia'
            ? data.date.format('YYYY-MM-DD')
            : data.dateRange[1].format('YYYY-MM-DD'),
        type_calendar,
        has_blackout: false,
        location,
      };

      await countryCalendarService.createHoliday(calendarId, payload);
      await fetchHolidays();
      notification.success({ message: 'Festivo creado exitosamente' });
      holidayDrawerOpen.value = false;
    } catch (error) {
      console.error('Error saving holiday:', error);
      notification.error({ message: 'Error al crear el festivo' });
    } finally {
      isActionLoading.value = false;
    }
  };
</script>

<style lang="scss" scoped>
  .calendar-detail-view {
    padding: 0 !important;
    background: #fff;
    min-height: 100vh;
  }

  .calendar-header {
    width: calc(100% + 64px) !important;
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    margin-left: -32px !important;
    margin-right: -32px !important;
    padding: 33px 32px !important;
    border-bottom: 1px solid #e6e8eb;
    position: relative !important;
    display: flex;
    justify-content: space-between;
    align-items: center;

    // Remove old wrapper styles since we moved structure
    .header-wrapper {
      display: contents;
    }

    // Remove divider hack
    .header-divider {
      display: none;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: 16px;

      .calendar-title {
        font-size: 24px !important;
        font-weight: 700;
        color: #2f353a;
        margin: 0;
      }

      .validity-badge {
        display: inline-block;
        padding: 4px 12px;
        background: #e7e7e7;
        /* Light grey background */
        border-radius: 4px;
        font-size: 14px;
        line-height: 22px;
        font-weight: 500;
        color: #212121;
      }
    }

    .header-right {
      .add-holiday-button {
        display: flex;
        align-items: center;
        gap: 8px;
        height: 40px;
        padding: 0 16px;
        font-size: 14px;
        font-weight: 500;
        border: 1px solid #2f353a;
        color: #2f353a;
        background: #fff;
        border-radius: 4px;
        cursor: pointer;

        &:hover {
          background: #f5f5f5;
        }
      }
    }
  }

  .view-body {
    padding: 24px 0 32px;
    /* Horizontal padding moved to children */

    .city-tabs-container {
      display: flex;
      align-items: center;
      width: 100%;
      margin-bottom: 32px !important;
      padding: 0 32px !important;
      /* Restore padding for content */
      position: relative;
      border-bottom: 1px solid #e6e8eb;
      /* Restore border line */
      /* margin-top: 24px !important; Removed - now handled by view-body padding-top */

      .tab-nav-arrow {
        background: none;
        border: none;
        color: #5f6d7e;
        font-size: 14px;
        cursor: pointer;
        padding: 8px;
        display: flex;
        align-items: center;
        justify-content: center;

        &:hover {
          color: #2f353a;
        }
      }

      .city-tabs {
        display: flex;
        gap: 24px;
        overflow-x: hidden;
        white-space: nowrap;
        flex-grow: 1;
        scroll-behavior: smooth;
        mask-image: linear-gradient(to right, black 95%, transparent 100%);
        height: 35px;
        /* Fixed height for consistency */
        align-items: flex-end;
        /* Align bottom to match border line */
      }

      .city-tab {
        background: none;
        border: none;
        font-size: 14px;
        color: #5f6d7e;
        cursor: pointer;
        padding: 0 0 8px 0;
        position: relative;
        border-bottom: 2px solid transparent;
        transition: all 0.2s;
        white-space: nowrap;

        &:hover {
          color: #2f353a;
        }

        &.active {
          color: #2f353a;
          border-bottom-color: #2f353a;
        }
      }
    }
  }

  .calendar-content {
    display: flex;
    gap: 32px;
    /* Increased gap */

    .sidebar {
      width: 280px;
      /* Slightly wider */
      flex-shrink: 0;
      border-right: none;
      /* No border here */
      padding-right: 0;
      background-color: #fcfcfc;
      /* Very subtle background or white */
      background: white;

      .search-section {
        margin-bottom: 24px;

        .search-input {
          height: 40px;
          border: 1px solid #d9d9d9;

          &:hover,
          &:focus {
            border-color: #2f353a;
          }

          .search-icon {
            color: #bfbfbf;
          }
        }
      }

      .holidays-summary {
        border-top: none;
        background: #f9f9f9;
        padding: 16px;
        border-radius: 8px;
        margin-top: -4px;

        .summary-header {
          margin-bottom: 16px !important;

          .year-select-sidebar {
            width: 100%;
            margin-bottom: 12px;

            :deep(.ant-select-selector) {
              height: 40px !important;
              border-radius: 4px;
              border-color: #d9d9d9;
              display: flex;
              align-items: center;
            }
          }

          .switch-row {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-top: 4px;
            margin-bottom: 16px;
            gap: 8px;

            .switch-label {
              font-size: 13px;
              color: #5f6d7e;
            }

            .disabled-switch {
              background-color: #d9d9d9;

              &.ant-switch-checked {
                background-color: #bd0d12 !important;
              }

              &:hover {
                &.ant-switch-checked {
                  background-color: #bd0d12 !important;
                }
              }
            }
          }
        }

        .summary-empty {
          font-size: 14px;
          color: #9aa6b8;
          line-height: 1.5;
          padding-top: 10px;
        }

        /* Estilo para el loading del listado */
        :deep(.ant-spin-nested-loading) {
          margin-top: 0px !important;
        }

        .activos-label {
          font-size: 12px;
          font-weight: 600;
          color: #2f353a;
          margin-bottom: 12px;
        }

        .desactivados-label {
          font-size: 12px;
          font-weight: 600;
          color: #5f6d7e;
          margin-top: 24px;
          margin-bottom: 12px;
        }

        .holiday-cards {
          display: flex;
          flex-direction: column;
          gap: 8px;
          max-height: 400px;
          overflow-y: auto;
        }

        .holiday-card {
          display: flex;
          justify-content: space-between;
          align-items: flex-start;
          padding: 12px;
          border: 1px solid #e6e8eb;
          border-radius: 8px;
          background: #fff;

          .card-left {
            display: flex;
            gap: 10px;
            align-items: flex-start;

            .card-dot {
              width: 8px;
              height: 8px;
              border-radius: 50%;
              margin-top: 5px;
              flex-shrink: 0;
            }

            .card-info {
              display: flex;
              flex-direction: column;
              gap: 2px;

              .card-date {
                font-size: 13px;
                font-weight: 600;
                color: #2f353a;
              }

              .card-name {
                font-size: 12px;
                color: #9aa6b8;
                max-width: 120px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
              }
            }
          }

          .card-right {
            display: flex;
            align-items: center;
            gap: 8px;

            .city-badge {
              display: flex;
              align-items: center;
              gap: 4px;
              font-size: 11px;
              color: #5f6d7e;
              background: transparent;
              padding: 2px 0;

              .city-icon {
                font-size: 10px;
              }
            }

            .card-menu {
              background: none;
              border: none;
              color: #7e8285;
              cursor: pointer;
              padding: 4px;
              font-size: 14px;

              &:hover {
                color: #2f353a;
              }

              &.disabled-icon {
                cursor: default;
                color: #bfbfbf;

                &:hover {
                  color: #bfbfbf;
                }
              }

              &.gear-icon {
                cursor: pointer;
                color: #5f6d7e;

                svg path {
                  stroke: #5f6d7e;
                }

                &:hover {
                  color: #2f353a;

                  svg path {
                    stroke: #2f353a;
                  }
                }
              }
            }
          }

          &.disabled {
            background: #fff;
            user-select: none;

            .card-left {
              .card-dot {
                opacity: 1;
              }

              .card-info {
                opacity: 0.6;
              }
            }

            .card-right {
              .city-badge {
                opacity: 0.6;
              }

              .card-menu {
                opacity: 1;
              }
            }
          }
        }
      }
    }

    .calendar-main {
      flex: 1;
      overflow: hidden;
      /* Contain grid */

      .calendar-toolbar {
        display: flex;
        justify-content: flex-end;
        /* Move everything to the right */
        align-items: center;
        gap: 32px;
        /* Space between Legend and Filter */
        margin-bottom: 24px;
        padding-right: 4px;

        .legend {
          display: flex;
          gap: 24px;

          .legend-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            white-space: nowrap;

            .legend-dot {
              width: 8px;
              height: 8px;
              border-radius: 50%;
            }

            &.tourist {
              color: #d80404;

              .legend-dot {
                background-color: #d80404;
              }
            }

            &.general {
              color: #2f353a;

              .legend-dot {
                background-color: #2f353a;
              }
            }

            &.city {
              color: #004e96;

              .legend-dot {
                background-color: #004e96;
              }
            }
          }
        }

        .filter-section {
          display: flex;
          align-items: center;
          gap: 12px;

          .filter-label {
            font-size: 14px;
            color: #2f353a;
          }

          .filter-select {
            width: 200px;

            :deep(.ant-select-selector) {
              height: 36px !important;
              border-radius: 4px;
              border-color: #d9d9d9;
            }
          }
        }
      }

      /* Dark Calendar Header Bar */
      .calendar-navigation {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 16px;
        background: #343a40;
        /* Dark background matching figma */
        border-radius: 6px 6px 0 0;
        margin-bottom: 0;

        .month-year-selectors {
          display: flex;
          gap: 12px;

          .month-select {
            width: 140px;

            :deep(.ant-select-selector) {
              height: 36px;
              background: #fff;
              border: none;
              border-radius: 4px;
            }
          }

          .year-select {
            width: 100px;

            :deep(.ant-select-selector) {
              height: 36px;
              background: #fff;
              border: none;
              border-radius: 4px;
            }
          }
        }

        .nav-arrows {
          display: flex;
          gap: 8px;

          .nav-arrow {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: none;
            border-radius: 4px !important;
            /* Rounded squares */
            color: #2f353a;
            cursor: pointer;
            transition: opacity 0.2s;

            &:hover {
              opacity: 0.9;
            }

            font-size: 12px;
          }
        }
      }

      .calendar-grid {
        border: 1px solid #e6e8eb;
        /* Light border */
        border-top: none;
        border-radius: 0 0 6px 6px;
        /* Bottom rounded corners */
        overflow: hidden;

        .calendar-row {
          display: grid;
          grid-template-columns: repeat(7, minmax(0, 1fr));

          &.header-row {
            background: #f9fafb;
            /* Slightly off-white header */
            border-bottom: 1px solid #e6e8eb;
          }
        }

        .calendar-cell {
          padding: 0;
          /* Remove default padding */
          text-align: center;
          border-right: 1px solid #e6e8eb;
          border-bottom: 1px solid #e6e8eb;
          position: relative;
          /* For absolutely positioning content if needed */

          &:last-child {
            border-right: none;
          }

          &.header-cell {
            font-size: 14px;
            font-weight: 600;
            color: #2f353a;
            padding: 16px 8px;
            /* Taller headers */
            background: #ffffff;
            /* Actually white in 2nd image */
          }

          &.day-cell {
            height: 120px;
            /* Taller cells */
            padding: 8px;
            text-align: right;
            vertical-align: top;
            background: #fff;
            display: flex;
            flex-direction: column;

            .day-number {
              font-size: 14px;
              color: #5f6d7e;
              font-weight: 500;
              text-align: right;
              margin-bottom: 4px;
            }

            .day-holidays {
              display: flex;
              flex-direction: column;
              gap: 4px;
              flex: 1;
              /* Take remaining space in day-cell */
              overflow: hidden;

              &.expanded {
                overflow-y: auto;

                /* Custom scrollbar style if needed */
                &::-webkit-scrollbar {
                  width: 4px;
                }

                &::-webkit-scrollbar-thumb {
                  background-color: #d9d9d9;
                  border-radius: 4px;
                }
              }
            }

            .holiday-event {
              background: #fff;
              border-left: 3px solid;
              padding: 4px 8px;
              font-size: 11px;
              text-align: left;
              border-radius: 4px;
              box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
              flex-shrink: 0;
              /* Prevent shrinking when scrolling */

              &.single-event {
                flex: 1;
                /* Grow to fill day-holidays */
                display: flex;
                flex-direction: column;
                justify-content: flex-start;
                box-shadow: none;
                /* Remove shadow to ensure clean lines */
                border-bottom: 1px solid #f0f0f0;
                /* Optional: lightweight separator instead of shadow */
              }

              .event-name {
                display: -webkit-box;
                color: #2f353a;
                font-weight: 500;
                white-space: normal;
                word-break: break-word;
                overflow: hidden;
                text-overflow: ellipsis;
                -webkit-line-clamp: 3;
                line-clamp: 3;
                -webkit-box-orient: vertical;
              }

              .event-city {
                display: flex;
                align-items: center;
                gap: 3px;
                font-size: 10px;
                color: #9aa6b8;
                margin-top: 2px;

                .event-city-icon {
                  font-size: 8px;
                }
              }
            }

            .more-events {
              font-size: 10px;
              color: #2f353a !important;
              /* Force dark color per user request */
              text-align: left;
              padding-left: 4px;
              cursor: pointer;
              margin-top: auto;
              /* Push to bottom if expanded */
              padding-top: 4px;
              padding-bottom: 4px;
              /* Ensure clickability */
              white-space: nowrap;
              /* Prevent wrapping */

              &:hover {
                text-decoration: none !important;
              }

              .hide-text {
                color: #5f6d7e;
              }
            }

            &.other-month {
              background-color: #fff;

              /* Keep white */
              .day-number {
                color: #d9d9d9;
              }
            }
          }
        }

        /* Remove bottom border from last row */
        .calendar-row:last-child .calendar-cell {
          border-bottom: none;
        }
      }
    }
  }
</style>

<style lang="scss">
  /* Global styles for holiday name tooltip */
  .holiday-name-tooltip {
    .ant-tooltip-inner {
      font-size: 11px !important;
      white-space: nowrap !important;
      max-width: none !important;
      padding: 4px 8px !important;
    }
  }
</style>
