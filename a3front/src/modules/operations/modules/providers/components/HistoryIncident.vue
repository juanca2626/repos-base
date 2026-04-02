<template>
  <div v-if="loading" class="loading-container">
    <a-spin tip="Cargando historial de incidencias..." />
  </div>

  <template v-else>
    <a-empty v-if="!hasIncidents" description="No hay incidencias registradas" />

    <a-collapse accordion v-else>
      <a-collapse-panel v-for="incidencia in dataStore.historyIncidents" :key="incidencia._id">
        <template #header>
          <div class="header-content">
            <span class="left">Ver incidencia</span>
            <span class="right">
              <font-awesome-icon :icon="['far', 'calendar']" />
              {{ formatDateForHeader(incidencia.createdAt) }}
            </span>
          </div>
        </template>

        <div>
          <p><strong>ISO:</strong> {{ incidencia.iso }}</p>
          <p><strong>Descripción:</strong> {{ incidencia.description }}</p>
        </div>
      </a-collapse-panel>
    </a-collapse>
  </template>
</template>

<script setup>
  import { ref, onMounted, computed } from 'vue';
  import { useDataStore } from '@operations/modules/providers/store/data.store';
  import { useReportStore } from '@/modules/operations/modules/providers/store/report.store';
  import { formatDateForHeader } from '@/modules/operations/shared/utils/dateUtils';
  import { message } from 'ant-design-vue';

  const dataStore = useDataStore();
  const reportStore = useReportStore();
  const loading = ref(true);

  const hasIncidents = computed(() => dataStore.historyIncidents.length > 0);

  onMounted(async () => {
    try {
      const id = reportStore.operational_service_id;
      await dataStore.getHistoryIndicents(id);
    } catch (error) {
      console.log(error);
      message.error('No se pudo obtener el historial de incidencias');
    } finally {
      loading.value = false;
    }
  });
</script>

<style scoped>
  ::v-deep(.ant-collapse-header) {
    background-color: #fff9db;
    padding: 10px 16px !important;
    border-radius: 6px;
  }

  .header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
  }

  .left {
    color: #c69157;
    font-weight: 400;
  }

  .right {
    font-size: 0.9rem;
    color: #777777;
  }

  .loading-container {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 200px; /* o usa 100vh si quieres centrar en toda la pantalla */
  }
</style>
