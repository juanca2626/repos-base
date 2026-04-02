<template>
  <div class="tab-content mt-2">
    <a-flex justify="space-between" align="middle" class="pt-1 pb-4">
      <!-- Contenedor de Typography -->
      <div style="display: flex; align-items: center">
        <a-typography-title :level="5" style="margin: 0">
          <font-awesome-icon :icon="['fas', 'globe']" /> Estado del servicio
          <a-select v-model:value="localServiceState" @change="handleStateChange">
            <a-select-option value="stateless">
              <font-awesome-icon :icon="['fas', 'circle-notch']" />&nbsp;&nbsp;Sin estado
            </a-select-option>
            <a-select-option value="onroute">
              <font-awesome-icon :icon="['fas', 'clock']" />&nbsp;&nbsp;En camino
            </a-select-option>
            <a-select-option value="delayed">
              <font-awesome-icon :icon="['fas', 'triangle-exclamation']" />&nbsp;&nbsp;Con demora
            </a-select-option>
            <a-select-option value="start">
              <font-awesome-icon :icon="['fas', 'hourglass-start']" />&nbsp;&nbsp;Inicio de svs
            </a-select-option>
            <a-select-option value="end">
              <font-awesome-icon :icon="['fas', 'hourglass-end']" />&nbsp;&nbsp;Fin de svs
            </a-select-option>
            <a-select-option value="noshow">
              <font-awesome-icon :icon="['far', 'calendar-xmark']" />&nbsp;&nbsp;No show
            </a-select-option>
            <a-select-option value="reset">
              <font-awesome-icon :icon="['fas', 'rotate-right']" />&nbsp;&nbsp;Resetear estado
            </a-select-option>
          </a-select>
        </a-typography-title>
      </div>

      <a-space>
        <font-awesome-icon
          :icon="['fas', 'chevron-up']"
          @click="$emit('closeExpand', record)"
          style="cursor: pointer"
        />
      </a-space>
    </a-flex>
  </div>

  <a-row>
    <a-col :span="5">
      <GuideList :guides="record?.gui" />
    </a-col>
    <a-col :span="14">
      <GoogleMap :routes="record?.routes" />
    </a-col>
    <a-col :span="5">
      <ServiceTimeline :monitoring="record?.monitoring" />
    </a-col>
  </a-row>
</template>

<script setup lang="ts">
  // import { ref, watch } from 'vue';
  import GuideList from '@operations/modules/tower/components/GuideList.vue';
  import GoogleMap from '@operations/modules/tower/components/GoogleMaps.vue';
  import ServiceTimeline from '@operations/modules/tower/components/ServiceTimeline.vue';
  import { ref, watch } from 'vue';
  import { useDataStore } from '../store/data.store';

  const dataStore = useDataStore();

  const props = defineProps({
    record: Object,
    serviceState: String, // Estado del servicio desde el padre
  });

  const emit = defineEmits(['update-service-state']);

  // 🔹 Estado local que reflejará el estado seleccionado
  const localServiceState = ref(props.serviceState);

  // 🔹 Emitir el evento cuando cambia el select
  const handleStateChange = (newState: string) => {
    emit('update-service-state', props?.record?.id, newState);
    dataStore.addMonitoring({
      operational_service_id: props?.record?.id,
      event: newState,
      monitored_by: 'YMM',
    });
  };

  // 🔹 Sincronizar con cambios externos
  watch(
    () => props.serviceState,
    (newVal) => {
      localServiceState.value = newVal;
    }
  );

  // const guidesData = [
  //   {
  //     name: 'Percy Yucra',
  //     code: 'LIMAGP',
  //     tags: ['Guía', 'Escort'],
  //     phone: '+56 987 654321',
  //     email: 'percy@yucra.com',
  //   },
  //   {
  //     name: 'Juan Pérez',
  //     code: 'LIMBX',
  //     tags: ['Guía'],
  //     phone: '+51 912 345678',
  //     email: 'juan@perez.com',
  //   },
  // ];

  // const text = `A dog is a type of domesticated animal.Known for its loyalty and faithfulness,it can be found as a welcome guest in many households across the world.`;
  // const activeKey = ref(['1']);

  // watch(activeKey, (val) => {
  //   console.log(val);
  // });
</script>

<style scoped>
  .monitoring-expanded {
    padding: 16px;
    background: #f5f5f5;
    border-radius: 4px;
  }
</style>
