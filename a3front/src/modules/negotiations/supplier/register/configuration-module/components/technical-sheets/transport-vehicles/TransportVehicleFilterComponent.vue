<template>
  <div class="container-filters">
    <a-row :gutter="24">
      <a-col :span="8">
        <label>Ingresa placa de la unidad</label>
        <a-input
          v-model:value="formState.licensePlate"
          placeholder="Buscar"
          class="mt-2"
          @input="handleLicensePlate"
        >
          <template #prefix>
            <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
          </template>
        </a-input>
      </a-col>
      <a-col :span="5">
        <label>Tipo de unidades</label>
        <a-select
          style="width: 100%"
          class="mt-2"
          mode="multiple"
          v-model:value="formState.typeUnitTransportId"
          :options="typeUnitOptions"
          :max-tag-count="maxTagCount"
          @select="handleSelectTypeUnit"
          @deselect="handleDeselectTypeUnit"
          :loading="isLoading"
        >
          <template v-if="isLoading" #notFoundContent>
            <a-spin size="small" />
          </template>
          <template #maxTagPlaceholder="omittedValues">
            <span>+ {{ omittedValues.length }} ...</span>
          </template>
          <template #option="{ value, name }">
            <div class="custom-select-type-units">
              <font-awesome-icon
                :class="[isSelected(value) ? 'icon-color-selected' : 'icon-color-not-selected']"
                :icon="[
                  isSelected(value) ? 'fas' : 'far',
                  isSelected(value) ? 'square-check' : 'square',
                ]"
                size="xl"
              />
              <span style="margin-left: 8px">{{ name }}</span>
            </div>
          </template>
          <template #tagRender="{ label, onClose }">
            <a-tag class="tag-selected-multiple" @close="onClose"> {{ label }}</a-tag>
          </template>
          <template #menuItemSelectedIcon />
        </a-select>
      </a-col>
      <a-col :span="5">
        <label>Estado de documentos</label>
        <a-select
          class="w-100 mt-2"
          ref="select"
          v-model:value="formState.documentStatus"
          :options="vehicleDocumentStatusOptions"
          @change="handleDocumentStatus"
        />
      </a-col>
      <a-col :span="6" class="container-actions">
        <div class="container-button mt-2">
          <a-button type="link" @click="cleanFilters()" class="btn-clean-filters">
            <IconMagicWand :width="15" :height="15" />
            Limpiar filtros
          </a-button>
        </div>
      </a-col>
    </a-row>
  </div>
</template>

<script setup lang="ts">
  import { useTransportVehicleFilter } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/transport-vehicles/useTransportVehicleFilter';
  import IconMagicWand from '@/components/icons/IconMagicWand.vue';

  const {
    formState,
    typeUnitOptions,
    maxTagCount,
    vehicleDocumentStatusOptions,
    isLoading,
    cleanFilters,
    isSelected,
    handleDocumentStatus,
    handleSelectTypeUnit,
    handleDeselectTypeUnit,
    handleLicensePlate,
  } = useTransportVehicleFilter();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .btn-clean-filters {
    font-size: 14px !important;
    font-weight: 500;
    color: $color-primary-strong;
  }

  .container-filters {
    width: 100%;
    border: 1px solid $color-black-8;
    border-radius: 4px;
    padding: 24px;
    gap: 4px;
  }

  .container-actions {
    display: flex;
    align-items: flex-end;
  }

  .container-button {
    width: 100%;
    display: flex;
    justify-content: flex-end;
  }

  .custom-select-type-units {
    gap: 8px;

    .icon-color-selected {
      color: $color-primary-strong;
    }

    .icon-color-not-selected {
      color: $color-gray-ligth;
    }

    span {
      font-weight: 400;
      color: $color-black;
    }
  }
</style>
