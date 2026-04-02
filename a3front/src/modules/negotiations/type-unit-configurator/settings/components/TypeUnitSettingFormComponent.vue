<template>
  <a-drawer
    :open="showDrawerForm"
    :title="isEditMode ? 'Editar configuración' : 'Agregar configuración'"
    :width="700"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
  >
    <a-spin :spinning="isLoadingForm">
      <a-flex gap="middle" vertical>
        <a-form
          layout="vertical"
          :model="formState"
          class="mt-4"
          ref="formRefTypeUnitSetting"
          :rules="formRules"
        >
          <a-row :gutter="16" v-if="!isEditMode">
            <a-col class="gutter-row" :span="6">
              <a-form-item label="Periodo" name="periodYear">
                <a-select
                  v-model:value="formState.periodYear"
                  class="w-100"
                  placeholder="Seleccionar"
                  show-search
                  :filter-option="filterOption"
                  :options="periodYears"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="6">
              <a-form-item label="Tipo de traslado" name="transferId">
                <a-select
                  v-model:value="formState.transferId"
                  class="w-100"
                  placeholder="Seleccionar"
                  show-search
                  :filter-option="filterOption"
                  :options="transfers"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Lugar de operación" name="locationKey">
                <a-select
                  v-model:value="formState.locationKey"
                  class="w-100"
                  placeholder="Seleccionar"
                  show-search
                  :filter-option="filterOption"
                  :options="countryLocations"
                  @change="handleCountryLocations"
                />
              </a-form-item>
            </a-col>
          </a-row>
          <a-row>
            <a-col class="gutter-row" :span="24">
              <a-card>
                <template #title>
                  <div class="container-card-title">
                    <div>
                      <span class="card-title custom-primary-font">Listado de configuraciones</span>
                    </div>
                    <div>
                      <a-button
                        type="link"
                        class="link-secondary add-setting-detail"
                        @click="addSettingDetail"
                      >
                        <font-awesome-icon :icon="['fas', 'plus']" />
                        Agregar configuración
                      </a-button>
                    </div>
                  </div>
                </template>
                <a-table
                  :columns="detailColumns"
                  :dataSource="formState.settingDetails"
                  :pagination="false"
                  size="small"
                  class="mt-1"
                >
                  <template #headerCell="{ column }">
                    <span class="header-cell-title">
                      {{ column.title }}
                    </span>
                  </template>
                  <template #bodyCell="{ column, record, index }">
                    <template v-if="column.key === 'typeUnit'">
                      <a-select
                        v-model:value="record.typeUnitTransportId"
                        show-search
                        :filter-option="filterOption"
                        :options="typeUnits"
                        class="select-type-unit-transport"
                      />
                    </template>
                    <template v-else-if="column.key === 'capacityRange'">
                      <a-flex align="center" justify="space-between">
                        <a-input-number
                          v-model:value="record.minimumCapacity"
                          :min="0"
                          class="cell-input-number"
                        />
                        -
                        <a-input-number
                          v-model:value="record.maximumCapacity"
                          :min="0"
                          class="cell-input-number"
                        />
                      </a-flex>
                    </template>
                    <template v-else-if="column.key === 'quantityUnitsRequired'">
                      <a-input-number
                        v-model:value="record.quantityUnitsRequired"
                        :min="1"
                        class="cell-input-number"
                      />
                    </template>
                    <template v-else-if="column.key === 'representativeQuantity'">
                      <a-input-number
                        v-model:value="record.representativeQuantity"
                        :min="0"
                        class="cell-input-number"
                      />
                    </template>
                    <template v-else-if="column.key === 'trunkCarQuantity'">
                      <a-input-number
                        v-model:value="record.trunkCarQuantity"
                        :min="0"
                        class="cell-input-number"
                      />
                    </template>
                    <template v-else-if="column.key === 'trunkRepresentativeQuantity'">
                      <a-input-number
                        v-model:value="record.trunkRepresentativeQuantity"
                        :min="0"
                        class="cell-input-number"
                      />
                    </template>
                    <template v-else-if="column.key === 'quantityGuides'">
                      <a-input-number
                        v-model:value="record.quantityGuides"
                        :min="0"
                        class="cell-input-number"
                      />
                    </template>
                    <template v-else-if="column.key === 'actions'">
                      <a-button
                        type="link"
                        class="delete-button"
                        @click="deleteSettingDetail(index)"
                      >
                        <font-awesome-icon :icon="['fas', 'minus']" />
                      </a-button>
                    </template>
                  </template>
                </a-table>
              </a-card>
            </a-col>
          </a-row>

          <a-row :gutter="16" v-if="!isEditMode" class="mt-3">
            <a-col class="gutter-row" :span="24">
              <a-card>
                <div class="mt-3 mb-3">
                  <a-checkbox v-model:checked="applyToOtherLocations">
                    Aplicar configuración a otras sedes
                  </a-checkbox>
                </div>
                <a-form-item
                  v-if="applyToOtherLocations"
                  label="Seleccione las sedes donde se copiará la configuración:"
                  class="mt-3"
                >
                  <a-select
                    v-model:value="cloneSelectedLocations"
                    mode="multiple"
                    max-tag-count="responsive"
                    class="clone-selected-locations"
                    placeholder="Seleccione sedes"
                    popupClassName="custom-dropdown-backend"
                    :filter-option="filterOption"
                    :options="countryLocationsToClone"
                  />
                </a-form-item>
              </a-card>
            </a-col>
          </a-row>
        </a-form>
      </a-flex>
    </a-spin>
    <template #footer>
      <a-row :gutter="10">
        <a-col :span="12">
          <a-button type="primary" class="btn-secondary ant-btn-md w-100" @click="handleClose">
            Cancelar
          </a-button>
        </a-col>
        <a-col :span="12">
          <a-button
            type="primary"
            class="ant-btn-md w-100"
            @click="handleSubmit()"
            :disabled="isLoadingForm"
          >
            Guardar
          </a-button>
        </a-col>
      </a-row>
    </template>

    <SettingSaveResultComponent
      v-model:showModal="showSettingSaveResult"
      :resultsByLocation="resultsByLocation"
    />
  </a-drawer>
