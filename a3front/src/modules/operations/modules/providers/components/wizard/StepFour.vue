<template>
  <a-flex justify="center">
    <a-typography-title :level="5" :style="{ color: '#1284ED' }">
      <a-badge count="4" :number-style="{ backgroundColor: '#1284ED' }" />
      Resumen de reporte
    </a-typography-title>
  </a-flex>

  <div class="report-container">
    <p>
      <strong>Estado del reporte:</strong>
      <span class="status-badge">Servicio con incidencia</span>
    </p>

    <a-collapse class="custom-collapse">
      <a-collapse-panel key="1" header="Ver incidencia">
        <p>Detalles sobre incidencias...</p>

        <div class="tag-container">
          <a-tag
            v-for="(incident, index) in reportStore.incident_type as any[]"
            :key="index"
            color="red"
          >
            {{ incident.description }}
          </a-tag>
        </div>

        <br />

        <p>Detalles sobre file(s)...</p>
        <div class="tag-container">
          <a-tag v-for="file in files" :key="file._id" color="red">
            {{ file.file_number }}
          </a-tag>
        </div>

        <div>
          <p v-if="reportStore.description">
            <strong>Description:</strong> {{ reportStore.description }}
          </p>
          <p><strong>PNR:</strong> {{ reportStore.pnr || 'No registrado' }}</p>
          <p><strong>Cantidad de maletas:</strong> {{ reportStore.luggage ?? 0 }}</p>
        </div>
      </a-collapse-panel>
    </a-collapse>

    <p><strong>Datos adicionales:</strong></p>
    <a-collapse class="custom-collapse">
      <a-collapse-panel key="2" header="Ver descripción del servicio">
        <p>Descripción detallada del servicio...</p>
      </a-collapse-panel>
    </a-collapse>

    <p><strong>Tareas asignadas:</strong></p>
    <a-collapse class="custom-collapse">
      <a-collapse-panel key="3" header="2/3 tareas realizadas">
        <p>Lista de tareas completadas y pendientes...</p>
      </a-collapse-panel>
    </a-collapse>
  </div>
</template>

<script setup lang="ts">
  import { computed, watch } from 'vue';
  import { useReportStore } from '@/modules/operations/modules/providers/store/report.store';
  import { useButtonStore } from '@/modules/operations/modules/providers/store/button.store';
  import type { File } from '../../interfaces/file';

  const reportStore = useReportStore();
  const buttonStore = useButtonStore();

  // Computed para files con tipado
  const files = computed<File[]>(() => reportStore.files);

  // Evaluar si el reporte está completo
  const isReportComplete = computed(() => {
    const { pnr, luggage } = reportStore;

    return typeof pnr === 'string' && pnr.trim() !== '' && typeof luggage === 'number';
  });

  // Controlar el botón "Enviar"
  watch(
    isReportComplete,
    (isComplete) => {
      buttonStore.setButtonState('btnSend', !isComplete); // true = deshabilitado
    },
    { immediate: true }
  );
</script>

<style scoped>
  .report-container {
    margin-top: 16px;
  }

  .status-badge {
    background-color: #fff3cd;
    color: #856404;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 14px;
    margin-left: 5px;
  }

  .custom-collapse :deep(.ant-collapse-header) {
    color: #1890ff;
    font-weight: bold;
  }

  .custom-collapse {
    background: #f0f8ff;
    border-radius: 5px;
    margin-bottom: 10px;
  }

  .tag-container {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-bottom: 10px;
  }
</style>
