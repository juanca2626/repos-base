<template>
  <div class="mt-4">
    <ListInfoPanelComponent :isTransportVehicleActive="false" />
  </div>

  <div class="table-negotiation-services mt-3">
    <a-table
      :columns="columns"
      :data-source="data"
      :pagination="false"
      row-key="id"
      :scroll="{ x: true }"
    >
      <template #headerCell="{ column }">
        <template v-if="column.key === 'statusDni'">
          <span class="d-block"> DOCUMENTO </span>
          <span> DE IDENTIDAD </span>
        </template>
        <template v-else-if="column.key === 'statusDriverLicense'">
          <span class="d-block"> LICENCIA DE </span>
          <span> CONDUCIR </span>
        </template>
        <template v-else-if="column.key === 'statusCriminalRecord'">
          <span class="d-block"> ANTEC. </span>
          <span> PENALES </span>
        </template>
        <template v-else-if="column.key === 'statusPoliceRecord'">
          <span class="d-block"> ANTEC. </span>
          <span> POLICIALES </span>
        </template>
        <template v-else-if="column.key === 'statusDriverRecord'">
          <span class="d-block"> RECORD DEL </span>
          <span> CONDUCTOR </span>
        </template>
        <template v-else>
          <span>
            {{ column.title }}
          </span>
        </template>
      </template>
      <template #bodyCell="{ column, record }">
        <template v-if="getDriverDocumentKey(column.key)">
          <DocumentStatusComponent
            :technicalSheetType="TypeTechnicalSheetEnum.VEHICLE_DRIVER"
            :status="record.driverDocuments[getDriverDocumentKey(column.key)].status"
            :date="record.driverDocuments[getDriverDocumentKey(column.key)].expirationDate"
            :observations="record.driverDocuments[getDriverDocumentKey(column.key)].lastObservation"
            :extension="record.driverDocuments[getDriverDocumentKey(column.key)].extension"
            @onDocumentStatus="
              handleDocumentStatus(record.driverDocuments[getDriverDocumentKey(column.key)])
            "
            @onExtensionSuccessNotify="
              (extensionId: string) => handleExtensionSuccessNotify(extensionId, record)
            "
          />
        </template>
        <template v-else-if="column.key === 'action'">
          <a-dropdown :trigger="['click']" placement="bottomRight">
            <font-awesome-icon :icon="['fas', 'ellipsis-vertical']" class="icon-more-options" />

            <template #overlay>
              <a-menu class="menu-more-options">
                <a-menu-item @click="handleEdit(record)">
                  <div class="container-menu-item">
                    <font-awesome-icon :icon="['far', 'pen-to-square']" />
                    <span class="text-option-menu-item"> Editar </span>
                  </div>
                </a-menu-item>
                <a-menu-item @click="handleDestroy(record.id)">
                  <div class="container-menu-item">
                    <font-awesome-icon :icon="['far', 'trash-can']" />
                    <span class="text-option-menu-item"> Eliminar </span>
                  </div>
                </a-menu-item>
                <a-menu-item>
                  <div class="container-menu-item" @click="handleDocumentExtension(record)">
                    <font-awesome-icon :icon="['far', 'calendar-plus']" />
                    <span class="text-option-menu-item">Sol. Prórroga</span>
                  </div>
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
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
  </div>
  <contextHolder />

  <TransportDocumentFormComponent
    v-model:showDrawerForm="showDrawerDriverDocumentForm"
    :typeTechnicalSheet="TypeTechnicalSheetEnum.VEHICLE_DRIVER"
    :selectedDocument="selectedDocument"
  />

  <TransportDocumentReviewFormComponent
    v-model:showDrawerForm="showDrawerDriverDocumentReviewForm"
    :typeTechnicalSheet="TypeTechnicalSheetEnum.VEHICLE_DRIVER"
    :documentId="selectedDocument.id"
  />

  <TransportDocumentExtensionFormComponent
    v-model:showDrawerForm="showDrawerDocumentExtensionForm"
    :parentId="supplierVehicleDriverId"
    :documentExtensionInfo="documentExtensionInfo"
    :typeTechnicalSheet="TypeTechnicalSheetEnum.VEHICLE_DRIVER"
  />

  <ActiveExtensionNotifyComponent
    v-model:showModal="showActiveExtensionNotify"
    :documentExtensionInfo="documentExtensionInfo"
    :documentExtensionIds="documentExtensionIds"
    :typeTechnicalSheet="TypeTechnicalSheetEnum.VEHICLE_DRIVER"
  />
</template>

<script setup lang="ts">
  import { useTransportDriverList } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/transport-drivers/useTransportDriverList';
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import ListInfoPanelComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-vehicles/partials/ListInfoPanelComponent.vue';
  import DocumentStatusComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/DocumentStatusComponent.vue';
  import TransportDocumentFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/documents/TransportDocumentFormComponent.vue';
  import TransportDocumentReviewFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/documents/TransportDocumentReviewFormComponent.vue';
  import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
  import TransportDocumentExtensionFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/documents/TransportDocumentExtensionFormComponent.vue';
  import ActiveExtensionNotifyComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/documents/ActiveExtensionNotifyComponent.vue';

  const {
    data,
    columns,
    pagination,
    showDrawerDriverDocumentForm,
    showDrawerDriverDocumentReviewForm,
    selectedDocument,
    supplierVehicleDriverId,
    showDrawerDocumentExtensionForm,
    documentExtensionInfo,
    documentExtensionIds,
    showActiveExtensionNotify,
    onChange,
    getDriverDocumentKey,
    contextHolder,
    handleEdit,
    handleDestroy,
    handleDocumentStatus,
    handleDocumentExtension,
    handleExtensionSuccessNotify,
  } = useTransportDriverList();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .popover-content {
    max-width: 300px;

    &-text {
      font-size: 12px;
      font-weight: 400;
      text-align: justify;
      margin-top: 4px;
    }
  }

  .popover-title {
    font-size: 12px;
    font-weight: 500;
  }

  .menu-more-options {
    width: 150px;
    border-radius: 0;
    padding: 0;

    :deep(.ant-dropdown-menu-item) {
      padding: 12px 16px;
    }

    .container-menu-item {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .text-option-menu-item {
      font-size: 16px;
      font-weight: 400;
      line-height: 24px;
      color: $color-black;
    }
  }

  .icon-more-options {
    height: 16px;
    cursor: pointer;
    outline: none;
  }
</style>
