<template>
  <a-drawer
    v-model:open="isOpen"
    title="Estado de servicio"
    placement="right"
    :width="525"
    :maskClosable="false"
    :keyboard="false"
    @close="close"
  >
    <div class="wizard-container">
      <component :is="steps[currentStep].component" />
    </div>

    <template #footer>
      <a-row :gutter="[10, 10]">
        <a-col :span="currentStep > 0 ? 12 : 24">
          <a-button v-if="currentStep > 0" @click="prevStep" block size="large">
            <font-awesome-icon :icon="['fas', 'arrow-left']" class="icon-left" />
            Regresar
          </a-button>
        </a-col>
        <a-col :span="currentStep > 0 ? 12 : 24">
          <a-button
            v-if="currentStep < steps.length - 1"
            @click="nextStep"
            type="primary"
            block
            size="large"
            :disabled="isBtnNextDisabled"
          >
            Siguiente
            <font-awesome-icon :icon="['fas', 'arrow-right']" class="icon-right" />
          </a-button>
          <a-button
            v-if="currentStep === steps.length - 1"
            @click="submitWizard"
            type="primary"
            block
            size="large"
            :disabled="isBtnSendDisabled"
          >
            <font-awesome-icon :icon="['fas', 'paper-plane']" class="icon-right" />
            Enviar
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
</template>

<script lang="ts" setup>
  import { computed, ref } from 'vue';
  import { storeToRefs } from 'pinia';
  import { message } from 'ant-design-vue';
  import { useDrawerStore } from '@/composables/useDrawerStore';
  import StepOne from './wizard/StepOne.vue';
  import StepTwo from './wizard/StepTwo.vue';
  import StepThree from './wizard/StepThree.vue';
  import StepFour from './wizard/StepFour.vue';
  import { useReportStore } from '@/modules/operations/modules/providers/store/report.store';
  import { useButtonStore } from '@/modules/operations/modules/providers/store/button.store';
  import { useDataStore } from '@/modules/operations/modules/providers/store/data.store';
  import { useFileStore } from '../store/file.store';
  import { toPlainObject } from '@/utils/toPlainObject';

  const reportStore = useReportStore();
  const buttonStore = useButtonStore();
  const dataStore = useDataStore();
  const fileStore = useFileStore();
  const currentStep = ref(0);

  const isBtnNextDisabled = computed(() => buttonStore.isButtonDisabled('btnNext'));
  const isBtnSendDisabled = computed(() => buttonStore.isButtonDisabled('btnSend'));

  const drawerStore = useDrawerStore();
  const { isOpen } = storeToRefs(drawerStore);

  const close = () => {
    currentStep.value = 0;
    reportStore.resetReport();
    buttonStore.disableAll();
    fileStore.clearFiles();
  };

  const steps = [
    { component: StepOne },
    { component: StepTwo },
    { component: StepThree },
    { component: StepFour },
  ];

  const nextStep = () => {
    if (currentStep.value < steps.length - 1) currentStep.value++;
  };

  const prevStep = () => {
    if (currentStep.value > 0) currentStep.value--;
  };

  const submitWizard = () => {
    const reportData = toPlainObject({
      operational_service_id: reportStore.operational_service_id,
      description: reportStore.description,
      pnr: reportStore.pnr,
      luggage: reportStore.luggage,
      passport: reportStore.passport,
      email: reportStore.email,
      passengerName: reportStore.passengerName,
      phone: reportStore.phone,
      images: reportStore.images,
      notes: reportStore.notes,
      incident_type: reportStore.incident_type,
      files: reportStore.files,
    });

    dataStore.handleCreateReport(reportData);
    message.success('Reporte enviado!');
  };
</script>

<style scoped>
  .icon-left {
    margin-right: 8px;
  }

  .icon-right {
    margin-left: 8px;
  }
</style>
