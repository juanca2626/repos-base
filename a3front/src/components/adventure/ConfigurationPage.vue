<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">Configuración</h1>
    </a-col>
  </a-row>
  <div class="content-page">
    <div class="filters-section my-3">
      <div class="filters-header">
        <span class="filters-title">TIPO DE CAMBIO</span>
      </div>
      <div class="filters-content">
        <a-form :model="configuration" layout="vertical">
          <a-row type="flex" justify="space-between" align="bottom" style="gap: 10px">
            <a-col>
              <a-form-item label="Valor:" name="value" class="mb-0">
                <a-input
                  v-model:value="configuration.data.value"
                  placeholder="Ingrese la equivalencia"
                />
              </a-form-item>
            </a-col>

            <a-col>
              <a-form-item label="Fecha de inicio:" name="startDate" class="mb-0">
                <a-date-picker
                  class="w-100"
                  v-model:value="configuration.data.startDate"
                  :format="dateFormat"
                  value-format="YYYY-MM-DD"
                  placeholder="Seleccione.."
                />
              </a-form-item>
            </a-col>

            <a-col>
              <a-form-item label="Fecha de fin:" name="endDate" class="mb-0">
                <a-date-picker
                  class="w-100"
                  v-model:value="configuration.data.endDate"
                  :format="dateFormat"
                  value-format="YYYY-MM-DD"
                  placeholder="Seleccione.."
                />
              </a-form-item>
            </a-col>

            <a-col>
              <a-button type="primary" :disabled="isLoading" @click="handleOk">
                <SaveOutlined /> Guardar
              </a-button>
            </a-col>
          </a-row>
        </a-form>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { onBeforeMount } from 'vue';
  import { useConfiguration } from '@/composables/adventure';
  import { SaveOutlined } from '@ant-design/icons-vue';

  const dateFormat = 'DD/MM/YYYY';

  const { isLoading, configuration, fetchConfiguration, updateConfiguration, error } =
    useConfiguration();

  onBeforeMount(async () => {
    await fetchConfiguration();
  });

  const handleOk = async () => {
    await updateConfiguration();

    if (!error.value) {
      await fetchConfiguration();
    }
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';
</style>
