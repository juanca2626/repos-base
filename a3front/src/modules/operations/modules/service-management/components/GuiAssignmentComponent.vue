<template>
  <a-form layout="vertical" v-bind="{}" class="my-3" @submit.prevent="">
    <a-row>
      <a-col :span="24">
        <a-form-item label="Buscar guía:">
          <a-input @input="onSearchInputChange" label-in-value />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="[8, 8]">
      <a-col>
        <a-typography-text strong style="font-size: 16px">Proveedores sugeridos:</a-typography-text>
        <a-table
          rowKey="id"
          style="margin: 10px 0"
          expand-icon-as-cell="false"
          expand-icon-column-index="{-1}"
          table-layout="fixed"
          :columns="columns"
          :data-source="dataStore.providers"
          :row-class-name="getRowClassNameAssignedGuides"
          :loading="dataStore.loadingModal"
          :pagination="false"
          :scroll="{ y: 300 }"
        >
          <!-- Iteramos sobre las celdas como antes -->
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'code'">
              {{ record.code }}
            </template>
            <template v-else-if="column.key === 'fullname'">
              {{ record.fullname }}
            </template>
            <template v-else-if="column.key === 'contract'"> {{ record.contract }} </template>
            <template v-else-if="column.key === 'preferent'">
              <StarFilled v-if="record.preferent" :style="{ color: '#ffcc00', fill: '#ffcc00' }" />
              <StarOutlined v-else :style="{ fill: '#BABCBD ' }" />
            </template>
            <template v-else-if="column.key === 'state'">
              <template v-if="record.state === 'success'">
                <a-tag style="font-size: 14px" :color="record.state">
                  <font-awesome-icon :icon="['fas', 'circle-check']" /> Disponible
                </a-tag>
              </template>
              <template v-else-if="record.state === 'error'">
                <a-tag style="font-size: 14px" :color="record.state">
                  <font-awesome-icon :icon="['fas', 'circle-exclamation']" /> No disponible
                </a-tag>
                <span style="display: block; line-height: 15px; margin-top: 5px">
                  <template v-if="record.blocked.source === 'operational-guidelines-service'">
                    Bloqueado por pautas operativas
                  </template>
                  <template v-else-if="record.blocked.source === 'block-calendar-service'">
                    Bloqueado por calendario de bloqueos<br />
                    <a-divider style="margin: 5px 0" />
                    Motivo: {{ record.blocked.data.reason }}
                  </template>
                  <template v-else-if="record.blocked.source === 'assigned-service'">
                    File: {{ record.blocked.data.file }}
                  </template>
                </span>
              </template>
            </template>
            <template v-else-if="column.key === 'selection'">
              <a-checkbox
                :disabled="record.state === 'error'"
                @change="(event: any) => handleCheckboxChange('providerAssignment', record, event)"
              />
            </template>
            <template v-else>
              {{ record[column.key] }}
            </template>
          </template>
        </a-table>
      </a-col>
    </a-row>
    <a-row style="padding: 10px 0">
      <a-col :span="24">
        <a-checkbox v-model:checked="dataStore.sendServiceOrder"
          >Enviar orden de servicio</a-checkbox
        >
      </a-col>
    </a-row>
  </a-form>
</template>

<script lang="ts" setup>
  import { computed, onMounted, ref } from 'vue';
  import { debounce } from 'lodash'; // Puedes usar lodash para simplificar el debounce
  import { StarOutlined, StarFilled } from '@ant-design/icons-vue';

  import { useSelectionStore } from '@operations/shared/stores/selection.store';
  import { useColumnStore } from '@operations/shared/stores/column.store';
  import { useDataStore } from '@operations/modules/service-management/store/data.store';

  const columnStore = useColumnStore();
  const dataStore = useDataStore();
  const selectionStore = useSelectionStore();

  // Props para recibir los datos dinámicos del modal
  const props = defineProps({
    data: {
      type: [Object, Array], // Permite que data sea un objeto o un array
      required: true,
    },
  });

  // Normaliza la data para que siempre sea un array
  const normalizedData = computed(() => {
    return Array.isArray(props.data) ? props.data : [props.data];
  });

  const columns = columnStore.getColumnsByType('providerAssignment');

  // Debounce para la búsqueda
  const searchQuery = ref<string>('');
  const debouncedSearch = debounce(async (query: string) => {
    if (!query.trim()) {
      // Si no hay texto, puedes restablecer los datos originales
      await dataStore.getPreferentsGUI(props.data);
    } else {
      const searchParams = { term: query };
      await dataStore.getGuidesByTerm(searchParams);
    }
  }, 300);

  // Evento para manejar el cambio del input
  const onSearchInputChange = (event: Event) => {
    const value = (event.target as HTMLInputElement).value;
    searchQuery.value = value;
    debouncedSearch(value);
  };

  // Clase para filas deshabilitadas
  const getRowClassNameAssignedGuides = (record: any) => {
    return record.state === 'error' ? 'disabled-row' : '';
  };

  // Manejo de cambios en el checkbox
  const handleCheckboxChange = (type: string, record: any, event: Event) => {
    const isChecked = (event.target as HTMLInputElement).checked;
    selectionStore.toggleItem(type, record, isChecked);
  };

  onMounted(() => {
    dataStore.getPreferentsGUI(normalizedData.value);
  });
</script>
