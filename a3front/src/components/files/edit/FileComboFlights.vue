<template>
  <a-select
    ref="selectRef"
    size="small"
    class="mx-2"
    style="min-width: 150px; max-width: 190px"
    v-if="props.typeFlight === 'AEIFLT'"
    v-model:value="flight_destiny"
    showSearch
    label-in-value
    placeholder="Selecciona Destino"
    :filter-option="false"
    :not-found-content="select_search_origin_international.fetching ? undefined : null"
    :options="select_search_origin_international.data"
    @search="search_destiny"
    @change="saveFlight"
    :disabled="flightStore.isLoading"
    @blur="handleBlur"
    @autofocus="true"
  >
    <template v-if="select_search_origin_international.fetching" #notFoundContent>
      <a-spin size="small" />
    </template>
  </a-select>
  <a-select
    ref="selectRef"
    size="small"
    class="mx-2"
    style="min-width: 150px; max-width: 190px"
    v-if="props.typeFlight === 'AECFLT'"
    v-model:value="flight_origin_domestic"
    showSearch
    label-in-value
    placeholder="Selecciona Origen"
    :filter-option="false"
    :not-found-content="select_search_origin_domestic.fetching ? undefined : null"
    :options="select_search_origin_domestic.data"
    @search="search_domestic_destiny"
    @change="saveFlight"
    :disabled="flightStore.isLoading"
    @blur="handleBlur"
    @autofocus="true"
  >
    <template v-if="select_search_origin_domestic.fetching" #notFoundContent>
      <a-spin size="small" />
    </template>
  </a-select>
