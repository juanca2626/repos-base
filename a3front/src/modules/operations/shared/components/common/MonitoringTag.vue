<template>
  <a-tag
    class="monitoring-tag"
    :style="{ 'background-color': currentServiceState.color }"
    @click="handleClick"
  >
    <font-awesome-icon :icon="currentServiceState.icon" />
    &nbsp;
    <font-awesome-icon :icon="isExpanded ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']" />
  </a-tag>
  <p
    v-if="currentServiceState.text"
    :style="{ color: currentServiceState.color }"
    style="margin-top: 5px; line-height: 15px"
  >
    {{ currentServiceState.text }}
  </p>
</template>

<script setup lang="ts">
  import { ref, computed, defineProps, defineEmits } from 'vue';

  // Emitir eventos hacia el padre
  const emit = defineEmits(['toggle-expand']);

  // Props para recibir el estado del servicio
  const props = defineProps({
    initialServiceState: { type: String, required: true },
  });

  // Estado de expansión (abierto/cerrado) -> ahora lo maneja el padre
  const isExpanded = ref(false);

  // Lista de estados del servicio
  const serviceStates: any = {
    stateless: { icon: ['fas', 'circle-notch'], color: '#1284ED', text: 'Sin estado' },
    onroute: { icon: ['fas', 'clock'], color: '#f97800', text: 'En camino' },
    delayed: { icon: ['fas', 'triangle-exclamation'], color: '#FFCC00', text: 'Con demora' },
    start: { icon: ['fas', 'hourglass-start'], color: '#07DF81', text: 'Inicio de servicio' },
    end: { icon: ['fas', 'hourglass-end'], color: '#7E8285', text: 'Fin de servicio' },
    noshow: { icon: ['far', 'calendar-xmark'], color: '#d80404', text: '' },
    reset: { icon: ['fas', 'rotate-right'], color: '#9254de', text: '' },
  };

  // Computed para obtener el estado actual basado en la prop
  const currentServiceState = computed(() => {
    return serviceStates[props.initialServiceState] || serviceStates['stateless'];
  });

  // Función para manejar el clic y emitir evento al padre
  const handleClick = () => {
    isExpanded.value = !isExpanded.value; // Cambio de ícono
    emit('toggle-expand'); // Notifica al padre
  };
</script>

<style scoped>
  .monitoring-tag {
    color: #ffffff !important;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.3s ease;
    padding: 10px;
  }
</style>
