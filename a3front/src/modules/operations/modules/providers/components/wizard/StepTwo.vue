<template>
  <a-flex justify="center">
    <a-typography-title :level="5" :style="{ color: '#1284ED' }">
      <a-badge count="2" :number-style="{ backgroundColor: '#1284ED' }" />
      Descripción del servicio
    </a-typography-title>
  </a-flex>

  <div class="report-container">
    <a-form layout="vertical">
      <!-- Comentarios -->
      <a-form-item label="¿Tienes algo más que contarnos?" required>
        <a-textarea :maxlength="500" :rows="4" placeholder="Escribe aquí..." />
        <span class="char-count">0 / 500 caracteres</span>
      </a-form-item>

      <a-row :gutter="[16, 16]">
        <!-- PNR -->
        <a-col :span="12">
          <a-form-item label="PNR:" required>
            <a-input v-model:value="reportStore.pnr" placeholder="Escribe aquí..." />
          </a-form-item>
        </a-col>

        <!-- Cantidad de maletas -->
        <a-col :span="12">
          <a-form-item label="Cantidad de maletas:" required>
            <a-input-number v-model:value="reportStore.luggage" :min="0" style="width: 100%" />
          </a-form-item>
        </a-col>
      </a-row>

      <!-- Datos del pasajero -->
      <div class="passenger-info">
        <div class="header">
          <font-awesome-icon :icon="['fas', 'user']" class="icon" />
          <span>Datos del pasajero</span>
          <div class="actions">
            <font-awesome-icon :icon="['fas', 'edit']" class="action-icon" />
            <font-awesome-icon :icon="['fas', 'trash']" class="action-icon" />
          </div>
        </div>

        <a-row :gutter="[16, 0]">
          <!-- Nombre del pasajero -->
          <a-col :span="24">
            <a-form-item label="Nombre del pasajero">
              <a-input v-model:value="reportStore.passengerName" placeholder="Buscar aquí" />
            </a-form-item>
          </a-col>

          <!-- Celular y Correo -->
          <a-col :span="12">
            <a-form-item label="Celular:">
              <a-input v-model:value="reportStore.phone" placeholder="Escribe aquí..." />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Correo:">
              <a-input v-model:value="reportStore.email" placeholder="Escribe aquí..." />
            </a-form-item>
          </a-col>
        </a-row>
      </div>

      <!-- Botón para añadir contacto -->
      <a-button type="link" class="add-contact">
        <font-awesome-icon :icon="['fas', 'plus']" class="add-icon" /> Añadir contacto
      </a-button>
    </a-form>
  </div>
</template>

<script setup lang="ts">
  import { computed, watch } from 'vue';
  import { useReportStore } from '@/modules/operations/modules/providers/store/report.store';
  import { useButtonStore } from '@/modules/operations/modules/providers/store/button.store';

  const reportStore = useReportStore();
  const buttonStore = useButtonStore();

  // 🔎 Computed: evalúa si debe estar deshabilitado el botón
  const isNextDisabled = computed(() => {
    const { pnr, luggage, passengerName, phone, email } = reportStore;

    return (
      !pnr ||
      luggage === null ||
      passengerName.trim() === '' ||
      phone.trim() === '' ||
      email.trim() === ''
    );
  });

  // 🔄 Sincronizar el botón "Siguiente"
  watch(
    isNextDisabled,
    (disabled) => {
      buttonStore.setButtonState('btnNext', disabled);
    },
    { immediate: true }
  );
</script>

<style scoped>
  .report-container {
    margin-top: 16px;
  }

  /* Contador de caracteres */
  .char-count {
    font-size: 12px;
    color: #888;
    display: block;
    text-align: right;
  }

  /* Sección de datos del pasajero */
  .passenger-info {
    background: #e8f4ff;
    padding: 16px;
    border-radius: 5px;
    margin-top: 16px;
  }

  /* Encabezado */
  .header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-weight: bold;
    margin-bottom: 10px;
  }

  .icon {
    margin-right: 8px;
  }

  .actions {
    display: flex;
    gap: 10px;
  }

  .action-icon {
    cursor: pointer;
    color: #666;
  }

  .action-icon:hover {
    color: #1284ed;
  }

  /* Botón de añadir contacto */
  .add-contact {
    margin-top: 10px;
    font-weight: bold;
    color: #1284ed;
  }

  .add-icon {
    margin-right: 5px;
  }
</style>
