// useRouteIdComposable.ts
import { useRoute } from 'vue-router';
import { computed, type ComputedRef } from 'vue';

export function useRouteIdComposable(): {
  id: number | undefined; // valor actual (retrocompatible)
  idRef: ComputedRef<number | undefined>; // fuente reactiva para watch
} {
  const route = useRoute();

  const idRef = computed<number | undefined>(() => {
    const p = route?.params?.id as string | undefined;
    return p != null ? Number(p) : undefined;
  });

  // Retrocompat: `id` como getter del valor actual
  return {
    get id() {
      return idRef.value;
    },
    idRef,
  };
}
