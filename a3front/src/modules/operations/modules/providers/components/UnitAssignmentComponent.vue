<template>
  <a-form layout="vertical" v-bind="{}" @submit.prevent="">
    <!-- <pre>
    {{ assignment }}
    </pre> -->
    <!-- <pre>
    {{ props.data }}
    </pre> -->

    <a-row :gutter="[16, 32]">
      <a-col :span="24">
        <a-form-item label="Escoger chofer:">
          <a-select
            v-model:value="selectedDriverId"
            show-search
            :filter-option="filterOptionDriver"
            placeholder="Por favor selecciona un chofer"
          >
            <a-select-option
              v-for="driver in drivers"
              :key="driver._id"
              :value="driver._id"
              :data="driver"
            >
              {{ driver.fullname }} | {{ driver.phone }}
            </a-select-option>
          </a-select>
        </a-form-item>

        <template v-if="selectedDriver">
          <a-space direction="vertical">
            <a-typography-text>
              Nombre:
              <a-typography-text strong>
                {{ selectedDriver.fullname }}
              </a-typography-text>
              <br />
              Celular:
              <a-typography-text strong>
                {{ selectedDriver.phone }}
              </a-typography-text>
            </a-typography-text>
          </a-space>
        </template>
      </a-col>

      <a-col :span="24">
        <a-form-item label="Escoger unidad:">
          <a-select
            v-model:value="selectedVehicleId"
            show-search
            :filter-option="filterOptionVehicle"
            placeholder="Por favor selecciona una unidad"
          >
            <a-select-option
              v-for="vehicle in vehicles"
              :key="vehicle._id"
              :value="vehicle._id"
              :data="vehicle"
            >
              {{ vehicle.plate }} ({{ vehicle.type }})
            </a-select-option>
          </a-select>
        </a-form-item>

        <template v-if="selectedVehicle">
          <a-space direction="vertical">
            <a-typography-text>
              Placa:
              <a-typography-text strong>
                {{ selectedVehicle.plate }}
              </a-typography-text>
              <br />
              Tipo de vehículo:
              <a-typography-text strong>
                {{ selectedVehicle.type }}
              </a-typography-text>
            </a-typography-text>
          </a-space>
        </template>
      </a-col>
    </a-row>
  </a-form>
</template>

<script lang="ts" setup>
  import { onMounted, reactive } from 'vue';
  import { defineProps } from 'vue';
  import { storeToRefs } from 'pinia';

  import { useDataStore } from '@operations/modules/providers/store/data.store';

  const dataStore = useDataStore();
  const {
    drivers,
    vehicles,
    selectedDriver,
    selectedVehicle,
    selectedDriverId,
    selectedVehicleId,
  } = storeToRefs(dataStore);
  // const { formCreateReturn } = storeToRefs(dataStore);

  // Props para recibir los datos dinámicos del modal
  const props = defineProps({
    data: {
      type: [Object, Array], // Permite que data sea un objeto o un array
      required: true,
    },
  });

  const filterOptionDriver = (input: string, option: any) => {
    const driver = option.data; // Obtenemos el objeto completo desde la opción
    return (
      driver.fullname.toLowerCase().includes(input.toLowerCase()) || // Búsqueda por fullname
      driver.dni.toLowerCase().includes(input.toLowerCase()) // Búsqueda por dni
    );
  };

  const filterOptionVehicle = (input: string, option: any) => {
    const vehicle = option.data; // Obtenemos el objeto completo desde la opción
    return (
      vehicle.plate.toLowerCase().includes(input.toLowerCase()) || // Búsqueda por plate
      vehicle.type.toLowerCase().includes(input.toLowerCase()) // Búsqueda por modelo
    );
  };

  const operationalService: any = reactive({ ...props.data });

  const fetchData = async () => {
    const providerCode = operationalService.assignment.provider.code;
    await Promise.all([
      dataStore.getDriversByProvider({ provider: providerCode }),
      dataStore.getVehiclesByProvider({ provider: providerCode }),
    ]);
  };

  onMounted(() => {
    fetchData();
  });
</script>

<style lang="scss" scoped></style>
