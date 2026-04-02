<template>
  <template
    v-if="
      itinerary.entity === 'service' ||
      itinerary.entity === 'flight' ||
      (itinerary.entity === 'hotel' && format !== 'general')
    "
  >
    <popover-hover-and-click placement-click="bottom">
      <template v-if="format === 'general'">
        <b style="font-weight: 600; color: #c63838; cursor: pointer">
          <span
            >{{ adl }} ADL
            <template v-if="chd > 0"> {{ chd }} CHD</template>
          </span>
        </b>
      </template>
      <template v-else>
        <span @click="searchPaxs" class="cursor-pointer">
          <font-awesome-icon :icon="['far', 'circle-user']" />
        </span>
      </template>
      <template #content-hover> {{ t('files.label.assign_pax') }} </template>
      <template #content-click>
        <template v-if="format == 'general'">
          <a-row
            align="top"
            justify="space-between"
            v-if="itinerary.entity === 'service' || itinerary.entity === 'flight'"
          >
            <a-col>
              <BaseInputPassengers
                v-model="quantityPassengers"
                :adultsMax="filesStore.getFile.adults"
                :childrenMax="filesStore.getFile.children"
              />
            </a-col>
            <a-col>
              <a-button
                danger
                type="link"
                size="large"
                :loading="loading"
                class="d-flex ant-row-middle text-600 mt-1"
                @click="saveQuantityPassengers()"
              >
                <i class="bi bi-floppy" style="font-size: 25px"></i>
              </a-button>
            </a-col>
          </a-row>
          <div v-else style="display: none">&nbsp;</div>
        </template>
        <template v-else>
          <div style="overflow-y: auto; max-height: 150px; min-width: 150px">
            <template v-for="(passenger, p) in passengers" :key="p">
              <a-row>
                <a-col>
                  <span
                    :style="`font-size: 16px; cursor: ${loading ? 'default' : 'pointer'}`"
                    @click="updatePassenger(passenger)"
                    :class="passenger.flag_checked ? 'paxs-items--selected' : 'paxs-items'"
                  >
                    <template v-if="!loading">
                      <font-awesome-icon
                        icon="fa-solid fa-square-check"
                        v-if="passenger.flag_checked"
                      />
                      <font-awesome-icon icon="fa-solid fa-square" v-else />
                    </template>
                    <template
                      v-if="
                        (passenger.name != '' && passenger.name != null) ||
                        (passenger.surnames != '' && passenger.surnames != null)
                      "
                    >
                      <span class="ms-2">{{ passenger.name }} {{ passenger.surnames }}</span>
                    </template>
                    <template v-else>
                      <span class="ms-2">(Pasajero {{ passenger.sequence_number }})</span>
                    </template>
                  </span>
                </a-col>
              </a-row>
            </template>
          </div>
        </template>
      </template>
      <template #content-buttons>
        <a-button
          v-if="format != 'general'"
          type="primary"
          class="text-center text-600 mt-3"
          style="width: 100%"
          default
          size="small"
          :loading="loading"
          @click="savePassengers()"
          >{{ t('global.button.save') }}</a-button
        >
        <div class="d-none" v-else>&nbsp;</div>
      </template>
    </popover-hover-and-click>
  </template>
  <template v-else>
    <b
      style="font-weight: 600; color: #c63838; cursor: pointer"
      v-if="itinerary.entity === 'hotel'"
    >
      <span
        >{{ adl }} ADL
        <template v-if="chd > 0"> {{ chd }} CHD</template>
      </span>
    </b>
    <!-- b style="font-weight: 600; color: #c63838; cursor: pointer" v-else>
      <span>{{ totalPaxFlights }} PAX </span>
    </b -->
  </template>
</template>

