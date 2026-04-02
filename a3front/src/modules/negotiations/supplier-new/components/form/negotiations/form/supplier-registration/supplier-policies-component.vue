<template>
  <div
    v-if="isEditMode || isServicesCompleteTemp"
    class="module-negotiations supplier-policies-component divider-section"
  >
    <div class="container">
      <PoliciesEmptyStateFormComponent
        v-if="isEditMode && !getShowFormComponent(FormComponentEnum.MODULE_POLICIES)"
        title="Políticas"
        :formSpecific="FormComponentEnum.MODULE_POLICIES"
        @onAddInformation="activePanelItemPolicy"
      />
      <div v-else>
        <div>
          <div class="title-section-form title-section-form-flex">
            <div>Políticas</div>
            <template v-if="getIsEditFormComponent(FormComponentEnum.MODULE_POLICIES)">
              <div class="section-form-button-edit" @click="handleShowForm">
                Editar <font-awesome-icon :icon="['fas', 'pen-to-square']" />
              </div>
            </template>
          </div>

          <template v-if="!getIsEditFormComponent(FormComponentEnum.MODULE_POLICIES)">
            <div class="empty-policies" v-if="sourceData.length === 0">
              No tienes políticas configuradas
            </div>
            <div class="container-actions">
              <div>
                <a-button
                  type="primary"
                  @click="handleAddPolicy"
                  :class="[
                    'btn-add-policy',
                    sourceData.length > 0 ? 'button-primary-add-record' : 'button-action-black',
                  ]"
                >
                  <font-awesome-icon :icon="['fas', 'plus']" />
                  Agregar política
                </a-button>
              </div>
              <div class="options-download" v-if="sourceData.length > 0">
                <CustomDownloadIcon :width="17" :height="17" :strokeWidth="2.2" />
                <span>Descargar políticas</span>
              </div>
            </div>
          </template>
        </div>

        <template v-if="getIsEditFormComponent(FormComponentEnum.MODULE_POLICIES)">
          <div class="mt-3 mb-3">
            <template v-for="row in sourceData">
              <div class="edit-data-summary pl-edit-data-summary">
                <template v-for="column in columnsForSummary">
                  <span class="summary-title">
                    <template v-if="column.dataIndex === 'cancellationDescriptions'">
                      <template v-if="row.cancellationDescriptions.length > 0">
                        {{ column.title }}:
                      </template>
                    </template>
                    <template v-else> {{ column.title }}: </template>
                  </span>
                  <span
                    class="summary-description"
                    :class="{ 'ellipsis-text': column.dataIndex === 'cancellationDescriptions' }"
                  >
                    <template v-if="column.dataIndex === 'pax'">
                      <span>
                        {{ `${row.pax_min} - ${row.pax_max}` }}
                      </span>
                    </template>
                    <template v-else-if="column.dataIndex === 'cancellationDescriptions'">
                      <span>
                        {{ getSummaryCancellationDescription(row) }}
                      </span>
                    </template>
                    <template v-else>
                      {{ row[column.dataIndex] }}
                    </template>
                  </span>
                </template>
              </div>
            </template>
          </div>
        </template>
        <template v-else>
          <div v-if="sourceData.length > 0">
            <a-table
              :bordered="false"
              :columns="columns"
              :row-key="(record: any) => record.id"
              :data-source="sourceData"
              :pagination="false"
              :showSorterTooltip="false"
              :loading="isLoading"
              class="custom-policies-table-list"
              :row-class-name="getRowClassName"
            >
              <template #bodyCell="{ column, text, record }">
                <template v-if="column.dataIndex === 'pax'">
                  <div class="tag-state-pax">
                    {{ `${record.pax_min} - ${record.pax_max} pax` }}
                  </div>
                </template>
                <template v-else-if="column.dataIndex === 'cancellationDescriptions'">
                  <p v-for="row in record.cancellationDescriptions">
                    {{ row }}
                  </p>
                </template>
                <template v-else-if="column.dataIndex === 'actions'">
                  <div class="container-table-actions">
                    <a-button type="link" @click="handleEditPolicy(record.id)">
                      <font-awesome-icon :icon="['far', 'pen-to-square']" class="btn-icon" />
                    </a-button>
                    <a-button type="link" @click="handleDestroy(record.id)">
                      <font-awesome-icon :icon="['far', 'fa-trash-can']" class="btn-icon" />
                    </a-button>
                    <a-button type="link" @click="handleClone(record.id)">
                      <font-awesome-icon :icon="['far', 'clone']" class="btn-icon" />
                    </a-button>
                  </div>
                </template>
              </template>
            </a-table>
          </div>
        </template>
      </div>
    </div>
    <contextHolder />
  </div>
</template>

<script setup lang="ts">
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
  import PoliciesEmptyStateFormComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/policies/policies-empty-state-form-component.vue';
  import CustomDownloadIcon from '@/modules/negotiations/supplier/components/icons/CustomDownloadIcon.vue';
  import { useSupplierPoliciesComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/supplier-policies.composable';

  defineOptions({
    name: 'SupplierPoliciesComponent',
  });

  const {
    getShowFormComponent,
    columns,
    sourceData,
    isLoading,
    isEditMode,
    getIsEditFormComponent,
    columnsForSummary,
    handleShowForm,
    contextHolder,
    getRowClassName,
    handleEditPolicy,
    handleAddPolicy,
    handleDestroy,
    getSummaryCancellationDescription,
    activePanelItemPolicy,
    handleClone,
    isServicesCompleteTemp,
  } = useSupplierPoliciesComposable();
</script>

<style lang="scss">
  @import '@/scss/_variables.scss';

  .custom-table-row-striped {
    background-color: $color-blue-ultra-light;
  }

  .supplier-policies-component {
    .container {
      margin-top: 20px;
    }

    .custom-policies-table-list {
      table {
        border: 0px;
        border-radius: 0px;
      }

      .ant-table-container {
        border-radius: 0px;
        overflow: hidden;
      }

      .ant-table-thead {
        .ant-table-cell {
          background: $color-gray-ice;
          border-radius: 0 !important;
          font-size: 16px;
          font-weight: 500;
          color: $color-black-2;
          height: 56px !important;

          &::before {
            display: none !important;
          }
        }
      }

      .ant-table-column-has-sorters {
        background-color: $color-gray-blue-1 !important;

        &:hover {
          background-color: $color-gray-blue-2 !important;
        }
      }

      .ant-table-column-sort {
        background-color: $color-gray-blue-1 !important;

        &:hover {
          background-color: $color-gray-blue-2 !important;
        }
      }

      .ant-table-tbody {
        .ant-table-cell {
          height: 104px !important;
          font-size: 16px;
          font-weight: 400;
          color: $color-black-graphite;
        }
      }
    }
  }
</style>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .btn-add-policy {
    width: 200px;
  }

  .empty-policies {
    font-size: 16px;
    font-weight: 400;
    color: $color-black;
    margin-top: 15px;
  }

  .container-actions {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 15px;
    margin-bottom: 15px;
  }

  .container-table-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;

    .btn-icon {
      width: 21px;
      height: 21px;
      color: $color-blue;
    }
  }

  .options-download {
    display: flex;
    align-items: center;
    align-content: center;
    gap: 4px;
    cursor: pointer;

    span {
      font-size: 16px;
      font-weight: 500;
      color: #575b5f;
    }
  }
</style>
