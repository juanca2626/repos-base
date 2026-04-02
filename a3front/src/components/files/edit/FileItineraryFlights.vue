<template>
  <template v-if="loading">
    <a-skeleton active :title="false" :class="['mt-3', data?.class]" />
  </template>
  <template v-else>
    <template v-for="(flight, f) in data.flights" :key="f">
      <template v-if="filesStore.isLoadingPassengers">
        <a-skeleton :paragraph="{ rows: 2 }" :title="false" active />
      </template>
      <a-row
        v-else
        :class="[
          'itineraries mb-1',
          f == 0 ? data?.class : '',
          showItinerary == flight.id ? 'active' : '',
        ]"
        justify="space-between"
        align="bottom"
        style="gap: 10px"
      >
        <a-col :span="2">
          <label for="">
            <small :class="{ 'no-visible': f > 0 }" class="text-500">PNR</small>
            <a-input
              :disabled="flightStore.isLoading"
              size="middle"
              type="text"
              min="0"
              class="form-control input-flight w-100"
              v-model:value="flight.pnr"
              placeholder="Escribe aquí .."
              maxlength="40"
              v-if="flight.form_on"
            />
            <template v-else>
              <span class="flight-labels" v-if="flight.pnr && flight.pnr !== ''">{{
                flight.pnr
              }}</span>
              <span class="flight-labels" v-else>-</span>
            </template>
          </label>
        </a-col>
        <a-col :span="4">
          <label for="">
            <small :class="{ 'text-uppercase': true, 'no-visible': f > 0 }" class="text-500">{{
              t('files.label.airline')
            }}</small>
            <a-select
              size="middle"
              :options="airlinesOptions.data"
              v-model:value="flight.airline_code"
              label="name"
              placeholder="Seleccione"
              showSearch
              label-in-value
              :filter-option="false"
              @search="searchAirline"
              class="w-100"
              v-if="flight.form_on"
            >
            </a-select>
            <template v-else>
              <span class="flight-labels" v-if="flight.airline_code && flight.airline_code !== ''">
                <a-tooltip>
                  <template
                    #title
                    v-if="
                      (typeof flight.airline_code === 'object'
                        ? flight.airline_code.label
                        : flight.airline_name
                      ).length > 17
                    "
                  >
                    <small class="text-uppercase">
                      {{
                        typeof flight.airline_code === 'object'
                          ? flight.airline_code.label
                          : flight.airline_name
                      }}
                    </small>
                  </template>
                  {{
                    truncateString(
                      typeof flight.airline_code === 'object'
                        ? flight.airline_code.label
                        : flight.airline_name,
                      17
                    )
                  }}
                </a-tooltip>
              </span>
              <span class="flight-labels" v-else>-</span>
            </template>
          </label>
        </a-col>
        <a-col :span="3">
          <label for="">
            <small :class="{ 'text-uppercase': true, 'no-visible': f > 0 }" class="text-500">
              {{ t('files.label.number_flight') }}
            </small>
            <a-input
              v-model:value="flight.airline_number"
              :disabled="flightStore.isLoading"
              size="middle"
              type="text"
              min="0"
              class="form-control input-flight w-100"
              placeholder="Escribe aquí .."
              maxlength="40"
              v-if="flight.form_on"
            />
            <template v-else>
              <span
                class="flight-labels"
                v-if="flight.airline_number && flight.airline_number !== ''"
              >
                {{ flight.airline_number }}
              </span>
              <span class="flight-labels" v-else>-</span>
            </template>
          </label>
        </a-col>
        <a-col :span="5">
          <label for="">
            <small :class="{ 'text-uppercase': true, 'no-visible': f > 0 }" class="text-500">{{
              t('files.label.arrival_departure_hour')
            }}</small>
            <template v-if="flight.form_on">
              <a-row type="flex" align="middle" justify="space-between">
                <a-col>
                  <font-awesome-icon :icon="['fas', 'plane-departure']" class="text-dark-gray" />
                </a-col>
                <a-col>
                  <base-input-time
                    :allowClear="false"
                    value-format="HH:mm"
                    v-model="flight.departure_time"
                    format="HH:mm"
                    @input="updateTimeFlight($event, flight, f, 'departure_time')"
                  />
                </a-col>
                <a-col>
                  <font-awesome-icon :icon="['fas', 'arrow-right']" class="text-gray" />
                </a-col>
                <a-col>
                  <font-awesome-icon :icon="['fas', 'plane-arrival']" class="text-dark-gray" />
                </a-col>
                <a-col>
                  <base-input-time
                    :allowClear="false"
                    value-format="HH:mm"
                    v-model="flight.arrival_time"
                    format="HH:mm"
                    @input="updateTimeFlight($event, flight, f, 'arrival_time')"
                  />
                </a-col>
              </a-row>
            </template>
            <template v-else>
              <span class="flight-labels" v-if="flight.arrival_time && flight.arrival_time !== ''">
                <a-row type="flex" align="middle" justify="space-between" style="gap: 12px">
                  <a-col>
                    <font-awesome-icon :icon="['fas', 'plane-departure']" class="text-dark-gray" />
                  </a-col>
                  <a-col>
                    {{ flight.departure_time }}
                  </a-col>
                  <a-col>
                    <font-awesome-icon :icon="['fas', 'arrow-right']" class="text-gray" />
                  </a-col>
                  <a-col>
                    <font-awesome-icon :icon="['fas', 'plane-arrival']" class="text-dark-gray" />
                  </a-col>
                  <a-col>
                    {{ flight.arrival_time }}
                  </a-col>
                </a-row>
              </span>
              <span class="flight-labels" v-else>-</span>
            </template>
          </label>
        </a-col>
        <a-col :span="7">
          <label for="">
            <small :class="{ 'text-uppercase': true, 'no-visible': f > 0 }" class="text-500">{{
              t('global.label.passengers')
            }}</small>
            <a-select
              v-model:value="flight.passengersSelect"
              placeholder="Selecciona pasajeros"
              mode="multiple"
              max-tag-count="responsive"
              size="middle"
              class="w-100 d-block"
              :options="passenger_options"
              :disabled="flightStore.isLoading"
              v-if="flight.form_on"
            />
            <template v-else>
              <span
                class="flight-labels"
                v-if="flight.passengersSelect && flight.passengersSelect.length > 0"
              >
                <label>
                  {{ truncateString(flight.passengersSelect?.[0]?.label ?? '', 40) }}
                </label>
                <label v-if="flight.passengersSelect.length > 1">
                  +{{ flight.passengersSelect.length - 1 }}
                </label>
              </span>
              <span class="flight-labels" v-else>-</span>
            </template>
          </label>
        </a-col>
        <a-col>
          <a-row
            type="flex"
            justify="space-between"
            align="bottom"
            style="gap: 7px"
            :class="[flight.form_on ? 'mb-1' : '']"
          >
            <a-col>
              <template
                v-if="
                  flight.airline_code &&
                  flight.airline_number &&
                  flight.departure_time &&
                  flight.arrival_time &&
                  flight.passengersSelect &&
                  flight.passengersSelect.length > 0
                "
              >
                <font-awesome-icon :icon="['fas', 'circle-check']" size="lg" class="text-success" />
              </template>
              <template v-else>
                <font-awesome-icon
                  :icon="['fas', 'triangle-exclamation']"
                  class="text-warning"
                  size="lg"
                  fade
                />
              </template>
            </a-col>
            <a-col>
              <span class="cursor-pointer">
                <a-tooltip>
                  <template #title>
                    <template v-if="flight.passengersSelect && flight.passengersSelect.length > 0">
                      {{ t('files.label.update_flight') }}
                    </template>
                    <template v-else>
                      {{ t('files.message.minimal_information_to_save') }}</template
                    >
                  </template>
                  <template v-if="flight.form_on === true">
                    <font-awesome-icon
                      @click="
                        updateSubFlight(flight);
                        flight.form_on = false;
                      "
                      icon="fa-solid fa-save"
                      v-if="
                        flight.passengersSelect &&
                        flight.passengersSelect.length > 0 &&
                        !flightStore.isLoading
                      "
                    />
                    <font-awesome-icon style="opacity: 0.5" icon="fa-solid fa-save" v-else />
                  </template>
                  <template v-else>
                    <font-awesome-icon
                      @click="flight.form_on = !flight.form_on"
                      icon="fa-solid fa-edit"
                    />
                  </template>
                </a-tooltip>
              </span>
            </a-col>
            <a-col>
              <span class="cursor-pointer" @click="willRemoveFlight(flight.id)">
                <a-tooltip>
                  <template #title>
                    {{ t('files.label.delete_flight') }}
                  </template>
                  <template v-if="!flightStore.isLoading">
                    <font-awesome-icon :icon="['fas', 'trash-can']" />
                  </template>
                  <template v-else>
                    <font-awesome-icon :icon="['fas', 'trash-can']" style="opacity: 0.5" />
                  </template>
                </a-tooltip>
              </span>
            </a-col>
            <a-col v-if="flight.form_on">
              <a-tooltip>
                <template #title>{{ t('global.button.close') }}</template>
                <span class="cursor-pointer" @click="flight.form_on = !flight.form_on">
                  <font-awesome-icon :icon="['far', 'circle-xmark']" />
                </span>
              </a-tooltip>
            </a-col>
          </a-row>
        </a-col>
      </a-row>
    </template>
  </template>

  <template v-if="itinerary.show_new_flight">
    <a-row
      :class="['itineraries', 'mb-1', data.flights.length === 0 ? data?.class : '']"
      justify="space-between"
      align="bottom"
      style="gap: 10px"
    >
      <a-col :span="2">
        <label for="">
          <small :class="{ 'no-visible': data.flights.length > 0 }" class="text-500">PNR</small>
          <a-input
            :disabled="flightStore.isLoading"
            size="middle"
            type="text"
            min="0"
            class="form-control input-flight w-100"
            v-model:value="new_flight.pnr"
            placeholder="Escribe aquí .."
            maxlength="40"
          />
        </label>
      </a-col>
      <a-col :span="4">
        <label for="">
          <small :class="{ 'text-uppercase': true, 'no-visible': data.flights.length > 0 }">{{
            t('files.label.airline')
          }}</small>
          <a-select
            size="middle"
            :options="airlinesOptions.data"
            :not-found-content="airlinesOptions.fetching ? undefined : null"
            v-model:value="new_flight.airline_code"
            placeholder="Seleccione"
            showSearch
            label-in-value
            :filter-option="false"
            @search="searchAirline"
            class="w-100"
          >
            <template v-if="airlinesOptions.fetching" #notFoundContent>
              <a-spin size="small" />
            </template>
          </a-select>
        </label>
      </a-col>
      <a-col :span="3">
        <label for="">
          <small :class="{ 'text-uppercase': true, 'no-visible': data.flights.length > 0 }">{{
            t('files.label.number_flight')
          }}</small>
          <a-input
            :disabled="flightStore.isLoading"
            v-model:value="new_flight.airline_number"
            size="middle"
            type="text"
            min="0"
            class="form-control input-flight w-100"
            placeholder="Escribe aquí .."
            maxlength="40"
          />
        </label>
      </a-col>
      <a-col :span="5">
        <label for="">
          <small :class="{ 'text-uppercase': true, 'no-visible': data.flights.length > 0 }">{{
            t('files.label.arrival_departure_hour')
          }}</small>
          <a-row type="flex" align="middle" justify="space-between">
            <a-col>
              <font-awesome-icon :icon="['fas', 'plane-departure']" class="text-dark-gray" />
            </a-col>
            <a-col>
              <base-input-time
                :allowClear="false"
                value-format="HH:mm"
                v-model="new_flight.departure_time"
                format="HH:mm"
                @input="updateTimeNewFlight($event, new_flight, 'departure_time')"
              />
            </a-col>
            <a-col>
              <font-awesome-icon :icon="['fas', 'arrow-right']" class="text-gray" />
            </a-col>
            <a-col>
              <font-awesome-icon :icon="['fas', 'plane-arrival']" class="text-dark-gray" />
            </a-col>
            <a-col>
              <base-input-time
                :allowClear="false"
                value-format="HH:mm"
                v-model="new_flight.arrival_time"
                format="HH:mm"
                @input="updateTimeNewFlight($event, new_flight, 'arrival_time')"
              />
            </a-col>
          </a-row>
        </label>
      </a-col>
      <a-col :span="7">
        <label for="">
          <small :class="{ 'text-uppercase': true, 'no-visible': data.flights.length > 0 }">
            {{ t('files.label.passengers') }}
          </small>
          <a-select
            v-model:value="new_flight.passengersSelect"
            placeholder="Selecciona pasajeros"
            mode="multiple"
            max-tag-count="responsive"
            size="middle"
            class="w-100 d-block"
            :options="new_passenger_options"
            :disabled="flightStore.isLoading"
          />
        </label>
      </a-col>
      <a-col>
        <div class="d-flex cursor-pointer mb-2">
          <a-tooltip>
            <template #title>
              <template
                v-if="new_flight.passengersSelect && new_flight.passengersSelect.length > 0"
              >
                {{ t('global.button.save') }} {{ t('global.label.new') }}
              </template>
              <template v-else> {{ t('files.message.minimal_information_to_save') }}</template>
            </template>
            <font-awesome-icon
              @click="storeSubFlight()"
              icon="fa-solid fa-save"
              v-if="
                new_flight.passengersSelect &&
                new_flight.passengersSelect.length > 0 &&
                !flightStore.isLoading
              "
            />
            <font-awesome-icon style="opacity: 0.5" icon="fa-solid fa-save" v-else />
          </a-tooltip>
        </div>
      </a-col>
    </a-row>
  </template>

  <a-modal v-model:visible="showModalRemoveFlight" :maskClosable="false" class="text-center">
    <h3>Eliminar vuelo</h3>
    <p>Recuerde que luego de proceder no podrá recuperar la información de vuelos.</p>
    <h5>¿Deseas continuar?</h5>
    <template #footer>
      <div class="text-center">
        <a-button
          type="default"
          class="bnt-default"
          default
          :disabled="flightStore.isLoading"
          @click="handleCancel"
          size="large"
        >
          {{ t('global.button.cancel') }}
        </a-button>
        <a-button
          type="primary"
          primary
          :loading="flightStore.isLoading"
          @click="removeFlight"
          size="large"
        >
          <span :class="flightStore.isLoading ? 'ms-2' : ''">
            {{ t('global.button.continue') }}
          </span>
        </a-button>
      </div>
    </template>
  </a-modal>
