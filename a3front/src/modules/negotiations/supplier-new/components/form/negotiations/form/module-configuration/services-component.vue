<template>
  <div v-if="isEditMode || isCommercialInformationComplete" class="services-component">
    <EmptyStateFormGlobalComponent
      v-if="isEditMode && !getShowFormComponent(FormComponentEnum.MODULE_SERVICES)"
      title="Servicios"
      :formSpecific="FormComponentEnum.MODULE_SERVICES"
    />
    <div v-else>
      <div class="mt-2">
        <div class="title-form">
          <div>Servicios</div>
          <div
            v-if="getIsEditFormComponent(FormComponentEnum.MODULE_SERVICES)"
            class="edit-form"
            @click="handleShowForm"
          >
            Editar <font-awesome-icon :icon="['fas', 'pen-to-square']" />
          </div>
        </div>
        <div
          class="form-container"
          v-if="!getIsEditFormComponent(FormComponentEnum.MODULE_SERVICES)"
        >
          <div>
            <a-input
              allow-clear
              @change="handleSearch"
              v-model="searchQuery"
              class="input-field"
              placeholder="Buscar servicios"
            >
              <template #suffix> <IconSearch /> </template>
            </a-input>
          </div>
          <div class="action-buttons">
            <div>
              <font-awesome-icon :icon="['fas', 'filter']" />
              <span>Agregar servicio</span>
            </div>
            <div>
              <font-awesome-icon :icon="['fas', 'filter']" />
              <span>Añadir filtro</span>
            </div>
          </div>
        </div>
      </div>

      <template v-if="getIsEditFormComponent(FormComponentEnum.MODULE_SERVICES)">
        <div class="mt-3 pl-edit-data-summary">
          <div>
            <template v-for="row in summaryData">
              <div class="summary-container">
                <template v-for="column in columnsForSummary">
                  <span class="summary-title"> {{ column.title }}: </span>
                  <span class="summary-description">
                    {{ row[column.dataIndex] }}
                  </span>
                </template>
              </div>
            </template>
          </div>
          <div class="mt-2 show-more-services">Ver los 234 servicios</div>
        </div>
      </template>
      <template v-else>
        <div class="container-table">
          <div class="title-table">Listado de servicios</div>
          <div>
            <a-table
              :bordered="true"
              :columns="columns"
              :row-key="(record: any) => record.id"
              :data-source="sourceData"
              :pagination="false"
              :showSorterTooltip="false"
              :loading="loading"
            >
              <template #bodyCell="{ column, text, record }">
                <template v-if="column.dataIndex === 'status'">
                  <a-tag :class="record.status ? 'tag-state-active' : 'tag-state-inactive'">
                    {{ record.status ? 'Activo' : 'Inactivo' }}
                  </a-tag>
                </template>
                <template v-if="column.dataIndex === 'pax'">
                  <a-tag class="tag-state-pax">
                    {{ record.pax }}
                  </a-tag>
                </template>
                <template v-if="column.dataIndex === 'actions'">
                  <a-dropdown :trigger="['click']">
                    <template #overlay>
                      <a-menu>
                        <a-menu-item key="1">Cambiar estado</a-menu-item>
                        <a-menu-item key="2">Ver componente</a-menu-item>
                      </a-menu>
                    </template>
                    <a class="ant-dropdown-link" @click.prevent>
                      <font-awesome-icon class="actions" :icon="['fas', 'ellipsis']" />
                    </a>
                  </a-dropdown>
                </template>
              </template>
            </a-table>
            <div class="container-footer">
              <div class="group-buttons">
                <div>
                  <a-button class="a-btn-cancel" type="default" @click="handleClose">
                    Cancelar
                  </a-button>
                </div>
                <div>
                  <a-button class="a-btn-save" type="primary" @click="handleSave">
                    Guardar datos
                  </a-button>
                </div>
              </div>
              <div>
                <a-pagination
                  :showSizeChanger="false"
                  :showLessItems="true"
                  :hideOnSinglePage="true"
                  v-model:current="currentPage"
                  v-model:pageSize="pageSize"
                  :total="total"
                  :show-quick-jumper="false"
                  @change="handleChangePagination"
                />
              </div>
            </div>
          </div>
        </div>
      </template>
    </div>
  </div>
  <!-- <ServicesListComponent /> -->
</template>

