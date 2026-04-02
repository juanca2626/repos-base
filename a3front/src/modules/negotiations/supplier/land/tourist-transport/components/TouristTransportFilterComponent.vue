<template>
  <div class="container-filters">
    <a-form layout="vertical" :model="formState" class="mt-4 filter-form">
      <a-row :gutter="24">
        <a-col :span="7">
          <a-form-item label="Ingresa código o nombre del proveedor">
            <a-input-search
              v-model:value="formState.name"
              placeholder="Buscar por nombre o descripción"
              style="width: 100%"
              class="search-input"
              @search="onSearch"
            />
          </a-form-item>
        </a-col>
        <a-col :span="5">
          <a-form-item label="Tipos de unidades">
            <a-select
              mode="multiple"
              class="select-multiple-negotiation"
              style="width: 100%"
              :loading="isLoading"
              v-model:value="formState.typeUnitTransportId"
              :options="typeUnitOptions"
              name="supplier_sub_classification_id"
              :max-tag-count="maxTagCount"
              @select="handleSelectTypeUnit"
              @deselect="handleDeselectTypeUnit"
            >
              <template #maxTagPlaceholder="omittedValues">
                <span>+ {{ omittedValues.length }} ...</span>
              </template>
              <template #option="{ value, name }">
                <div class="select-multiple-opt-negotiation">
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
          </a-form-item>
        </a-col>
        <a-col :span="5">
          <a-form-item label="Estado del proveedor">
            <a-select ref="select" v-model:value="formState.status" @change="handleChangeStatus">
              <a-select-option :value="null">Todos</a-select-option>
              <a-select-option :value="1">Activo</a-select-option>
              <a-select-option :value="0">Inactivo</a-select-option>
              <a-select-option :value="2">Pendiente de desactivación</a-select-option>
            </a-select>
          </a-form-item>
        </a-col>
        <a-col :span="5">
          <a-form-item>
            <a-flex justify="flex-end" align="middle" class="mt-4">
              <a-button
                type="link"
                :style="{ color: '#bd0d12' }"
                @click="cleanFilters()"
                :disabled="isLoading"
              >
                <svg
                  class="colorPrimary_SVG"
                  xmlns="http://www.w3.org/2000/svg"
                  viewBox="0 0 576 512"
                  width="24"
                  height="24"
                >
                  <path
                    d="M234.7 42.7L197 56.8c-3 1.1-5 4-5 7.2s2 6.1 5 7.2l37.7 14.1L248.8 123c1.1 3 4 5 7.2 5s6.1-2 7.2-5l14.1-37.7L315 71.2c3-1.1 5-4 5-7.2s-2-6.1-5-7.2L277.3 42.7 263.2 5c-1.1-3-4-5-7.2-5s-6.1 2-7.2 5L234.7 42.7zM46.1 395.4c-18.7 18.7-18.7 49.1 0 67.9l34.6 34.6c18.7 18.7 49.1 18.7 67.9 0L529.9 116.5c18.7-18.7 18.7-49.1 0-67.9L495.3 14.1c-18.7-18.7-49.1-18.7-67.9 0L46.1 395.4zM484.6 82.6l-105 105-23.3-23.3 105-105 23.3 23.3zM7.5 117.2C3 118.9 0 123.2 0 128s3 9.1 7.5 10.8L64 160l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L128 160l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L128 96 106.8 39.5C105.1 35 100.8 32 96 32s-9.1 3-10.8 7.5L64 96 7.5 117.2zm352 256c-4.5 1.7-7.5 6-7.5 10.8s3 9.1 7.5 10.8L416 416l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L480 416l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L480 352l-21.2-56.5c-1.7-4.5-6-7.5-10.8-7.5s-9.1 3-10.8 7.5L416 352l-56.5 21.2z"
                  />
                </svg>
                Limpiar filtros
              </a-button>
            </a-flex>
          </a-form-item>
        </a-col>
      </a-row>
    </a-form>
  </div>
</template>
<script setup lang="ts">
  import { useTouristTransportFilter } from '@/modules/negotiations/supplier/land/tourist-transport/composables/useTouristTransportFilter';

  const {
    formState,
    cleanFilters,
    isLoading,
    isSelected,
    onSearch,
    typeUnitOptions,
    maxTagCount,
    handleChangeStatus,
    handleSelectTypeUnit,
    handleDeselectTypeUnit,
  } = useTouristTransportFilter();
</script>

<style scoped lang="scss">
  .colorPrimary_SVG {
    fill: #bd0d12;
    margin-right: 5px;
  }

  .container-filters {
    width: 100%;
    padding: 20px;
    background-color: white;
    border-left: 1px solid #e8e8e8;
    border-right: 1px solid #e8e8e8;
    border-bottom: 1px solid #e8e8e8;
    border-bottom-right-radius: 5px;
    border-bottom-left-radius: 5px;
    margin-bottom: 1rem;
  }

  .ant-select-item-option-selected:not(.ant-select-item-option-disabled) {
    background-color: #f9f9f9 !important;
  }

  .select-multiple-opt-negotiation {
    display: flex;
    align-items: center;
    gap: 8px;

    .icon-color-selected {
      color: #bd0d12;
    }

    .icon-color-not-selected {
      color: #bec0c2;
    }

    span {
      font-weight: 400;
      color: #2f353a;
    }
  }
</style>
