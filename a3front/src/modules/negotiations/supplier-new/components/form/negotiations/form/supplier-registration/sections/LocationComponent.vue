<template>
  <div class="location-component b-bottom">
    <!-- Loading overlay bloqueante (solo al guardar) -->
    <div v-if="isLoading" class="loading-overlay">
      <a-spin size="small" />
    </div>

    <h2 v-if="isEditMode" class="title-section">Ubicación</h2>

    <!-- Modo lectura -->
    <ReadModeComponent v-if="!isEditMode" title="Ubicación" @edit="handleEditMode">
      <div class="read-item">
        <span class="read-item-label">País / Ciudad</span>
        <span class="read-item-value">{{ readData.location }}</span>
      </div>
      <div class="read-item">
        <span class="read-item-label">Zona turística</span>
        <span class="read-item-value">{{ readData.zone }}</span>
      </div>
      <div class="read-item" v-if="!isStaff">
        <span class="read-item-label">Dirección comercial</span>
        <span class="read-item-value">{{ readData.commercialAddress }}</span>
      </div>
      <div class="read-item">
        <span class="read-item-label">Dirección fiscal</span>
        <span class="read-item-value">{{ readData.fiscalAddress }}</span>
      </div>
    </ReadModeComponent>

    <!-- Modo edición -->
    <div v-else class="location-form">
      <a-form ref="formRef" :model="formState" :rules="rules" layout="vertical">
        <a-form-item name="commercial_locations">
          <template #label>
            <required-label class="form-label" label="País / Ciudad:" />
          </template>
          <a-select
            v-model:value="formState.commercial_locations"
            placeholder="Selecciona una ubicación"
            popupClassName="custom-dropdown-backend"
            :options="locations"
            :field-names="{ label: 'name', value: 'id' }"
            :filter-option="filterOption"
            show-search
            allow-clear
          />
        </a-form-item>

        <a-form-item name="zone_id">
          <template #label>
            <span class="form-label">Zona turística:</span>
          </template>
          <a-select
            v-model:value="formState.zone_id"
            placeholder="Selecciona una zona turística"
            popupClassName="custom-dropdown-backend"
            :options="zones"
            :field-names="{ label: 'name', value: 'zone_id' }"
            :filter-option="filterOption"
            :disabled="zonesDisabled"
            show-search
            allow-clear
          />
        </a-form-item>

        <a-form-item v-if="!isStaff" name="commercial_address">
          <template #label>
            <required-label class="form-label" label="Dirección comercial:" />
          </template>
          <template v-if="showMapSection">
            <GMapAutocomplete
              v-model="formState.commercial_address"
              placeholder="Ingresa la dirección comercial"
              :country-restriction="searchRestrictions.countryRestriction"
              :bounds="searchRestrictions.searchBounds"
              :country-name="searchRestrictions.countryName || ''"
              @place-changed="handlePlaceChanged"
            />
          </template>
          <template v-else>
            <a-input
              v-model:value="formState.commercial_address"
              placeholder="Ingresa la dirección comercial"
            />
          </template>
        </a-form-item>

        <a-form-item name="fiscal_address">
          <template #label>
            <required-label class="form-label" label="Dirección fiscal:" />
          </template>
          <template v-if="showMapSection">
            <GMapAutocomplete
              v-model="formState.fiscal_address"
              placeholder="Ingresa la dirección fiscal"
              :country-restriction="searchRestrictions.countryRestriction"
              :bounds="searchRestrictions.searchBounds"
              :country-name="searchRestrictions.countryName || ''"
            />
          </template>
          <template v-else>
            <a-input
              v-model:value="formState.fiscal_address"
              placeholder="Ingresa la dirección fiscal"
            />
          </template>
        </a-form-item>

        <template v-if="!isStaff">
          <div v-if="showMapSection" class="container-maps">
            <div class="title-maps">Visualiza la ubicación</div>
            <GoogleMapSelectorComponent
              :initialCenter="formState.geolocation"
              @location-changed="handleLocationChanged"
            />
          </div>
        </template>
        <div v-if="requiresPlaceOperation" class="mt-5">
          <PlaceOperationComponent
            :ref="(el) => (placeOperationRef = el)"
            :selectedLocation="String(formState.commercial_locations ?? '')"
          />
        </div>

        <div class="form-actions" :class="{ 'mt-4': !showMapSection }">
          <a-button @click="handleCancel">Cancelar</a-button>
          <a-button type="primary" :disabled="!isFormValid" @click="handleSave"
            >Guardar datos</a-button
          >
        </div>
      </a-form>
    </div>
  </div>
