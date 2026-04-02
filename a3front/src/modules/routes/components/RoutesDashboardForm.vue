<template>
  <div class="routes-dashboard">
    <div class="dashboard-header">
      <h2>Horarios y cupos disponibles</h2>
      <a-button type="default" danger style="font-weight: 600" @click="borrarFiltros">
        Limpiar filtros
      </a-button>
    </div>

    <!-- Contenedor principal del formulario/calendario -->
    <RoutesForm />
  </div>
</template>

<script setup lang="ts">
  import RoutesForm from './RoutesForm.vue';

  function borrarFiltros() {
    // Evento de limpieza general, si quieres que afecte globalmente, podrías emitirlo.
    console.log('Filtros limpiados');
  }
</script>

<style scoped>
  .routes-dashboard {
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .dashboard-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fff;
    border-radius: 10px;
    border: 1px solid #f0f0f0;
    padding: 16px 20px;
  }

  .dashboard-header h2 {
    margin: 0;
    font-weight: 700;
    font-size: 18px;
    color: #222;
  }
</style>
