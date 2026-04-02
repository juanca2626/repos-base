<template>
  <div class="commercial-location-component">
    <div class="container">
      <EmptyStateFormGlobalComponent
        v-if="!getShowFormComponent(FormComponentEnum.PLACE_OPERATION)"
        title="Lugar(es) de operación"
        :formSpecific="FormComponentEnum.PLACE_OPERATION"
      />
      <div v-else>
        <div class="title-form">
          <div>Lugar(es) de operación</div>
          <div
            v-if="getIsEditFormComponent(FormComponentEnum.PLACE_OPERATION)"
            class="edit-form"
            @click="handleShowForm"
          >
            Editar <font-awesome-icon :icon="['fas', 'pen-to-square']" />
          </div>
        </div>
        <spin-global-component
          tip="Cargando..."
          :spinning="getLoadingFormComponent(FormComponentEnum.PLACE_OPERATION)"
        >
          <div v-if="!getIsEditFormComponent(FormComponentEnum.PLACE_OPERATION)">
            <a-form ref="formRef" :model="formState">
              <div v-for="(location, index) in formState.locations" :key="location.id">
                <a-form-item
                  :name="['locations', index, 'supplierSubClassificationId']"
                  :rules="{
                    required: true,
                    message: 'Por favor, selecciona clasificación del proveedor.',
                  }"
                >
                  <template v-slot:label>
                    <div class="form-label">Clasificación del proveedor:</div>
                  </template>

                  <div class="form-items-delete">
                    <div>
                      <a-select
                        style="width: 422px"
                        placeholder="Seleccionar"
                        allow-clear
                        mode="multiple"
                        v-model:value="location.supplierSubClassificationId"
                      >
                        <a-select-option
                          v-for="option in supplierSubClassifications"
                          :key="option.id"
                          :value="option.id"
                          :disabled="!supplierSubClassificationId.includes(option.id)"
                        >
                          {{ option.name }}
                        </a-select-option>
                      </a-select>
                    </div>
                    <div>
                      <font-awesome-icon
                        :icon="['far', 'trash-can']"
                        @click="handleRemoveLocation(location)"
                      />
                    </div>
                  </div>
                </a-form-item>

                <a-form-item
                  :name="['locations', index, 'city']"
                  :rules="{
                    required: true,
                    message: 'Por favor, selecciona el departamento o ciudad.',
                  }"
                >
                  <template v-slot:label>
                    <div class="form-label">Departamento / Ciudad:</div>
                  </template>
                  <div class="form-items-maps">
                    <div>
                      <a-select
                        v-model:value="location.city"
                        style="width: 422px"
                        placeholder="Seleccionar"
                        allow-clear
                        show-search
                        :filter-option="filterOption"
                        :options="
                          location.countryStateLocations?.length
                            ? location.countryStateLocations
                            : countryStateLocations
                        "
                        :field-names="{ label: 'name', value: 'id' }"
                        @change="(value: any) => handleFilterCountryStateLocations(index, value)"
                      />
                    </div>
                    <div>
                      <font-awesome-icon :icon="['fas', 'location-dot']" />
                    </div>
                  </div>
                </a-form-item>

                <a-form-item :name="['locations', index, 'zone']">
                  <template v-slot:label>
                    <div class="form-label">
                      Zona / Sede (Opcional)
                      <a-tooltip placement="top">
                        <template #title>
                          <span>
                            Las zonas/sedes dependen del Departamento/Ciudad seleccionado y solo
                            aplican si hay tarifas diferenciadas.
                          </span>
                        </template>
                        <font-awesome-icon :icon="['fas', 'circle-info']" class="cursor-pointer" />
                      </a-tooltip>
                    </div>
                  </template>
                  <div class="form-items-maps">
                    <div>
                      <a-select
                        v-model:value="location.zone"
                        style="width: 422px"
                        placeholder="Seleccionar"
                        allow-clear
                        show-search
                        :filter-option="filterOption"
                        :disabled="location.zonesLocationsDisabled"
                        :loading="location.zonesLocationsLoading"
                        :options="location.zonesLocations?.length ? location.zonesLocations : []"
                        :field-names="{ label: 'name', value: 'id' }"
                      />
                    </div>
                    <div>
                      <font-awesome-icon :icon="['fas', 'location-dot']" />
                    </div>
                  </div>
                </a-form-item>
              </div>

              <div class="description-form-locations">
                Agrega un nuevo lugar de operación si las tarifas varían por lugar.
              </div>

              <div>
                <div class="add-form-locations" @click="handleAddLocations()">
                  <font-awesome-icon :icon="['fas', 'plus']" /> Agregar lugar de operación
                </div>
              </div>
            </a-form>

            <div class="options-button">
              <a-button size="large" type="default" @click="handleClose"> Cancelar </a-button>

              <a-button
                size="large"
                type="primary"
                :disabled="!getIsFormValid"
                :loading="getLoadingButtonComponent(FormComponentEnum.PLACE_OPERATION)"
                @click="handleSave"
              >
                Guardar datos
              </a-button>
            </div>
          </div>
          <ListItemsGlobalComponent v-else :items="getListItem" />
        </spin-global-component>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import EmptyStateFormGlobalComponent from '@/modules/negotiations/supplier-new/components/global/empty-state-form-global-component.vue';
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
  import ListItemsGlobalComponent from '@/modules/negotiations/supplier-new/components/global/list-items-global-component.vue';
  import SpinGlobalComponent from '@/modules/negotiations/supplier-new/components/global/spin-global-component.vue';
  import { usePlaceOperationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/place-operation.composable';

  defineOptions({
    name: 'PlaceOperationComponent',
  });

  const {
    formState,
    formRef,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getListItem,
    getIsFormValid,
    supplierSubClassifications,
    countryStateLocations,
    supplierSubClassificationId,
    handleClose,
    handleSave,
    handleShowForm,
    handleRemoveLocation,
    handleAddLocations,
    handleFilterCountryStateLocations,
    filterOption,
  } = usePlaceOperationComposable();
</script>

<style lang="scss">
  .commercial-location-component {
    border-top: 1px solid #babcbd;
    margin-top: 1rem;

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
        color: #1284ed;
        cursor: pointer;
      }
    }

    .ant-form-item-control {
      width: 422px;
    }

    .container {
      margin-top: 20px;
    }

    .ant-form-item {
      margin-bottom: 10px !important;
    }

    .ant-form-item-required::before {
      display: none !important;
    }

    .form-label {
      font-weight: 700;
      font-size: 14px;
      line-height: 20px;
      color: #2f353a;
    }

    .ant-row {
      display: block;
    }

    .ant-form-item-required::after {
      display: inline-block;
      margin-inline-end: 4px;
      color: #ff4d4f;
      font-size: 14px;
      line-height: 1;
      content: '*' !important;
    }

    .tooltip-float {
      position: absolute;
      bottom: 37px;
      left: 161px;

      svg {
        cursor: pointer;
      }
    }

    .list-items-form {
      .container-item {
        display: flex;
        gap: 0.5rem;
      }

      .title-item {
        font-weight: 600;
        font-size: 14px;
        line-height: 24px;
        color: #7e8285;
      }

      .value-item {
        font-weight: 400;
        font-size: 14px;
        line-height: 24px;
        color: #7e8285;
      }
    }

    .item-form-code {
      display: flex;
      gap: 0.5rem;
      align-items: center;

      svg {
        height: 20px;
        color: #575b5f;
        cursor: pointer;
      }
    }

    .options-button {
      display: flex;
      gap: 1rem;

      .ant-btn-default {
        width: 118px;
        height: 48px;
        gap: 8px;
        border-radius: 5px;
        border-width: 1px;
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

      .ant-btn-primary:disabled {
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

    .form-items-delete {
      display: flex;
      align-items: center;
      gap: 1rem;
      cursor: pointer;
    }

    .form-items-maps {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .description-form-locations {
      font-weight: 500;
      font-size: 14px;
      line-height: 20px;
      color: #2f353a;
      margin-top: 1rem;
    }

    .add-form-locations {
      font-weight: 500;
      font-size: 16px;
      line-height: 32px;
      color: #1284ed;
      margin-top: 0.5rem;
      margin-bottom: 1rem;
      cursor: pointer;
    }
  }
</style>
