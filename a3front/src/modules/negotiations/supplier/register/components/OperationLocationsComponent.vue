<!-- OperationLocationsComponent.vue -->
<template>
  <div>
    <a-row :gutter="24">
      <a-col :span="24" class="pb-5">
        <a-alert type="info" show-icon class="custom-alert">
          <template #icon>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 352 512"
              width="13"
              height="16"
              fill="#1284ed"
            >
              <path
                d="M96.1 454.4c0 6.3 1.9 12.5 5.4 17.7l17.1 25.7a32 32 0 0 0 26.6 14.3h61.7a32 32 0 0 0 26.6-14.3l17.1-25.7a32 32 0 0 0 5.4-17.7l0-38.4H96l.1 38.4zM0 176c0 44.4 16.5 84.9 43.6 115.8 16.5 18.9 42.4 58.2 52.2 91.5 0 .3 .1 .5 .1 .8h160.2c0-.3 .1-.5 .1-.8 9.9-33.2 35.7-72.6 52.2-91.5C335.6 260.9 352 220.4 352 176 352 78.6 272.9-.3 175.5 0 73.4 .3 0 83 0 176zm176-80c-44.1 0-80 35.9-80 80 0 8.8-7.2 16-16 16s-16-7.2-16-16c0-61.8 50.2-112 112-112 8.8 0 16 7.2 16 16s-7.2 16-16 16z"
              />
            </svg>
          </template>
          <template #message>
            <span class="custom-alert-message">
              Consideraciones importantes para completar esta sección:
            </span>
          </template>
          <template #description>
            <ul class="item-list">
              <li>
                Añade los lugares donde el proveedor brinde sus servicios, ya sea en una ciudad o
                zona en especifico, siendo este opcional.
              </li>
              <li>
                Las opciones de zonas y/o sedes son a criterio de lo selección previa en
                Departamento/Ciudad elegida.
              </li>
              <li>
                Aplica siempre en cuando se tengan tarifas diferenciadas en dichos lugares para
                estos sean reflejados en del tarifario del proveedor.
              </li>
              <li>
                Al eliminar el Departamento/Ciudad se borrará la fila compuesta con el campo de
                Zona/Sede.
              </li>
            </ul>
          </template>
        </a-alert>
      </a-col>
    </a-row>
    <a-row
      :gutter="24"
      v-for="(location, index) in formStateNegotiation.operationLocations"
      :key="index"
    >
      <!-- Departamento / Ciudad -->
      <a-col :span="12">
        <a-form-item
          :name="['operationLocations', index, 'primaryLocationKey']"
          :rules="formRules.primaryLocationKey"
        >
          <template #label>
            <template v-if="index === 0">
              <required-label label="Departamento / Ciudad:" />
            </template>
            <template>
              <span>Departamento / Ciudad:</span>
            </template>
          </template>
          <div :class="{ 'select-with-remove': index > 0 }">
            <a-select
              v-model:value="location.primaryLocationKey"
              placeholder="Seleccione una ubicación"
              :options="locationsByStateCity"
              :filter-option="filterOption"
              :loading="isLoadingLocations"
              :showArrow="true"
              show-search
              popupClassName="custom-dropdown-backend"
              @change="handleChangeLocationStateCity(index)"
              :class="[
                {
                  'ant-select-status-error':
                    extraValidations.errorUniqueLocationKey.error &&
                    extraValidations.errorUniqueLocationKey.index === index,
                },
              ]"
              style="width: 100%"
              :disabled="!isEditable"
            />
            <template v-if="index > 0">
              <a-button
                class="btn-input"
                @click="removeOperationLocation(index)"
                :disabled="!isEditable"
              >
                <i class="icon-trash-2"></i>
              </a-button>
            </template>
          </div>
          <span
            v-if="
              extraValidations.errorUniqueLocationKey.error &&
              extraValidations.errorUniqueLocationKey.index === index
            "
            class="ml-select-option ant-form-item-explain-error"
          >
            El lugar de operación seleccionado ya está en uso
          </span>
        </a-form-item>
      </a-col>
      <!-- Zona / Sede -->
      <a-col :span="12">
        <a-form-item>
          <template #label>
            <div class="label-container">
              <span>Zona / Sede:</span>
              <span class="optional-text">(Opcional)</span>
            </div>
          </template>
          <a-select
            :disabled="location.locationOptionsByZone.length === 0 || !isEditable"
            v-model:value="location.zoneKey"
            placeholder="Seleccione una zone/sede"
            popupClassName="custom-dropdown-backend"
            :options="location.locationOptionsByZone"
            :filter-option="filterOption"
            :showArrow="true"
            show-search
            allowClear
            @change="handleChangeLocationOptionZone(index)"
          >
          </a-select>
        </a-form-item>
      </a-col>
    </a-row>
    <a-row :gutter="24">
      <!-- Agregar lugar de operacion -->
      <a-col :span="12">
        <a-form-item>
          <a-button type="link" class="link" @click="addOperationLocation" :disabled="!isEditable">
            <font-awesome-icon :icon="['fas', 'plus']" />
            Agregar lugar de operación
          </a-button>
        </a-form-item>
      </a-col>
    </a-row>
  </div>
</template>

<script setup lang="ts">
  import { useOperationLocation } from '@/modules/negotiations/supplier/register/composables/useOperationLocation';
  import RequiredLabel from '@/modules/negotiations/supplier/components/RequiredLabel.vue';

  defineProps({
    isEditable: {
      type: Boolean,
      default: false,
    },
  });

  const {
    formStateNegotiation,
    filterOption,
    locationsByStateCity,
    handleChangeLocationStateCity,
    isLoadingLocations,
    addOperationLocation,
    removeOperationLocation,
    formRules,
    extraValidations,
    handleChangeLocationOptionZone,
  } = useOperationLocation();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .item-list {
    padding-left: 20px;
    font-size: 14px;
  }

  .custom-alert-message {
    font-weight: 600;
    font-size: 14px;
  }

  :deep(.custom-alert .ant-alert-icon) {
    margin-top: 4px;
  }

  :deep(.custom-alert.ant-alert) {
    background-color: $color-blue-light;
    border-color: $color-blue-soft;
    border-width: 2px;
  }

  .ant-alert-info {
    display: flex;
    align-items: start;
  }
</style>
