<template>
  <a-drawer
    v-model:open="isOpen"
    title="Configuración de transporte y staff"
    placement="right"
    :width="526"
    :closable="false"
    class="config-drawer"
  >
    <template #extra>
      <button class="close-btn" @click="isOpen = false">
        <CloseIcon />
      </button>
    </template>

    <div class="drawer-content">
      <StepProgress :current-step="currentStep" />

      <ConfigurationForm v-if="currentStep === 1" :form="form" />
      <VehicleSelectionForm
        v-else-if="currentStep === 2"
        :initial-units="form.units"
        @update:count="(v) => (registeredCount = v)"
        @update:units="handleUnitsUpdate"
      />

      <DrawerFooter
        :next-text="currentStep === 1 ? 'Siguiente' : 'Confirmar'"
        :disabled="isNextDisabled"
        @cancel="isOpen = false"
        @next="handleNext"
      />
    </div>
  </a-drawer>
</template>

<script setup lang="ts">
  import { computed, ref, reactive, watch } from 'vue';
  import { CloseIcon } from '../icons';
  import StepProgress from './StepProgress.vue';
  import ConfigurationForm from './ConfigurationForm.vue';
  import VehicleSelectionForm from './VehicleSelectionForm.vue';
  import DrawerFooter from './DrawerFooter.vue';
  import { useTransportConfiguration } from '../composables/useTransportConfiguration';
  import dayjs from 'dayjs';

  const props = defineProps<{
    open: boolean;
    initialData?: any;
    initialStep?: number;
  }>();

  const emit = defineEmits(['update:open']);

  const { addGroup, updateGroup, transportCodeOptions } = useTransportConfiguration();

  const currentStep = ref(1);
  const registeredCount = ref(0);

  const form = reactive({
    groupId: null as string | null,
    dateFrom: null as any,
    dateTo: null as any,
    city: null as string | null,
    segmentation: null as string | null,
    client: null as string | null,
    activity: null as string | null,
    requiresDetail: 0,
    activityDetail: '',
    units: [] as any[],
  });

  const isNextDisabled = computed(() => {
    if (currentStep.value === 1) {
      const hasBasicFields =
        !!form.dateFrom && !!form.dateTo && !!form.city && !!form.segmentation && !!form.activity;
      const hasClientIfRequired = form.segmentation === 'cliente' ? !!form.client : true;
      const hasDetailIfRequired = form.requiresDetail === 1 ? !!form.activityDetail?.trim() : true;

      return !(hasBasicFields && hasClientIfRequired && hasDetailIfRequired);
    } else {
      const validUnits = form.units.filter((u) => u.code && !u.isEditing);
      return validUnits.length === 0;
    }
  });

  const resetForm = () => {
    form.groupId = null;
    form.dateFrom = null;
    form.dateTo = null;
    form.city = null;
    form.segmentation = null;
    form.client = null;
    form.activity = null;
    form.requiresDetail = 0;
    form.activityDetail = '';
    form.units = [];
    currentStep.value = 1;
    registeredCount.value = 0;
  };

  watch(
    () => props.open,
    (newVal) => {
      if (newVal) {
        resetForm();

        currentStep.value = props.initialStep || 1;

        if (props.initialData) {
          const data = { ...props.initialData };

          form.groupId = data.groupId || null;
          form.city = data.city || null;
          form.segmentation = data.segmentation || null;
          form.client = data.client || null;
          form.activity = data.activity || null;
          form.requiresDetail = data.requiresDetail || 0;
          form.activityDetail = data.activityDetail || '';

          form.dateFrom = data.dateFrom ? dayjs(data.dateFrom) : null;
          form.dateTo = data.dateTo ? dayjs(data.dateTo) : null;
          form.units = data.units ? JSON.parse(JSON.stringify(data.units)) : [];
        }
      } else {
        setTimeout(resetForm, 300);
      }
    }
  );

  const handleUnitsUpdate = (updatedUnits: any[]) => {
    form.units = [...updatedUnits];
  };

  const isOpen = computed({
    get: () => props.open,
    set: (value) => emit('update:open', value),
  });

  const handleNext = () => {
    if (currentStep.value < 2) {
      currentStep.value++;
    } else {
      saveConfiguration();
    }
  };

  const saveConfiguration = () => {
    const validUnits = form.units.filter((u) => u.code);

    const groupId = form.groupId || `g${Date.now()}`;

    const formattedData = {
      groupId: groupId,
      segmentation: form.segmentation,
      activity: form.activity,
      since: form.dateFrom ? dayjs(form.dateFrom).format('DD/MM/YYYY') : '',
      until: form.dateTo ? dayjs(form.dateTo).format('DD/MM/YYYY') : '',
      product: props.initialData?.product ?? 0,
      productsItem: props.initialData?.productsItem ?? [],
      items: validUnits.map((u, index) => ({
        ...u,
        id: u.id || index + Date.now(),
        name:
          transportCodeOptions.value.find((o) => o.value === u.code)?.label.split(' - ')[1] || '',
        isEditing: false,
      })),
    };

    if (form.groupId) {
      updateGroup(form.groupId, formattedData);
    } else {
      addGroup(formattedData);
    }

    isOpen.value = false;
  };
</script>

<style lang="scss">
  @import '../styles/TransportConfigurationDrawer.scss';
</style>
