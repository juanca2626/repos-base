<template>
  <ListInfoPanelComponent :isTransportVehicleActive="true" />

  <div class="table-negotiation-services mt-3">
    <a-table
      :columns="columns"
      :data-source="data"
      :pagination="false"
      row-key="id"
      :scroll="{ x: true }"
    >
      <template #headerCell="{ column }">
        <span v-if="column.key === 'statusCirculationCard'">
          {{ column.title }}
          <div>
            <a-popover placement="bottom">
              <template #content>
                <div class="popover-content">
                  <span class="popover-title"> Tarjeta Única de Circulación </span>
                  <span class="popover-content-text d-block">
                    Es expedida por el MTC que autoriza a una unidad vehicular para la prestación
                    del servicio de transporte público terrestre.
                  </span>
                </div>
              </template>
              <font-awesome-icon :icon="['fas', 'circle-question']" />
            </a-popover>
          </div>
        </span>
        <span v-else>
          {{ column.title }}
        </span>
      </template>
      <template #bodyCell="{ column, record, index }">
        <template v-if="column.key === 'typeUnitCode'">
          {{ record.typeUnit.code }}
        </template>
        <template v-else-if="column.key === 'vehicleInfo'">
          <span class="d-block">
            {{ record.autoBrand.name }}
          </span>
          <span class="d-block">
            {{ record.manufacturingYear }}
          </span>
        </template>
        <template v-else-if="column.key === 'statusPhotos'">
          <VehiclePhotoComponent
            :status="record.vehiclePhoto.status"
            :observation="record.vehiclePhoto.lastObservation"
            @onPhotoClick="handleVehiclePhoto(record.vehiclePhoto)"
          />
        </template>

        <template v-else-if="column.key === 'statusSoat'">
          <DocumentStatusComponent
            :technicalSheetType="TypeTechnicalSheetEnum.TRANSPORT_VEHICLE"
            :status="record.vehicleDocuments.soat.status"
            :date="record.vehicleDocuments.soat.expirationDate"
            :observations="record.vehicleDocuments.soat.lastObservation"
            :extension="record.vehicleDocuments.soat.extension"
            @onDocumentStatus="handleDocumentStatus(record.vehicleDocuments.soat)"
            @onExtensionSuccessNotify="
              (extensionId: string) => handleExtensionSuccessNotify(extensionId, record)
            "
          />
        </template>

        <template v-else-if="column.key === 'statusInspectionCertificate'">
          <DocumentStatusComponent
            :technicalSheetType="TypeTechnicalSheetEnum.TRANSPORT_VEHICLE"
            :status="record.vehicleDocuments.inspection_certificate.status"
            :date="record.vehicleDocuments.inspection_certificate.expirationDate"
            :observations="record.vehicleDocuments.inspection_certificate.lastObservation"
            :extension="record.vehicleDocuments.inspection_certificate.extension"
            @onDocumentStatus="handleDocumentStatus(record.vehicleDocuments.inspection_certificate)"
            @onExtensionSuccessNotify="
              (extensionId: string) => handleExtensionSuccessNotify(extensionId, record)
            "
          />
        </template>

        <template v-else-if="column.key === 'statusSecure'">
          <DocumentStatusComponent
            :technicalSheetType="TypeTechnicalSheetEnum.TRANSPORT_VEHICLE"
            :status="record.vehicleDocuments.secure.status"
            :date="record.vehicleDocuments.secure.expirationDate"
            :observations="record.vehicleDocuments.secure.lastObservation"
            :extension="record.vehicleDocuments.secure.extension"
            @onDocumentStatus="handleDocumentStatus(record.vehicleDocuments.secure)"
            @onExtensionSuccessNotify="
              (extensionId: string) => handleExtensionSuccessNotify(extensionId, record)
            "
          />
        </template>

        <template v-else-if="column.key === 'statusPropertyCard'">
          <DocumentStatusComponent
            :technicalSheetType="TypeTechnicalSheetEnum.TRANSPORT_VEHICLE"
            :status="record.vehicleDocuments.property_card.status"
            :date="record.vehicleDocuments.property_card.expirationDate"
            :observations="record.vehicleDocuments.property_card.lastObservation"
            :extension="record.vehicleDocuments.property_card.extension"
            @onDocumentStatus="handleDocumentStatus(record.vehicleDocuments.property_card)"
            @onExtensionSuccessNotify="
              (extensionId: string) => handleExtensionSuccessNotify(extensionId, record)
            "
          />
        </template>

        <template v-else-if="column.key === 'statusCirculationCard'">
          <DocumentStatusComponent
            :technicalSheetType="TypeTechnicalSheetEnum.TRANSPORT_VEHICLE"
            :status="record.vehicleDocuments.circulation_card.status"
            :date="record.vehicleDocuments.circulation_card.expirationDate"
            :observations="record.vehicleDocuments.circulation_card.lastObservation"
            :extension="record.vehicleDocuments.circulation_card.extension"
            @onDocumentStatus="handleDocumentStatus(record.vehicleDocuments.circulation_card)"
            @onExtensionSuccessNotify="
              (extensionId: string) => handleExtensionSuccessNotify(extensionId, record)
            "
          />
        </template>

        <template v-else-if="column.key === 'statusGpsCertificate'">
          <DocumentStatusComponent
            :technicalSheetType="TypeTechnicalSheetEnum.TRANSPORT_VEHICLE"
            :status="record.vehicleDocuments.gps_certificate.status"
            :date="record.vehicleDocuments.gps_certificate.expirationDate"
            :observations="record.vehicleDocuments.gps_certificate.lastObservation"
            :extension="record.vehicleDocuments.gps_certificate.extension"
            @onDocumentStatus="handleDocumentStatus(record.vehicleDocuments.gps_certificate)"
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
                <a-menu-item @click="handleDocumentExtension(record)">
                  <div class="container-menu-item">
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
    v-model:showDrawerForm="showDrawerVehicleDocumentForm"
    :typeTechnicalSheet="TypeTechnicalSheetEnum.TRANSPORT_VEHICLE"
    :selectedDocument="selectedDocument"
  />

  <TransportDocumentReviewFormComponent
    v-model:showDrawerForm="showDrawerVehicleDocumentReviewForm"
    :typeTechnicalSheet="TypeTechnicalSheetEnum.TRANSPORT_VEHICLE"
    :documentId="selectedDocument.id"
  />

  <TransportVehiclePhotoFormComponent
    v-model:showDrawerForm="showDrawerVehiclePhotoForm"
    :selectedVehiclePhoto="selectedVehiclePhoto"
  />

  <TransportVehiclePhotoReviewFormComponent
    v-model:showDrawerForm="showDrawerVehiclePhotoReviewForm"
    :selectedVehiclePhoto="selectedVehiclePhoto"
  />

  <TransportDocumentExtensionFormComponent
    v-model:showDrawerForm="showDrawerDocumentExtensionForm"
    :parentId="supplierTransportVehicleId"
    :documentExtensionInfo="documentExtensionInfo"
    :typeTechnicalSheet="TypeTechnicalSheetEnum.TRANSPORT_VEHICLE"
  />

  <ActiveExtensionNotifyComponent
    v-model:showModal="showActiveExtensionNotify"
    :documentExtensionInfo="documentExtensionInfo"
    :documentExtensionIds="documentExtensionIds"
    :typeTechnicalSheet="TypeTechnicalSheetEnum.TRANSPORT_VEHICLE"
  />
