<template>
  <div class="supplier-ticket-page-list">
    <div class="filters-section">
      <div class="filter-container-group">
        <div class="filters-container">
          <div>
            <a-input
              class="search-text"
              v-model:value="storeSearchSupplierTicket.codeOrName"
              placeholder="Buscar proveedor"
              @change="storeSearchSupplierTicket.setCodeOrName($event.target.value)"
            >
              <template #suffix>
                <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
              </template>
            </a-input>
          </div>

          <div class="group-options">
            <div class="button-filter">
              Tipo de tarifa <font-awesome-icon :icon="['fas', 'chevron-down']" />
            </div>
            <div class="button-filter-custom">
              <font-awesome-icon :icon="['fas', 'filter']" /> Añadir filtro
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="table-section">
      <div class="total-supplier">
        {{ totalEntriesText }}
      </div>
      <a-table
        class="table-supplier-tickets"
        size="small"
        :columns="columns"
        :data-source="data"
        :loading="isLoading"
        :pagination="false"
      >
        <template #bodyCell="{ column, record }">
          <template v-if="column.key === 'created'">
            <div>
              <div class="bold-text">
                {{ record.user.code }}
              </div>
              <div>
                {{ record.created_at }}
              </div>
            </div>
          </template>
          <template v-else-if="column.key === 'status'">
            <span>
              <a-tag
                :color="record.status.value ? '#DFFFE9' : '#FFFCE4'"
                :style="{ color: record.status.value ? '#00A15B' : '#E4B804', fontWeight: 600 }"
              >
                {{ record.status.value ? 'Activo' : 'Inactivo' }}
              </a-tag>
            </span>
          </template>
          <template v-else-if="column.key === 'supplier_name'">
            <div>
              <div>
                {{ record.name }}
              </div>
              <div class="progress-wrapper">
                <div class="progress-container">
                  <div
                    class="progress-bar"
                    :style="getProgressStyle(record.modules_percentage.percentage)"
                  ></div>
                </div>
                <div
                  class="progress-percentage"
                  :style="{
                    color: record.modules_percentage.percentage || 0 < 100 ? '#D80404' : '#07DF81',
                  }"
                >
                  {{ record.modules_percentage.percentage || 0 }}%
                </div>
              </div>
            </div>
          </template>
          <template v-else-if="column.key === 'state'">
            <font-awesome-icon
              v-if="record.tickets !== null && record.tickets.belongs_state == 1"
              :icon="['fas', 'check']"
            />
            <font-awesome-icon v-else :icon="['fas', 'xmark']" />
          </template>
          <template v-else-if="column.key === 'payment_method'">
            <a-tag
              v-if="record.tickets !== null"
              color="#EDEDFF"
              :style="{ color: '#2E2B9E', fontWeight: 600 }"
            >
              {{ record.tickets.payment_method.name.toUpperCase() }}
            </a-tag>
          </template>
          <template v-else-if="column.key === 'type'">
            <a-tag
              v-if="record.tickets !== null"
              color="#EDEDFF"
              :style="{ color: '#2E2B9E', fontWeight: 600 }"
            >
              {{ record.tickets.type_tickets == 'PHYSICAL' ? 'FISICA' : 'VIRTUAL' }}
            </a-tag>
          </template>
          <template v-else-if="column.key === 'action'">
            <a-dropdown trigger="['click']">
              <template #overlay>
                <a-menu>
                  <a-menu-item key="1" @click="redirectToForm(record.supplier)">
                    <font-awesome-icon :icon="['far', 'pen-to-square']" /> Editar
                  </a-menu-item>
                  <a-menu-item key="2">
                    <font-awesome-icon :icon="['far', 'clone']" /> Clonar
                  </a-menu-item>
                  <a-menu-item key="3">
                    <font-awesome-icon :icon="['fas', 'user-xmark']" /> Desactivar
                  </a-menu-item>
                  <a-menu-item key="4">
                    <font-awesome-icon :icon="['fas', 'dollar-sign']" /> Tarifario negociado
                  </a-menu-item>
                  <a-menu-item key="5">
                    <font-awesome-icon :icon="['fas', 'clock-rotate-left']" /> Historial de cambios
                  </a-menu-item>
                </a-menu>
              </template>
              <font-awesome-icon
                class="cursor-pointer"
                :style="{
                  fontSize: '19px',
                  padding: '6px',
                  borderRadius: '3px',
                  height: '1.5rem',
                  width: '1.5rem',
                }"
                :icon="['fas', 'ellipsis']"
              />
            </a-dropdown>
          </template>
        </template>
      </a-table>
      <CustomPagination
        v-show="!isLoading"
        v-model:current="pagination.current"
        v-model:pageSize="pagination.pageSize"
        :total="pagination.total"
        :disabled="data?.length === 0"
        :showSizeChanger="false"
        @change="onChange"
      />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import { useRouter } from 'vue-router';
  import { useTicketsList } from '@/modules/negotiations/supplier/tickets/composables/useTicketsList';
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import { useSearchSupplierTicketsFiltersStore } from '@/modules/negotiations/supplier/tickets/store/searchSupplierTicketsFilters.store';

  const {
    columns,
    data,
    pagination,
    isLoading,
    onChange,
    // statesResources,
    // downloadReport,
    // isLoadingReport,
  } = useTicketsList();
  const storeSearchSupplierTicket = useSearchSupplierTicketsFiltersStore();

  const router = useRouter();

  const getProgressStyle = (percent: number) => ({
    width: `${percent}%`,
    backgroundColor: percent === 100 ? '#07DF81' : '#D80404',
  });

  const redirectToForm = (supplier: { id: number }) => {
    router.push({
      path: `/negotiations/suppliers/tickets/edit/${supplier.id}`,
    });
  };

  function pluralize(total: number, singular: string, plural: string) {
    return `${total} ${total === 1 ? singular : plural}`;
  }

  const totalEntriesText = computed(() =>
    pluralize(pagination.value.total, 'Entrada añadida', 'Entradas añadidas')
  );
