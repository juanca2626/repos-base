import { ref, type Ref } from 'vue';
import { supportApi } from '@/modules/statements/api/statementsApi';
import type { StatementResponseInterface } from '@/modules/statements/interfaces/statement-response.interface';

export const useStatementsResources = () => {
  const statements: Ref<StatementResponseInterface[] | null> = ref(null);
  const loading = ref<boolean>(false);
  const error = ref<Error | null>(null);

  const fetchStatements = async (): Promise<void> => {
    loading.value = true;
    error.value = null;

    try {
      const response = await supportApi.get<StatementResponseInterface[]>('statements');
      statements.value = response.data;
    } catch (e) {
      error.value = e instanceof Error ? e : new Error('Error desconocido al cargar las facturas');
      console.error('Error al obtener las facturas:', e);
    } finally {
      loading.value = false;
    }
  };

  return {
    statements,
    loading,
    error,
    fetchStatements,
  };
};
