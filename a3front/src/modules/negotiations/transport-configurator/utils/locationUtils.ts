import { ref } from 'vue';
import { useSupportResourcesStore } from '@/modules/negotiations/store/supportResourceStore';
import type { Location } from '@/modules/negotiations/transport-configurator/interfaces/unit-transport-configurator.interface';
import { useTransportConfiguratorStore } from '@/modules/negotiations/transport-configurator/store/TransportConfiguratorStore';

// Función para cargar las opciones de ubicación
export const loadLocationOptions = async (): Promise<Location[]> => {
  const store = useTransportConfiguratorStore();
  const locationOptions = ref<Location[]>(store.locationOptions); // Usar las locaciones del store
  const supportResourcesStore = useSupportResourcesStore();

  if (store.locationOptions.length === 0) {
    try {
      const response = await supportResourcesStore.fetchSupportResources(
        ['states'], // Pasamos los "resourceKeys" como array
        { country_id: 89 } // Parámetros adicionales como objeto
      );

      // Verificar si `response` contiene lo esperado (en este caso, `states`)
      if (response && response.states) {
        const locations = response.states.map((state: unknown) => ({
          label: state.name,
          value: state.id,
        }));

        // Actualizamos el store con las opciones de ubicación
        store.setLocationOptions(locations);
        locationOptions.value = locations;
      }
    } catch (error) {
      console.error('Error al obtener las opciones de sedes:', error);
    }
  } else {
    locationOptions.value = store.locationOptions;
  }

  return locationOptions.value;
};
