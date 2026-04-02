<template>
  <div class="country-calendar-list">
    <!-- Header -->
    <div class="main-header-bar">
      <div class="justify-header">
        <div>
          <a-typography-title :level="4" class="section-title">
            Listado de calendarios por países
          </a-typography-title>
        </div>
        <div>
          <a-button type="primary" class="button-new-calendar" @click="handleAddCalendar">
            <font-awesome-icon :icon="['fas', 'plus']" />
            Calendario
          </a-button>
        </div>
      </div>
    </div>

    <!-- Search - Icon at the end like Figma -->
    <div class="search-section">
      <a-input
        v-model:value="searchText"
        placeholder="Buscar nombre de país"
        allow-clear
        class="search-input"
      >
        <template #suffix>
          <font-awesome-icon :icon="['fas', 'magnifying-glass']" class="search-icon" />
        </template>
      </a-input>
    </div>

    <!-- Table -->
    <a-table
      :columns="columns"
      :data-source="dataSource"
      :row-selection="rowSelection"
      :pagination="false"
      :loading="loading"
      row-key="id"
      class="calendar-table"
      :row-class-name="
        (_record: any) => {
          const currentYear = new Date().getFullYear();
          if (_record.enabled === false) return 'disabled-row';
          if (_record.yearTo < currentYear) return 'expired-row';
          return '';
        }
      "
    >
      <template #bodyCell="{ column, record }">
        <template v-if="column.key === 'createdAt'">
          {{ formatDate(record.createdAt) }}
        </template>
        <template v-else-if="column.key === 'period'">
          <span class="period-text">
            {{ record.yearFrom }}
            <font-awesome-icon :icon="['fas', 'arrow-right']" class="arrow-icon" />
            {{ record.yearTo }}
          </span>
        </template>
        <template v-else-if="column.key === 'actions'">
          <div class="actions-cell">
            <!-- Icono calendario → navega al detalle -->
            <a-button type="text" class="action-button" @click="handleNavigate(record)">
              <svg
                width="16"
                height="16"
                viewBox="0 0 16 16"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <g clip-path="url(#clip0_21606_60507)">
                  <path
                    d="M5.42857 2H10.5714V0.75C10.5714 0.335938 10.9536 0 11.4286 0C11.9036 0 12.2857 0.335938 12.2857 0.75V2H13.7143C14.975 2 16 2.89531 16 4V14C16 15.1031 14.975 16 13.7143 16H2.28571C1.02321 16 0 15.1031 0 14V4C0 2.89531 1.02321 2 2.28571 2H3.71429V0.75C3.71429 0.335938 4.09643 0 4.57143 0C5.04643 0 5.42857 0.335938 5.42857 0.75V2ZM1.71429 14C1.71429 14.275 1.97 14.5 2.28571 14.5H13.7143C14.0286 14.5 14.2857 14.275 14.2857 14V6H1.71429V14Z"
                    fill="#1284ED"
                  />
                </g>
                <defs>
                  <clipPath id="clip0_21606_60507">
                    <rect width="16" height="16" fill="white" />
                  </clipPath>
                </defs>
              </svg>
            </a-button>
            <!-- Tuerca → abre ActivateCalendarDrawer -->
            <a-button type="text" class="action-button" @click="handleSettings(record)">
              <svg
                width="24"
                height="24"
                viewBox="0 0 24 24"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <g clip-path="url(#clip0_20168_3770)">
                  <path
                    d="M12 15C13.6569 15 15 13.6569 15 12C15 10.3431 13.6569 9 12 9C10.3431 9 9 10.3431 9 12C9 13.6569 10.3431 15 12 15Z"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                  <path
                    d="M19.4 15C19.2669 15.3016 19.2272 15.6362 19.286 15.9606C19.3448 16.285 19.4995 16.5843 19.73 16.82L19.79 16.88C19.976 17.0657 20.1235 17.2863 20.2241 17.5291C20.3248 17.7719 20.3766 18.0322 20.3766 18.295C20.3766 18.5578 20.3248 18.8181 20.2241 19.0609C20.1235 19.3037 19.976 19.5243 19.79 19.71C19.6043 19.896 19.3837 20.0435 19.1409 20.1441C18.8981 20.2448 18.6378 20.2966 18.375 20.2966C18.1122 20.2966 17.8519 20.2448 17.6091 20.1441C17.3663 20.0435 17.1457 19.896 16.96 19.71L16.9 19.65C16.6643 19.4195 16.365 19.2648 16.0406 19.206C15.7162 19.1472 15.3816 19.1869 15.08 19.32C14.7842 19.4468 14.532 19.6572 14.3543 19.9255C14.1766 20.1938 14.0813 20.5082 14.08 20.83V21C14.08 21.5304 13.8693 22.0391 13.4942 22.4142C13.1191 22.7893 12.6104 23 12.08 23C11.5496 23 11.0409 22.7893 10.6658 22.4142C10.2907 22.0391 10.08 21.5304 10.08 21V20.91C10.0723 20.579 9.96512 20.258 9.77251 19.9887C9.5799 19.7194 9.31074 19.5143 9 19.4C8.69838 19.2669 8.36381 19.2272 8.03941 19.286C7.71502 19.3448 7.41568 19.4995 7.18 19.73L7.12 19.79C6.93425 19.976 6.71368 20.1235 6.47088 20.2241C6.22808 20.3248 5.96783 20.3766 5.705 20.3766C5.44217 20.3766 5.18192 20.3248 4.93912 20.2241C4.69632 20.1235 4.47575 19.976 4.29 19.79C4.10405 19.6043 3.95653 19.3837 3.85588 19.1409C3.75523 18.8981 3.70343 18.6378 3.70343 18.375C3.70343 18.1122 3.75523 17.8519 3.85588 17.6091C3.95653 17.3663 4.10405 17.1457 4.29 16.96L4.35 16.9C4.58054 16.6643 4.73519 16.365 4.794 16.0406C4.85282 15.7162 4.81312 15.3816 4.68 15.08C4.55324 14.7842 4.34276 14.532 4.07447 14.3543C3.80618 14.1766 3.49179 14.0813 3.17 14.08H3C2.46957 14.08 1.96086 13.8693 1.58579 13.4942C1.21071 13.1191 1 12.6104 1 12.08C1 11.5496 1.21071 11.0409 1.58579 10.6658C1.96086 10.2907 2.46957 10.08 3 10.08H3.09C3.42099 10.0723 3.742 9.96512 4.0113 9.77251C4.28059 9.5799 4.48572 9.31074 4.6 9C4.73312 8.69838 4.77282 8.36381 4.714 8.03941C4.65519 7.71502 4.50054 7.41568 4.27 7.18L4.21 7.12C4.02405 6.93425 3.87653 6.71368 3.77588 6.47088C3.67523 6.22808 3.62343 5.96783 3.62343 5.705C3.62343 5.44217 3.67523 5.18192 3.77588 4.93912C3.87653 4.69632 4.02405 4.47575 4.21 4.29C4.39575 4.10405 4.61632 3.95653 4.85912 3.85588C5.10192 3.75523 5.36217 3.70343 5.625 3.70343C5.88783 3.70343 6.14808 3.75523 6.39088 3.85588C6.63368 3.95653 6.85425 4.10405 7.04 4.29L7.1 4.35C7.33568 4.58054 7.63502 4.73519 7.95941 4.794C8.28381 4.85282 8.61838 4.81312 8.92 4.68H9C9.29577 4.55324 9.54802 4.34276 9.72569 4.07447C9.90337 3.80618 9.99872 3.49179 10 3.17V3C10 2.46957 10.2107 1.96086 10.5858 1.58579C10.9609 1.21071 11.4696 1 12 1C12.5304 1 13.0391 1.21071 13.4142 1.58579C13.7893 1.96086 14 2.46957 14 3V3.09C14.0013 3.41179 14.0966 3.72618 14.2743 3.99447C14.452 4.26276 14.7042 4.47324 15 4.6C15.3016 4.73312 15.6362 4.77282 15.9606 4.714C16.285 4.65519 16.5843 4.50054 16.82 4.27L16.88 4.21C17.0657 4.02405 17.2863 3.87653 17.5291 3.77588C17.7719 3.67523 18.0322 3.62343 18.295 3.62343C18.5578 3.62343 18.8181 3.67523 19.0609 3.77588C19.3037 3.87653 19.5243 4.02405 19.71 4.21C19.896 4.39575 20.0435 4.61632 20.1441 4.85912C20.2448 5.10192 20.2966 5.36217 20.2966 5.625C20.2966 5.88783 20.2448 6.14808 20.1441 6.39088C20.0435 6.63368 19.896 6.85425 19.71 7.04L19.65 7.1C19.4195 7.33568 19.2648 7.63502 19.206 7.95941C19.1472 8.28381 19.1869 8.61838 19.32 8.92V9C19.4468 9.29577 19.6572 9.54802 19.9255 9.72569C20.1938 9.90337 20.5082 9.99872 20.83 10H21C21.5304 10 22.0391 10.2107 22.4142 10.5858C22.7893 10.9609 23 11.4696 23 12C23 12.5304 22.7893 13.0391 22.4142 13.4142C22.0391 13.7893 21.5304 14 21 14H20.91C20.5882 14.0013 20.2738 14.0966 20.0055 14.2743C19.7372 14.452 19.5268 14.7042 19.4 15V15Z"
                    stroke="#1284ED"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  />
                </g>
                <defs>
                  <clipPath id="clip0_20168_3770">
                    <rect width="24" height="24" fill="white" />
                  </clipPath>
                </defs>
              </svg>
            </a-button>
          </div>
        </template>
      </template>
    </a-table>

    <!-- Custom Pagination - Exactly like Figma -->
    <div class="custom-pagination">
      <div class="pagination-container">
        <button
          class="pagination-arrow"
          :disabled="currentPage === 1"
          @click="goToPage(currentPage - 1)"
        >
          ←
        </button>
        <span class="pagination-separator"></span>

        <template v-for="page in visiblePages" :key="page">
          <button
            v-if="page !== '...'"
            class="pagination-item"
            :class="{ active: currentPage === page }"
            @click="goToPage(page as number)"
          >
            {{ page }}
          </button>
          <span v-else class="pagination-ellipsis">...</span>
          <span class="pagination-separator"></span>
        </template>

        <button
          class="pagination-arrow"
          :disabled="currentPage === totalPages"
          @click="goToPage(currentPage + 1)"
        >
          →
        </button>
      </div>
    </div>

    <ActivateCalendarDrawer
      v-model:open="activateDrawerOpen"
      :calendar="selectedCalendar"
      @submit="handleActivateSubmit"
    />

    <ExtendCalendarDrawer
      v-model:open="extendDrawerOpen"
      :country="selectedCountryForExtension"
      @submit="handleExtendSubmit"
    />
  </div>
