<template>
  <a-form layout="vertical">
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
            @change="() => handleRadioChange(record.type)"
          />
        </template>
        <template v-else-if="!['type', 'available', 'paxs'].includes(column.key)">
          {{ record[column.key] }}
        </template>
      </template>
    </a-table>
  </a-form>
</template>

<script lang="ts" setup>
  import { computed, onMounted, onUnmounted, ref, watch } from 'vue';
  import { useColumnStore } from '@operations/shared/stores/column.store';
  import { useDataStore } from '@operations/modules/service-management/store/data.store';
  import { useRadioStore } from '@/modules/operations/modules/service-management/store/radio.store';

  export interface TableRecord {
    type: string;
    paxs: number;
    state: string;
    [key: string]: any;
  }

  interface Props {
    data: {
      assignment: {
        vehicle_type?: string;
      };
      operationalService: {
        total_paxs: number;
      };
    };
  }

  const props = defineProps<Props>();
  console.log('🚀 ~ props.data:', {
    assignment: props.data.assignment,
    operationalService: props.data.operationalService,
  });

  const columnStore = useColumnStore();
  const dataStore = useDataStore();
  const radioStore = useRadioStore();

  // Computed properties
  const normalizedData = computed(() => (Array.isArray(props.data) ? props.data : [props.data]));

  const vehicleType = computed(() => props.data.assignment.vehicle_type);
  const totalPaxs = computed(() => Number(props.data.operationalService.total_paxs));
  const columnsConfigTRP = computed(() => columnStore.getColumnsByType('configTRP'));

  // Methods
  const isDisabled = (record: TableRecord): boolean =>
    record.paxs < totalPaxs.value || record.state === 'error';

  const handleRadioChange = (type: string): void => {
    radioStore.selectVehicleType(type);
  };

  const getRowClassNameAssignedGuides = (record: TableRecord): string =>
    record.state === 'error' ? 'disabled-row' : '';

  // Watchers
  watch(
    [() => dataStore.configTRP, vehicleType],
    ([configTRP, currentVehicleType]) => {
      if (configTRP?.length && currentVehicleType) {
        radioStore.selectVehicleType(currentVehicleType);
      }
    },
    { immediate: true }
  );

  const localTrpData = ref<any[]>([]);

  const headquarter = computed(() => (props.data as any).operationalService?.service.city_in.code);

  const client_code = computed(
    () => (props.data as any).operationalService?.files?.[0]?.file?.client?.code
  );

  const date_in = computed(() => (props.data as any).operationalService?.datetime_start);

  const paxs = computed(() => (props.data as any).operationalService?.total_paxs);

  const service_type = computed(() => (props.data as any).operationalService?.service?.type);

  const getTrpData = async () => {
    localTrpData.value = []; // Limpiar visualmente los datos anteriores

    const payload = {
      headquarter: headquarter.value,
      client_code: client_code.value,
      date_in: date_in.value,
      paxs: paxs.value,
      service_type: service_type.value,
    };

    await dataStore.getTrp(payload);

    // Cuando termina la carga, se asignan los nuevos datos
    localTrpData.value = [...dataStore.configTRP];
  };

  // Lifecycle hooks
  onMounted(async () => {
    await dataStore.getCarriers(normalizedData.value);
    // await initializeVehicleType();
    getTrpData();
  });

  onUnmounted(() => {
    radioStore.clearAll();
  });

  watch(
    () => (props.data as any).operationalService?.id,
    (newId, oldId) => {
      if (newId !== undefined && newId !== null && newId !== oldId) {
        getTrpData();
      }
    }
  );
</script>

<style scoped>
  .disabled-row {
    opacity: 0.5;
    pointer-events: none;
  }
</style>
