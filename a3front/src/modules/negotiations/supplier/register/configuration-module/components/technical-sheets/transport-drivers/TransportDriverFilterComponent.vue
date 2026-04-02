<template>
  <div class="container-filters">
    <a-row :gutter="24">
      <a-col :span="6">
        <label>Ingresa nombre del chofer:</label>
        <a-input
          v-model:value="formState.name"
          placeholder="Buscar"
          class="mt-2"
          @input="handleName"
        >
          <template #prefix>
            <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
          </template>
        </a-input>
      </a-col>
      <a-col :span="6">
        <label>Ingresa apellidos del chofer:</label>
        <a-input
          v-model:value="formState.surnames"
          placeholder="Buscar"
          class="mt-2"
          @input="handleSurnames"
        >
          <template #prefix>
            <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
          </template>
        </a-input>
      </a-col>
      <a-col :span="5">
        <label>Estado de documentos</label>
        <a-select
          class="w-100 mt-2"
          ref="select"
          v-model:value="formState.documentStatus"
          :options="driverDocumentStatusOptions"
          @change="handleDocumentStatus"
        />
      </a-col>
      <a-col :span="7" class="container-actions">
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
  import { useTransportDriverFilter } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/transport-drivers/useTransportDriverFilter';
  import IconMagicWand from '@/components/icons/IconMagicWand.vue';

  const {
    formState,
    driverDocumentStatusOptions,
    handleName,
    handleSurnames,
    cleanFilters,
    handleDocumentStatus,
  } = useTransportDriverFilter();
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