<script setup>
  import { ref, toRefs } from 'vue';
  import PopoverHoverAndClick from '@/components/files/reusables/PopoverHoverAndClick.vue';
  import BaseInputPassengers from '@/components/files/reusables/BaseInputPassengers.vue';
  import { notification } from 'ant-design-vue';
  import { useFilesStore } from '@/stores/files';
  import { useSocketsStore } from '@/stores/global';
  import dayjs from 'dayjs';
  import { getUserCode, getUserId } from '@/utils/auth';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const emit = defineEmits(['onRefreshItineraryCache']);

  const socketsStore = useSocketsStore();
  const filesStore = useFilesStore();

  const props = defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
  });
  const loading = ref(false);

  const { itinerary, format = 'hotel', adl, chd, passengers, type, object_id } = toRefs(props.data);

  const quantityADL = ref(0);
  const quantityCHD = ref(0);
  const quantityADLMax = ref(0);
  const quantityCHDMax = ref(0);
  const values = ref([]);

  const quantityPassengers = ref({
    ADL: props.data.adl,
    CHD: props.data.chd,
  });

  const totalPaxFlights = ref(0);
  if (itinerary.value.entity === 'flight') {
    itinerary.value.flights.forEach((flight) => {
      totalPaxFlights.value += flight.accommodations.length;
    });
  }

  const updatePassengerId = async (passenger_ids) => {
    passengers.value
      .filter((passenger) => passenger_ids.includes(passenger.id))
      .map(async (passenger) => {
        if (passenger.type === 'ADL') {
          quantityADL.value += 1;
        }

        if (passenger.type === 'CHD') {
          quantityCHD.value += 1;
        }

        passenger.flag_checked = true;
      });
  };

  const savePassengers = async () => {
    if (quantityADL.value == quantityADLMax.value && quantityCHD.value == quantityCHDMax.value) {
      loading.value = true;
      await filesStore.accommodations({
        file_number: filesStore.getFile.fileNumber,
        itinerary_id: itinerary.value.id,
        type: type.value,
        object_id: object_id.value,
        passengers: values.value,
      });
      emit('onRefreshItineraryCache');
      loading.value = false;
    } else {
      notification['error']({
        message: t('files.label.update_paxs'),
        description: t('files.message.failed_selected_paxs'),
        duration: 5,
      });
      return false;
    }
  };

  const updatePassenger = (_passenger) => {
    if (loading.value) {
      return false;
    }

    let _passenger_id = _passenger.id;
    let index = validatePax(_passenger_id);

    if (index > -1) {
      if (_passenger.type == 'ADL') {
        quantityADL.value--;
      }
      if (_passenger.type == 'CHD') {
        quantityCHD.value--;
      }

      values.value.splice(index, 1);
      _passenger.flag_checked = false;
    } else {
      if (_passenger.type == 'ADL') {
        quantityADL.value++;
      }
      if (_passenger.type == 'CHD') {
        quantityCHD.value++;
      }
      values.value.push(_passenger_id);
      _passenger.flag_checked = true;
    }
  };

  const validatePax = (_passenger_id) => {
    return values.value.indexOf(_passenger_id);
  };

  const searchPaxs = async () => {
    quantityADL.value = 0;
    quantityCHD.value = 0;
    quantityADLMax.value = 0;
    quantityCHDMax.value = 0;
    let accommodations = [];

    passengers.value.map((passenger) => {
      passenger.flag_checked = false;
      return passenger;
    });

    if (format && format.value === 'service') {
      accommodations = itinerary.value.accommodations;
      quantityADLMax.value = itinerary.value.adults;
      quantityCHDMax.value = itinerary.value.children;
    }

    if (type.value === 'room' || type.value === 'unit') {
      for (const room of itinerary.value.rooms) {
        if ((room.id === object_id.value && type.value === 'room') || type.value === 'unit') {
          for (const unit of room.units) {
            if ((unit.id == object_id.value && type.value === 'unit') || type.value === 'room') {
              quantityADLMax.value += unit.adult_num;
              quantityCHDMax.value += unit.child_num;

              for (const accommodation of unit.accommodations) {
                accommodations.push(accommodation);
              }
            }
          }
        }
      }
    }

    values.value = [...new Set(accommodations.map((a) => a.file_passenger_id))];
    updatePassengerId(values.value);
  };

  const saveQuantityPassengers = async () => {
    let params = {
      total_adults: quantityPassengers.value.ADL,
      total_children: quantityPassengers.value.CHD,
    };

    loading.value = true;
    await filesStore.putQuantityPassengers(object_id.value, params);
    loading.value = false;

    socketsStore.send({
      success: true,
      type: 'update_itinerary',
      action: 'quantity_paxs',
      message: 'Modificación de Itinerario',
      user_id: getUserId(),
      user_code: getUserCode(),
      date: dayjs().format('YYYY-MM-DD'),
      time: dayjs().format('HH:mm:ss'),
      file_number: filesStore.getFile.fileNumber,
      itinerary_id: itinerary.value.id,
      description: `Se actualizó la cantidad de pasajeros asociados al itinerario.`,
    });
  };
</script>

<style lang="scss">
  .paxs-items {
    font-weight: normal;

    &--selected {
      color: #eb5757;
    }

    &:hover {
      color: #eb5757;
    }
  }
</style>
