<template>
  <a-row class="header-bar" justify="space-between" align="center">
    <a-col>
      <a-typography-title :level="5">
        <font-awesome-icon :icon="`fa-solid fa-${icon}`" />
        {{ title }}
      </a-typography-title>
    </a-col>
    <a-col>
      <a-button v-if="btn" type="primary" class="ant-btn-md" @click="handleAction">
        <font-awesome-icon v-if="btn.icon" :icon="`fa-solid fa-${btn.icon}`" />
        {{ btn.title }}
      </a-button>
    </a-col>
  </a-row>
</template>

<script lang="ts" setup>
  interface Props {
    title: string;
    icon: string;
    btn?: {
      title: string;
      action: string;
      icon: string;
    };
  }

  defineProps<Props>();
  const emit = defineEmits(['handlerShowDrawer', 'otherEvents']);

  const handleAction = () => {
    emit('handlerShowDrawer', true);
  };

  // const notificaciones = ref([
  //   {
  //     body: 'Tienes una nueva solicitud de amistad',
  //     module: 'Usuarios',
  //     actions: ['Aceptar', 'Rechazar'],
  //     confirmation: () => alert('Solicitud confirmada'),
  //   },
  //   {
  //     body: 'Reporte mensual disponible',
  //     module: 'Reportes',
  //     confirmation: () => console.log('Ver reporte'),
  //   },
  //   {
  //     body: 'Cambio de contraseña exitoso',
  //   },
  // ]);
</script>

<style lang="scss" scoped>
  .ant-btn {
    font-size: 15px;
  }
  .header-bar {
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    color: #f9f9f9;
    background-color: rgb(250, 250, 249);
    padding: 20px;
    height: 65px;
    h5 {
      color: #2f353a;
      font-family: Montserrat;
      font-size: 18px;
      font-style: normal;
      font-weight: 600;
      line-height: normal;
    }
    svg {
      margin-right: 10px;
    }
    // .action {
    //   position: absolute;
    //   top: 14px;
    //   right: 20px;
    //   z-index: 9;
    // }
  }
</style>
