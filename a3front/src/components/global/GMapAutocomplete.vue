<template>
  <div class="gmap-autocomplete-container">
    <input
      :key="inputKey"
      ref="autocompleteInput"
      type="text"
      :placeholder="placeholder"
      class="ant-input css-w750bm css-dev-only-do-not-override-w750bm"
      autocomplete="off"
      @input="onInput"
    />
  </div>
</template>

<script setup lang="ts">
  import { ref, onMounted, onBeforeUnmount, watch, nextTick, defineProps, defineEmits } from 'vue';
  import { Loader } from '@googlemaps/js-api-loader';

  const props = defineProps({
    apiKey: {
      type: String,
      default: 'AIzaSyAnQ9faN-VhBWrcMG2gswmU4NB7VOus9zQ',
    },
    modelValue: {
      type: String,
      default: '',
    },
    placeholder: {
      type: String,
      default: 'Ingrese una dirección',
    },
    options: {
      type: Object,
      default: () => ({}),
    },
    countryRestriction: {
      type: Array as () => string[],
      default: () => [],
    },
    bounds: {
      type: Object as () => google.maps.LatLngBounds | null,
      default: null,
    },
    countryName: {
      type: String,
      default: '',
    },
  });

  const emit = defineEmits(['update:modelValue', 'place-changed']);

  const autocompleteInput = ref<HTMLInputElement | null>(null);
  const autocomplete = ref<google.maps.places.Autocomplete | null>(null);
  const inputKey = ref(0);
  const isGoogleReady = ref(false);
  const hasSelectedPlace = ref(false);

  const onInput = async (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target) {
      emit('update:modelValue', target.value);

      // Cuando el usuario edita luego de seleccionar un place,
      // reinicializamos la instancia para evitar que Google quede "pegado"
      // al último resultado seleccionado.
      if (hasSelectedPlace.value) {
        hasSelectedPlace.value = false;
        await recreateAutocomplete(target.value);
      }
    }
  };

  // Helper: fetch approximate bounds for a country (ISO or name) using Geocoder
  const getCountryBounds = async (
    countryIsoOrName?: string
  ): Promise<google.maps.LatLngBounds | null> => {
    try {
      if (!countryIsoOrName || !(window as any).google?.maps?.Geocoder) return null;
      const geocoder = new google.maps.Geocoder();
      const results = await geocoder.geocode({ address: countryIsoOrName });
      if (results?.results?.length) {
        const g = results.results[0].geometry;
        // Prefer viewport; fallback to bounds
        if (g.viewport) return g.viewport;
        if (g.bounds) return g.bounds;
      }
    } catch (e) {
      console.log(e);
    }
    return null;
  };

  const buildAutocompleteOptions = async (): Promise<google.maps.places.AutocompleteOptions> => {
    const autocompleteOptions: google.maps.places.AutocompleteOptions = {
      ...props.options,
      fields: ['formatted_address', 'geometry', 'place_id'],
    };

    if (props.countryRestriction && props.countryRestriction.length > 0) {
      autocompleteOptions.componentRestrictions = {
        country: props.countryRestriction,
      };
    }

    if (props.bounds) {
      autocompleteOptions.bounds = props.bounds;
    } else if (props.countryRestriction && props.countryRestriction.length === 1) {
      const inferred = await getCountryBounds(props.countryRestriction[0]);
      if (inferred) {
        autocompleteOptions.bounds = inferred;
      }
    } else if (props.countryName) {
      const inferred = await getCountryBounds(props.countryName);
      if (inferred) {
        autocompleteOptions.bounds = inferred;
      }
    }

    return autocompleteOptions;
  };

  const destroyAutocomplete = () => {
    if (autocomplete.value) {
      google.maps.event.clearInstanceListeners(autocomplete.value);
      autocomplete.value = null;
    }
  };

  const attachAutocomplete = async () => {
    if (!isGoogleReady.value || !autocompleteInput.value) return;

    destroyAutocomplete();

    autocompleteInput.value.value = props.modelValue || '';

    const autocompleteOptions = await buildAutocompleteOptions();

    autocomplete.value = new google.maps.places.Autocomplete(
      autocompleteInput.value,
      autocompleteOptions
    );

    autocomplete.value.addListener('place_changed', () => {
      if (!autocomplete.value) return;

      const place = autocomplete.value.getPlace();
      if (!place) return;

      hasSelectedPlace.value = true;
      emit('place-changed', place);

      if (place.formatted_address) {
        emit('update:modelValue', place.formatted_address);
      }
    });
  };

  const recreateAutocomplete = async (currentValue = '') => {
    destroyAutocomplete();
    inputKey.value++;
    await nextTick();

    if (autocompleteInput.value) {
      autocompleteInput.value.value = currentValue;
    }

    await attachAutocomplete();
  };

  onMounted(async () => {
    const loader = new Loader({
      apiKey: props.apiKey,
      version: 'weekly',
      libraries: ['places'],
    });

    try {
      await loader.load();
      isGoogleReady.value = true;
      await attachAutocomplete();
    } catch (error) {
      console.log(error);
    }
  });

  onBeforeUnmount(() => {
    destroyAutocomplete();
  });

  watch(
    () => props.modelValue,
    (newValue) => {
      if (autocompleteInput.value && autocompleteInput.value.value !== newValue) {
        autocompleteInput.value.value = newValue;
      }
    },
    { immediate: true }
  );

  // Watcher para actualizar restricciones cuando cambien las props
  watch(
    [() => props.countryRestriction, () => props.bounds, () => props.countryName],
    async () => {
      hasSelectedPlace.value = false;
      await recreateAutocomplete(props.modelValue || '');
    },
    { deep: true }
  );
</script>

<style scoped>
  .gmap-autocomplete-container {
    width: 422px;

    input.ant-input {
      width: 100%;
      height: 34px; /* altura estándar como antd */
      padding: 4px 11px;
      font-size: 14px;
      line-height: 1.5715;
      color: rgba(0, 0, 0, 0.88);
      background-color: #fff;
      background-image: none;
      border: 1px solid #d9d9d9;
      border-radius: 6px;
      transition: all 0.2s;

      &:hover {
        border-color: #c92e2e;
      }

      &:focus {
        border-color: #c92e2e;
        box-shadow: 0 0 0 2px rgba(5, 145, 255, 0.1);
        outline: 0;
      }

      &::placeholder {
        color: rgba(0, 0, 0, 0.25);
      }
    }
  }
</style>
