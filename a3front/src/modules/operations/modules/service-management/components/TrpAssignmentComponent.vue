<template>
  <a-form layout="vertical" v-bind="{}" class="my-3" @submit.prevent="">
    <a-row>
      <a-col :span="24">
        <a-form-item label="Buscar proveedor:">
          <a-input @input="onSearchInputChange" label-in-value />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="[16, 32]">
      <a-col>
        <a-typography-text strong style="font-size: 16px">Proveedores sugeridos:</a-typography-text>
        <a-table
          rowKey="id"
          expand-icon-as-cell="false"
          expand-icon-column-index="{-1}"
          table-layout="fixed"
          :columns="columns"
          :data-source="dataStore.providers"
          :row-class-name="getRowClassNameAssignedGuides"
          :loading="dataStore.loading"
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
            <template v-else-if="column.key === 'contract'">
              {{ record.contract }}
            </template>
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
              <a-radio
                :value="record.code"
                :checked="radioStore.getSingleProviderCode() === record.code"
                :disabled="record.state === 'error'"
                @change="() => handleChangeProvider(record.code)"
              />
            </template>
            <template v-else>
              {{ record[column.key] }}
            </template>
          </template>
        </a-table>
      </a-col>
      <a-col :span="24" v-if="serviceData?.service_type !== 'PKS'">
        <a-typography-text strong style="font-size: 16px">Escoge el vehículo:</a-typography-text>
        <a-table
          rowKey="index"
          :columns="columnsConfigTRP"
          :data-source="localTrpData"
          :row-class-name="getRowClassNameAssignedGuides"
          :loading="dataStore.loading"
          :pagination="false"
          style="width: 100%"
        >
          <template #bodyCell="{ column, record }">
            <template v-if="column.key === 'selection'">
              <a-radio
                :value="record.type"
                :checked="radioStore.vehicle_type === record.type"
                :disabled="isDisabled(record)"
                @change="() => handleChangeVehicleType(record.type)"
              />
            </template>
            <template v-else-if="!['type', 'available', 'paxs'].includes(column.key)">
              {{ record[column.key] }}
            </template>
          </template>
        </a-table>
      </a-col>
    </a-row>
    <a-row style="padding: 30px 0 5px 0">
      <a-col :span="24">
        <a-checkbox v-model:checked="dataStore.sendServiceOrder">
          Enviar orden de servicio
        </a-checkbox>
      </a-col>
    </a-row>
  </a-form>
</template>

<script lang="ts" setup>
  import { debounce } from 'lodash';
  import { computed, onMounted, ref, watch } from 'vue';
  import { StarOutlined, StarFilled } from '@ant-design/icons-vue';

  import { useColumnStore } from '@operations/shared/stores/column.store';
  import { useDataStore } from '@operations/modules/service-management/store/data.store';
  import { useRadioStore } from '@/modules/operations/modules/service-management/store/radio.store';
  import type { TableRecord } from './UpdateVehicleTypeComponent.vue';

  // Stores
  const columnStore = useColumnStore();
  const dataStore = useDataStore();
  const radioStore = useRadioStore();

  // Props
  const props = defineProps({
    data: {
      type: Object,
      required: true,
    },
  });

  // Propiedades computadas
  const normalizedData = computed(() => (Array.isArray(props.data) ? props.data : [props.data]));

  const selectedData = computed(() => {
    if (normalizedData.value.length === 0) return null;

    return normalizedData.value.reduce((max, current) =>
      (current.total_paxs || 0) > (max.total_paxs || 0) ? current : max
    );
  });

  // Datos derivados de selectedData
  const serviceData = computed(() => {
    if (!selectedData.value) return null;

    return {
      headquarter: selectedData.value.headquarter,
      client_code: selectedData.value.client?.code,
      date_in: selectedData.value.datetime_start,
      paxs: selectedData.value.total_paxs,
      service_type: selectedData.value.service?.type,
    };
  });

  const totalPaxs = computed(() => Number(selectedData.value?.total_paxs || 0));

  // Configuración de columnas
  const columns = columnStore.getColumnsByType('providerAssignment');
  const columnsConfigTRP = columnStore.getColumnsByType('configTRP');

  // Referencias reactivas
  const searchQuery = ref<string>('');
  const localTrpData = ref<any[]>([]);

  // Búsqueda con debounce
  const debouncedSearch = debounce(async (query: string) => {
    const searchParams = { term: query };
    await dataStore.getCarriersByTerm(searchParams);
  }, 300);

  // Manejadores de eventos
  const onSearchInputChange = (event: Event) => {
    const value = (event.target as HTMLInputElement).value;
    searchQuery.value = value;
    debouncedSearch(value);
  };

  const getRowClassNameAssignedGuides = (record: any) =>
    record.state === 'error' ? 'disabled-row' : '';

  const isDisabled = (record: TableRecord): boolean =>
    record.paxs < totalPaxs.value || record.state === 'error';

  const handleChangeProvider = (code: string): void => radioStore.selectProviderCode(code, false);

  const handleChangeVehicleType = (type: string): void => radioStore.selectVehicleType(type);

  // Funciones de lógica de negocio
  // const getZones = async () => {
  //   await dataStore.getZones();

  //   const validZone = dataStore.zones.find(
  //     (zone) =>
  //       zone.headquarter === serviceData.value?.headquarter ||
  //       zone.zones.includes(serviceData.value?.headquarter)
  //   );

  //   console.log('VALID ZONE:', validZone?.headquarter);
  // };

  const getTrpData = async () => {
    if (!serviceData.value) return;

    await dataStore.getZones();
    localTrpData.value = [];

    const payload = {
      headquarter: serviceData.value.headquarter,
      client_code: serviceData.value.client_code,
      date_in: serviceData.value.date_in,
      paxs: serviceData.value.paxs,
      service_type: serviceData.value.service_type,
    };

    await dataStore.getTrp(payload);
    localTrpData.value = [...dataStore.configTRP];
  };

  const shouldFetchTrpData = computed(() => serviceData.value?.service_type !== 'PKS');

  // Hooks de ciclo de vida
  onMounted(() => {
    if (shouldFetchTrpData.value) {
      getTrpData();
    }
  });

  // Watchers
  watch(
    () => selectedData.value?.id,
    (newId, oldId) => {
      if (newId !== undefined && newId !== null && newId !== oldId) {
        if (shouldFetchTrpData.value) {
          getTrpData();
        }
      }
    }
  );
</script>
