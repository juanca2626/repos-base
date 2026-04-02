import { reactive } from 'vue';

export const useStatementsForm = () => {
  const formState = reactive({
    descripcion: '',
    fecha: null,
    total: 0,
  });

  const resetForm = () => {
    formState.descripcion = '';
    formState.fecha = null;
    formState.total = 0;
  };

  return {
    formState,
    resetForm,
  };
};
