<template>
  <a-row type="flex" justify="space-between" align="middle" style="gap: 10px">
    <a-col class="d-flex">
      <a-button
        :disabled="filesStore.isLoadingAsync || !readonly || step > 0"
        @click="handleCloseModify()"
        class="text-600"
        danger
        type="primary"
        size="large"
      >
        <font-awesome-icon :icon="['fas', 'arrow-left']" />
      </a-button>
    </a-col>
    <a-col flex="auto">
      <span class="text-uppercase text-dark-gray text-500"> Modificación de Pasajeros </span>
    </a-col>
    <a-col class="d-flex" style="gap: 7px">
      <template v-if="!flagSimulationProccess">
        <a-button
          v-if="!readonly"
          class="text-600"
          danger
          size="large"
          v-bind:disabled="selected.length == 0"
          @click="handleRemove()"
        >
          <font-awesome-icon :icon="['far', 'trash-can']" size="lg" />
        </a-button>
        <template v-if="flagSimulation">
          <a-button
            class="text-600"
            :disabled="filesStore.isLoadingAsync || step > 0"
            danger
            size="large"
            v-if="readonly"
            @click="readonly = !readonly"
          >
            <font-awesome-icon :icon="['fas', 'user-pen']" size="lg" />
          </a-button>
          <a-button
            class="text-600"
            :disabled="filesStore.isLoadingAsync || step > 0"
            danger
            size="large"
            v-if="!readonly"
            @click="handleSave()"
          >
            <font-awesome-icon :icon="['far', 'floppy-disk']" size="lg" />
          </a-button>
          <a-button
            v-if="readonly"
            class="text-600"
            :disabled="filesStore.isLoadingAsync || step > 0"
            danger
            @click="handleAddPassenger()"
            size="large"
          >
            <font-awesome-icon :icon="['fas', 'user-plus']" size="lg" />
          </a-button>
        </template>
      </template>

      <a-button
        v-else
        type="default"
        class="text-600"
        default
        v-on:click="validateSimulation(false)"
        :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
        size="large"
      >
        <font-awesome-icon :icon="['fas', 'right-from-bracket']" />
      </a-button>
    </a-col>
  </a-row>

  <template
    v-if="
      filesStore.getFile.suggested_accommodation_sgl > 0 ||
      filesStore.getFile.suggested_accommodation_dbl > 0 ||
      filesStore.getFile.suggested_accommodation_tpl > 0
    "
  >
    <div class="mt-3">
      <a-skeleton active v-if="filesStore.isLoadingModifyPaxs" />
      <template v-else>
        <a-table
          size="small"
          class="ant-table-striped"
          bordered
          :showHeader="true"
          :columns="
            columns.filter(
              (column) => (column.dataIndex === 'id' && !readonly) || column.dataIndex !== 'id'
            )
          "
          :pagination="false"
          :data-source="passengersTemp"
        >
          <template #headerCell="{ column }">
            <div class="text-center">
              <small class="text-uppercase">{{ column.title }}</small>
            </div>
          </template>
          <template #bodyCell="{ column, record }">
            <div class="text-center">
              <template v-if="column.dataIndex === 'id'">
                <span class="cursor-pointer" v-on:click="togglePax(record.id)">
                  <font-awesome-icon
                    :icon="['far', selected.indexOf(record.id) > -1 ? 'square-check' : 'square']"
                    :class="selected.indexOf(record.id) > -1 ? 'text-danger' : 'text-dark-gray'"
                    size="lg"
                  />
                </span>
              </template>
              <template v-else-if="column.dataIndex === 'room_type'">
                <template v-if="readonly">
                  <strong>{{
                    record.room_type != undefined &&
                    record.room_type != '' &&
                    record.room_type != null
                      ? t(filesStore.showRoomType(record.room_type))
                      : '-'
                  }}</strong>
                </template>
                <template v-else>
                  <a-select
                    size="small"
                    v-model:value="record.room_type"
                    style="width: 120px"
                    :options="rooms"
                    @change="toggleFlagChanges(true)"
                  ></a-select>
                </template>
              </template>
              <template v-else-if="column.dataIndex === 'accommodation'">
                <template v-if="record.room_type > 1">
                  <template v-if="readonly">
                    <template v-for="pax in record.accommodation">
                      <a-tag color="warning">
                        <template v-if="pax.name || pax.surnames">
                          {{ pax.name }} {{ pax.surnames }}
                        </template>
                        <template v-else>
                          <i style="border-bottom: 1px dashed #ccc">Pasajero sin nombre</i>
                        </template>
                      </a-tag>
                    </template>
                  </template>
                  <template v-else>
                    <a-select
                      size="small"
                      mode="tags"
                      id="passengers"
                      style="width: 100%"
                      :status="
                        parseInt(accommodations[record.file_passenger_id].length) ===
                        parseInt(record.room_type - 1)
                          ? ``
                          : `error`
                      "
                      v-model:value="accommodations[record.file_passenger_id]"
                      max-tag-count="responsive"
                      :field-names="{ label: 'label', value: 'file_passenger_id' }"
                      @change="(value) => updatePassengerAccommodation(record, value)"
                      :options="options[record.file_passenger_id]"
                    >
                    </a-select>
                  </template>
                </template>
              </template>
              <template v-else-if="column.dataIndex === 'cost_by_passenger'">
                <b v-if="record.cost_by_passenger > 0">
                  $ {{ formatNumber({ number: record.cost_by_passenger, digits: 2 }) }}
                </b>
              </template>
              <template v-else>
                {{ record[column.dataIndex] }}
              </template>
            </div>
          </template>
        </a-table>
      </template>
    </div>
  </template>
  <template v-else>
    <a-alert type="warning">
      No hay acomodos disponibles para hacer la modificación de pasajeros
    </a-alert>
  </template>

  <template v-if="flagChanges && readonly">
    <div class="mt-3" v-if="flagSimulation">
      <template v-if="step == 0">
        <simulation-service-list
          :quantity_adults="quantity_adults"
          :quantity_children="quantity_children"
          :count_sgl="count_sgl"
          :count_dbl="count_dbl"
          :count_tpl="count_tpl"
          v-if="flagSimulationProccess"
        />

        <!-- template>
          <a-alert type="info" class="mb-3">
            <template #description>
              <a-row type="flex" justify="start" align="top" style="gap: 10px">
                <a-col>
                  <i class="bi bi-exclamation-circle" style="font-size: 18px"></i>
                </a-col>
                <a-col>
                  <p class="text-700 mb-1 p-0">Error en la simulación</p>
                  La simulación no encontró hoteles disponibles en las fechas actuales. Por lo tanto
                  no se puede completar la simulación.
                </a-col>
              </a-row>
            </template>
          </a-alert>
        </template -->

        <div class="box-buttons">
          <a-row type="flex" justify="end" align="middle">
            <a-col>
              <a-button
                v-if="!flagSimulationProccess || filesStore.getSimulations.length === 0"
                type="default"
                class="mx-2 px-4 text-600"
                v-on:click="handleA2()"
                default
                :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
                size="large"
              >
                {{ t('global.label.goto') }}
                <span class="text-lowercase"> {{ t('files.button.quote') }}</span>
              </a-button>
              <a-button
                type="primary"
                class="mx-2 px-4 text-600"
                v-on:click="nextStep()"
                default
                v-if="flagSimulationProccess && filesStore.getSimulations.length > 0"
                :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
                size="large"
              >
                Actualizar el File
              </a-button>
              <a-button
                v-if="!flagSimulationProccess && flagSimulation && flagChanges"
                type="default"
                class="mx-2 px-4 text-600"
                v-on:click="handleA2()"
                default
                :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
                size="large"
              >
                <font-awesome-icon :icon="['fas', 'right-from-bracket']" />
              </a-button>
            </a-col>
          </a-row>
        </div>
      </template>

      <template v-if="step == 1">
        <template v-if="filesStore.getSimulations.length > 0">
          <template
            v-for="(_simulation, s) in filesStore.getSimulations"
            :key="'item-simulation-' + i"
          >
            <hotel-merge
              v-if="_simulation.type == 'hotel'"
              type="new"
              :filter="_simulation.params"
              :show_communication="true"
              :flag_simulation="true"
              :title="false"
              :to="[_simulation.hotel]"
              :buttons="false"
              ref="items"
            />

            <service-merge
              type="new"
              v-if="_simulation.type == 'service'"
              :filter="_simulation.params"
              :flag_simulation="true"
              :show_communication="true"
              :title="false"
              :to="[_simulation.service]"
              :buttons="false"
              ref="items"
            />
          </template>
        </template>
      </template>
    </div>
    <div class="mt-3" v-else>
      <a-alert type="info" class="mb-3">
        <template #description>
          <a-row type="flex" justify="start" align="top" style="gap: 10px">
            <a-col>
              <i class="bi bi-exclamation-circle" style="font-size: 18px"></i>
            </a-col>
            <a-col>
              <p class="text-700 mb-1 p-0">Error en la simulación</p>
              La simulación no puede procesarse porque la fecha del FILE es pasada. La recomendación
              es cotizar nuevamente los servicios.
            </a-col>
          </a-row>
        </template>
      </a-alert>

      <div class="box-buttons">
        <a-row type="flex" justify="end" align="middle">
          <a-col>
            <a-button
              type="default"
              class="mx-2 px-4 text-600"
              v-on:click="handleA2()"
              default
              :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              {{ t('global.label.goto') }}
              <span class="text-lowercase"> {{ t('files.button.quote') }}</span>
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>
  </template>

  <a-modal v-model:visible="modal_add" title="Nuevo Pasajero" :width="450">
    <a-alert
      class="text-warning"
      type="warning"
      show-icon
      description="Al agregar nuevos pasajeros entrarás en el simulador de costos."
    >
    </a-alert>

    <div id="files-layout">
      <div class="d-block w-100 mt-3">
        <a-row type="flex" justify="space-between">
          <div class="d-flex">
            <div class="passengers-group">
              <div class="passenger-input">
                <span class="cursor-pointer" @click="flag_adult = !flag_adult">
                  <font-awesome-icon
                    :icon="['far', flag_adult ? 'square-check' : 'square']"
                    :class="flag_adult ? 'text-danger' : 'text-dark-gray'"
                    size="xl"
                  />
                </span>
                <small
                  class="passenger-label cursor-pointer text-uppercase"
                  @click="flag_adult = !flag_adult"
                  >{{ t('global.label.adults') }}</small
                >
                <template v-if="flag_adult">
                  <a-input
                    v-model="form.adult"
                    :value="form.adult"
                    min="0"
                    :readonly="true"
                    class="passenger-value border-gray-500"
                  />
                  <div class="passenger-controls">
                    <a-button @click="() => increment('adult')" class="passenger-button">
                      <template #icon><font-awesome-icon :icon="['fas', 'chevron-up']" /></template>
                    </a-button>
                    <a-button @click="() => decrement('adult')" class="passenger-button">
                      <template #icon
                        ><font-awesome-icon :icon="['fas', 'chevron-down']"
                      /></template>
                    </a-button>
                  </div>
                </template>
              </div>
            </div>
          </div>
          <div class="d-flex">
            <div class="passengers-group">
              <div class="passenger-input">
                <span class="cursor-pointer" @click="flag_child = !flag_child">
                  <font-awesome-icon
                    :icon="['far', flag_child ? 'square-check' : 'square']"
                    :class="flag_child ? 'text-danger' : 'text-dark-gray'"
                    size="xl"
                  />
                </span>
                <small
                  class="passenger-label cursor-pointer text-uppercase"
                  @click="flag_child = !flag_child"
                  >{{ t('global.label.children') }}</small
                >
                <template v-if="flag_child">
                  <a-input
                    v-model="form.children"
                    :value="form.children"
                    min="0"
                    :readonly="true"
                    class="passenger-value border-gray-500"
                  />
                  <div class="passenger-controls">
                    <a-button @click="() => increment('children')" class="passenger-button">
                      <template #icon><font-awesome-icon :icon="['fas', 'chevron-up']" /></template>
                    </a-button>
                    <a-button @click="() => decrement('children')" class="passenger-button">
                      <template #icon
                        ><font-awesome-icon :icon="['fas', 'chevron-down']"
                      /></template>
                    </a-button>
                  </div>
                </template>
              </div>
            </div>
          </div>
        </a-row>
      </div>

      <div class="d-block w-100 mt-3">
        <div class="bg-light p-4" v-if="flag_child && form.children > 0">
          <span class="d-block text-500 mb-2" style="font-size: 12px">
            {{ t('global.column.birthdate') }}:
            <b class="text-danger text-700" style="font-size: 14px">*</b>
          </span>

          <a-form :label-col="{ style: { width: '100px' } }" :wrapper-col="{ span: 14 }">
            <template v-for="count in form.children">
              <a-form-item>
                <template #label> {{ t('global.label.child') }} #{{ count }} </template>
                <a-date-picker
                  value-format="YYYY-MM-DD"
                  :disabledDate="disabledDate"
                  v-model:value="form.dates[count - 1]"
                />
              </a-form-item>
            </template>
          </a-form>
        </div>
      </div>
    </div>

    <template #footer>
      <div class="text-center">
        <a-button
          type="default"
          default
          @click="handleCancel"
          v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          {{ t('global.button.cancel') }}
        </a-button>
        <a-button
          type="primary"
          primary
          :loading="loading"
          @click="handleProcessAdd"
          v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          {{ t('global.button.continue') }}
        </a-button>
      </div>
    </template>
  </a-modal>
