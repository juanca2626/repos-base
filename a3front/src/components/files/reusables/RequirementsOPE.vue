<template>
  <a-popover placement="topLeft">
    <template #title>
      <small class="d-block text-center text-uppercase text-danger text-700">
        {{ t('global.label.requirements') }} - {{ t('global.label.pass_to_ope') }}
        <b v-if="sede && itinerary.entity !== 'flight'">({{ sede }})</b>
      </small>
    </template>
    <template #content>
      <template
        v-for="option in options.filter(
          (option) => !option.entity || option.entity.includes(itinerary.entity)
        )"
      >
        <a-row type="flex" align="middle" justify="start" style="gap: 7px" class="mb-1">
          <a-col>
            <template v-if="option.valid">
              <font-awesome-icon :icon="['far', 'circle-check']" class="text-success" />
            </template>
            <template v-else>
              <font-awesome-icon :icon="['far', 'face-frown']" class="text-danger" beat-fade />
            </template>
          </a-col>
          <a-col>
            <span>{{ option.name }}</span>
          </a-col>
        </a-row>
      </template>

      <!-- p class="mb-0">Faltan aprobar {{ pending_itineraries }} itinerarios de {{ total_itineraries }} (No se consideran vuelos en esta validación)</p -->
    </template>
    <font-awesome-icon :icon="['fas', 'list-check']" size="lg" :shake="itinerary.validationOpe" />
  </a-popover>
</template>

<script setup>
  import { onBeforeMount, toRefs, ref, computed } from 'vue';
  import { useFilesStore } from '@store/files';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();
  const sede = ref('');
  // const total_itineraries = ref(0);
  // const pending_itineraries = ref(0);

  const props = defineProps({
    itinerary: {
      type: Object,
    },
  });

  const options = computed(() => [
    { name: t('global.label.option_arrival_time'), valid: false, field: 'start_time' },
    { name: t('global.label.option_departure_time'), valid: false, field: 'departure_time' },
    {
      name: t('global.label.option_paxs'),
      valid: false,
      field: 'accommodations',
      entity: ['service', 'hotel'],
    },
    {
      name: t('global.label.option_flights'),
      valid: false,
      field: 'flights-info',
      entity: ['flight'],
    },
    {
      name: t('global.label.option_paxs'),
      valid: false,
      field: 'accommodations',
      entity: ['flight'],
    },
    {
      name: t('global.label.option_origin_flight'),
      valid: false,
      field: 'origin',
      entity: ['flight'],
    },
    {
      name: t('global.label.option_destiny_flight'),
      valid: false,
      field: 'destiny',
      entity: ['flight'],
    },
  ]);

  const { itinerary } = toRefs(props);

  onBeforeMount(() => {
    options.value.map((option) => {
      let value = false;

      if (option.field === 'start_time' && itinerary.value.start_time) {
        value = true;
      }

      if (option.field === 'departure_time' && itinerary.value.departure_time) {
        value = true;
      }

      if (
        option.field === 'origin' &&
        itinerary.value.city_in_iso &&
        itinerary.value.city_in_name
      ) {
        value = true;
      }

      if (
        option.field === 'destiny' &&
        itinerary.value.city_out_iso &&
        itinerary.value.city_out_name
      ) {
        value = true;
      }

      if (option.field === 'accommodations') {
        if (
          itinerary.value.entity === 'flight' &&
          itinerary.value.permitted_paxs === itinerary.value.total_paxs
        ) {
          value = true;
        }

        if (
          itinerary.value.entity === 'service' &&
          itinerary.value.accommodations.length ===
            parseInt(itinerary.value.adults) + parseInt(itinerary.value.children)
        ) {
          value = true;
        }
      }

      if (option.field === 'flights-info' && itinerary.value.flights_completed) {
        value = true;
      }

      option.valid = value;
    });

    itinerary.value.validationOpe = options.value
      .filter((option) => !option.entity || option.entity.includes(itinerary.entity))
      .some((option) => !option.valid);
  });

  const itineraries = filesStore.getFileItineraries.filter(
    (file_itinerary) => file_itinerary.city_in_iso === itinerary.value.city_in_iso
  );
  // total_itineraries.value = itineraries.length;
  // pending_itineraries.value = itineraries.filter((file_itinerary) => file_itinerary.validationOpe).length;
  sede.value = itineraries[0].city_in_name;
</script>
