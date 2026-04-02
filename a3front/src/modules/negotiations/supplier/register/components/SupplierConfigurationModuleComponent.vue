<template>
  <div class="container">
    <div class="header">
      Selecciona la clasificación con la que deseas continuar para configurar sus módulos:
    </div>
    <div v-for="item in classifications" :key="item.id" class="classification-item">
      <div>
        <div class="classification-name">
          {{ `Clasificación de ${item.name}` }}
        </div>
        <div class="text-justify">
          <a-tag v-if="item.complete" class="classification-status-complete">
            <font-awesome-icon :icon="['fas', 'circle-check']" /> Configuración completada
          </a-tag>
          <a-tag v-else class="classification-status-pending">
            <font-awesome-icon :icon="['far', 'clock']" /> Esperando configuración
          </a-tag>
        </div>
      </div>
      <div
        @click="redirectToConfigForm(item.supplier_sub_classification_id)"
        class="continue cursor-pointer"
      >
        Continuar <font-awesome-icon :icon="['fas', 'arrow-right']" />
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';

  const { formStateNegotiation, setConfigSubClassification } = useSupplierFormStoreFacade();
  const classifications = computed(() => formStateNegotiation.classifications);

  const redirectToConfigForm = (subClassificationId: number) => {
    setConfigSubClassification(subClassificationId);
  };
</script>

<style scoped lang="scss">
  .container {
    margin-top: 48px;
    margin-bottom: 48px;
  }

  .header {
    margin-bottom: 30px;
  }

  .classification-item {
    padding: 24px 20px;
    border-radius: 6px;
    border: 1px solid #e7e7e7;
    margin-bottom: 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .classification-name {
    color: #2f353a;
    font-weight: 600;
    font-size: 16px;
    line-height: 20px;
    margin-bottom: 8px;
  }

  .classification-status-pending {
    color: #f97800;
    background: #fff2dd;
  }

  .classification-status-complete {
    color: #00a15b;
    background: #dfffe9;
  }

  .continue {
    color: #bd0d12;
    font-weight: 600;
    font-size: 16px;
  }

  .text-justify {
    text-align: justify;
  }
</style>
