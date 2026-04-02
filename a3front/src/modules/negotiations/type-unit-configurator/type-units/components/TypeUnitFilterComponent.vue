<template>
  <div class="module-negotiations">
    <div class="container-title">
      <a-typography-text strong> Filtra tus unidades </a-typography-text>
      <a-button class="link-secondary" type="link" @click="handleDownloadResult">
        <template #icon>
          <CustomDownloadIcon :width="24" :height="24" :strokeWidth="2" stroke="currentColor" />
        </template>
        Descargar resultados
      </a-button>
    </div>
    <a-form class="mt-4" layout="vertical">
      <div class="box-filter">
        <a-row :gutter="16">
          <a-col :span="8">
            <a-form-item label="Buscar por código o tipo de unidad">
              <a-input
                placeholder="Buscar por código o tipo de unidad"
                v-model:value="formState.codeOrName"
                allow-clear
                @input="handleCodeOrName"
              >
                <template #prefix>
                  <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
                </template>
              </a-input>
            </a-form-item>
          </a-col>
          <a-col :span="4">
            <a-form-item label="Estado">
              <a-select
                placeholder="Estado"
                v-model:value="formState.status"
                popupClassName="custom-dropdown-backend"
                :allowClear="false"
                @change="handleStatus"
              >
                <a-select-option :value="null">Todos</a-select-option>
                <a-select-option :value="1">Activo</a-select-option>
                <a-select-option :value="0">Inactivo</a-select-option>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :span="12" align="right" align-self="center">
            <a-form-item>
              <a-flex justify="flex-end" align="middle" class="mt-4">
                <a-button type="link" @click="cleanFilters" class="btn-clear">
                  <div class="container-btn-clear">
                    <IconMagicWand :width="15" :height="15" />
                    <span class="text-btn-clear">Limpiar filtros</span>
                  </div>
                </a-button>
              </a-flex>
            </a-form-item>
          </a-col>
        </a-row>
      </div>
    </a-form>

    <DownloadResultComponent v-model:showModal="showDownloadResult" />
  </div>
</template>

<script setup lang="ts">
  import { useTypeUnitFilter } from '@/modules/negotiations/type-unit-configurator/type-units/composables/useTypeUnitFilter';
  import IconMagicWand from '@/components/icons/IconMagicWand.vue';
  import CustomDownloadIcon from '@/modules/negotiations/supplier/components/icons/CustomDownloadIcon.vue';
  import DownloadResultComponent from '@/modules/negotiations/type-unit-configurator/type-units/components/DownloadResultComponent.vue';

  const {
    formState,
    handleCodeOrName,
    showDownloadResult,
    cleanFilters,
    handleStatus,
    handleDownloadResult,
  } = useTypeUnitFilter();
</script>

<style scoped>
  .container-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .container-btn-clear {
    display: flex;
    align-items: center;

    .text-btn-clear {
      font-weight: 500;
      font-size: 14px;
    }
  }

  .mt-4 {
    margin-top: 1rem;
  }

  .box-filter {
    border: 1px solid #e7e7e7;
    padding: 1rem;
    border-radius: 4px;
  }
</style>
