<template>
  <a-form layout="vertical" class="incident-form">
    <!-- Tipo de incidencia -->
    <a-form-item v-if="hasIncidentData" label="Tipo de incidencia:" required>
      <a-select
        :value="selectedTypes"
        mode="multiple"
        placeholder="Selecciona uno o más tipos"
        :open="dropdownOpen"
        :label-in-value="true"
        @change="onSelectChange"
        @dropdownVisibleChange="dropdownOpen = $event"
        style="width: 100%"
      >
        <template #dropdownRender>
          <div class="custom-dropdown">
            <a-checkbox
              v-for="option in incidentOptions"
              :key="option.value"
              :checked="selectedTypes.some((item) => item.value === option.value)"
              @change="
                (e: Event) => toggleSelection(option, (e.target as HTMLInputElement).checked)
              "
            >
              {{ option.label }}
            </a-checkbox>
          </div>
        </template>
      </a-select>
    </a-form-item>

    <!-- Checkbox para asignar a un file -->
    <a-form-item>
      <div class="assign-checkbox">
        <a-checkbox v-model:checked="assignToFile" @change="onToggleAssign">
          Asignar a un file
        </a-checkbox>
      </div>
    </a-form-item>

    <!-- Selector de files -->
    <SelectFiles v-if="assignToFile" @update:selected="onFilesSelected" />

    <HistoryIncident />

    <!-- Descripción -->
    <a-form-item label="Descripción de la incidencia:" required class="mt-basic">
      <a-textarea
        v-model:value="description"
        placeholder="Describe aquí la incidencia"
        :maxlength="250"
      />
      <p class="char-counter">{{ description.length }} / 250 caracteres</p>
    </a-form-item>

    <!-- Subida de archivos -->
    <a-form-item label="Sube aquí imágenes de la incidencia:">
      <a-upload-dragger>
        <p class="upload-icon"><UploadOutlined /></p>
        <p class="upload-text">Haga clic o arrastre el archivo a esta área para cargar</p>
        <p class="upload-hint">Soporte para todo tipo de documentos.</p>
      </a-upload-dragger>
    </a-form-item>

    <!-- Nota -->
    <p class="required-message">* Campos obligatorios</p>
  </a-form>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted, watch } from 'vue';
  import { UploadOutlined } from '@ant-design/icons-vue';
  import { useDataStore } from '@operations/modules/providers/store/data.store';
  import { useReportStore } from '@/modules/operations/modules/providers/store/report.store';
  import { useButtonStore } from '@/modules/operations/modules/providers/store/button.store';
  import type { Incident } from '../interfaces/incidents';
  import SelectFiles from './SelectFiles.vue';
  import type { File } from '../interfaces/file';
  import HistoryIncident from './HistoryIncident.vue';

  const dataStore = useDataStore();
  const reportStore = useReportStore();
  const buttonStore = useButtonStore();

  const selectedTypes = ref<{ label: string; value: string }[]>([]);

  const assignToFile = computed({
    get: () => reportStore.assignFile,
    set: (val: boolean) => reportStore.setReportData({ assignFile: val }),
  });

  const description = computed({
    get: () => reportStore.description,
    set: (val: string) => {
      reportStore.setReportData({ description: val });
    },
  });

  const dropdownOpen = ref(false);

  // Computed para mostrar el select solo si ya hay data
  const incidentOptions = computed(() =>
    dataStore.incidents.map((incident: Incident) => ({
      label: incident.description,
      value: incident.iso,
    }))
  );

  const hasIncidentData = computed(() => incidentOptions.value.length > 0);

  // Preselección desde el store
  const selectMatchingTypes = () => {
    const selected = reportStore.incident_type
      .map((incident: any) =>
        incidentOptions.value.find((option: any) => option.value === incident.iso)
      )
      .filter(Boolean);

    selectedTypes.value = selected as { label: string; value: string }[];
    updateIncidentType();
  };

  // Selección desde el dropdown
  const toggleSelection = (option: { label: string; value: string }, isChecked: boolean) => {
    if (isChecked) {
      if (!selectedTypes.value.some((item) => item.value === option.value)) {
        selectedTypes.value.push(option);
      }
    } else {
      selectedTypes.value = selectedTypes.value.filter((item) => item.value !== option.value);
    }
    updateIncidentType();
  };

  const onSelectChange = (newValues: { label: string; value: string }[]) => {
    selectedTypes.value = newValues;
    updateIncidentType();
  };

  // Guardar en el store
  const updateIncidentType = () => {
    const formattedData = selectedTypes.value.map((item) => ({
      iso: item.value,
      description: item.label,
    }));
    reportStore.setReportData({ incident_type: formattedData });
    buttonStore.setButtonState('btnNext', selectedTypes.value.length === 0);
  };

  // Asignación de file toggle
  const onToggleAssign = (event: Event) => {
    const checked = (event.target as HTMLInputElement).checked;
    assignToFile.value = checked;
    reportStore.setReportData({ assignFile: checked });

    if (!checked) {
      reportStore.setReportData({ files: [] });
    }
  };

  const onFilesSelected = (files: File[]) => {
    reportStore.setReportData({ files });
  };

  // Carga inicial
  onMounted(async () => {
    if (!dataStore.incidents.length) {
      await dataStore.getProcessIncidents();
    }
    selectMatchingTypes();
  });

  // Sincronizar estado inicial del checkbox con store
  watch(
    [
      () => reportStore.status_services.ok,
      () => reportStore.incident_type,
      () => reportStore.files,
      () => reportStore.assignFile,
      () => reportStore.description,
    ],
    ([ok, incidentType, files, assign, description]) => {
      if (ok === true) {
        buttonStore.setButtonState('btnNext', false);
        return;
      }

      if (assign && files.length === 0) {
        buttonStore.setButtonState('btnNext', true);
        return;
      }

      if (incidentType.length === 0 && !assign) {
        buttonStore.setButtonState('btnNext', true);
        return;
      }

      if (!description || description.trim() === '') {
        buttonStore.setButtonState('btnNext', true);
        return;
      }

      buttonStore.setButtonState('btnNext', false);
    },
    { immediate: true }
  );
</script>

<style scoped>
  .incident-form {
    font-family: Arial, sans-serif;
    max-width: 500px;
    margin-top: 16px;
  }
  .char-counter {
    font-size: 12px;
    color: #999;
    text-align: right;
    margin-top: 2px;
  }
  .required-message {
    color: red;
    font-size: 12px;
    font-weight: bold;
  }
  .upload-icon {
    font-size: 24px;
    color: #1890ff;
    text-align: center;
  }
  .upload-text {
    font-size: 14px;
    text-align: center;
    font-weight: bold;
  }
  .upload-hint {
    font-size: 12px;
    text-align: center;
    color: #999;
  }
  .custom-dropdown {
    display: flex;
    flex-direction: column;
    padding: 8px;
  }
  .assign-checkbox {
    display: flex;
    justify-content: flex-end;
  }
  .mt-basic {
    margin-top: 20px;
  }
</style>
