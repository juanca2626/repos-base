import { useRoute, useRouter } from 'vue-router';
import { onMounted, watch, onBeforeUnmount } from 'vue';

// interface CustomRouteMetaType {
//   supplierClassification?: string;
//   breadcrumb?: string;
//   [key: string]: any;
// }

// interface CustomHistoryState {
//   supplierClassification?: string;
//   [key: string]: any;
// }

// const globalState = {
//   supplierClassificationRef: ref<string | null>(null),
// };

export function useSupplierClassificationGlobalComposable() {
  const route = useRoute();
  const router = useRouter();

  // const { supplierClassificationRef } = globalState;
  // const isHandlingEvent = ref(false);

  const isPageRefreshDetected = (): boolean => {
    return (
      document.referrer === '' ||
      (route.name === 'supplier-register-form' && !sessionStorage.getItem('navigatedFromApp'))
    );
  };

  // const cleanupState = (skipEvent = false) => {
  //   localStorage.removeItem('currentSupplierClassification');
  //   sessionStorage.removeItem('navigatedFromApp');
  //   supplierClassificationRef.value = null;

  //   if (!isHandlingEvent.value && !skipEvent) {
  //     window.dispatchEvent(
  //       new CustomEvent('supplierClassificationChanged', {
  //         detail: { value: null },
  //       })
  //     );
  //   }
  // };

  // const updateSupplierClassification = (forceReset = false) => {
  // if (isPageRefreshDetected() || forceReset) {
  //   cleanupState(true);
  //   return;
  // }

  // const routeMeta = route.meta as CustomRouteMetaType;
  // const historyState = history.state as CustomHistoryState;
  // const routeParams = router.currentRoute.value.params as Record<string, any>;

  // const newValue =
  //   // 1. Metadatos de la ruta
  //   routeMeta.supplierClassification ||
  //   // 2. Parámetros de consulta
  //   (typeof route.query.supplierClassification === 'string'
  //     ? route.query.supplierClassification
  //     : undefined) ||
  //   // 3. Estado del historial
  //   (historyState && historyState.supplierClassification) ||
  //   // 4. Parámetros de estado de la ruta actual
  //   (routeParams.state && routeParams.state.supplierClassification) ||
  //   // 5. Valor en localStorage como último recurso
  //   localStorage.getItem('currentSupplierClassification') ||
  //   // Si todo lo anterior falla, usar null
  //   null;

  // if (supplierClassificationRef.value !== newValue) {
  //   supplierClassificationRef.value = newValue;
  // }
  // };

  // const navigateToSupplierRegister = async (supplierClassificationEnum: any) => {
  //   supplierClassificationRef.value = supplierClassificationEnum;

  //   localStorage.setItem('currentSupplierClassification', supplierClassificationEnum);
  //   sessionStorage.setItem('navigatedFromApp', 'true');

  //   await nextTick();

  //   if (router.currentRoute.value.name === 'supplier-register-form') {
  //     await router.replace({
  //       name: 'supplier-register-form',
  //       state: {
  //         supplierClassification: supplierClassificationEnum,
  //       },
  //     });

  //     window.dispatchEvent(
  //       new CustomEvent('supplierClassificationChanged', {
  //         detail: { value: supplierClassificationEnum },
  //       })
  //     );
  //   } else {
  //     await router.push({
  //       name: 'supplier-register-form',
  //       state: {
  //         supplierClassification: supplierClassificationEnum,
  //       },
  //     });

  //     window.dispatchEvent(
  //       new CustomEvent('supplierClassificationChanged', {
  //         detail: { value: supplierClassificationEnum },
  //       })
  //     );
  //   }
  // };

  // const navigateToSupplierRegisterForm = async (classification: any = null): Promise<void> => {
  //   try {
  //     let supplierClassification = classification;

  //     if (!supplierClassification && route.meta) {
  //       supplierClassification = route.meta.supplierSubClassification as string;
  //     }

  //     if (supplierClassification) {
  //       handleCompleteForm(supplierClassification);
  //       // await navigateToSupplierRegister(supplierClassification);
  //     } else {
  //       await router.push({ name: 'supplier-register-form' });
  //     }
  //   } catch (error) {
  //     await router.push({ name: 'supplier-register-form' });
  //   }
  // };

  // const handleCustomEvent = (event: CustomEvent) => {
  //   if (isHandlingEvent.value) return;

  //   try {
  //     isHandlingEvent.value = true;
  //     // const newValue = event.detail?.value || null;

  //     // if (supplierClassificationRef.value !== newValue) {
  //     //   supplierClassificationRef.value = newValue;
  //     // }
  //   } finally {
  //     setTimeout(() => {
  //       isHandlingEvent.value = false;
  //     }, 100);
  //   }
  // };

  const cleanup = () => {
    // window.removeEventListener('supplierClassificationChanged', handleCustomEvent as EventListener);
  };

  const handleBeforeUnload = () => {
    if (route.name === 'supplier-register-form') {
      // cleanupState(true);
    }
  };

  onMounted(() => {
    if (isPageRefreshDetected()) {
      // cleanupState(true);
    } else {
      // updateSupplierClassification();
    }

    // window.addEventListener('supplierClassificationChanged', handleCustomEvent as EventListener);
    window.addEventListener('beforeunload', handleBeforeUnload);
  });

  onBeforeUnmount(() => {
    cleanup();
    window.removeEventListener('beforeunload', handleBeforeUnload);
  });

  watch(
    [() => route.fullPath, () => router.currentRoute.value],
    () => {
      if (!isPageRefreshDetected()) {
        // updateSupplierClassification();
      }
    },
    { deep: true }
  );

  // const handleCompleteForm = (supplierClassificationEnum: any) => {
  //   if (supplierClassificationEnum) {
  //     localStorage.setItem('currentSupplierClassification', supplierClassificationEnum);

  //     if (supplierClassificationRef.value !== supplierClassificationEnum) {
  //       supplierClassificationRef.value = supplierClassificationEnum;
  //     }
  //   } else {
  //     cleanupState(true);
  //   }
  // };

  return {
    // supplierClassification: supplierClassificationRef,
    // updateSupplierClassification,
    // navigateToSupplierRegister,
    // navigateToSupplierRegisterForm,
    // resetSupplierClassification: cleanupState,
    // handleCompleteForm,
    cleanup,
  };
}