<script setup lang="ts">
  import IconSearch from '@/modules/negotiations/supplier-new/icons/icon-search.vue';
  import { useServicesComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/module-configuration/services.composable';
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
  import EmptyStateFormGlobalComponent from '@/modules/negotiations/supplier-new/components/global/empty-state-form-global-component.vue';
  import { useProgressiveFormFlowComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/progressive-form-flow.composable';

  defineOptions({
    name: 'ServicesComponent',
  });

  const {
    columns,
    currentPage,
    pageSize,
    total,
    sourceData,
    loading,
    isEditMode,
    searchQuery,
    handleSearch,
    getShowFormComponent,
    getIsEditFormComponent,
    columnsForSummary,
    summaryData,
    handleChangePagination,
    handleSave,
    handleShowForm,
    handleClose,
  } = useServicesComposable();

  const { isCommercialInformationComplete } = useProgressiveFormFlowComposable();
</script>

<style lang="scss">
  @import '@/scss/_variables.scss';

  .services-component {
    border-top: 1px solid #babcbd;
    padding-top: 1rem;
    margin-top: 0.5rem;

    .show-more-services {
      font-size: 16px;
      font-weight: 500;
      color: #1284ed;
      text-decoration: underline;
    }

    .summary-container {
      display: flex;
      gap: 12px;
    }

    .summary-title {
      font-size: 14px;
      font-weight: 600;
      color: #7e8285;
    }

    .summary-description {
      font-size: 14px;
      font-weight: 400;
      color: #7e8285;
      margin-right: 10px;
    }

    .title-form {
      font-weight: 600;
      font-size: 16px;
      line-height: 23px;
      color: #2f353a;
      margin-bottom: 1rem;
      display: flex;
      gap: 0.75rem;

      .edit-form {
        font-weight: 500;
        font-size: 16px;
        line-height: 24px;
        color: $color-blue;
        cursor: pointer;
      }
    }

    .form-container {
      display: flex;
      justify-content: space-between;
      align-items: center;

      .ant-input {
        height: 48px;
      }
    }

    .input-field {
      width: 308px;
    }

    .actions {
      height: 24px;
      width: 24px;
      color: #101828;
      cursor: pointer;
    }

    .action-buttons {
      display: flex;
      color: #1284ed;
      cursor: pointer;
      gap: 24px !important;

      span {
        font-weight: 500;
        font-size: 16px;
        line-height: 32px;
        text-decoration: underline;
        text-decoration-style: solid;
      }
    }

    .title-table {
      font-weight: 500;
      font-size: 16px;
      line-height: 24px;
      vertical-align: middle;
      color: #575b5f;
      margin-bottom: 1rem;
    }

    .container-table {
      margin-top: 1rem;
    }

    thead {
      th {
        background: #e6ebf2 !important;
        height: 56px !important;

        .ant-table-column-title {
          font-weight: 500;
          font-size: 16px;
          line-height: 24px;
          vertical-align: middle;
          color: #575b5f;
        }

        .ant-table-column-sorter-inner {
          top: 10.79px;
          left: 17.33px;
          border-width: 1.2px;
          color: #575757;
        }
      }
    }

    .tag-state-pax {
      height: 32px;
      border-radius: 4px;
      padding: 4px 8px;
      background: #ededff;
      border-color: #ededff;
      color: #2e2b9e;
      font-weight: 600;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0;
      vertical-align: middle;
    }

    .tag-state-active {
      height: 32px;
      border-radius: 4px;
      padding: 4px 8px;
      background: $color-info-light;
      border-color: $color-info-light;
      color: #288a5f;
      font-weight: 600;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0;
      vertical-align: middle;
    }

    .tag-state-inactive {
      height: 32px;
      border-radius: 4px;
      padding: 4px 8px;
      background: #fff1f0;
      border-color: #fff1f0;
      color: #dd394a;
      font-weight: 600;
      font-size: 16px;
      line-height: 24px;
      letter-spacing: 0;
      vertical-align: middle;
    }

    .container-footer {
      margin-top: 1.5rem;
      display: flex;
      justify-content: space-between;
      align-items: center;

      .a-btn-cancel {
        width: 118px;
        height: 48px;
        border-width: 1px;
        border-radius: 5px;
        padding: 12px 24px;
        color: $color-black;
        border-color: $color-black !important;
        font-weight: 600;
      }

      .a-btn-save {
        width: 159px;
        height: 48px;
        gap: 8px;
        border-radius: 5px;
        border-width: 1px;
        color: #ffffff;
        background: #2f353a;
        border-color: #2f353a !important;

        &:hover,
        &:active {
          color: #ffffff;
          background: #2f353a;
          border-color: #2f353a !important;
        }
      }

      .group-buttons {
        display: flex;
        gap: 1rem;
      }

      .ant-pagination li:nth-child(3) {
        border-left-color: $color-black-5 !important;
        border-right-color: $color-black-5 !important;
      }

      .ant-pagination li:nth-last-child(3) {
        border-right-color: $color-black-5 !important;
      }

      .ant-pagination-prev {
        margin-inline-end: 0;
        border-radius: 4px 0 0 4px;
        border: 1px solid $color-black-5;

        &:hover {
          border-radius: 0 !important;
          border-right-color: $color-black-5;
        }

        &:active {
          border-radius: 0 !important;
          border-right-color: $color-black-5;
        }
      }

      .ant-pagination-next {
        border: 1px solid $color-black-5;
        margin-inline-end: 0;
        border-radius: 0 4px 4px 0;

        &:hover {
          border-radius: 0 !important;
          border-right-color: $color-black-5;
        }

        &:active {
          border-radius: 0 !important;
          border-right-color: $color-black-5;
        }
      }

      .ant-pagination-item {
        border-top-color: $color-black-5 !important;
        border-bottom-color: $color-black-5 !important;
        margin-inline-end: 0;
        border-radius: 0 !important;
      }

      .ant-pagination-item-after-jump-prev {
        border-right-color: $color-black-5 !important;
      }

      .ant-pagination-jump-next {
        border: 1px solid $color-black-5 !important;
        margin-inline-end: 0;
        border-radius: 0 !important;

        &:active {
          background: transparent !important;
        }

        &:hover {
          background: transparent !important;
        }
      }

      .ant-pagination-jump-prev {
        border: 1px solid $color-black-5 !important;
        margin-inline-end: 0;
        border-radius: 0 !important;
      }

      .ant-pagination-item-link-icon {
        color: $color-black-5 !important;

        &:hover {
          color: #939496;
        }
      }

      .ant-pagination-item-active {
        background: #939496;
        border-color: $color-black-5;
        color: #ffffff;

        a {
          color: #ffffff;
        }
      }
    }
  }
</style>
<style scoped lang="scss">
  @import '@/scss/components/negotiations/_supplierForm.scss';
</style>