</template>

<script setup>
  import { ref, toRefs, computed, reactive, onMounted } from 'vue';
  import { truncateString } from '@/utils/files.js';
  import { useFilesStore, useFlightStore } from '@store/files';
  import dayjs from 'dayjs';
  import utc from 'dayjs/plugin/utc';
  import { debounce } from 'lodash-es';
  import BaseInputTime from '@/components/files/reusables/BaseInputTime.vue';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  dayjs.extend(utc);

  const emit = defineEmits(['onRefreshItineraryCache']);

  const flightStore = useFlightStore();
  const filesStore = useFilesStore();

  const props = defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
  });
  // Desestructuramos las props para acceder a fileId
  const { fileId, flights, itinerary } = toRefs(props.data);

  const itemIdTemp = ref(null);
  const showModalRemoveFlight = ref(false);
  const showItinerary = ref('');
  const airlinesOptions = reactive({
    data: [],
    value: [],
    fetching: false,
  });
  const new_flight = ref([]);

  const willRemoveFlight = (id) => {
    showModalRemoveFlight.value = true;
    itemIdTemp.value = id;
  };

  const removeFlight = async () => {
    await flightStore.removeSub({
      fileId: fileId.value,
      fileItineraryId: itinerary.value.id,
      itemId: itemIdTemp.value,
    });
    handleCancel();
    emit('onRefreshItineraryCache');
  };

  const storeSubFlight = async () => {
    // if (itinerary.value.city_in_iso == null || itinerary.value.city_out_iso == null) {
    //   notification['error']({
    //     message: `Error`,
    //     description: 'El origen o el destino de vuelos son campos requeridos',
    //     duration: 5,
    //   });
    //   return false;
    // }
    let airline_code_ = '';
    let airline_name_ = '';
    if (new_flight.value.airline_code) {
      if (typeof new_flight.value.airline_code === 'object') {
        airline_code_ = new_flight.value.airline_code.value;
        airline_name_ = new_flight.value.airline_code.label;
      } else {
        // si lo mantuvo tal cuál vino del api
        airline_code_ = new_flight.value.airline_code;
        airline_name_ = new_flight.value.airline_name;
      }
    }

    let airline_number_ = '';
    if (new_flight.value.airline_number) {
      airline_number_ = new_flight.value.airline_number;
    }
    let pnr_ = '';
    if (new_flight.value.pnr) {
      pnr_ = new_flight.value.pnr;
    }

    let departure_time_ = new_flight.value.departure_time;
    let arrival_time_ = new_flight.value.arrival_time;

    let paxs_ = [];
    if (new_flight.value.passengersSelect) {
      new_flight.value.passengersSelect.forEach((p) => {
        paxs_.push(p);
      });
    }
    const data_ = {
      airline_code: airline_code_,
      airline_name: airline_name_,
      airline_number: airline_number_,
      pnr: pnr_,
      departure_time: departure_time_,
      arrival_time: arrival_time_,
      nro_pax: paxs_.length,
      accommodations: paxs_,
    };
    emit('onRefreshItineraryCache');
    await flightStore.storeSub({
      fileId: fileId.value,
      fileItineraryId: itinerary.value.id,
      data: data_,
    });
  };
  const updateSubFlight = async (flight) => {
    // if (itinerary.value.city_in_iso == null || itinerary.value.city_out_iso == null) {
    //   notification['error']({
    //     message: `Error`,
    //     description: 'El origen o el destino de vuelos son campos requeridos',
    //     duration: 5,
    //   });
    //   return false;
    // }
    let airline_code_ = '';
    let airline_name_ = '';
    if (flight.airline_code) {
      if (typeof flight.airline_code === 'object') {
        // Si cambio desde el combo
        airline_code_ = flight.airline_code.value;
        airline_name_ = flight.airline_code.label;
      } else {
        // si lo mantuvo tal cuál vino del api
        airline_code_ = flight.airline_code;
        airline_name_ = flight.airline_name;
      }
    }
    let airline_number_ = '';
    if (flight.airline_number) {
      airline_number_ = flight.airline_number;
    }
    let pnr_ = '';
    if (flight.pnr) {
      pnr_ = flight.pnr;
    }

    let departure_time_ = flight.departure_time;
    let arrival_time_ = flight.arrival_time;
    let paxs_ = [];

    if (flight.passengersSelect) {
      flight.passengersSelect.forEach((p) => {
        if (p.value) {
          paxs_.push(p.value);
        } else {
          paxs_.push(p);
        }
      });
    }
    const data_ = {
      airline_code: airline_code_,
      airline_name: airline_name_,
      airline_number: airline_number_,
      pnr: pnr_,
      departure_time: departure_time_,
      arrival_time: arrival_time_,
      nro_pax: paxs_.length,
      accommodations: paxs_,
    };
    emit('onRefreshItineraryCache');
    await flightStore.updateSub({
      fileId: fileId.value,
      fileItineraryId: itinerary.value.id,
      itemId: flight.id,
      data: data_,
    });
  };

  const handleCancel = () => {
    itemIdTemp.value = null;
    showModalRemoveFlight.value = false;
  };

  const loadFlights = () => {
    setFlightPassengers();
  };

  const passenger_options = computed(() => {
    return filesStore.getFilePassengers.map((row, index) => ({
      label:
        (row.name ? row.name : 'Pasajero ' + (index + 1)) +
        ' (' +
        row.type +
        ') ' +
        (row.type == 'CHD' ? ' (' + calculateAge(row.date_birth) + 'y)' : ''),
      value: row.id,
      type: row.type,
      disabled: false,
      age_child: row.type == 'CHD' ? calculateAge(row.date_birth) : 0,
    }));
  });

  const new_passenger_options = computed(() => {
    return filesStore.getFilePassengers.map((row, index) => ({
      label:
        (row.name ? row.name : 'Pasajero ' + (index + 1)) +
        ' (' +
        row.type +
        ') ' +
        (row.type == 'CHD' ? ' (' + calculateAge(row.date_birth) + 'y)' : ''),
      value: row.id,
      type: row.type,
      disabled: false,
      age_child: row.type == 'CHD' ? calculateAge(row.date_birth) : 0,
    }));
  });

  const calculateAge = (birthdate) => {
    // Convertir la cadena de fecha a un objeto Date
    const birthDate = new Date(birthdate);

    // Obtener la fecha actual
    const today = new Date();

    // Calcular la diferencia en años
    let age = today.getFullYear() - birthDate.getFullYear();

    // Ajustar la edad si la fecha de nacimiento aún no ha ocurrido este año
    const monthDifference = today.getMonth() - birthDate.getMonth();
    const dayDifference = today.getDate() - birthDate.getDate();

    if (monthDifference < 0 || (monthDifference === 0 && dayDifference < 0)) {
      age--;
    }

    return age;
  };

  const isValid = (itinerary) => {
    console.log(itinerary.flights);

    if (itinerary.flights.length === 0) {
      return false;
    }

    // Si algún vuelo cumple la condición, devolvemos false
    const conditionMet = itinerary.flights.some(
      (flight) => flight.nro_pax === flight.accommodations.length
    );

    return conditionMet;
  };

  const setFlightPassengers = () => {
    let data_ = filesStore.getFileItineraries.filter((itinerary) => itinerary.entity === 'flight');

    data_.forEach((s) => {
      if (
        itinerary.value.id !== s.id &&
        dayjs(s.date_in).format('YYYY-MM-DD') ===
          dayjs(itinerary.value.date_in).format('YYYY-MM-DD') &&
        itinerary.value.object_code === s.object_code &&
        (itinerary.value.city_in_iso === s.city_in_iso ||
          itinerary.value.city_out_iso === s.city_out_iso)
      ) {
        // Debería obtener todos los paxs con los que debe contar el itinerary en selectedOptions
        const selectedIds = new Set();

        for (const flight of s.flights) {
          for (const acc of flight.accommodations) {
            selectedIds.add(acc.file_passenger_id);
          }
        }

        passenger_options.value.forEach((option) => {
          option.disabled = selectedIds.has(option.value);
        });
        console.log(passenger_options.value);
      }
    });

    // Intento de poner los seleccionados
    for (const flight of flights.value) {
      flight.passengersSelect = [];
    }

    // Creamos un mapa de passenger_options válidos para búsqueda rápida
    const validPassengers = passenger_options.value.filter((po) => !po.disabled);

    // Asociamos pasajeros a vuelos
    for (const flight of flights.value) {
      const matchedPassengers = [];

      for (const acc of flight.accommodations) {
        const match = validPassengers.find((po) => po.value === acc.file_passenger_id);
        if (match && !matchedPassengers.includes(match)) {
          matchedPassengers.push(match);
        }
      }

      flight.passengersSelect = matchedPassengers;
    }

    // Verificación aver si hay espacio para agregar más paxs en el itinerary
    itinerary.value.show_new_flight = isValid(itinerary.value) || flights.value.length === 0;

    // Formulario desglose de pax, con bloqueo de los ya agregados
    new_passenger_options.value.forEach((po) => {
      flights.value.forEach((f) => {
        f.passengersSelect.forEach((p_selected) => {
          if (p_selected.value === po.value) {
            po.disabled = true;
          }
        });
      });

      passenger_options.value.forEach((option) => {
        if (option.disabled && option.value === po.value) {
          po.disabled = true;
        }
      });
    });
  };

  const searchAirline = debounce(async (value) => {
    if (value !== '' || (value === '' && airlinesOptions.data.length == 0)) {
      airlinesOptions.data = [];
      airlinesOptions.fetching = true;

      await flightStore.getListAirlines({ query: value.toUpperCase() });
      const results = flightStore.listAirlines;

      airlinesOptions.data = results;
      airlinesOptions.fetching = false;
    }
  }, 300);

  /*
  const handleFlightInformationAutocomplete = async (numberFlight, index) => {
    const flightNumber = numberFlight.target.value;
    if (flightNumber) {
      const flightInformation = await flightStore.flightInformation({
        flight_number: flightNumber,
      });
      const { date, airline, flight } = flightInformation;

      const arrival = date.arrival_estimated
        ? dayjs(date.arrival_estimated).utc().format('HH:mm')
        : null;
      const departure = date.departure_estimated
        ? dayjs(date.departure_estimated).utc().format('HH:mm')
        : null;

      const flightData = {
        pnr: flight.iata,
        airline_code: airline.iata,
        airline_name: airline.name,
        arrival_time: arrival,
        departure_time: departure,
      };

      if (index === null) {
        Object.assign(
          new_flight.value,
          flightData,
          arrival && departure ? { timeRange: [departure, arrival] } : {}
        );
      } else {
        Object.assign(flights.value[index], flightData);
        if (arrival && departure) {
          flightTimes.value[index] = [departure, arrival];
        }
      }
    }
  };
  */

  const updateTimeFlight = debounce(($event, flight, f, type) => {
    flights.value[f][type] = flight[type];

    console.log('VALUE: ', flight[type], flights.value[f]);

    /*
    setTimeout(() => {
      loadFlights();
    }, 100);
    */
  }, 350);

  const updateTimeNewFlight = debounce(($event, flight, type) => {
    new_flight.value[type] = flight[type];
  }, 350);

  const loading = ref(true);

  onMounted(() => {
    airlinesOptions.data = flightStore.listAirlines;
    loadFlights();

    setTimeout(() => {
      loading.value = false;
    }, 350);
  });
</script>

<style scope>
  .flight-ico {
    margin: 23px 5px 0;
  }

  .input-flight {
    border-color: #d8dddd !important;
    border-radius: 5px !important;
  }

  .input-flight:focus-visible,
  .input-flight:active,
  .input-flight:hover {
    border-color: #d32d65 !important;
  }

  .no-visible {
    visibility: hidden;
    display: none !important;
  }

  .flight-labels {
    width: 100%;
    display: flex;
    font-weight: 600;
    color: gray;
  }
</style>
