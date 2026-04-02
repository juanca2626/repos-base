<template>
  <a-collapse v-model:activeKey="activeKeys">
    <a-collapse-panel key="1" header="Todas las sedes">
      <a-table :columns="columns" :data-source="allSedesData" :pagination="false">
        <template #bodyCell="{ column }">
          <template v-if="column.dataIndex === 'actions'">
            <a-space>
              <a-button type="link" icon="EditOutlined" />
              <a-button type="link" icon="DeleteOutlined" />
            </a-space>
          </template>
        </template>
      </a-table>
    </a-collapse-panel>

    <a-collapse-panel key="2" header="Lima">
      <a-alert
        message="Si requieres una configuración de transporte distinta a la mostrada, contáctate con neg@limatours.com.pe"
        type="warning"
        show-icon
      />
      <a-table :columns="columnsLima" :data-source="limaData" :pagination="false">
        <template #bodyCell="{ column, record }">
          <template v-if="column.dataIndex === 'actions'">
            <a-space>
              <a-button type="link" icon="EditOutlined" />
              <a-button type="link" icon="DeleteOutlined" />
            </a-space>
          </template>
          <template v-if="column.dataIndex === 'observaciones' && record.tipo === 'Guías'">
            <span class="prefered">Preferente</span>
            <span class="blocked">Bloqueado</span>
            <span class="blocked">Bloqueado</span>
          </template>
          <template v-if="column.dataIndex === 'observaciones' && record.tipo === 'Transporte'">
            <a-space>
              <a-tag color="blue">TAUT</a-tag>
              <a-tag color="blue">TH01</a-tag>
              <a-tag color="blue">TSPC</a-tag>
              <a-tag color="blue">TSPL</a-tag>
              <a-tag color="blue">TMN8</a-tag>
              <a-tag color="blue">TBUS</a-tag>
            </a-space>
          </template>
        </template>
      </a-table>
    </a-collapse-panel>
  </a-collapse>
</template>

<script>
  import { defineComponent, ref } from 'vue';

  export default defineComponent({
    setup() {
      const activeKeys = ref(['1', '2']);

      const columns = [
        { title: 'Tipo de pauta', dataIndex: 'tipo', key: 'tipo' },
        { title: 'Detalle', dataIndex: 'detalle', key: 'detalle' },
        { title: 'Observaciones', dataIndex: 'observaciones', key: 'observaciones' },
        { title: 'Acciones', dataIndex: 'actions', key: 'actions' },
      ];

      const allSedesData = [
        {
          tipo: 'Aguas',
          detalle: 'Caja de 20 litros',
          observaciones: 'Colocar solo en el transporte.',
        },
        {
          tipo: 'Letrero especial',
          detalle: 'Si',
          observaciones: 'No cambiar las dimensiones ni el color.',
        },
        {
          tipo: 'Uniforme especial',
          detalle: 'Si',
          observaciones: 'Pantalón azul y camisa con el logo en el lado izquierdo del hombro.',
        },
      ];

      const columnsLima = [
        { title: 'Tipo de pauta', dataIndex: 'tipo', key: 'tipo' },
        { title: 'Detalle', dataIndex: 'detalle', key: 'detalle' },
        { title: 'Observaciones', dataIndex: 'observaciones', key: 'observaciones' },
        { title: 'Acciones', dataIndex: 'actions', key: 'actions' },
      ];

      const limaData = [
        {
          tipo: 'Badge especial',
          detalle: 'Si',
          observaciones: 'No cambiar las dimensiones ni el color.',
        },
        { tipo: 'Guías', detalle: 'Preferente y bloqueados', observaciones: '' },
        { tipo: 'Transporte', detalle: 'Excursiones', observaciones: '' },
        { tipo: 'Transporte', detalle: 'Full days', observaciones: '' },
      ];

      return {
        activeKeys,
        columns,
        allSedesData,
        columnsLima,
        limaData,
      };
    },
  });
</script>

<style scoped>
  .prefered {
    color: green;
    margin-right: 8px;
  }
  .blocked {
    color: red;
    margin-right: 8px;
  }
</style>
