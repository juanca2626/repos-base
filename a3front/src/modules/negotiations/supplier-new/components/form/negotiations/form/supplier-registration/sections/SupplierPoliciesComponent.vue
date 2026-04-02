<template>
  <div class="module-negotiations supplier-policies-component">
    <div class="container">
      <PoliciesEmptyStateFormComponent
        v-if="isEditMode && !getShowFormComponent(FormComponentEnum.MODULE_POLICIES)"
        title="Políticas"
        :formSpecific="FormComponentEnum.MODULE_POLICIES"
        @onAddInformation="activePanelItemPolicy"
      />
      <div v-else>
        <div class="title-section-form">
          <div>Políticas</div>
        </div>

        <div class="empty-policies" v-if="sourceData.length === 0">
          No tienes políticas configuradas
        </div>

        <div class="container-actions">
          <div>
            <a-button type="default" @click="handleAddPolicy" class="btn-add-policy-outlined">
              <font-awesome-icon :icon="['fas', 'plus']" />
              Agregar política
            </a-button>
          </div>
          <div class="options-download" v-if="sourceData.length > 0">
            <CustomDownloadIcon :width="17" :height="17" :strokeWidth="2.2" />
            <span>Descargar políticas</span>
          </div>
        </div>

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
              <template v-else-if="column.dataIndex === 'validity'">
                {{ formatDate(record.date_from) }} - {{ formatDate(record.date_to) }}
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
      </div>
    </div>

    <!-- Modal de confirmación para editar -->
    <a-modal
      v-model:open="isEditConfirmModalOpen"
      centered
      :closable="true"
      width="590px"
      :footer="null"
      wrapClassName="edit-confirm-modal"
    >
      <span class="text-question-edit"> ¿Estás seguro de editar los datos? </span>

      <div class="modal-buttons">
        <a-button @click="handleCancelEdit" class="btn-cancel-edit"> Cancelar </a-button>
        <a-button type="primary" danger @click="handleConfirmEdit" class="btn-confirm-edit">
          Editar datos
        </a-button>
      </div>
    </a-modal>

    <!-- Modal de confirmación para clonar -->
    <a-modal
      v-model:open="isCloneConfirmModalOpen"
      centered
      :closable="true"
      width="590px"
      :footer="null"
      wrapClassName="clone-confirm-modal"
    >
      <span class="text-question-clone"> ¿Estás seguro de copiar los datos de la política? </span>

      <div class="modal-buttons">
        <a-button @click="handleCancelClone" class="btn-cancel-clone"> Cancelar </a-button>
        <a-button
          type="primary"
          @click="handleConfirmClone"
          class="btn-confirm-clone"
          :loading="isCloning"
        >
          Copiar datos
        </a-button>
      </div>
    </a-modal>

    <!-- Modal de confirmación para eliminar -->
    <a-modal
      v-model:open="isDeleteConfirmModalOpen"
      centered
      :closable="true"
      width="590px"
      :footer="null"
      wrapClassName="delete-confirm-modal"
    >
      <span class="text-question-delete"> ¿Estás seguro de eliminar esta política? </span>

      <p class="text-description-delete">
        Esta acción no se puede deshacer. La política dejará de estar disponible.
        <strong>¿Desea continuar?</strong>
      </p>

      <div class="modal-buttons-delete">
        <a-button @click="handleCancelDelete" class="btn-cancel-delete"> Cancelar </a-button>
        <a-button
          type="primary"
          danger
          @click="handleConfirmDelete"
          class="btn-confirm-delete"
          :loading="isDeleting"
        >
          Eliminar
        </a-button>
      </div>
    </a-modal>
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
    getRowClassName,
    handleEditPolicy,
    handleAddPolicy,
    handleDestroy,
    activePanelItemPolicy,
    handleClone,
    formatDate,
    isEditConfirmModalOpen,
    handleConfirmEdit,
    handleCancelEdit,
    isCloneConfirmModalOpen,
    handleConfirmClone,
    handleCancelClone,
    isCloning,
    // Delete modal
    isDeleteConfirmModalOpen,
    handleConfirmDelete,
    handleCancelDelete,
    isDeleting,
  } = useSupplierPoliciesComposable();
</script>

