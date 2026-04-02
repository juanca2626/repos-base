<template>
  <a-flex justify="center">
    <a-typography-title :level="5" :style="{ color: '#1284ED' }">
      <a-badge
        count="3"
        :number-style="{
          backgroundColor: '#1284ED',
        }"
      />
      Tareas asignadas
    </a-typography-title>
  </a-flex>

  <div class="report-container">
    <div class="task-row">
      <a-checkbox disabled>Solicitud copia de los pasaportes</a-checkbox>
      <span class="passenger-count">6/10 pasajeros</span>
    </div>

    <div class="task-row">
      <a-checkbox v-model:checked="taskChecked">Titulo de la tarea 3</a-checkbox>
    </div>

    <a-form layout="vertical" :style="{ marginTop: '10px' }">
      <a-form-item label="¿Por qué no se ejecutó la tarea?" required>
        <a-textarea
          v-model:value="reason"
          placeholder="Describe aquí el motivo"
          :maxlength="250"
          :rows="4"
        />
        <span class="char-count">{{ reason.length }} / 250 caracteres</span>
      </a-form-item>

      <span class="required-text">* Campos obligatorios</span>
    </a-form>
  </div>
</template>

<script setup>
  import { ref } from 'vue';

  const taskChecked = ref(false);
  const reason = ref('');
</script>

<style scoped>
  .report-container {
    margin-top: 16px;
  }

  .task-header {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .task-title {
    color: #1284ed;
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .task-number {
    background-color: #1284ed;
    color: white;
    font-weight: bold;
  }

  .task-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 8px;
  }

  .passenger-count {
    color: red;
    font-weight: bold;
    font-size: 14px;
  }

  .char-count {
    font-size: 12px;
    color: #888;
    display: block;
    text-align: right;
  }

  .required-label {
    font-weight: bold;
    color: red;
  }

  .required-text {
    color: red;
    font-size: 12px;
    font-weight: bold;
    display: block;
    margin-top: -10px;
  }
</style>