</template>

<script setup lang="ts">
  import { type PropType } from 'vue';
  import type { OperationLocationData } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
  import { useTransportVehicleList } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/transport-vehicles/useTransportVehicleList';
  import CustomPagination from '@/components/global/CustomPaginationComponent.vue';
  import ListInfoPanelComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-vehicles/partials/ListInfoPanelComponent.vue';
  import DocumentStatusComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/DocumentStatusComponent.vue';
  import VehiclePhotoComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-vehicles/partials/VehiclePhotoComponent.vue';
  import TransportVehiclePhotoFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-vehicles/photos/TransportVehiclePhotoFormComponent.vue';
  import TransportVehiclePhotoReviewFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-vehicles/photos/TransportVehiclePhotoReviewFormComponent.vue';
  import { TypeTechnicalSheetEnum } from '@/modules/negotiations/supplier/register/configuration-module/enums/type-technical-sheet.enum';
  import TransportDocumentReviewFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/documents/TransportDocumentReviewFormComponent.vue';
  import TransportDocumentFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/documents/TransportDocumentFormComponent.vue';
  import TransportDocumentExtensionFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/documents/TransportDocumentExtensionFormComponent.vue';
  import ActiveExtensionNotifyComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/documents/ActiveExtensionNotifyComponent.vue';

  const props = defineProps({
    selectedLocation: {
      type: Object as PropType<OperationLocationData>,
      required: true,
    },
  });

  const emit = defineEmits(['onTransportVehicleListUnmounted']);

  const {
    data,
    columns,
    pagination,
    showDrawerVehicleDocumentForm,
    showDrawerVehicleDocumentReviewForm,
    showDrawerVehiclePhotoForm,
    showDrawerVehiclePhotoReviewForm,
    selectedDocument,
    selectedVehiclePhoto,
    showActiveExtensionNotify,
    supplierTransportVehicleId,
    documentExtensionInfo,
    showDrawerDocumentExtensionForm,
    documentExtensionIds,
    onChange,
    handleEdit,
    handleDestroy,
    contextHolder,
    handleDocumentStatus,
    handleVehiclePhoto,
    handleExtensionSuccessNotify,
    handleDocumentExtension,
  } = useTransportVehicleList(emit, props.selectedLocation);
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