</script>

<style lang="scss">
  @import '@/scss/_variables.scss';

  .supplier-ticket-page-list {
    * {
      font-family: $font_general !important;
    }

    .text-center {
      text-align: center;
    }

    .header-text {
      font-weight: 600;
      font-size: 18px;
      align-items: center;
      padding: 12px 7px;
    }

    .navigation-buttons {
      text-align: center;
      align-items: flex-start;
      display: flex;
      justify-content: center;
      gap: 15px;
      padding: 12px 7px;
    }

    .rounded-button {
      border-radius: 50px !important;
    }

    .filters-section {
      padding: 20px 20px 10px 20px;
    }

    .filters-header {
      display: flex;
      justify-content: space-between;
      padding: 10px 0;
      font-size: 14px;
      font-weight: 600;
    }

    .download-results {
      color: #1284ed;
      font-weight: 600;
      font-size: 16px;
      line-height: 14px;
    }

    .ant-input-affix-wrapper {
      height: 35px;
    }

    .ant-select-selector {
      height: 35px !important;
    }

    .filters-container {
      display: flex;
      justify-content: space-between;

      .search-text {
        width: 308px;
        height: 48px;
        border-radius: 4px;
        padding: 12px 16px;
        background: #ffffff;
        border: 1px solid #575b5f;
      }

      .group-options {
        display: flex;
        gap: 1rem;
      }
    }

    .filter-label {
      font-weight: 500;
      font-size: 14px;
      margin-bottom: 5px;
    }

    .full-width {
      width: 100%;
    }

    .clear-filters {
      display: flex;
      justify-content: end;
      color: #bd0d12;
      gap: 5px;
    }

    .table-section {
      padding: 10px 20px 20px 20px;
    }

    .table-supplier-tickets {
      table {
        border-radius: 8px;
        border: 1px #e4e5e6 solid;
      }

      .ant-table-thead {
        tr {
          height: 72px;
        }

        .ant-table-cell {
          background: #e6ebf2;
          border-radius: 0 !important;
          font-weight: 500;
          font-size: 16px;
          line-height: 24px;
          vertical-align: middle;
          color: #575b5f;

          &::before {
            display: none !important;
          }
        }
      }

      .ant-table-tbody {
        .ant-table-cell {
          font-size: 13px;
        }
      }

      .ant-pagination {
        justify-content: center;
      }
    }

    .bold-text {
      font-weight: 600;
    }

    .progress-wrapper {
      display: grid;
      grid-template-columns: 80% auto;
      grid-gap: 8px;
      align-items: center;
    }

    .progress-container {
      display: flex;
      align-items: center;
      gap: 8px;
      background-color: #e0e0e0;
      border-radius: 8px;
      height: 12px;
      overflow: hidden;
      width: 100%;
      max-width: 400px;
    }

    .progress-bar {
      height: 100%;
      transition: width 0.3s ease;
    }

    .progress-percentage {
      font-size: 14px;
      color: #333;
    }

    .cursor-pointer {
      cursor: pointer;
    }

    .button-filter {
      width: 166px;
      height: 48px;
      gap: 8px;
      border-radius: 4px;
      padding: 8px 16px;
      border: 1px solid #575b5f;
      display: flex;
      justify-content: center;
      justify-items: center;
      align-items: center;
      cursor: pointer;
    }

    .button-filter-custom {
      width: 166px;
      height: 48px;
      gap: 8px;
      border-radius: 4px;
      padding: 8px 16px;
      border: 1px solid #1284ed;
      color: #1284ed;
      text-decoration: underline;
      display: flex;
      justify-content: center;
      justify-items: center;
      align-items: center;
      cursor: pointer;
    }

    .total-supplier {
      margin-bottom: 10px;
      font-weight: 500;
      font-size: 16px;
      line-height: 24px;
      vertical-align: middle;
      color: #575b5f;
    }
  }
</style>