</template>

<script setup>
  import { onBeforeMount, ref } from 'vue';
  import { notification } from 'ant-design-vue';
  import { debounce } from 'lodash-es';
  import { useFilesStore } from '@/stores/files';
  import { formatNumber } from '@/utils/files.js';
  import { useI18n } from 'vue-i18n';
  import dayjs from 'dayjs';
  import SimulationServiceList from '../reusables/SimulationServiceList.vue';
  import HotelMerge from '../reusables/HotelMerge.vue';
  import ServiceMerge from '../reusables/ServiceMerge.vue';
  import Cookies from 'js-cookie';

  const { t } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();
  const emit = defineEmits(['onCloseModify']);

  const modal_add = ref(false);
  const flag_adult = ref(false);
  const flag_child = ref(false);
  const selected = ref([]);
  const readonly = ref(true);
  const form = ref({
    adult: 0,
    children: 0,
    dates: [],
  });
  const passengersTemp = ref([]);

  const items = ref(null);

  const rooms = [
    { label: 'SIMPLE', value: '1' },
    { label: 'DOBLE', value: '2' },
    { label: 'TRIPLE', value: '3' },
  ];

  const handleCloseModify = () => {
    emit('onCloseModify');
  };

  const columns = [
    {
      title: '',
      dataIndex: 'id',
    },
    {
      title: 'Tipo de pasajero',
      dataIndex: 'type',
    },
    {
      title: 'Nombre',
      dataIndex: 'name',
    },
    {
      title: 'Apellido',
      dataIndex: 'surnames',
    },
    {
      title: 'Tipo de habitación',
      dataIndex: 'room_type',
    },
    {
      title: 'Acomodo',
      dataIndex: 'accommodation',
    },
    {
      title: 'Fecha de nacimiento',
      dataIndex: 'date_birth',
    },
    {
      title: 'Costo por pax',
      dataIndex: 'cost_by_passenger',
    },
  ];

  const handleAddPassenger = () => {
    modal_add.value = true;
  };

  const handleCancel = () => {
    modal_add.value = false;
  };

  const paxMax = ref(0);
  const accommodationMax = ref(0);

  const handleProcessAdd = async () => {
    const adults = parseInt(form.value?.adult || 0);
    const children = parseInt(form.value?.children || 0);
    const total = adults + children;

    for (let item = 1; item <= total; item++) {
      accommodationMax.value++;
      paxMax.value++;

      let id = accommodationMax.value;
      let file_id_passenger = paxMax.value;
      let type = item <= adults ? 'ADL' : 'CHD';
      let name = 'PAX';
      let surname = `${type} #${item}`;

      let passenger = {
        id: id,
        file_passenger_id: file_id_passenger,
        name: name,
        surnames: surname,
        date_birth: type == 'CHD' ? form.value.dates[item - adults] : null,
        type: type,
        room_type: '1',
        accommodation: [],
        cost_by_passenger: '-',
        label: `${name} ${surname}`,
      };

      passengersTemp.value.push(passenger);
      validateAccommodations();
    }

    flagChanges.value = true;
    readonly.value = false;

    handleCancel();
  };

  const handleRemove = () => {
    passengersTemp.value = passengersTemp.value.filter(
      (passenger) => !selected.value.includes(passenger.id)
    );

    selected.value = [];

    validateAccommodations();
  };

  const quantity_adults = ref(0);
  const quantity_children = ref(0);
  const count_sgl = ref(0);
  const count_dbl = ref(0);
  const count_tpl = ref(0);

  const handleSave = () => {
    readonly.value = true;

    if (filesStore.getFile.dateIn <= dayjs().format('YYYY-MM-DD')) {
      return false;
    }

    quantity_adults.value = 0;
    quantity_children.value = 0;
    count_sgl.value = 0;
    count_dbl.value = 0;
    count_tpl.value = 0;

    passengersTemp.value.forEach((passenger) => {
      if (passenger.type == 'ADL') {
        quantity_adults.value += 1;
      }

      if (passenger.type == 'CHD') {
        quantity_children.value += 1;
      }

      if (passenger.room_type == 1) {
        count_sgl.value += 1;
      }

      if (passenger.room_type == 2) {
        count_dbl.value += 1;
      }

      if (passenger.room_type == 3) {
        count_tpl.value += 1;
      }
    });

    if (readonly.value && flagChanges.value) {
      flagSimulationProccess.value = true;
    }
  };

  const togglePax = (_pax) => {
    let index = selected.value.indexOf(_pax);
    if (index > -1) {
      selected.value.splice(index, 1);
    } else {
      selected.value.push(_pax);
    }
  };

  const accommodations = ref({});
  const options = ref({});
  // const paxsIgnored = ref([]);

  const increment = (type) => {
    const currentValue = parseInt(form.value[type], 10) || 0;
    updatePassenger(type, currentValue + 1);
  };

  const decrement = (type) => {
    const currentValue = parseInt(form.value[type], 10) || 0;
    updatePassenger(type, currentValue - 1);
  };

  const updatePassenger = (type, currentValue) => {
    if (currentValue >= 0) {
      form.value[type] = currentValue;
    }
  };

  const flagChanges = ref(false);
  const flagSimulation = ref(true);
  const flagSimulationProccess = ref(false);

  const updatePassengerAccommodation = debounce((record, value) => {
    console.log('ACOMODO: ', record.accommodation, value);
    // -----------------------------------------------------------------------------------
    localStorage.setItem('accommodation_previous', JSON.stringify(record.accommodation));
    record.accommodation = value;

    const passengerList = options.value?.[record.file_passenger_id] || [];
    const passengerIds = Array.isArray(value) ? value : [];

    const flagPermitted =
      passengerList.some(
        (passenger) =>
          passenger.room_type >= record.room_type &&
          passengerIds.includes(passenger.file_passenger_id)
      ) || passengerIds.length === 0;

    if (!flagPermitted) {
      const accomodation_previous = JSON.parse(localStorage.getItem('accommodation_previous'));
      record.accommodation = accomodation_previous;
      accommodations.value[record.file_passenger_id] = accomodation_previous;

      notification.error({
        message: 'Error de asignación',
        description: `El pasajero no está permitido en esta asignación.`,
      });

      localStorage.removeItem('accommodation_previous');
      return false;
    }

    if (!(record.accommodation.length <= record.room_type - 1 && record.type == 'ADL')) {
      const accomodation_previous = JSON.parse(localStorage.getItem('accommodation_previous'));
      record.accommodation = accomodation_previous;
      accommodations.value[record.file_passenger_id] = accomodation_previous;

      notification.error({
        message: 'Error de asignación',
        description: `No está permitida la asignación seleccionada. Por favor, intente nuevamente.`,
      });

      localStorage.removeItem('accommodation_previous');
      return false;
    }
  }, 350);

  const toggleFlagChanges = (newValue) => {
    flagChanges.value = newValue;
  };

  const validateAccommodations = async () => {
    accommodations.value = {};
    options.value = {};

    for (const record of passengersTemp.value) {
      console.log(record.accommodation);
      if (record.accommodation) {
        accommodations.value[record.file_passenger_id] = record.accommodation.map((pax) => pax.id);
        options.value[record.file_passenger_id] = passengersTemp.value.filter(
          (pax) => pax.file_passenger_id !== record.file_passenger_id
        );
      }
    }
  };

  const validateSimulation = async (flag_simulation = true) => {
    await filesStore.fetchModifyPaxs();
    passengersTemp.value = JSON.parse(JSON.stringify(filesStore.getModifyPaxs));

    passengersTemp.value.forEach((pax) => {
      paxMax.value = pax.file_passenger_id > paxMax.value ? pax.file_passenger_id : paxMax.value;
      accommodationMax.value = pax.id > accommodationMax.value ? pax.id : accommodationMax.value;
    });

    validateAccommodations();

    if (flag_simulation) {
      flagSimulation.value = disabledDate(new Date());
    } else {
      flagSimulation.value = flag_simulation;
      flagChanges.value = flag_simulation;
    }
  };

  onBeforeMount(async () => {
    await validateSimulation();
  });

  const disabledDate = (current) => {
    return current && current < dayjs(filesStore.getFile.dateIn, 'YYYY-MM-DD');
  };

  const handleA2 = () => {
    Cookies.set('a3_client_code', filesStore.getFile.clientCode, { domain: window.DOMAIN });
    Cookies.set('a3_client_id', filesStore.getFile.clientId, { domain: window.DOMAIN });

    localStorage.setItem('a3_file_id', filesStore.getFile.id);
    window.location.href = window.url_app + 'quotes';
  };

  const step = ref(0);

  const nextStep = () => {
    step.value++;
  };
</script>
