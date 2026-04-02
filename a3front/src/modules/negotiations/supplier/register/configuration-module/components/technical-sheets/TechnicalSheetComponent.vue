<template>
  <div>
    <a-spin :spinning="isLoading || isLoadingLocation">
      <div class="header-container">
        <div>
          <span class="title-unit-list"> {{ listTitle }} </span>
          <span>
            <ul class="ul-unit-quantity">
              <li class="li-unit-quantity">
                {{ registeredRecordsText }}
              </li>
            </ul>
          </span>
          <div class="container-automatic-notifications">
            <a-switch v-model:checked="automaticNotifications" />
            <span class="title-automatic-notifications"> Notificaciones automáticas. </span>
          </div>
        </div>

        <div class="dropdown-option-notification">
          <a-dropdown :trigger="['click']" placement="bottomRight">
            <a-button class="btn-option-notifications" @click.prevent>
              <font-awesome-icon
                icon="fa-solid fa-ellipsis-vertical"
                :style="{ fontSize: '16px' }"
              />
            </a-button>

            <template #overlay>
              <a-menu class="menu-option-notifications">
                <template v-for="(group, index) in notificationOptions" :key="group.category">
                  <template v-for="(item, itemIndex) in group.data" :key="item.key">
                    <a-menu-item @click="handleNotificationOption(item)">
                      <div class="container-menu-item">
                        <template v-if="item.icon === 'activity'">
                          <CustomPulseIcon />
                        </template>
                        <template v-else>
                          <CustomPaperPlaneIcon />
                        </template>
                        {{ item.name }}
                      </div>
                    </a-menu-item>
                    <a-menu-divider
                      v-if="
                        itemIndex === group.data.length - 1 &&
                        index < notificationOptions.length - 1
                      "
                    />
                  </template>
                </template>
              </a-menu>
            </template>
          </a-dropdown>

          <a-button @click="handleTypeTechnicalSheet" class="btn-type-technical-sheet">
            <font-awesome-icon
              :icon="isTransportVehicleActive ? ['far', 'id-badge'] : ['fa', 'fa-car']"
              class="icon-type-technical-sheet"
            />
            {{ isTransportVehicleActive ? 'Ficha de conductores' : 'Ficha de unidades' }}
          </a-button>

          <a-button type="primary" class="btn-add-unit" @click="handleNewRecord">
            <font-awesome-icon icon="fa-solid fa-plus" :style="{ fontSize: '16px' }" />
            {{ isTransportVehicleActive ? 'Agregar unidad' : 'Agregar conductor' }}
          </a-button>
        </div>
      </div>

      <template v-if="isTransportVehicleActive">
        <template v-if="locationData.length > 0">
          <TabOperationLocationsComponent :data="locationData" @handleTabClick="handleTabClick" />
        </template>
        <div class="custom-tab-content">
          <FilterHeaderComponent @onDownload="showDownloadResult = true" />

          <TransportVehicleFilterComponent />
          <div class="mt-5">
            <TransportVehicleListComponent
              :selectedLocation="selectedLocation"
              @onTransportVehicleListUnmounted="onTransportVehicleListUnmounted"
            />
          </div>
        </div>
      </template>
      <template v-else>
        <div class="custom-tab-content">
          <FilterHeaderComponent @onDownload="showDownloadResult = true" />

          <TransportDriverFilterComponent />

          <TransportDriverListComponent />
        </div>
      </template>
    </a-spin>

    <template v-if="locationData.length > 0">
      <TransportVehicleFormComponent
        v-model:showDrawerForm="showDrawerVehicleForm"
        :locationData="locationData"
      />
    </template>

    <DownloadResultComponent
      v-model:showModal="showDownloadResult"
      :locationData="isTransportVehicleActive ? locationData : []"
      :initialFilename="initialFilename"
      :onDownload="handleDownloadResult"
    />

    <TransportDriverFormComponent v-model:showDrawerForm="showDrawerDriverForm" />
  </div>
</template>

<script setup lang="ts">
  import DownloadResultComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/DownloadResultComponent.vue';
  import TabOperationLocationsComponent from '@/modules/negotiations/supplier/register/configuration-module/components/TabOperationLocationsComponent.vue';
  import TransportVehicleFilterComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-vehicles/TransportVehicleFilterComponent.vue';
  import TransportVehicleListComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-vehicles/TransportVehicleListComponent.vue';
  import TransportVehicleFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-vehicles/TransportVehicleFormComponent.vue';
  import TransportDriverFilterComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-drivers/TransportDriverFilterComponent.vue';
  import TransportDriverListComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-drivers/TransportDriverListComponent.vue';
  import CustomPulseIcon from '@/modules/negotiations/supplier/components/icons/CustomPulseIcon.vue';
  import CustomPaperPlaneIcon from '@/modules/negotiations/supplier/components/icons/CustomPaperPlaneIcon.vue';
  import FilterHeaderComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/partials/FilterHeaderComponent.vue';
  import TransportDriverFormComponent from '@/modules/negotiations/supplier/register/configuration-module/components/technical-sheets/transport-drivers/TransportDriverFormComponent.vue';
  import { useTechnicalSheet } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/useTechnicalSheet';

  const {
    isLoading,
    isLoadingLocation,
    locationData,
    selectedLocation,
    automaticNotifications,
    notificationOptions,
    isTransportVehicleActive,
    showDrawerVehicleForm,
    registeredRecordsText,
    showDownloadResult,
    initialFilename,
    listTitle,
    showDrawerDriverForm,
    onTransportVehicleListUnmounted,
    handleTabClick,
    handleNotificationOption,
    handleTypeTechnicalSheet,
    handleDownloadResult,
    handleNewRecord,
  } = useTechnicalSheet();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .dropdown-option-notification {
    display: flex;
    gap: 8px;
  }

  .btn-option-notifications {
    width: 58px;
    height: 45px;
    color: $color-primary-strong;
    border-color: $color-primary-strong;
    padding-right: 20px;
    padding-left: 20px;
    gap: 8px;
  }

  .menu-option-notifications {
    border-radius: 0;
    padding: 0;

    :deep(.ant-dropdown-menu-item) {
      padding: 12px 16px;
    }

    .container-menu-item {
      display: flex;
      gap: 10px;
      align-items: center;
    }
  }

  .title-automatic-notifications {
    font-size: 14px;
    font-weight: 400;
    line-height: 24px;
  }

  .container-automatic-notifications {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .ul-unit-quantity {
    font-size: 12px;
    font-weight: 500;
    display: inline-flex;
    padding-left: 25px;
  }

  .li-unit-quantity {
    color: $color-black-3;
  }

  .title-unit-list {
    font-size: 16px;
    font-weight: 600;
    line-height: 24px;
  }

  .header-container {
    padding: 16px;
    display: flex;
    justify-content: space-between;
  }

  .btn-add-unit {
    height: 45px;
    gap: 2px;
  }

  .icon-type-technical-sheet {
    width: 16px;
    height: 20px;
  }

  .btn-type-technical-sheet {
    height: 45px;
    color: $color-primary-strong;
    border-color: $color-primary-strong;
    font-size: 16px;
    font-weight: 600;
    gap: 8px;
  }
</style>
