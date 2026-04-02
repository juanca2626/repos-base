<template>
  <div class="mt-4 mb-5">
    <spin-global-component :spinning="isLoading">
      <div class="title-form">
        <span>Lugares de operación</span>
      </div>
      <div>
        <a-form
          :model="{
            formPlaceOperations,
          }"
          ref="formRefPlaceOperation"
        >
          <template v-for="(location, index) in formPlaceOperations">
            <a-form-item
              :name="['formPlaceOperations', index, 'primaryLocationKey']"
              :class="{ 'mt-50': index > 0 }"
              :rules="formRules.primaryLocationKey"
            >
              <template #label>
                <required-label class="form-label" label="Departamento / Ciudad:" />
              </template>
              <div class="flex-row-group">
                <a-select
                  v-model:value="location.primaryLocationKey"
                  placeholder="Seleccionar"
                  style="width: 100%"
                  show-search
                  :options="stateCityLocations"
                  :filter-option="filterOption"
                  @change="handleChangeStateCityLocation(index)"
                />
                <div class="flex-row-group width-container-action">
                  <span
                    class="action-icon"
                    @click="handleDeletePlaceOperation(index)"
                    v-if="index > 0"
                  >
                    <icon-trash :width="20" :height="20" />
                  </span>
                </div>
              </div>
            </a-form-item>

            <!-- <a-form-item class="mt-50">
              <template #label>
                <div class="form-label">
                  Zona / Sede: (Opcional)
                  <font-awesome-icon icon="fa-solid fa-circle-info" class="info-icon" />
                </div>
              </template>
              <div class="flex-row-group">
                <a-select
                  v-model:value="location.zoneLocationKey"
                  :disabled="location.zoneLocations.length === 0"
                  placeholder="Seleccionar"
                  allow-clear
                  show-search
                  :options="location.zoneLocations"
                  :filter-option="filterOption"
                />
                <div class="width-container-action"></div>
              </div>
            </a-form-item> -->
          </template>
        </a-form>

        <div class="btn-add-operation-location mt-50">
          <div @click="handleAddPlaceOperation" class="cursor-pointer">
            <font-awesome-icon :icon="['fas', 'plus']" />
            <span> Agregar lugar de operación </span>
          </div>
        </div>
      </div>
    </spin-global-component>
  </div>
</template>

<script setup lang="ts">
  import SpinGlobalComponent from '@/modules/negotiations/supplier-new/components/global/spin-global-component.vue';
  import RequiredLabel from '@/modules/negotiations/supplier-new/components/required-label.vue';
  import { usePlaceOperationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/locations/place-operation.composable';
  import IconTrash from '@/modules/negotiations/supplier-new/icons/icon-trash.vue';
  import type { PlaceOperationFormProps } from '@/modules/negotiations/supplier-new/interfaces/supplier-registration/locations';

  const props = defineProps<PlaceOperationFormProps>();

  const {
    formPlaceOperations,
    formRules,
    formRefPlaceOperation,
    stateCityLocations,
    isLoading,
    handleAddPlaceOperation,
    handleDeletePlaceOperation,
    filterOption,
    handleChangeStateCityLocation,
    validateFields,
    resetFields,
    getRequestPayload,
    fetchPlaceOperationsList,
  } = usePlaceOperationComposable(props);

  // expose for parent
  defineExpose({
    fetchPlaceOperationsList,
    getRequestPayload,
    validateFields,
    resetFields,
  });
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  :deep(.ant-form-item-label > label::after) {
    content: '' !important;
  }

  .info-icon {
    margin-left: 4px;
  }

  .flex-row-group {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .mt-50 {
    margin-top: 50px !important;
  }

  .width-container-action {
    width: 25px;
  }

  .action-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    padding: 2px;
  }

  .btn-add-operation-location {
    color: $color-blue;
    font-size: 16px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;

    span {
      margin-left: 4px;
    }
  }
</style>
