<template>
  <!-- ===== SPINNER MIENTRAS CARGA ===== -->
  <div v-if="loading" class="loading-container">
    <a-spin size="large" />
    <div class="loading-text">Cargando...</div>
  </div>

  <!-- ===== RESULTADOS ===== -->
  <div v-else-if="salidasFiltradas.length" ref="resultsSection">
    <!-- ===== MEMBRETE SUPERIOR NEGRO ===== -->
    <div class="module-header">
      <span class="module-title">Salida</span>
      <span class="module-title"> | Total Pax</span>
    </div>

    <!-- ===== CARDS POR CADA SALIDA ===== -->
    <a-card v-for="salida in salidasFiltradas" :key="salida.id">
      <!-- ===== HEADER SALIDA ===== -->
      <div class="card-header">
        <h5>{{ salida.numero }}</h5>
        <a-tag color="blue"
          >Total de pasajeros: <strong>{{ salida.total_pax }}</strong>
        </a-tag>
      </div>

      <!-- ===== ENCABEZADO TABLA ===== -->
      <div class="table-header">
        <div class="col">FILE</div>
        <div class="col">CANT. PAX</div>
        <div class="col">NOMBRE DEL GRUPO</div>
        <div class="col">PROGRAMA</div>
        <div class="col">CLIENTE</div>
        <div class="col">ESPECIALISTA</div>
        <div class="col">MAPI / TICKETS</div>
        <div class="col">OBSERVACIONES</div>
      </div>

      <!-- ===== BODY TABLA ===== -->
      <div class="table-body">
        <div class="row" v-for="item in salida.data" :key="item.id">
          <div class="col">{{ item.file }}</div>
          <div class="col">{{ item.pax }}</div>
          <div class="col">{{ item.group }}</div>

          <!-- PROGRAMA + FECHA -->
          <div class="col">
            <div class="program-name">
              {{ item.programa }}
            </div>
            <div class="program-date">
              <a-tag>
                {{ item.fechaSalida }}
              </a-tag>
            </div>
          </div>

          <div class="col">{{ item.client }}</div>
          <div class="col">{{ item.specialist }}</div>

          <!-- STATUS MAPI -->
          <div class="col">
            {{ item.status }}
          </div>

          <div class="col">{{ item.obs }}</div>
        </div>
      </div>
    </a-card>
  </div>
  <a-empty v-else description="Sin resultados" />
</template>

<script setup>
  import { computed, nextTick, onMounted, ref, watch } from 'vue';

  const props = defineProps({
    composable: {
      type: Object,
      required: true,
    },
  });

  const salidasFiltradas = computed(() => props.composable.dashboards.value);
  const loading = computed(() => props.composable.loading.value);

  const resultsSection = ref(null);

  watch(
    () => salidasFiltradas.value,
    async (newVal) => {
      if (newVal.length) {
        await nextTick();
        resultsSection.value?.scrollIntoView({
          behavior: 'smooth',
          block: 'start',
        });
      }
    }
  );

  const { fetchTrackingControls } = props.composable;

  onMounted(async () => {
    await fetchTrackingControls(null, null);
  });
</script>

<style scoped>
  /* ===== HEADER SALIDA ===== */
  .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
  }

  /* ===== TABLA ===== */
  .table-header,
  .row {
    display: grid;
    grid-template-columns:
      110px /* FILE */
      110px /* CANT. PAX */
      220px /* NOMBRE DEL GRUPO */
      200px /* PROGRAMA */
      200px /* CLIENTE */
      160px /* ESPECIALISTA */
      150px /* MAPI / TICKETS */
      1fr; /* OBSERVACIONES */
    align-items: center;
  }

  .table-header {
    background: #f5f7fa;
    padding: 12px 0;
    font-weight: 700;
    font-size: 12px;
    color: #7b8794;
  }

  .row {
    padding: 14px 0;
    border-bottom: 1px solid #eee;
    font-size: 13px;
  }

  .col {
    padding: 0 16px;
    text-align: center;
  }

  /* ===== PROGRAMA ===== */
  .program-name {
    font-weight: 700;
    color: #23324a;
  }

  .program-date {
    font-size: 12px;
    color: #7b8794;
  }

  /* ===== MEMBRETE NEGRO ===== */
  .module-header {
    background: #1f2937;
    color: white;
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-radius: 8px 8px 0 0;
  }

  /* Salida → 0.5cm a la derecha */
  .module-header .module-title:first-child {
    margin-left: 0.5cm;
  }

  /* | Total Pax → 1.5cm a la izquierda */
  .module-header .module-title:last-child {
    margin-right: 1.5cm;
  }

  .module-title {
    font-size: 16px;
    font-weight: 600;
  }

  /* ===== LOADING ===== */
  .loading-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 300px;
  }

  .loading-text {
    margin-top: 12px;
    font-weight: 600;
    color: #7b8794;
  }
</style>