<style lang="scss">
  @import '@/scss/_variables.scss';

  .custom-table-row-striped {
    background-color: $color-blue-ultra-light;
  }

  .text-question-edit,
  .text-question-clone,
  .text-question-delete {
    font-size: 24px !important;
    line-height: 36px;
    font-weight: 700;
    color: #2f353a;
    display: block;
    text-align: left;
  }

  .text-description-delete {
    font-size: 16px;
    font-weight: 400;
    color: #667085;
    margin-top: 16px;
    margin-bottom: 0;
    line-height: 24px;
    text-align: left;

    strong {
      font-weight: 700;
      color: #2f353a;
    }
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
          height: 40px !important;
          padding: 5px 16px !important;
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

  .btn-add-policy-outlined {
    min-width: 180px;
    height: 48px;
    border: 1px solid #2f353a;
    border-radius: 5px;
    background-color: #ffffff;
    color: #2f353a;
    font-size: 16px;
    font-weight: 500;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;

    &:hover {
      border-color: #2f353a !important;
      color: #2f353a !important;
      background-color: #f9fafb !important;
    }
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

  .modal-buttons {
    display: flex;
    justify-content: center;
    gap: 12px;
    margin-top: 32px;

    .btn-cancel-edit,
    .btn-confirm-edit,
    .btn-cancel-clone,
    .btn-confirm-clone {
      min-width: 130px;
      height: 48px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 8px;
      padding: 12px 24px;
      font-family: Inter, sans-serif;
    }

    .btn-cancel-edit,
    .btn-cancel-clone {
      border: 1px solid #2f353a;
      color: #2f353a;
      background-color: #fff;
      box-shadow: 0px 1px 2px 0px rgba(16, 24, 40, 0.05);

      &:hover {
        border-color: #2f353a !important;
        color: #2f353a !important;
        background-color: #f9fafb !important;
      }
    }

    .btn-confirm-edit,
    .btn-confirm-clone {
      background-color: #d92d20;
      border-color: #d92d20;
      color: #fff;
      box-shadow: 0px 1px 2px 0px rgba(16, 24, 40, 0.05);

      &:hover {
        background-color: #b42318 !important;
        border-color: #b42318 !important;
      }
    }
  }

  .modal-buttons-delete {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    margin-top: 32px;

    .btn-cancel-delete,
    .btn-confirm-delete {
      min-width: 120px;
      height: 48px;
      font-size: 16px;
      font-weight: 600;
      border-radius: 8px;
      padding: 12px 20px;
      font-family: Inter, sans-serif;
    }

    .btn-cancel-delete {
      border: 1px solid #2f353a;
      color: #2f353a;
      background-color: #fff;
      box-shadow: 0px 1px 2px 0px rgba(16, 24, 40, 0.05);

      &:hover {
        border-color: #2f353a !important;
        color: #2f353a !important;
        background-color: #f9fafb !important;
      }
    }

    .btn-confirm-delete {
      background-color: #d92d20;
      border-color: #d92d20;
      color: #fff;
      box-shadow: 0px 1px 2px 0px rgba(16, 24, 40, 0.05);

      &:hover {
        background-color: #b42318 !important;
        border-color: #b42318 !important;
      }
    }
  }
</style>
<style lang="scss">
  :deep(.edit-confirm-modal) {
    .ant-modal-content {
      padding: 24px;
      border-radius: 12px;
      box-shadow:
        0px 8px 8px -4px rgba(16, 24, 40, 0.03),
        0px 20px 24px -4px rgba(16, 24, 40, 0.08);
    }

    .ant-modal-header {
      padding: 0;
      margin-bottom: 0;
      border-bottom: none;
      text-align: center;
    }

    .ant-modal-title {
      font-size: 18px;
      font-weight: 600;
      color: #101828;
      text-align: center;
      line-height: 28px;
      width: 100%;
    }

    .ant-modal-body {
      padding: 0;
      margin-top: 0;
    }

    .ant-modal-close {
      top: 20px;
      right: 20px;
      width: 24px;
      height: 24px;

      .ant-modal-close-x {
        width: 24px;
        height: 24px;
        line-height: 24px;
        font-size: 16px;
        color: #667085;
      }
    }
  }

  :deep(.clone-confirm-modal) {
    .ant-modal-content {
      padding: 24px;
      border-radius: 12px;
      box-shadow:
        0px 8px 8px -4px rgba(16, 24, 40, 0.03),
        0px 20px 24px -4px rgba(16, 24, 40, 0.08);
    }

    .ant-modal-header {
      padding: 0;
      margin-bottom: 0;
      border-bottom: none;
      text-align: center;
    }

    .ant-modal-title {
      font-size: 18px;
      font-weight: 600;
      color: #101828;
      text-align: center;
      line-height: 28px;
      width: 100%;
    }

    .ant-modal-body {
      padding: 0;
      margin-top: 0;
    }

    .ant-modal-close {
      top: 20px;
      right: 20px;
      width: 24px;
      height: 24px;

      .ant-modal-close-x {
        width: 24px;
        height: 24px;
        line-height: 24px;
        font-size: 16px;
        color: #667085;
      }
    }
  }

  :deep(.delete-confirm-modal) {
    .ant-modal-content {
      padding: 24px;
      border-radius: 12px;
      box-shadow:
        0px 8px 8px -4px rgba(16, 24, 40, 0.03),
        0px 20px 24px -4px rgba(16, 24, 40, 0.08);
    }

    .ant-modal-header {
      padding: 0;
      margin-bottom: 0;
      border-bottom: none;
      text-align: center;
    }

    .ant-modal-title {
      font-size: 18px;
      font-weight: 600;
      color: #101828;
      text-align: center;
      line-height: 28px;
      width: 100%;
    }

    .ant-modal-body {
      padding: 0;
      margin-top: 0;
    }

    .ant-modal-close {
      top: 20px;
      right: 20px;
      width: 24px;
      height: 24px;

      .ant-modal-close-x {
        width: 24px;
        height: 24px;
        line-height: 24px;
        font-size: 16px;
        color: #667085;
      }
    }
  }
</style>
