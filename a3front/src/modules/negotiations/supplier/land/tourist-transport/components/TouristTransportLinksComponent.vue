<template>
  <navigation-button-link-component
    title="¡Gestiona y configura con las siguientes opciones de navegación!"
    :buttons="navigationButtons"
  />
</template>
<script setup lang="ts">
  import { ref } from 'vue';
  import NavigationButtonLinkComponent from '@/modules/negotiations/supplier/components/NavigationButtonLinkComponent.vue';
  interface Button {
    text: string;
    type?: 'primary' | 'default' | 'dashed' | 'text' | 'link';
    onClick: () => void;
  }
  const navigationButtons = ref<Button[]>([
    {
      text: 'Listado de proveedores',
      type: 'primary',
      onClick: () => {
        // Lógica para manejar el clic
        console.log('Listado de proveedores clicked');
      },
    },
    {
      text: 'Listado de preferentes',
      onClick: () => {
        console.log('Listado de preferentes clicked');
      },
    },
    {
      text: 'Plantilla de tarifarios',
      onClick: () => {
        console.log('Plantilla de tarifarios clicked');
      },
    },
    {
      text: 'Notificaciones',
      onClick: () => {
        console.log('Notificaciones clicked');
      },
    },
  ]);
</script>

<style scoped lang="scss"></style>
