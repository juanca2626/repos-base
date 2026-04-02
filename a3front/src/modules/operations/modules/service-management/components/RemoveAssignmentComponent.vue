<template>
  <a-form layout="vertical" v-bind="{}" @submit.prevent="">
    <!-- <pre>
    {{ assignment }}
    </pre> -->
    <a-row :gutter="[16, 16]">
      <a-col :span="24">
        <a-alert message="Se eliminará la asignación del proveedor." type="warning" show-icon />
        <!--
        <a-alert
          v-if="assignment.service_orders.length === 0"
          message="Se eliminará la asignación del proveedor. No existen órdenes de servicio asociadas."
          type="warning"
          show-icon
        />
        <a-alert
          v-else-if="assignment.service_orders.length > 0"
          message="Se eliminará la asignación del proveedor. Al existir una orden de servicio, se procederá a enviar la notificación de cancelación respectiva."
          type="warning"
          show-icon
        />
        -->
      </a-col>

      <a-col :span="24">
        <a-typography-text>
          ¿Seguro que quieres eliminar la asignación del proveedor
          {{ assignment.provider.code }}?
        </a-typography-text>
      </a-col>
    </a-row>
  </a-form>
</template>

<script lang="ts" setup>
  import { onMounted, reactive } from 'vue';

  // Props para recibir los datos dinámicos del modal
  const props = defineProps({
    data: {
      type: [Object, Array], // Permite que data sea un objeto o un array
      required: true,
    },
  });

  // Estado local derivado de las props
  const assignment: any = reactive({ ...props.data });

  onMounted(() => {});
</script>

<style lang="scss" scoped></style>
