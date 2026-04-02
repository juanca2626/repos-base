<template>
  <AReusableModal>
    <template #default="{ type, formUpdateLock, blockingReasonsOptions, selectedLocksSummary }">
      <div v-if="type === 'update' || type === 'deleteOne'">
        <a-row>
          <a-col :span="6">
            <a-tag class="custom-tag" color="purple">
              {{ formUpdateLock.provider.code }}
            </a-tag>
          </a-col>
          <a-col :span="18">
            <a-typography-paragraph strong class="text-right">
              {{ formUpdateLock.provider.fullname }}
            </a-typography-paragraph>
          </a-col>
        </a-row>

        <a-flex gap="middle" vertical class="my-3">
          <a-typography-text strong>Motivo de bloqueo:</a-typography-text>
          <a-select
            v-model:value="formUpdateLock.blockingReason"
            label-in-value
            placeholder="Seleccione un motivo de bloqueo"
            :options="blockingReasonsOptions"
          />
        </a-flex>

        <a-flex gap="middle" vertical class="mt-4">
          <a-flex align="middle">
            <a-typography-text strong>Fechas y horario:</a-typography-text>
            <a-checkbox v-model:checked="formUpdateLock.completeDay" class="ml-auto">
              Día completo
            </a-checkbox>
          </a-flex>
          <a-range-picker
            v-model:value="formUpdateLock.timeRange"
            :show-time="formUpdateLock.completeDay ? false : { format: 'HH:mm' }"
            :format="formUpdateLock.completeDay ? 'DD/MM/YYYY' : 'DD/MM/YYYY HH:mm'"
            :placeholder="['Fecha Inicio', 'Fecha Fin']"
          />
        </a-flex>

        <a-flex gap="middle" vertical class="my-3">
          <a-typography-text strong>Observaciones: </a-typography-text>
          <a-textarea v-model:value="formUpdateLock.observations" show-count :maxlength="300" />
        </a-flex>
      </div>

      <div v-else-if="type === 'deleteMultiple'">
        <p>
          <strong>¿Deseas eliminar el bloqueo seleccionado?</strong><br />
          Estás a un paso de eliminar el (los) bloqueo(s) para:
        </p>
        <table class="table-delete-summary">
          <thead>
            <tr>
              <th>Código</th>
              <th>Motivo</th>
              <th>Fechas</th>
              <th>Horario</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(v, index) in selectedLocksSummary" :key="index">
              <td>{{ v.code }}</td>
              <td>{{ v.motive }}</td>
              <td>{{ v.dates }}</td>
              <td>{{ v.time }}</td>
            </tr>
          </tbody>
        </table>

        <a-alert
          message="Se deben revisar los usuarios y fechas seleccionados para realizar esta acción."
          type="warning"
          show-icon
          class="mt-4"
        />
      </div>
    </template>
  </AReusableModal>
</template>

<style scoped>
  .table-delete-summary {
    width: 100%;
    font-size: 14px;
    text-align: center;
  }
  .table-delete-summary th {
    background-color: #2f353a;
    color: #fff;
    padding: 10px;
  }
  .table-delete-summary td {
    padding: 10px;
  }
</style>
