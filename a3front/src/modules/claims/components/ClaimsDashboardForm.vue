<template>
  <div class="claims-dashboard">
    <!-- Mostrar formulario si no está en modo listado -->
    <ClaimForm v-if="!isListing" @goToList="showListing" />

    <!-- Mostrar tabla solo si está en modo listado -->
    <div v-else>
      <div class="table-header">
        <a-button type="primary" danger @click="showForm">Agregar Reclamo</a-button>
        <a-input placeholder="Búsqueda sobre el resultado" v-model:value="searchQuery" />
      </div>

      <!-- Opciones de filtro -->
      <div class="filter-panel">
        <a-radio-group v-model:value="filter.status">
          <a-radio value="pendientes">Pendientes</a-radio>
          <a-radio value="cerrados">Cerrados</a-radio>
          <a-radio value="todos">Todos</a-radio>
        </a-radio-group>

        <a-radio-group v-model:value="filter.dateType">
          <a-radio value="recepcion">Fecha Recepción</a-radio>
          <a-radio value="fechaIn">Fecha In</a-radio>
        </a-radio-group>

        <a-date-picker v-model:value="filter.startDate" placeholder="Desde" />
        <a-date-picker v-model:value="filter.endDate" placeholder="Hasta" />

        <a-button type="primary" icon="search" />
      </div>

      <!-- Tabla de reclamos -->
      <a-table :data-source="claimsStore.claims" :columns="columns" />
    </div>
  </div>
</template>

<script setup>
  import { ref } from 'vue';
  import ClaimForm from './ClaimForm.vue';
  import { useClaimsStore } from '@/modules/claims/stores/claims-store.js';
  const claimsStore = useClaimsStore();
  const isListing = ref(false);
  const searchQuery = ref('');
  const filter = ref({
    status: 'todos',
    dateType: 'recepcion',
    startDate: null,
    endDate: null,
  });

  const columns = [
    { title: 'CÓDIGO QUEJA', dataIndex: 'codigo', key: 'codigo' },
    { title: 'N° FILE', dataIndex: 'fileNumber', key: 'fileNumber' },
    { title: 'USUARIO', dataIndex: 'usuario', key: 'usuario' },
    { title: 'CLIENTE', dataIndex: 'cliente', key: 'cliente' },
    { title: 'NOMBRE DEL FILE', dataIndex: 'fileName', key: 'fileName' },
    { title: 'KAM', dataIndex: 'kam', key: 'kam' },
    { title: 'EJE. QR', dataIndex: 'ejecutorQR', key: 'ejecutorQR' },
    { title: 'FECHA RECEPCION', dataIndex: 'fechaRecepcion', key: 'fechaRecepcion' },
    { title: 'FECHA RESOLUCION', dataIndex: 'fechaResolucion', key: 'fechaResolucion' },
    { title: 'DIAS GESTIÓN', dataIndex: 'diasGestion', key: 'diasGestion' },
    { title: 'COMENTARIO', dataIndex: 'comentario', key: 'comentario' },
    { title: 'RESPUESTA', dataIndex: 'respuesta', key: 'respuesta' },
    { title: 'TIPO', dataIndex: 'tipo', key: 'tipo' },
    { title: 'ESTADO', dataIndex: 'estado', key: 'estado' },
    // { title: 'Acciones', key: 'acciones', align: 'center' },
  ];

  const showListing = async () => {
    isListing.value = true;
    await claimsStore.fetchClaims();
  };

  const showForm = () => {
    isListing.value = false;
  };
</script>

<style scoped>
  .claims-dashboard {
    padding: 20px;
  }

  .table-header {
    display: flex;
    justify-content: space-between;
    margin-bottom: 10px;
  }

  .filter-panel {
    display: flex;
    align-items: center;
    gap: 10px;
    background: #f5f5f5;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
  }
</style>