</template>

<script lang="ts" setup>
  import { ref, computed, onMounted, watch } from 'vue';
  import type { TableColumnsType } from 'ant-design-vue';
  import type { CountryCalendarItem } from '../interfaces/country-calendar.interface';

  import { countryCalendarService } from '../services/countryCalendarService';
  import { useCalendarQueries } from '../composables/useCalendarQueries';
  import ActivateCalendarDrawer from './ActivateCalendarDrawer.vue';
  import ExtendCalendarDrawer from './ExtendCalendarDrawer.vue';

  const emit = defineEmits<{
    (e: 'add'): void;
    (e: 'settings', record: CountryCalendarItem): void;
  }>();

  import { debounce } from 'lodash-es';

  // State
  const searchText = ref('');
  const searchDebounced = ref('');
  const selectedRowKeys = ref<number[]>([]);
  const currentPage = ref(1);
  const pageSize = ref(10); // Standard page size

  // Debounce search update
  const handleSearchUpdate = debounce((val: string) => {
    searchDebounced.value = val;
    currentPage.value = 1; // Reset to first page on search
  }, 500);

  // Watch input changes
  watch(searchText, (val: string) => {
    handleSearchUpdate(val);
  });

  // Vue Query integration
  const { useCalendars } = useCalendarQueries();
  const { data: calendarData, isLoading } = useCalendars({
    page: currentPage,
    pageSize: pageSize,
    search: searchDebounced,
  });

  const dataSource = computed(() => calendarData.value?.data || []);
  const totalItems = computed(() => calendarData.value?.total || 0);
  const loading = computed(() => isLoading.value);

  const activateDrawerOpen = ref(false);
  const extendDrawerOpen = ref(false);
  const selectedCalendar = ref<CountryCalendarItem | null>(null);
  const selectedCountryForExtension = ref('');

  // Pagination computed
  const totalPages = computed(() => Math.ceil(totalItems.value / pageSize.value));

  const visiblePages = computed(() => {
    const pages: (number | string)[] = [];
    const total = totalPages.value;
    const current = currentPage.value;

    if (total <= 7) {
      for (let i = 1; i <= total; i++) {
        pages.push(i);
      }
    } else {
      pages.push(1);
      if (current > 3) {
        pages.push('...');
      }

      const start = Math.max(2, current - 1);
      const end = Math.min(total - 1, current + 1);

      for (let i = start; i <= end; i++) {
        if (!pages.includes(i)) {
          pages.push(i);
        }
      }

      if (current < total - 2) {
        pages.push('...');
      }
      if (!pages.includes(total)) {
        pages.push(total);
      }
    }
    return pages;
  });

  // Remove manual fetchData as Vue Query handles it

  const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
      currentPage.value = page;
      // No explicit fetchData call needed
    }
  };

  // Table columns
  const columns: TableColumnsType = [
    {
      title: 'Fecha de creación',
      dataIndex: 'createdAt',
      key: 'createdAt',
      sorter: true,
      width: '20%',
    },
    {
      title: 'País',
      dataIndex: 'country',
      key: 'country',
      sorter: true,
      width: '25%',
    },
    {
      title: 'Periodo: Año de inicio - Año de fin',
      key: 'period',
      sorter: true,
      width: '35%',
    },
    {
      title: 'Acciones',
      key: 'actions',
      width: '20%',
      align: 'center',
    },
  ];

  // Row selection
  const rowSelection = computed(() => ({
    selectedRowKeys: selectedRowKeys.value,
    onChange: (keys: number[]) => {
      selectedRowKeys.value = keys;
    },
  }));

  // Methods
  const formatDate = (date: string): string => {
    return new Date(date).toLocaleDateString('es-PE', {
      day: '2-digit',
      month: '2-digit',
      year: 'numeric',
    });
  };

  const handleAddCalendar = () => {
    emit('add');
  };

  const handleExtendSubmit = () => {
    // Logic to handle extensions submission
    console.log('Extension submitted for', selectedCountryForExtension.value);
    extendDrawerOpen.value = false;
  };

  const handleNavigate = (record: CountryCalendarItem) => {
    emit('settings', record);
  };

  const handleSettings = (record: CountryCalendarItem) => {
    selectedCalendar.value = record;
    activateDrawerOpen.value = true;
  };

  import type { Dayjs } from 'dayjs';

  import { useQueryClient } from '@tanstack/vue-query';
  import { notification } from 'ant-design-vue';

  const queryClient = useQueryClient();

  const handleActivateSubmit = async (payload: { dateFrom: Dayjs; dateTo: Dayjs }) => {
    if (selectedCalendar.value) {
      // Validate countryId exists
      if (!selectedCalendar.value.countryId) {
        notification.error({
          message: 'Error de datos',
          description: 'No se encontró el identificador del país para este calendario.',
          placement: 'topRight',
        });
        return;
      }

      try {
        const calendarId = selectedCalendar.value.id;
        console.log(
          'Activating calendar',
          calendarId,
          payload.dateFrom.format('YYYY-MM-DD'),
          payload.dateTo.format('YYYY-MM-DD')
        );

        await countryCalendarService.updateCalendar(calendarId, {
          year_from: payload.dateFrom.format('YYYY-MM-DD'),
          year_to: payload.dateTo.format('YYYY-MM-DD'),
          status: 'active',
        });

        notification.success({
          message: 'Notificación de calendario',
          description: 'La vigencia ha sido actualizada.',
          placement: 'topRight',
        });

        // Invalidate queries to refresh list
        queryClient.invalidateQueries({ queryKey: ['calendars'] });

        activateDrawerOpen.value = false;
        selectedCalendar.value = null;
      } catch (error: any) {
        console.error('Error updating calendar', error);
        notification.error({
          message: 'Error al actualizar',
          description:
            error.response?.data?.message || 'No se pudo actualizar la vigencia del calendario.',
          placement: 'topRight',
        });
      }
    }
  };

  onMounted(() => {
    // Vue Query fetches automatically
  });
