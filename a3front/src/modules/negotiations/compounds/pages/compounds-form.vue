<template>
  <div class="compounds-form-page">
    <!-- Sidebar + Contenido derecho -->
    <div class="compounds-form-body">
      <CompoundsSidebarComponent />

      <!-- Columna derecha: header + steps + botones -->
      <div class="content-column">
        <!-- Título y stepper: solo en la columna derecha -->
        <CompoundsPageHeader />

        <Step1Container v-if="currentStep === 0" />
        <Step2Container v-if="currentStep === 1" />
        <Step3Container v-if="currentStep === 2" />
        <Step4Container v-if="currentStep === 3" />

        <!-- Botones al fondo del contenido (dentro del scroll) -->
        <div class="footer-actions">
          <a-button v-if="currentStep > 0" class="back-button" @click="prevStep"> Atrás </a-button>
          <a-button class="save-button" @click="guardarDatos"> Guardar Datos </a-button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import CompoundsPageHeader from '../components/global/compounds-page-header.vue';
  import CompoundsSidebarComponent from '../components/sidebar/CompoundsSidebarComponent.vue';
  import Step1Container from '../components/steps/step1/step1-container.vue';
  import Step2Container from '../components/steps/step2/step2-container.vue';
  import Step3Container from '../components/steps/step3/step3-container.vue';
  import Step4Container from '../components/steps/step4/step4-container.vue';
  import { useCompoundsComposable } from '../composables/use-compounds.composable';

  defineOptions({ name: 'CompoundsFormPage' });

  const { currentStep, guardarDatos, prevStep } = useCompoundsComposable();
</script>

<style lang="scss" scoped>
  .compounds-form-page {
    min-height: 100%;
    display: flex;
    flex-direction: column;
  }

  .compounds-form-body {
    display: flex;
    flex: 1;
    overflow: hidden;
  }

  /* Columna derecha: scroll completo incluyendo el botón */
  .content-column {
    flex: 1;
    padding: 24px;
    overflow-y: auto;
    display: flex;
    flex-direction: column;
  }

  /* Botones al final del contenido, dentro del mismo scroll */
  .footer-actions {
    margin-top: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .back-button {
    height: 44px;
    min-width: 120px;
    padding: 0 32px;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 500;
    background-color: #fff;
    border-color: #595959;
    color: #2f353a;
    cursor: pointer;

    &:hover,
    &:focus,
    &:active {
      border-color: #2f353a !important;
      color: #2f353a !important;
      background-color: #fafafa !important;
    }
  }

  .save-button {
    height: 44px;
    min-width: 140px;
    padding: 0 32px;
    border-radius: 5px;
    font-size: 14px;
    font-weight: 600;
    background-color: #bd0d12;
    border-color: #bd0d12;
    color: #fff;
    cursor: pointer;

    &:hover,
    &:focus,
    &:active {
      background-color: #a50b10 !important;
      border-color: #a50b10 !important;
      color: #fff !important;
    }
  }
</style>
