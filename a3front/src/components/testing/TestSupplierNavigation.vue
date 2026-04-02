<template>
  <div class="test-supplier-navigation">
    <h3>Listado temporal de prueba</h3>
    <ul>
      <li @click="navigateToSupplierRegisterForm('RES')">Restaurante</li>
      <li @click="navigateToSupplierRegisterForm('STA')">Staff</li>
      <li @click="navigateToSupplierRegisterForm('AER')">Línea Aerea</li>
      <li @click="navigateToSupplierRegisterForm('LOD')">Lodges</li>
    </ul>
  </div>
</template>

<script setup>
  import { useSupplierClassificationGlobalComposable } from '@/modules/negotiations/supplier-new/composables/supplier-classification-global.composable';

  const { navigateToSupplierRegisterForm } = useSupplierClassificationGlobalComposable();
</script>

<style scoped lang="scss">
  .test-supplier-navigation {
    margin-bottom: 20px;
    padding: 10px;
    border: 1px dashed #ccc;
    border-radius: 4px;
    background-color: #f9f9f9;

    h3 {
      margin-bottom: 10px;
      font-weight: 600;
      color: #555;
    }

    ul {
      list-style: none;
      padding: 0;
      margin: 0;

      li {
        padding: 8px 12px;
        margin-bottom: 4px;
        background-color: #e9e9e9;
        border-radius: 4px;
        cursor: pointer;
        transition: background-color 0.2s;

        &:hover {
          background-color: #d0d0d0;
        }
      }
    }
  }
</style>
