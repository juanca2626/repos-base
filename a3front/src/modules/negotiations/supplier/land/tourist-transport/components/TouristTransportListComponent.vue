<template>
  <a-typography-text type="secondary" class="mb-2 mt-3"> Listado de proveedores</a-typography-text>
  <a-table
    :columns="columns"
    :data-source="data"
    :pagination="false"
    :loading="isLoading"
    @change="handleTableChange"
    row-key="id"
  >
    <template #headerCell="{ column }">
      <span>
        {{ column.title }}
      </span>
    </template>
    <template #bodyCell="{ column, record }">
      <template v-if="column.key === 'user'">
        <strong class="font-bold">{{ record.user.code ?? 'DOE' }}</strong>
        <p class="font-bold">{{ record.created_at_date }}</p>
      </template>
      <template v-else-if="column.key === 'status'">
        <a-tag v-if="isActiveSupplier(record.status.value)" class="tag-status success-light">
          Activo
        </a-tag>
        <a-tag
          v-else-if="isPendingDeactivationSupplier(record.status.value)"
          class="tag-status success-warning"
        >
          Pendiente de desactivación
        </a-tag>
        <div v-else>
          <a-tag class="tag-status" color="error"> Desactivado </a-tag>
          <small class="view-info" v-if="record.deactivation_reason">
            <ToolTipInformationComponent title="Motivo de desactivación">
              <template #content>
                <p>{{ record.deactivation_reason.reasons }}</p>
              </template>
              <template #footer>
                <div class="container-deactivation-reason">
                  <span>{{ record.deactivation_reason.user.code ?? 'DOE' }} desactivó</span>
                  <span>{{ record.deactivation_reason.date }}</span>
                </div>
              </template>
              <font-awesome-icon :icon="['fas', 'circle-info']" />
              <small class="view-info-text">Ver información</small>
            </ToolTipInformationComponent>
          </small>
        </div>
      </template>
      <template v-else-if="column.key === 'code'">{{ record.code }}</template>
      <template v-else-if="column.key === 'name'">
        <span class="view-info-progress">
          <ToolTipInformationComponent title="Observaciones del proveedor">
            <template #content>
              <div class="observations-list">
                <template v-for="(group_module, index) in record.modules_percentage.group_modules">
                  <h3>{{ index + 1 }}. {{ group_module.name }}</h3>
                  <ul>
                    <li v-for="module in group_module.modules">
                      <template v-if="module.status">
                        <font-awesome-icon
                          :icon="['fas', 'circle-check']"
                          class="status-complete"
                        />
                      </template>
                      <template v-else>
                        <font-awesome-icon
                          :icon="['fas', 'circle-xmark']"
                          class="status-incomplete"
                        />
                      </template>
                      {{ module.name }}
                    </li>
                  </ul>
                </template>
              </div>
            </template>
            <font-awesome-icon :icon="['fas', 'circle-info']" />
          </ToolTipInformationComponent>
        </span>
        {{ record.name }}
        <a-progress
          class="progress-bar-supplier"
          :percent="record.modules_percentage.percentage"
          :stroke-color="getProgressColor(record.modules_percentage.percentage)"
        >
          <template #format="percent">
            <span :style="{ color: getProgressColor(record.modules_percentage.percentage) }"
              >{{ percent }} %</span
            >
          </template>
        </a-progress>
      </template>
      <template v-else-if="column.key === 'document'">
        {{ record.document }}
      </template>
      <template v-else-if="column.key === 'quantity_type_unit_transports'">
        <a-space>
          <template v-for="quantityTypeUnitTransport in record.quantity_type_unit_transports">
            <a-tag class="w-auto h-auto info-light">
              <p class="type-unit-name">{{ quantityTypeUnitTransport.name }}</p>
              <a-badge :count="quantityTypeUnitTransport.quantity" />
            </a-tag>
          </template>
        </a-space>
      </template>
      <template v-else-if="column.key === 'options'">
        <template v-if="showAllOptions(record.modules_percentage.percentage)">
          <div class="dropdown-options-table">
            <a-dropdown>
              <template #overlay>
                <a-menu class="dropdown-options-table-menu">
                  <a-menu-item key="1" @click="goToEdit(record.id)">
                    <font-awesome-icon :icon="['far', 'pen-to-square']" />
                    <span>Editar</span>
                  </a-menu-item>
                  <a-menu-item key="2">
                    <font-awesome-icon :icon="['far', 'clone']" />
                    <span>Clonar</span>
                  </a-menu-item>
                  <a-menu-item key="3">
                    <font-awesome-icon :icon="['fas', 'user-xmark']" />
                    <span>Desactivar</span>
                  </a-menu-item>
                  <a-divider />
                  <a-menu-item key="4">
                    <font-awesome-icon :icon="['fas', 'dollar-sign']" />
                    <span>Tarifario negociado</span>
                  </a-menu-item>
                  <a-divider />
                  <a-menu-item key="5">
                    <font-awesome-icon :icon="['fas', 'clock-rotate-left']" />
                    <span>Historial de cambios</span>
                  </a-menu-item>
                </a-menu>
              </template>
              <a-button type="link" class="btn-option-link btn-config">
                <IconMenuOpen />
              </a-button>
            </a-dropdown>
          </div>
        </template>
        <template v-else>
          <a-button type="link" class="btn-option-link" @click="goToEdit(record.id)">
            <font-awesome-icon class="btn-edit-icon" :icon="['far', 'pen-to-square']" />
          </a-button>
        </template>
      </template>
    </template>
  </a-table>
  <CustomPagination
    v-model:current="pagination.current"
    v-model:pageSize="pagination.pageSize"
    :total="pagination.total"
    :disabled="data?.length === 0"
    @change="onChange"
  />
