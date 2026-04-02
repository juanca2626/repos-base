//useTrasnportingconfiguratorForm.ts
import { ref, onMounted, watch } from 'vue';
import { useTransportConfiguratorStore } from '@/modules/negotiations/transport-configurator/store/TransportConfiguratorStore';
import type {
  UnitTransportConfigurator,
  Location,
} from '@/modules/negotiations/transport-configurator/interfaces/unit-transport-configurator.interface';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import { supportApi } from '@/modules/negotiations/api/negotiationsApi';
import { loadLocationOptions } from '@/modules/negotiations/transport-configurator/utils/locationUtils';
import { on } from '@/modules/negotiations/api/eventBus';

interface transportConfiguratorFormPropsInterface {
  isDrawerOpen: boolean;
  editUnit: UnitTransportConfigurator;
  isEditMode: boolean;
}

type EmitType = (
  event: 'closeDrawer' | 'editUnit' | 'saveUnit',
  ...args: (boolean | number)[]
) => void;

// Composable para manejar el formulario de configuración de transporte
export const useTransportConfiguratorForm = (
  props: transportConfiguratorFormPropsInterface,
  emit: EmitType
) => {
  const store = useTransportConfiguratorStore();
  const isLoading = ref<boolean>(false); // Indicador de carga
  const localUnits = ref<UnitTransportConfigurator[]>([]); // Estado local para las unidades
  const locationOptions = ref<Location[]>(store.locationOptions); // Usa las locaciones del store
  const isDrawerOpen = ref<boolean>(props.isDrawerOpen);
  const isEditMode = ref<boolean>(props.isEditMode);
  const isFormValid = ref(false);

  // Función dedicada para cargarlas opciones de locaciones
  const fetchLocationOptions = async () => {
    isLoading.value = true;
    const locations = await loadLocationOptions();
    locationOptions.value = locations.map((loc) => ({
      value: loc.id, // Aquí aseguramos que el valor coincide con el id
      label: loc.state, // Aquí aseguramos que el label es el estado
    }));
    isLoading.value = false;
  };

  // Función para agregar una nueva unidad
  const addUnit = () => {
    const newUnit: UnitTransportConfigurator = {
      id: Date.now(),
      code: '',
      name: '',
      status: false,
      is_trunk: false,
      created_at: new Date().toISOString(),
      locations: [],
      isValid: false,
    };
    localUnits.value.push(newUnit);
  };

  // Función para eliminar una unidad por su id
  const deleteLocalUnit = (id: number) => {
    localUnits.value = localUnits.value.filter((unit) => unit.id !== id);
  };

  // // Función para validar todas las unidades locales
  // const validateLocalUnits = () => {
  //   const isValid = localUnits.value.every(
  //     (unit) => unit.code.trim() !== '' && unit.name.trim() !== '' && unit.locations.length > 0
  //   );
  //   console.log('Validación de todas las unidades locales:', isValid);
  //   return isValid;
  // };

  // Función para reiniciar el estado local
  const resetLocalUnits = () => {
    localUnits.value = [];
  };

  //Función para mapear una unidad local al formato esperado por la API
  const mapUnitForApi = (unit: UnitTransportConfigurator) => {
    const mappedUnit = {
      id: unit?.id,
      code: unit.code,
      name: unit.name,
      status: unit.status ? 1 : 0,
      is_trunk: unit.is_trunk ? 1 : 0,
      locations: unit.locations.map((location) => ({
        country_id: 89,
        state_id: location.value || location.state_id,
      })),
    };
    console.log('Mapear Unidades para la  API:', mappedUnit);
    return mappedUnit;
  };

  //Función para guardar todas las unidades mediante llamadas a la API y manejar los errores en un solo lugar.
  const saveUnitsToApi = async (unitsPayload: ReturnType<typeof mapUnitForApi>[]) => {
    try {
      const promises = unitsPayload.map((unit) => {
        console.log('Unidad para la API:', unit);
        // Verifica si el ID de la unidad ya existe
        if (unit.id) {
          // Si el ID existe, usa PUT para actualizar la unidad existente
          return supportApi.put(`units/${unit.id}`, unit);
        } else {
          // Si no hay un ID, es una nueva unidad y usa POST para crearla
          return supportApi.post('units', unit);
        }
      });
      return await Promise.all(promises);
    } catch (error) {
      console.error('Error durante la llamada a la API:', error);
      handleError(error);
    }
  };

  //Función para procesar la respuesta de la API y actualizar el estado del store
  const processApiResponse = (
    responses: { data: { success: boolean; data: UnitTransportConfigurator } }[]
  ) => {
    responses.forEach((response) => {
      console.log('Respuesta de la API:', response);
      if (response.data.success) {
        const newUnit = mapUnitFromApiResponse(response.data.data);
        store.addUnit(newUnit);
        handleSuccessResponse(response.data);
      } else {
        handleError(new Error('Error al guardar la unidad'));
      }
    });
    resetLocalUnits();
  };

  //Función para mapear la respuesta de la API a un objeto UnitTransportConfigurator
  //const mapUnitFromApiResponse = (data: any): UnitTransportConfigurator => ({
  const mapUnitFromApiResponse = (data: UnitTransportConfigurator): UnitTransportConfigurator => ({
    id: data.id,
    name: data.name,
    code: data.code,
    status: data.status === 1,
    is_trunk: data.is_trunk === 1,
    created_at: data.created_at,
    locations: data.locations.map((loc: Location) => ({
      id: loc.id,
      state: loc.state,
      state_id: loc.state_id || loc.id, // Usa state_id si existe, de lo contrario usa id
      quantity: loc.quantity,
    })),
    isValid: true,
  });

  // Función para guardar las unidades locales en el store y API
  const saveForm = async (): Promise<void> => {
    isLoading.value = true;
    const unitsPayload = localUnits.value.map(mapUnitForApi);
    console.log('Payload de Unidades:', unitsPayload);
    const responses = await saveUnitsToApi(unitsPayload);
    if (responses) {
      processApiResponse(responses);
    }
    isLoading.value = false;
  };

  // Cargar locaciones cuando el componente es montado
  onMounted(async () => {
    if (locationOptions.value.length === 0) {
      await loadLocationOptions();
    }
  });

  on('editUnit', (item: UnitTransportConfigurator) => {
    console.log('Obteniendo editUnit desde eventBus:', item);
    localUnits.value = [{ ...(item as UnitTransportConfigurator) }];
    isEditMode.value = true;
    isDrawerOpen.value = true;
  });

  const handleAddUnit = () => {
    addUnit();
  };

  const handleUpdateForm = (updatedForm: UnitTransportConfigurator) => {
    console.log('Formulario actualizado en FormComponent:', updatedForm);
    const unit = localUnits.value.find((unit) => unit.id === updatedForm.id);
    if (unit) {
      Object.assign(unit, updatedForm);
      // isFormValid.value = validateLocalUnits();
      console.log('Estado de unidad actualizado:', unit, 'Formulario válido:', isFormValid.value);
    }
  };

  const handleDeleteLocalUnit = (id: number) => {
    deleteLocalUnit(id);
    console.log('Unidad eliminada:', id);
    // isFormValid.value = validateLocalUnits();
  };

  const handleSaveForm = async () => {
    console.log('Guardando formulario...');

    await saveForm();
    emit('saveUnit');
    emit('closeDrawer');
  };
  watch(
    () => props.isDrawerOpen,
    async (newVal) => {
      if (newVal) {
        await fetchLocationOptions(); // Asegura que las locaciones se carguen cada vez que se abre el drawer
        if (props.editUnit) {
          localUnits.value = [{ ...(props.editUnit as UnitTransportConfigurator) }];
          console.log('Editando unidad:', props.editUnit);
        } else {
          resetLocalUnits();
          addUnit();
        }
        // isFormValid.value = validateLocalUnits();
      }
    },
    { immediate: true }
  );

  watch(
    () => props.isDrawerOpen,
    (newVal) => {
      isDrawerOpen.value = newVal;
    }
  );

  onMounted(fetchLocationOptions);

  return {
    isDrawerOpen,
    isLoading,
    localUnits,
    locationOptions,
    isFormValid,
    isEditMode,
    fetchLocationOptions,
    addUnit,
    deleteLocalUnit,
    resetLocalUnits,
    saveForm,
    handleUpdateForm,
    handleDeleteLocalUnit,
    handleSaveForm,
    handleAddUnit,
    store,
  };
};
