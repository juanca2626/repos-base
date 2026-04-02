<template>
  <div class="vehicle-form-container">
    <div class="registered-count">Unidades registradas: {{ count }}</div>

    <div class="vehicle-list-wrapper">
      <div v-for="(v, index) in registeredVehicles" :key="index">
        <div v-if="v.isEditing" class="vehicle-content-box anim-fade-in">
          <div class="box-header">
            <div class="header-left">
              <ChevronBlueIcon class="icon-chevron" />
              <div class="car-icon-wrapper">
                <CarIcon class="icon-car" />
              </div>
              <span class="header-title">Datos de unidad</span>
            </div>
            <SaveIcon class="icon-save" @click="saveUnit(index)" />
          </div>

          <div class="box-form">
            <div class="form-row">
              <div class="form-field field-code">
                <label class="field-label">Código / Nombre</label>
                <a-select
                  v-model:value="v.code"
                  placeholder="Ingresa"
                  class="custom-box-select"
                  :bordered="false"
                  :options="transportCodeOptions"
                  popup-class-name="custom-select-dropdown"
                >
                  <template #suffixIcon><SelectChevronIcon /></template>
                </a-select>
              </div>
              <div class="form-field field-units">
                <label class="field-label">Unidades</label>
                <div class="custom-number-input">
                  <span class="number-value">{{ v.units }}</span>
                  <div class="number-controls">
                    <button class="btn-step" @click="v.units++"><NumUpIcon /></button>
                    <button class="btn-step" @click="v.units > 1 && v.units--">
                      <NumDownIcon />
                    </button>
                  </div>
                </div>
              </div>
            </div>

            <div class="form-row">
              <div class="form-field field-usage">
                <label class="field-label">Uso</label>
                <a-select
                  v-model:value="v.usage"
                  placeholder="Selecciona"
                  class="custom-box-select usage-select"
                  :bordered="false"
                  :options="transportUsageOptions"
                  popup-class-name="custom-select-dropdown"
                >
                  <template #suffixIcon><SelectChevronIcon /></template>
                </a-select>
              </div>

              <div class="form-field field-capacity">
                <div class="label-with-tooltip">
                  <label class="field-label">Rango de capacidad</label>
                  <a-tooltip
                    placement="top"
                    :align="{ offset: [0, 6] }"
                    :overlay-inner-style="{
                      background: 'rgb(33, 33, 33)',
                      color: 'white',
                      width: '199px',
                      height: '42px',
                      display: 'flex',
                      alignItems: 'center',
                      padding: '12px',
                      borderRadius: '8px',
                      fontSize: '12px',
                      marginLeft: '-145px',
                      justifyContent: 'center',
                    }"
                  >
                    <template #title>
                      <span>Unidad de medida: pasajeros</span>
                    </template>
                    <InfoCircleIcon
                      style="cursor: pointer; width: 14px; height: 14px; color: #7e8285"
                    />
                  </a-tooltip>
                </div>
                <div class="range-inputs" :class="{ 'has-error': getCapacityGap(v, index) }">
                  <a-input v-model:value="v.minCapacity" class="range-input">
                    <template #prefix><span class="range-label">MIN</span></template>
                  </a-input>
                  <a-input v-model:value="v.maxCapacity" class="range-input">
                    <template #prefix><span class="range-label">MAX</span></template>
                  </a-input>
                </div>
                <div v-if="getCapacityGap(v, index)" class="capacity-alert anim-fade-in">
                  <AlertIcon />
                  <span class="alert-text"
                    >Capacidad sin cobertura: {{ getCapacityGap(v, index) }}</span
                  >
                </div>
              </div>
            </div>

            <div class="form-row row-checkbox">
              <div class="checkbox-container">
                <a-checkbox v-model:checked="v.includeRepresentative" class="custom-checkbox">
                  <span class="checkbox-text">Incluye representante</span>
                </a-checkbox>
              </div>
              <div v-if="v.includeRepresentative" class="representative-quantity anim-fade-in">
                <span class="qty-label">Cantidad</span>
                <a-input v-model:value="v.representativeQty" class="qty-input" />
              </div>
            </div>
          </div>
        </div>
        <div v-else class="summary-card anim-fade-in">
          <div class="summary-header">
            <div class="header-content">
              <component :is="v.code === 2 ? VanIcon : CarIcon" class="icon-car-summary" />
              <span class="unit-name">{{ getLabel(v.code, transportCodeOptions) }}</span>
              <span class="unit-usage">Uso: {{ getLabel(v.usage, transportUsageOptions) }}</span>
            </div>
            <EditIcon class="icon-edit" @click="editUnit(index)" />
          </div>
          <div class="summary-details">
            <div class="details-column">
              <span class="detail-item"
                >Unidades: <span class="detail-value">{{ v.units }}</span></span
              >
              <span class="detail-item"
                >Representante Incluido:
                <span class="detail-value">{{ v.includeRepresentative ? 'Sí' : 'No' }}</span></span
              >
            </div>
            <div class="details-column">
              <span class="detail-item"
                >Capacidad:
                <span class="detail-value"
                  >{{ v.minCapacity }} - {{ v.maxCapacity }} pax</span
                ></span
              >
            </div>
          </div>
        </div>
      </div>

      <div class="add-unit-container">
        <button class="btn-add-unit" @click="addUnit">
          <PlusIcon />
          <span>Agregar unidad</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed, watch } from 'vue';
  import {
    ChevronBlueIcon,
    CarIcon,
    VanIcon,
    AlertIcon,
    EditIcon,
    SaveIcon,
    NumUpIcon,
    NumDownIcon,
    PlusIcon,
    SelectChevronIcon,
    InfoCircleIcon,
  } from '../icons';
  import { useTransportConfiguration } from '../composables/useTransportConfiguration';
  import type { Vehicle } from '../interfaces/vehicle.interface';

  const { transportCodeOptions, transportUsageOptions } = useTransportConfiguration();

  const props = defineProps<{
    initialUnits?: any[];
  }>();

  const emit = defineEmits(['update:count', 'update:units']);

  const registeredVehicles = ref<Vehicle[]>(
    props.initialUnits && props.initialUnits.length > 0
      ? props.initialUnits.map((u) => ({
          ...u,
          isEditing: u.isEditing !== undefined ? u.isEditing : false,
        }))
      : [
          {
            code: null,
            units: 1,
            usage: null,
            minCapacity: '',
            maxCapacity: '',
            includeRepresentative: false,
            representativeQty: '',
            isEditing: true,
          },
        ]
  );

  const count = computed(
    () => registeredVehicles.value.filter((v) => !v.isEditing || v.code).length
  );

  const getLabel = (value: any, options: any) => {
    const opts = options?.value || options;
    if (!Array.isArray(opts)) return value || 'Sin asignar';
    return opts.find((o: any) => o.value === value)?.label || value || 'Sin asignar';
  };

  watch(
    registeredVehicles,
    (newVal) => {
      emit('update:units', newVal);
      emit('update:count', count.value);
    },
    { deep: true }
  );

  const getCapacityGap = (v: Vehicle, index: number) => {
    if (!v.code || !v.usage || !v.minCapacity) return null;
    const newMin = parseInt(v.minCapacity);
    if (isNaN(newMin)) return null;

    const sameConfigs = registeredVehicles.value.filter(
      (item, idx) =>
        idx !== index &&
        item.code === v.code &&
        item.usage === v.usage &&
        item.minCapacity &&
        item.maxCapacity
    );

    if (sameConfigs.length === 0) return null;

    const ranges = sameConfigs
      .map((item) => ({
        min: parseInt(item.minCapacity),
        max: parseInt(item.maxCapacity),
      }))
      .filter((r) => !isNaN(r.min) && !isNaN(r.max))
      .sort((a, b) => a.min - b.min);

    const rangesBelow = ranges.filter((r) => r.max < newMin);
    if (rangesBelow.length > 0) {
      const highestMaxBelow = Math.max(...rangesBelow.map((r) => r.max));
      if (newMin > highestMaxBelow + 1) {
        return `${highestMaxBelow + 1} - ${newMin - 1}`;
      }
    }

    return null;
  };

  const addUnit = () => {
    const lastUnit = registeredVehicles.value[registeredVehicles.value.length - 1];
    if (
      lastUnit &&
      (!lastUnit.code || !lastUnit.usage || !lastUnit.minCapacity || !lastUnit.maxCapacity)
    ) {
      return;
    }

    registeredVehicles.value.push({
      code: null,
      units: 1,
      usage: null,
      minCapacity: '',
      maxCapacity: '',
      includeRepresentative: false,
      representativeQty: '',
      isEditing: true,
    });
    emit('update:units', registeredVehicles.value);
  };

  const saveUnit = (index: number) => {
    if (!registeredVehicles.value[index].code) return;
    registeredVehicles.value[index].isEditing = false;
    emit('update:count', count.value);
    emit('update:units', registeredVehicles.value);
  };

  const editUnit = (index: number) => {
    registeredVehicles.value[index].isEditing = true;
  };
</script>

<style lang="scss" scoped>
  @import '../styles/VehicleSelectionForm.scss';
</style>
