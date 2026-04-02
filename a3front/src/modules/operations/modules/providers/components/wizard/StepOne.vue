<template>
  <a-flex justify="center">
    <a-typography-title :level="5" :style="{ color: '#1284ED' }">
      <a-badge
        count="1"
        :number-style="{
          backgroundColor: '#1284ED',
        }"
      />
      Estado de servicio
    </a-typography-title>
  </a-flex>

  <div class="select-container">
    <label for="service-status" class="label">
      Estado del servicio: <span class="required">*</span>
    </label>

    <a-select
      id="service-status"
      v-model:value="selectedStatus"
      placeholder="Seleccionar estado"
      style="width: 100%"
    >
      <template #suffixIcon>
        <font-awesome-icon v-if="selectedIcon" :icon="selectedIcon" class="selected-icon" />
      </template>
      <a-select-option v-for="option in statusOptions" :key="option.value" :value="option.value">
        <span class="option-item">
          <font-awesome-icon :icon="option.icon" class="option-icon" />
          {{ option.label }}
        </span>
      </a-select-option>
    </a-select>
    <p
      v-if="reportStore.status_services.incidence && reportStore.incident_type.length === 0"
      class="error-message"
    >
      * Campos obligatorios
    </p>

    <IncidentComponent v-if="reportStore.status_services.incidence" />
  </div>
</template>

<script setup lang="ts">
  import { computed, watch } from 'vue';
  import IncidentComponent from '../IncidentComponent.vue';
  import { useReportStore } from '@/modules/operations/modules/providers/store/report.store';
  import { useButtonStore } from '@/modules/operations/modules/providers/store/button.store';

  const reportStore = useReportStore();
  const buttonStore = useButtonStore();

  const selectedStatus = computed({
    get: () => {
      if (reportStore.status_services.ok === true) return 'ok';
      if (reportStore.status_services.incidence === true) return 'incidence';
      return null;
    },
    set: (value) => {
      if (value === 'ok') {
        reportStore.setReportData({
          status_services: { ok: true, incidence: false },
          incident_type: [],
          assignFile: false,
          description: '',
          files: [],
        });
      } else if (value === 'incidence') {
        reportStore.setReportData({
          status_services: { ok: false, incidence: true },
          assignFile: false,
          description: '',
          files: [],
        });
      } else {
        reportStore.setReportData({
          status_services: { ok: false, incidence: false },
          assignFile: false,
          description: '',
          files: [],
        });
      }
    },
  });

  const statusOptions = [
    { value: 'ok', label: 'Servicio OK', icon: ['far', 'thumbs-up'] },
    { value: 'incidence', label: 'Con incidencia', icon: ['fas', 'exclamation-triangle'] },
  ];

  const selectedIcon = computed(() => {
    const option = statusOptions.find((opt) => opt.value === selectedStatus.value);
    return option ? option.icon : null;
  });

  watch(
    () => reportStore.status_services.ok,
    (ok) => {
      if (ok === true) {
        buttonStore.setButtonState('btnNext', false); // activar botón
      }
    }
  );
</script>

<style scoped>
  .select-container {
    display: flex;
    flex-direction: column;
    gap: 5px;
  }
  .label {
    font-weight: 500;
    color: #555;
  }
  .required {
    color: red;
  }
  .option-item {
    display: flex;
    align-items: center;
    gap: 8px;
  }
  .option-icon {
    color: #555;
  }
  .error-message {
    color: red;
    font-size: 12px;
  }
</style>