</template>

<script setup lang="ts">
  import ReadModeComponent from '@/modules/negotiations/products/configuration/components/ReadModeComponent.vue';
  import RequiredLabel from '@/modules/negotiations/supplier-new/components/required-label.vue';
  import GoogleMapSelectorComponent from '@/components/global/GoogleMapSelectorComponent.vue';
  import GMapAutocomplete from '@/components/global/GMapAutocomplete.vue';
  import PlaceOperationComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/locations/place-operation-component.vue';
  import { useLocationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/v2/useLocationComposable';

  const props = defineProps({
    showMapSection: {
      type: Boolean,
      required: false,
      default: true,
    },
  });

  defineOptions({
    name: 'LocationComponent',
  });

  const {
    formState,
    formRef,
    placeOperationRef,
    isEditMode,
    isLoading,
    locations,
    zones,
    zonesDisabled,
    requiresPlaceOperation,
    searchRestrictions,
    readData,
    isFormValid,
    isStaff,
    rules,
    handleEditMode,
    handleCancel,
    handleSave,
    handleLocationChanged,
    handlePlaceChanged,
    filterOption,
  } = useLocationComposable(props.showMapSection);
</script>

<style lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .location-component {
    position: relative;
    margin-bottom: 0;

    .title-section {
      font-size: 16px !important;
      line-height: 23px !important;
      font-weight: 600;
      color: #2f353a;
      margin-bottom: 16px;
    }
  }

  // Eliminar el espacio después del separador
  .location-component::v-deep .custom-separator {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
  }

  .location-component::v-deep .read-mode-container {
    padding-bottom: 0 !important;
  }

  .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
  }

  .loading-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background-color: #f0f5ff;
    border: 1px solid #d6e4ff;
    border-radius: 4px;
    margin-bottom: 12px;
    font-size: 14px;
    color: #1890ff;
  }

  .location-form {
    background-color: $color-white;

    .ant-form-item-required::before {
      display: none !important;
    }

    .ant-form-item-required::after {
      content: '' !important;
    }

    .ant-form-item {
      margin-bottom: 10px !important;
    }

    .ant-form-item-label > label {
      font-weight: 600;
      font-size: 16px;
      line-height: 24px;
      color: #2f353a;
    }

    .form-label {
      font-weight: 700;
      font-size: 14px;
      line-height: 20px;
      color: #2f353a;
    }

    .ant-form-item-control-input {
      width: 422px;
    }

    .ant-select,
    .ant-input {
      width: 422px !important;
    }

    .container-maps {
      margin: 20px 0;

      .title-maps {
        font-weight: 500;
        font-size: 14px;
        line-height: 20px;
        color: #2f353a;
        margin-bottom: 10px;
      }
    }

    .form-actions {
      display: flex;
      gap: 12px;
      justify-content: flex-start;
      margin-top: 24px;

      .ant-btn {
        height: 48px;
        font-weight: 600;
        font-size: 16px;
        line-height: 24px;
        padding: 0 24px;
      }

      .ant-btn-default {
        width: 118px;
        color: #2f353a;
        background: #ffffff;
        border-color: #2f353a !important;

        &:hover,
        &:active {
          color: #2f353a !important;
          background: #ffffff !important;
          border-color: #2f353a !important;
        }
      }

      .ant-btn-primary {
        width: 159px;
        background: #2f353a;
        border-color: #2f353a;
        color: #ffffff;

        &:hover,
        &:active {
          background: #2f353a;
          border-color: #2f353a;
        }

        &:disabled {
          color: #ffffff !important;
          background: #acaeb0 !important;
          border-color: #acaeb0 !important;

          &:hover,
          &:active {
            color: #ffffff !important;
            background: #acaeb0 !important;
            border-color: #acaeb0 !important;
          }
        }
      }
    }
  }
</style>
