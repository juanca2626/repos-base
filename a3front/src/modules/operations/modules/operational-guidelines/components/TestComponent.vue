<template>
  <a-card>
    <a-collapse>
      <a-collapse-panel key="1" header="Todas las sedes" extra="Actualizado el 15/10/2023">
        <a-table :columns="columns" :dataSource="data1" bordered />
      </a-collapse-panel>
      <a-collapse-panel key="2" header="Lima" extra="Actualizado el 23/10/2023">
        <a-alert
          message="Si requieres una configuración de transporte distinta a la mostrada, contáctate con neg@limatours.com.pe"
          type="warning"
          show-icon
        />
        <a-table :columns="columns" :dataSource="data2" bordered />
      </a-collapse-panel>
    </a-collapse>
  </a-card>
</template>

<script setup lang="ts">
  import { reactive } from 'vue';

  const columns = [
    {
      title: 'Tipo de pauta',
      dataIndex: 'type',
      key: 'type',
    },
    {
      title: 'Detalle',
      dataIndex: 'detail',
      key: 'detail',
    },
    {
      title: 'Observaciones',
      dataIndex: 'observations',
      key: 'observations',
    },
    {
      title: 'Acciones',
      key: 'actions',
      customRender: () => {
        return (
          <span>
            <a href="javascript:;">Edit</a>
            <a-divider type="vertical" />
            <a href="javascript:;">Delete</a>
          </span>
        );
      },
    },
  ];

  const data1 = reactive([
    {
      key: '1',
      type: 'Aguas',
      detail: 'Caja de 20 litros',
      observations: 'Colocar solo en el transporte.',
    },
    {
      key: '2',
      type: 'Letrero especial',
      detail: 'Sí',
      observations: 'No cambiar las dimensiones ni el color.',
    },
    {
      key: '3',
      type: 'Uniforme especial',
      detail: 'Sí',
      observations: 'Pantalón azul y camisa con el logo en el lado izquierdo del hombro.',
    },
  ]);

  const data2 = reactive([
    {
      key: '1',
      type: 'Badge especial',
      detail: 'Sí',
      observations: 'No cambiar las dimensiones ni el color.',
    },
    {
      key: '2',
      type: 'Guías',
      detail: 'Preferentes y bloqueados',
      observations: '<div>Preferente: Nombre Apellido</div><div>Bloqueado: Nombre Apellido</div>',
    },
    {
      key: '3',
      type: 'Transporte',
      detail: 'Excursiones',
      observations: '<div>TAUT: 1 PAX</div><div>THOI: 2-4 PAX</div>',
    },
    {
      key: '4',
      type: 'Transporte',
      detail: 'Full days',
      observations: '<div>TAUT: 1 PAX</div><div>THOI: 2-4 PAX</div>',
    },
  ]);
</script>

<style scoped>
  .mb-2 {
    margin-bottom: 16px;
  }
</style>