</template>
<script setup lang="ts">
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import ToolTipInformationComponent from '@/modules/negotiations/supplier/components/ToolTipInformationComponent.vue';
  import { useTouristTransportsList } from '@/modules/negotiations/supplier/land/tourist-transport/composables/useTouristTransportsList';
  import IconMenuOpen from '@/modules/negotiations/country-calendar/configuration/icons/IconMenuOpen.vue';

  const {
    columns,
    data,
    pagination,
    isLoading,
    onChange,
    handleTableChange,
    getProgressColor,
    isActiveSupplier,
    isPendingDeactivationSupplier,
    showAllOptions,
    goToEdit,
  } = useTouristTransportsList();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .btn-config {
    background-color: $color-black-8;
    border: 3px;
    border-radius: 0.5px;
  }

  .btn-config:hover .icon-hover {
    fill: $color-primary-strong;
  }

  .btn-edit-icon {
    font-size: 18px;
  }

  .btn-option-link {
    color: $color-black;
    font-size: 18px;
    font-weight: 600;
    display: inline-flex;
  }

  .container-deactivation-reason {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 8px;
  }

  .type-unit-name {
    font-weight: 700;
  }

  /* Estilos adicionales para las observaciones */
  .observations-list {
    list-style-type: none;
    padding-left: 0;
  }

  .observations-list h3 {
    font-size: 0.75rem !important;
    margin-bottom: 8px;
    font-weight: bold;
  }

  .observations-list ul {
    list-style-type: none;
    padding-left: 20px;
  }

  .observations-list li {
    margin-bottom: 4px;
    display: flex;
    align-items: center;
    font-size: 0.75rem;
  }

  .observations-list .status-incomplete {
    color: #ff4d4f;
    margin-right: 8px;
  }

  .observations-list .status-complete {
    color: #07df81;
    margin-right: 8px;
  }

  .dropdown-options-table-menu {
    border-radius: 0 !important;
    padding: 0 !important;
    :deep(.ant-dropdown-menu-item) {
      padding: 8px;

      .ant-dropdown-menu-title-content {
        margin-left: 10px !important;
        margin-right: 10px !important;
        svg {
          margin-right: 10px;
          font-size: 18px;
        }
        span {
          font-size: 16px;
        }
      }
    }

    .ant-divider {
      margin: 0 !important;
    }
  }
</style>
