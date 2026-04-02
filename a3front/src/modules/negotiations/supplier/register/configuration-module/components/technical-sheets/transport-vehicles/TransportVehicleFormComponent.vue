<template>
  <a-drawer
    :open="showDrawerForm"
    :title="formState.id ? 'Actualizar unidad' : 'Agregar unidad'"
    :width="500"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
  >
    <p class="form-title">Ingresar los siguientes datos para poder agregar la unidad:</p>

    <a-spin :spinning="isLoading">
      <a-flex gap="middle" vertical>
        <a-form
          layout="vertical"
          :model="formState"
          class="mt-4"
          ref="formRefTransportVehicle"
          :rules="formRules"
        >
          <a-row :gutter="16">
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Placa:" name="licensePlate">
                <a-input
                  v-model:value="formState.licensePlate"
                  placeholder="Ingrese la placa"
                  :maxlength="6"
                  @input="formState.licensePlate = $event.target.value.toUpperCase()"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Tipo de unidad:" name="typeUnitTransportId">
                <a-select
                  v-model:value="formState.typeUnitTransportId"
                  class="w-100"
                  placeholder="Seleccionar"
                  show-search
                  :filter-option="filterOption"
                  :options="typeUnits"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Marca/Modelo:" name="autoBrandId">
                <a-select
                  v-model:value="formState.autoBrandId"
                  class="w-100"
                  placeholder="Seleccionar"
                  show-search
                  :filter-option="filterOption"
                  :options="autoBrands"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Año:" name="manufacturingYear">
                <a-select
                  v-model:value="formState.manufacturingYear"
                  class="w-100"
                  placeholder="Seleccionar"
                  show-search
                  :filter-option="filterOption"
                  :options="getManufacturingYearsData()"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Cantidad de asientos:" name="numberSeats">
                <a-input-number
                  v-model:value="formState.numberSeats"
                  class="w-100"
                  :min="1"
                  :max="100"
                  :precision="0"
                  placeholder="Ingrese la cantidad de asientos"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Sede:" name="supplierBranchOfficeId">
                <a-select
                  v-model:value="formState.supplierBranchOfficeId"
                  class="w-100"
                  placeholder="Seleccionar"
                  show-search
                  :filter-option="filterOption"
                  :options="operationLocations"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="24">
              <a-form-item label="Descripción:" name="description">
                <a-textarea
                  v-model:value="formState.description"
                  :rows="2"
                  :maxlength="50"
                  placeholder="Ingrese la descripción (máximo 50 caracteres)"
                  class="textarea-custom"
                />
                <div class="observations-info" style="text-align: right; margin-top: 5px">
                  <span>{{ formState.description?.length ?? 0 }} / 50</span>
                </div>
              </a-form-item>
            </a-col>
          </a-row>
        </a-form>
      </a-flex>
    </a-spin>
    <template #footer>
      <a-row :gutter="10">
        <a-col :span="12">
          <a-button type="primary" class="btn-secondary ant-btn-md w-100" @click="handleClose"
            >Cancelar</a-button
          >
        </a-col>
        <a-col :span="12">
          <a-button
            type="primary"
            class="ant-btn-md w-100"
            @click="handleSubmit()"
            :disabled="isLoading"
          >
            Guardar
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
</template>
<script setup lang="ts">
  import type { PropType } from 'vue';
  import { useTransportVehicleForm } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/transport-vehicles/useTransportVehicleForm';
  import type { OperationLocationData } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';

  const { locationData } = defineProps({
    showDrawerForm: {
      type: Boolean,
      default: false,
    },
    locationData: {
      type: Array as PropType<OperationLocationData[]>,
      required: true,
    },
  });

  const emit = defineEmits(['update:showDrawerForm']);

  const {
    formRefTransportVehicle,
    formState,
    isLoading,
    formRules,
    operationLocations,
    autoBrands,
    typeUnits,
    filterOption,
    handleClose,
    handleSubmit,
    getManufacturingYearsData,
  } = useTransportVehicleForm(emit, locationData);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .form-title {
    font-size: 16px;
    font-weight: 400;
  }
</style>
