<template>
  <a-card class="timeline-container" title="Eventos del servicio" :bordered="false">
    <div class="timeline-scroll">
      <template v-if="dataStore.monitorings.length > 0">
        <a-timeline mode="alternate">
          <a-timeline-item v-for="(v, index) in dataStore.monitorings" :key="index">
            <template #dot>
              <template v-if="v.event === 'start'">
                <font-awesome-icon :icon="['fas', 'hourglass-start']" style="color: #000000" />
              </template>
              <template v-else-if="v.event === 'end'">
                <font-awesome-icon :icon="['fas', 'hourglass-end']" style="color: #000000" />
              </template>
              <template v-else-if="v.event === 'onroute'">
                <font-awesome-icon :icon="['fas', 'clock']" style="color: #000000" />
              </template>
              <template v-else-if="v.event === 'delayed'">
                <font-awesome-icon :icon="['fas', 'triangle-exclamation']" style="color: #000000" />
              </template>
            </template>
            <div class="timeline-content">
              <p class="event-title">
                <template v-if="v.event === 'start'">Inicio del servicio</template>
                <template v-else-if="v.event === 'end'">Fin del servicio</template>
                <template v-else-if="v.event === 'onroute'">En camino</template>
                <template v-else-if="v.event === 'noshow'">No show</template>
                <template v-else-if="v.event === 'delayed'">Con demora</template>
              </p>
              <p class="event-detail">{{ v.detail }}</p>
              <p class="event-date">
                {{ formatDate(v.createdAt) + ' ' + formatTime(v.createdAt, v.createdAt) }}
                <strong>hrs</strong>
              </p>
            </div>
          </a-timeline-item>
        </a-timeline>
      </template>
      <template v-else> No hay eventos registrados </template>
    </div>
  </a-card>
</template>

<script setup lang="ts">
  import { onMounted } from 'vue';
  import { useDataStore } from '../store/data.store';
  import moment from 'moment';

  const dataStore = useDataStore();

  const props = defineProps<{
    monitoring: any;
  }>();

  const formatDate = (date: string): string => {
    return moment(date).format('DD/MM');
  };

  const formatTime = (dateStart: string, dateEnd: string): string => {
    const formattedDateStart = moment(dateStart).format('HH:mm');
    const formattedDateEnd = moment(dateEnd).format('HH:mm');
    if (formattedDateStart === formattedDateEnd) return formattedDateStart;
    else return `${formattedDateStart} - ${formattedDateEnd}`;
  };

  // 🔹 Datos de la línea de tiempo
  // const events = ref([
  //   {
  //     title: 'Inicio del servicio',
  //     detail: 'Nombre del guía',
  //     date: '15/10/2022 15:45',
  //     icon: ClockCircleOutlined,
  //   },
  //   {
  //     title: 'Inicio del servicio',
  //     detail: 'Nombre del guía',
  //     date: '15/10/2022 15:45',
  //     icon: ClockCircleOutlined,
  //   },
  //   {
  //     title: 'Segunda parada',
  //     detail: 'Nombre del guía',
  //     date: '15/10/2022 15:45',
  //     icon: EnvironmentOutlined,
  //   },
  //   {
  //     title: 'Ingreso de incidencia',
  //     detail: 'Nombre del guía',
  //     date: '15/10/2022 15:45',
  //     icon: ExclamationCircleOutlined,
  //   },
  //   {
  //     title: 'Tercera parada',
  //     detail: 'Nombre del guía',
  //     date: '15/10/2022 15:45',
  //     icon: EnvironmentOutlined,
  //   },
  //   {
  //     title: 'Fin del servicio',
  //     detail: 'Nombre del guía',
  //     date: '15/10/2022 15:45',
  //     icon: ClockCircleOutlined,
  //   },
  // ]);

  onMounted(() => {
    console.log('🚀 ~ timelineStore.monitoring:', props.monitoring);
    if (props.monitoring) {
      dataStore.getMonitorings(props.monitoring?.operational_service_id);
    }
  });
</script>

<style scoped>
  .timeline-container {
    max-width: 100%;
    margin: auto;
    background-color: #ffffff;
  }

  /* 🔹 Limita la altura y agrega scroll */
  .timeline-scroll {
    padding-top: 16px;
    padding-bottom: 0px;
    max-height: 400px;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 8px; /* Espacio para el scroll */
  }

  /* 🔹 Personaliza el scrollbar */
  .timeline-scroll::-webkit-scrollbar {
    width: 6px;
  }

  .timeline-scroll::-webkit-scrollbar-thumb {
    background-color: #c0c0c0;
    border-radius: 3px;
  }

  .timeline-icon {
    font-size: 16px;
    color: #555;
  }

  .timeline-content {
    display: flex;
    flex-direction: column;
  }

  .event-title {
    font-weight: bold;
    margin-bottom: 2px;
  }

  .event-detail {
    color: #555;
    padding: 0;
    margin: 0;
  }

  .event-date {
    font-weight: bold;
    color: #000;
    padding: 0;
    margin: 0;
  }
</style>
