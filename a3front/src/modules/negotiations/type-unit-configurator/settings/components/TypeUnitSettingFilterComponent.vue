<template>
  <div class="module-negotiations">
    <a-spin :spinning="isLoading">
      <div class="container-title">
        <a-typography-text strong> Filtra tus configuraciones </a-typography-text>
      </div>
      <a-form class="mt-4" layout="vertical">
        <div class="box-filter">
          <a-row :gutter="16">
            <a-col :span="6">
              <a-form-item label="Periodo">
                <a-select
                  v-model:value="formState.periodYear"
                  popupClassName="custom-dropdown-backend"
                  :allowClear="false"
                  :options="periodYears"
                  @change="handlePeriodYear"
                />
              </a-form-item>
            </a-col>
            <a-col :span="6">
              <a-form-item label="Tipo de traslado">
                <a-select
                  v-model:value="formState.transferId"
                  popupClassName="custom-dropdown-backend"
                  :allowClear="false"
                  show-search
                  :options="transfers"
                  :filter-option="filterOption"
                  @change="handleTransfer"
                />
              </a-form-item>
            </a-col>
            <a-col :span="12" v-if="showActionButtons">
              <div class="container-actions">
                <template v-if="canUpdate">
                  <a-button class="btn-secondary ant-btn-sm" @click="handleEditSetting">
                    <font-awesome-icon :icon="['far', 'pen-to-square']" />
                    Editar
                  </a-button>
                </template>
                <template v-if="canDelete">
                  <a-button type="primary" class="ant-btn-sm" @click="handleDeleteSetting">
                    <font-awesome-icon :icon="['far', 'trash-can']" />
                    Eliminar
                  </a-button>
                </template>
              </div>
            </a-col>
          </a-row>
        </div>
        <div class="mt-3">
          <a-row :gutter="16">
            <a-col :span="24">
              <template v-if="typeUnitTransportLocations.length === 0 && !isLoading">
                <a-empty class="mt-3" description="No tiene configuraciones registradas." />
              </template>
              <template v-else>
                <a-tabs
                  v-model:activeKey="tabLocationKey"
                  size="middle"
                  :tab-bar-gutter="24"
                  :style="{ width: '100%' }"
                  type="line"
                  @change="handleTabChangeLocation"
                >
                  <a-tab-pane
                    v-for="(location, index) in typeUnitTransportLocations"
                    :key="index"
                    :tab="location.full_location_name"
                  />
                  <template #leftExtra>
                    <a-button
                      :disabled="isPreviousDisabled"
                      @click="goToPreviousTab()"
                      aria-label="Previous Tab"
                    >
                      <font-awesome-icon :icon="['fas', 'chevron-left']" />
                    </a-button>
                  </template>
                  <template #rightExtra>
                    <a-flex>
                      <a-button
                        :disabled="isNextDisabled"
                        @click="goToNextTab()"
                        aria-label="Next Tab"
                      >
                        <font-awesome-icon :icon="['fas', 'chevron-right']" />
                      </a-button>
                    </a-flex>
                  </template>
                </a-tabs>
              </template>
            </a-col>
          </a-row>
        </div>
      </a-form>
    </a-spin>
  </div>
</template>

<script setup lang="ts">
  import { useTypeUnitSettingFilter } from '@/modules/negotiations/type-unit-configurator/settings/composables/useTypeUnitSettingFilter';
  import { useTypeUnitConfiguratorPermission } from '@/modules/negotiations/type-unit-configurator/composables/useTypeUnitConfiguratorPermission';

  const { canUpdate, canDelete } = useTypeUnitConfiguratorPermission();

  const {
    isLoading,
    formState,
    periodYears,
    typeUnitTransportLocations,
    transfers,
    tabLocationKey,
    isPreviousDisabled,
    isNextDisabled,
    showActionButtons,
    goToPreviousTab,
    goToNextTab,
    handleTabChangeLocation,
    handlePeriodYear,
    handleTransfer,
    filterOption,
    handleEditSetting,
    handleDeleteSetting,
  } = useTypeUnitSettingFilter();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .container-actions {
    display: flex;
    justify-content: end;
    align-items: center;
    gap: 8px;
  }

  .container-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .box-filter {
    border: 1px solid $color-black-8;
    padding: 1rem 1rem 0 1rem;
    border-radius: 4px;
  }
</style>