</template>
<script setup lang="ts">
  import { useTypeUnitSettingForm } from '@/modules/negotiations/type-unit-configurator/settings/composables/useTypeUnitSettingForm';
  import SettingSaveResultComponent from '@/modules/negotiations/type-unit-configurator/settings/components/partials/SettingSaveResultComponent.vue';

  const {
    showDrawerForm,
    isLoadingForm,
    isEditMode,
    typeUnits,
    detailColumns,
    countryLocations,
    transfers,
    periodYears,
    formState,
    formRefTypeUnitSetting,
    formRules,
    applyToOtherLocations,
    cloneSelectedLocations,
    countryLocationsToClone,
    resultsByLocation,
    showSettingSaveResult,
    handleClose,
    handleSubmit,
    filterOption,
    addSettingDetail,
    deleteSettingDetail,
    handleCountryLocations,
  } = useTypeUnitSettingForm();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .container-card-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .card-title {
    font-size: 14px;
    font-weight: 500;
    color: $color-black-3;
  }

  .add-setting-detail {
    font-size: 14px !important;
    font-weight: 500 !important;
  }

  .delete-button {
    width: 20px;
    color: $color-black !important;
  }

  .select-type-unit-transport {
    width: 75px;
  }

  .header-cell-title {
    font-size: 13px;
  }

  .cell-input-number {
    width: 55px;
  }

  .form-title {
    font-size: 16px;
    font-weight: 400;
  }

  .add-extension-button {
    font-size: 13px;
    font-weight: 500;
    color: $color-blue;
  }

  .clone-selected-locations {
    width: 100% !important;
  }
</style>
