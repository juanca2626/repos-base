import { storeToRefs } from 'pinia';
import { useCompoundsStore } from '../store/compounds.store';

export const useCompoundsComposable = () => {
  const store = useCompoundsStore();
  const { currentStep, formState } = storeToRefs(store);
  const { setStep, nextStep, prevStep, addPasajero, removePasajero } = store;

  const guardarDatos = () => {
    // TODO: integrate API call
    console.log('Guardar datos:', formState.value);
  };

  return {
    currentStep,
    formState,
    setStep,
    nextStep,
    prevStep,
    addPasajero,
    removePasajero,
    guardarDatos,
  };
};
