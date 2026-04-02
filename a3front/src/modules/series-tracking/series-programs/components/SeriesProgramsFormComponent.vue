<template>
  <a-drawer title="Registro de pasajeros" v-model:open="formDrawer" width="700" @close="cancelForm">
    <a-form ref="formRef" :model="formState" :rules="rules" layout="vertical">
      <!-- CUADRO 1 -->
      <a-card class="header-card mb-4">
        <a-row :gutter="16">
          <a-col :span="12">
            <a-form-item label="1. Selecciona la salida" name="salidaId">
              <a-select
                v-model:value="formState.salidaId"
                placeholder="Seleccione salida"
                style="width: 100%"
                :options="departures"
                @change="handleDeparturesChange"
              />
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="2. Selecciona el programa" name="programaId">
              <a-select
                v-model:value="formState.programaId"
                placeholder="Seleccione Programa - Fecha"
                style="width: 100%"
                :options="programs"
                :disabled="!formState.salidaId"
              />
            </a-form-item>
          </a-col>
        </a-row>
      </a-card>

      <!-- CUADRO 2 -->
      <a-card class="header-card">
        <a-row :gutter="16">
          <a-col :span="8">
            <a-form-item label="Número de file" name="fileNumber">
              <a-input v-model:value="formState.fileNumber" placeholder="Ej: US890" />
            </a-form-item>
          </a-col>
          <a-col :span="8">
            <a-form-item label="Cant. pasajeros" name="passengersCount">
              <a-input-number
                v-model:value="formState.passengersCount"
                placeholder="#"
                style="width: 100%"
              />
            </a-form-item>
          </a-col>
          <a-col :span="8">
            <a-form-item label="Nombre del grupo / pax" name="groupName">
              <a-input
                v-model:value="formState.groupName"
                placeholder="Ej: Delegación Internacional Smith"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row :gutter="16">
          <a-col :span="12">
            <a-form-item label="Cliente" name="clienteId">
              <a-select
                v-model:value="formState.clienteId"
                show-search
                placeholder="Buscar cliente..."
                style="width: 100%"
                :options="clients"
                :filter-option="false"
                :not-found-content="loadingClients ? undefined : null"
                @search="handleClientSearch"
              >
                <template v-if="loadingClients" #notFoundContent>
                  <a-spin size="small" />
                </template>
              </a-select>
            </a-form-item>
          </a-col>
          <a-col :span="12">
            <a-form-item label="Especialista" name="especialistaId">
              <a-select
                v-model:value="formState.especialistaId"
                show-search
                placeholder="Seleccione especialista"
                style="width: 100%"
                :options="specialists"
                :filter-option="false"
                :not-found-content="loadingSpecialists ? undefined : null"
                @search="handleSpecialistsSearch"
              >
                <template v-if="loadingSpecialists" #notFoundContent>
                  <a-spin size="small" />
                </template>
              </a-select>
            </a-form-item>
          </a-col>
        </a-row>

        <a-form-item label="Entrada a machupicchu">
          <a-input
            v-model:value="formState.machuPicchuEntry"
            placeholder="Detalle horarios y circuitos..."
          />
        </a-form-item>

        <a-form-item label="Observaciones">
          <a-textarea
            v-model:value="formState.observations"
            rows="3"
            placeholder="Notas adicionales sobre el file o requerimientos especiales..."
          />
        </a-form-item>
      </a-card>
    </a-form>

    <!-- ACCIONES -->
    <div class="actions-footer">
      <a-button @click="cancelForm"> Cancelar </a-button>
      <a-button type="primary" block @click="saveForm()" :disabled="isLoading"> Guardar </a-button>
    </div>
  </a-drawer>
</template>

<script setup lang="ts">
  import { useSeriesProgramsForm } from '../composables/useSeriesProgramsForm';

  const props = defineProps({
    modelValue: {
      type: [String, Number],
      default: '',
    },
    label: {
      type: String,
      default: '',
    },
    placeholder: {
      type: Array,
      default: () => ['DD/MM/YYYY', 'DD/MM/YYYY'],
    },
    format: {
      type: String,
      default: 'DD/MM/YYYY',
    },
    showDrawer: {
      type: Boolean,
      default: false,
    },
  });
  const emit = defineEmits(['handlerShowDrawer']);

  const {
    formRef,
    formState,
    rules,
    departures,
    programs,
    clients,
    specialists,
    loadingClients,
    loadingSpecialists,
    handleClientSearch,
    handleSpecialistsSearch,
    handleDeparturesChange,
    saveForm,
    cancelForm,
    isLoading,
    formDrawer,
  } = useSeriesProgramsForm(props, emit);
</script>

<style scoped>
  .header-card {
    background-color: #fafafa;
    border: 1px solid #d9d9d9;
  }

  .mb-4 {
    margin-bottom: 16px;
  }

  .actions-footer {
    margin-top: 24px;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
  }
</style>