</template>
<script setup>
  import { ref, reactive, inject, nextTick, watch } from 'vue';
  import { useFlightStore } from '@store/files';
  import { debounce } from 'lodash-es';
  import useOrigins from '@/quotes/composables/useOrigins';
  import { notification } from 'ant-design-vue';
  // import { useSocketsStore } from '@/stores/global';

  const emit = defineEmits(['blur', 'change', 'focus-requested']);

  const props = defineProps({
    fileId: {
      type: Number,
      required: true,
    },
    itineraryId: {
      type: Number,
      required: true,
    },
    typeFlight: {
      type: String,
      required: true,
    },
    type: {
      type: String,
      required: true,
    },
    cityIso: {
      type: String,
      default: null,
    },
    valorInitial: {
      type: String,
      required: true,
      default: null,
    },
    requestFocus: Boolean,
  });

  const { origins, getOrigins, getOriginsDomestic } = useOrigins();

  const flight_destiny = ref('');

  const flight_origin_domestic = ref('');

  // const socketsStore = useSocketsStore();
  const flightStore = useFlightStore();

  const selectRef = ref(false);

  const focusState = inject('focusState', ref(false));

  watch(focusState, async (newValue) => {
    if (newValue && selectRef.value) {
      selectRef.value.focus();
      await nextTick();
      setTimeout(() => {
        const searchInput = document.querySelector(
          '.ant-select-dropdown:not(.ant-select-dropdown-hidden) .ant-select-search__field'
        );
        if (searchInput) {
          searchInput.focus();
        }
        emit('focus-requested');
        if (typeof focusState.value !== 'undefined') {
          focusState.value = false;
        }
      }, 100);
    }
  });

  const focusSelect = async () => {
    if (selectRef.value) {
      selectRef.value.focus();
      await nextTick();
      setTimeout(() => {
        const searchInput = document.querySelector(
          '.ant-select-dropdown:not(.ant-select-dropdown-hidden) .ant-select-search__field'
        );
        if (searchInput) {
          searchInput.focus();
        } else {
          console.error('No se encontró el campo de búsqueda');
        }
      }, 100);
    } else {
      console.error('La referencia al select no está disponible');
    }
  };

  defineExpose({
    focusSelect,
  });

  const select_search_origin_international = reactive({
    data: [],
    value: [],
    fetching: false,
  });

  const select_search_origin_domestic = reactive({
    data: [],
    value: [],
    fetching: false,
  });

  const handleBlur = (event) => {
    emit('blur', event);
  };

  const clearSearches = () => {
    if (props.typeFlight === 'AEIFLT') {
      search_destiny('');
    } else {
      search_domestic_destiny('');
    }
  };

  const search_destiny = debounce(async (value) => {
    select_search_origin_international.data = [];
    select_search_origin_international.fetching = true;

    if ((origins.value.length == 0 && value == '') || value != '') {
      await getOrigins(value.toUpperCase());
    }

    let code_in_use =
      flight_destiny.value.value !== undefined && flight_destiny.value.value !== ''
        ? flight_destiny.value.value
        : flight_destiny.value.value !== undefined && flight_destiny.value.value !== ''
          ? flight_destiny.value.value
          : '';

    const results = origins.value.map((row) => ({
      iso: row.iso,
      label: row.label,
      value: row.code,
      disabled: code_in_use !== '' ? row.code === code_in_use : false,
      pais: row.pais,
      codpais: row.codpais,
      codciu: row.codciu,
      ciudad: row.ciudad,
    }));

    select_search_origin_international.data = results;
    select_search_origin_international.fetching = false;
  }, 300);

  const search_domestic_destiny = debounce(async (value) => {
    select_search_origin_domestic.data = [];
    select_search_origin_domestic.fetching = true;

    if ((value == '' && flight_origin_domestic.value.length == 0) || value != '') {
      await getOriginsDomestic(value.toUpperCase());
    }

    let code_in_use =
      flight_origin_domestic.value.value !== undefined && flight_origin_domestic.value.value !== ''
        ? flight_origin_domestic.value.value
        : flight_origin_domestic.value.value !== undefined &&
            flight_origin_domestic.value.value !== ''
          ? flight_origin_domestic.value.value
          : '';

    const results = origins.value.map((row) => ({
      iso: row.iso,
      label: row.label,
      value: row.code,
      disabled: code_in_use !== '' ? row.code === code_in_use : false,
      pais: row.pais,
      codpais: row.codpais,
      codciu: row.codciu,
      ciudad: row.ciudad,
    }));

    select_search_origin_domestic.data = results;
    select_search_origin_domestic.fetching = false;
  }, 300);

  const saveFlight = async (value, option) => {
    // if(value.value === '' || typeof value.value !== 'undefined'){
    //     console.log("contenido en blanco",value.value);
    //     return false;
    // }
    if (props.typeFlight === 'AEIFLT') {
      if (props.cityIso != null || props.cityIso != '') {
        const data = { city_isos: [value.value, props.cityIso] };
        const response = await flightStore.validationFlight({ data: data });
        if (!response.validation) {
          notification.error({
            message: 'Error',
            description: `El origen y destino no puede ser nacional en un vuelo internacional`,
          });
          flight_destiny.value = [];
          flight_origin_domestic.value = [];
          focusSelect();
          return false;
        }
      }
    }

    if (props.cityIso != '') {
      if (props.cityIso == value.value) {
        notification.error({
          message: 'Error',
          description: `El origen y destino no pueden ser iguales`,
        });
        flight_destiny.value = [];
        flight_origin_domestic.value = [];
        return false;
      }
    }

    let data_ = {
      type_flight: props.type,
      city_iso: value.value,
      city_name:
        props.typeFlight === 'AEIFLT'
          ? flight_destiny.value.option.ciudad
          : flight_origin_domestic.value.option.ciudad,
      country_iso:
        props.typeFlight === 'AEIFLT'
          ? flight_destiny.value.option.codpais
          : flight_origin_domestic.value.option.codpais,
      country_name:
        props.typeFlight === 'AEIFLT'
          ? flight_destiny.value.option.pais
          : flight_origin_domestic.value.option.pais,
    };
    // console.log('valor seleccionado: ', data_);
    // return false;

    emit('change', value, option);
    await flightStore.updateCityIso({
      fileId: props.fileId,
      fileItineraryId: props.itineraryId,
      data: data_,
    });

    clearSearches();
  };
</script>

<style></style>
