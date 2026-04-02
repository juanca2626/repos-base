import { defineStore } from 'pinia';
import { ref, reactive } from 'vue';
import type { CompoundsFormState } from '../interfaces/compounds.interface';

export const useCompoundsStore = defineStore('compounds', () => {
  const currentStep = ref(0);

  const formState = reactive<CompoundsFormState>({
    fechaCotizacion: null,
    mercado: '',
    configuracionTransporte: null,
    rangoInput: '',
    pasajeros: ['1-5', '10', '15 - 20'],
    incluirGuia: true,
    incluirScort: false,
    incluirChofer: true,
    tipoServicio: null,
    busquedaServicio: '',
    dias: [{ id: '1', nombre: 'Día 1', servicios: [] }],
  });

  const setStep = (step: number) => {
    currentStep.value = step;
  };

  const nextStep = () => {
    if (currentStep.value < 3) currentStep.value++;
  };

  const prevStep = () => {
    if (currentStep.value > 0) currentStep.value--;
  };

  const addPasajero = () => {
    const val = formState.rangoInput.trim();
    if (val && !formState.pasajeros.includes(val)) {
      formState.pasajeros.push(val);
    }
    formState.rangoInput = '';
  };

  const removePasajero = (index: number) => {
    formState.pasajeros.splice(index, 1);
  };

  return {
    currentStep,
    formState,
    setStep,
    nextStep,
    prevStep,
    addPasajero,
    removePasajero,
  };
});
