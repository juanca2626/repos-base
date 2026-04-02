<template>
  <a-form layout="vertical" v-bind="{}" @submit.prevent="">
    <a-row :gutter="[16, 16]">
      <a-col :span="24">
        <a-typography-title :level="5">
          ¿Deseas crear un retorno de este servicio?
        </a-typography-title>
        <a-typography-text>
          Ingresar los siguientes datos para poder generar el retorno.
        </a-typography-text>
      </a-col>

      <a-col :span="12">
        <a-form-item label="Selecciona la fecha:">
          <a-date-picker
            v-model:value="dateStore.date"
            format="DD/MM/YYYY"
            :disabled-date="dateStore.disabledDate"
            @change="dateStore.setDate"
          />
        </a-form-item>
      </a-col>

      <a-col :span="12">
        <a-form-item label="Selecciona la hora:">
          <a-time-picker
            v-model:value="dateStore.time"
            format="HH:mm"
            :disabled-hours="() => dateStore.disabledHours(dateStore.date)"
            :disabled-minutes="
              (selectedHour: any) => dateStore.disabledMinutes(dateStore.date, selectedHour)
            "
            @change="dateStore.setTime"
          />
        </a-form-item>
      </a-col>
    </a-row>
  </a-form>
</template>

<script lang="ts" setup>
  import { onMounted } from 'vue';
  import { useDateStore } from '../store/date.store';

  const dateStore = useDateStore();

  onMounted(() => {
    dateStore.initializeDateTime();
  });
</script>