</script>

<style lang="scss" scoped>
  @import '@/scss/_variables.scss';

  .country-calendar-list {
    .main-header-bar {
      .justify-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
      }

      .section-title {
        margin: 0;
        font-weight: 600;
        color: #2f353a;
        font-size: 24px !important;
      }

      .button-new-calendar {
        font-size: 16px !important;
        height: 48px;
        gap: 8px;
        background: $color-white;
        color: #2f353a;
        border: 1px solid #2f353a;
        box-shadow: none !important;
        display: flex;
        align-items: center;

        &:hover {
          background: #f5f5f5;
          border: 1px solid #2f353a;
          color: #2f353a;
        }
      }
    }

    .search-section {
      margin: 16px 0;
      max-width: 350px;

      .search-input {
        height: 40px;
        border: 1px solid #d9d9d9;
        border-radius: 4px;

        &:hover,
        &:focus {
          border-color: #d9d9d9;
        }

        :deep(.ant-input) {
          &::placeholder {
            color: #bfbfbf;
          }
        }
      }

      .search-icon {
        color: #bfbfbf;
        font-size: 14px;
      }
    }

    .calendar-table {
      :deep(.ant-table-thead > tr > th) {
        background-color: #4a4a4a;
        color: #fff;
        font-weight: 500;
        padding: 12px 16px;
        font-size: 14px;

        &::before {
          display: none;
        }

        .ant-table-column-sorter {
          color: #fff;
        }
      }

      :deep(.ant-table-tbody > tr > td) {
        padding: 12px 16px;
        color: #595959;
        border-bottom: 1px solid #f0f0f0;
      }

      :deep(.ant-table-tbody > tr:hover > td) {
        background-color: #fafafa;
      }

      .period-text {
        display: inline-flex;
        align-items: center;
        gap: 8px;

        .arrow-icon {
          color: #8c8c8c;
          font-size: 12px;
        }
      }

      .actions-cell {
        display: inline-flex;
        align-items: center;
        gap: 4px;
      }

      .action-button {
        color: #1890ff;
        font-size: 16px;
        padding: 4px 8px;
        background: transparent !important;

        .action-icon {
          font-size: 18px;
          color: #1284ed;
        }

        &:hover {
          color: #40a9ff;
          background: transparent !important;

          .action-icon {
            color: #40a9ff;
          }
        }
      }
    }

    // Custom Pagination - Exactly like Figma
    .custom-pagination {
      display: flex;
      justify-content: flex-start;
      margin-top: 16px;
      padding: 8px 0;

      .pagination-container {
        display: inline-flex;
        align-items: center;
        border: 1px solid #d9d9d9;
        border-radius: 4px;
        overflow: hidden;
      }

      .pagination-arrow {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        background: transparent;
        border: none;
        color: #595959;
        cursor: pointer;
        font-size: 14px;

        &:hover:not(:disabled) {
          background-color: #f5f5f5;
        }

        &:disabled {
          color: #d9d9d9;
          cursor: not-allowed;
        }
      }

      .pagination-separator {
        width: 1px;
        height: 32px;
        background-color: #d9d9d9;
      }

      .pagination-item {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        background: transparent;
        border: none;
        color: #595959;
        cursor: pointer;
        font-size: 14px;

        &:hover:not(.active) {
          background-color: #f5f5f5;
        }

        &.active {
          background-color: #595959;
          color: #fff;
        }
      }

      .pagination-ellipsis {
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        color: #595959;
        font-size: 14px;
      }
    }

    // Disabled row styles
    :deep(.disabled-row) {
      background-color: #fafafa;
      color: #bfbfbf !important;

      td {
        color: #bfbfbf !important;
      }

      .action-button {
        // Keep action button active/colored even in disabled row
        color: #1890ff !important;
        background: transparent !important;

        svg path {
          stroke: #1890ff !important;
        }

        &:hover {
          color: #40a9ff !important;
          background: transparent !important;

          svg path {
            stroke: #40a9ff !important;
          }
        }
      }

      .arrow-icon {
        color: #bfbfbf !important;
      }
    }

    // Expired row styles (yearTo < current year)
    :deep(.expired-row) {
      td {
        color: #bfbfbf !important;
      }

      // No afectar la celda de acciones
      td:last-child {
        color: inherit !important;
        opacity: 1 !important;
      }

      .period-text {
        color: #bfbfbf !important;

        .arrow-icon {
          color: #bfbfbf !important;
        }
      }

      // Botones de acción sin cambios
      .actions-cell {
        .action-button {
          opacity: 1 !important;
        }
      }
    }
  }
</style>
