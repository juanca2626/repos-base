import { reactive, ref, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import { storeToRefs } from 'pinia';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/products/configuration/drawers/base/interfaces';
import type { BaseDrawerProps } from '@/modules/negotiations/products/configuration/drawers/base/interfaces';
import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import type { Configuration } from '@/modules/negotiations/products/configuration/domain/configuration/models/Configuration.model';

export function usePackageCreateDrawer(emit: DrawerEmitTypeInterface, props: BaseDrawerProps) {
  const { productSupplierType } = useSelectedServiceType();

  const supportResourcesStore = useSupportResourcesStore();
  const { operationalSeasons, programDurations } = storeToRefs(supportResourcesStore);

  const configurationStore = useConfigurationStore();

  const isLoading = ref<boolean>(false);
  const router = useRouter();

  const programsToOperate = computed(() =>
    programDurations.value.map((item) => ({
      label: item.name,
      value: item.code,
    }))
  );

  const operatingSeasons = computed(() =>
    operationalSeasons.value.map((item) => ({
      label: item.name,
      value: item.code,
    }))
  );

  const stepNumber = 1;

  const cancelButtonText = 'Cancelar';

  const nextButtonText = 'Siguiente';

  const isNextButtonDisabled = computed(() => {
    return formState.programsToOperateIds.length === 0 || formState.operatingSeasonIds.length === 0;
  });

  const formState = reactive<any>({
    programsToOperateIds: [],
    operatingSeasonIds: [],
    isGeneral: false,
    type: null,
  });

  const handleClose = (): void => {
    emit('update:showDrawerForm', false);
  };

  const isSelectedProgramToOperate = (value: number): boolean => {
    return formState.programsToOperateIds.includes(value);
  };

  const isSelectedOperatingSeason = (value: number): boolean => {
    return formState.operatingSeasonIds.includes(value);
  };

  const buildMultiDayConfigurationRequest = (): Configuration => {
    const configurations = [];

    for (const programCode of formState.programsToOperateIds) {
      configurations.push({
        programDurationCode: String(programCode),
        operationalSeasonCodes: formState.operatingSeasonIds.map((seasonCode: string | number) =>
          String(seasonCode)
        ),
      });
    }

    return {
      programDurations: configurations,
    };
  };

  const handleGoToConfiguration = async (): Promise<void> => {
    await handleSave();
  };

  const navigateToConfiguration = () => {
    const serviceId = props.productSupplierId;

    if (!serviceId) return;

    router.push({
      name: 'serviceConfiguration',
      params: { id: serviceId },
    });
  };

  const handleSave = async (): Promise<void> => {
    if (!props.productSupplierId) return;

    try {
      isLoading.value = true;

      const request = buildMultiDayConfigurationRequest();

      const { items } = await configurationStore.saveConfiguration(
        productSupplierType.value,
        props.productSupplierId,
        request
      );

      if (items.length > 0) {
        await navigateToConfiguration();
      }
    } catch (error) {
      console.error('Error saving multi day configuration:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const getSupportResources = async () => {
    try {
      isLoading.value = true;

      await supportResourcesStore.loadResources({
        serviceType: productSupplierType.value,
        keys: ['programDurations', 'operationalSeasons'],
      });

      // if (data.programDurations) {
      //   programsToOperate.value = data.programDurations.map((item) => {
      //     return {
      //       label: item.name,
      //       value: item.code,
      //     };
      //   });
      // }

      // if (data.operationalSeasons) {
      //   operatingSeasons.value = data.operationalSeasons.map((item) => {
      //     return {
      //       label: item.name,
      //       value: item.code,
      //     };
      //   });
      // }
    } catch (error) {
      console.error('Error fetching support resources data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  watch(
    () => props.showDrawerForm,
    async (newVal) => {
      if (newVal) {
        await getSupportResources();
      }
    }
  );

  const handleCancelGoBack = (): void => {
    handleClose();
  };

  return {
    isLoading,
    formState,
    programsToOperate,
    operatingSeasons,
    stepNumber,
    cancelButtonText,
    nextButtonText,
    isNextButtonDisabled,
    handleClose,
    handleCancelGoBack,
    handleGoToConfiguration,
    handleSave,
    isSelectedProgramToOperate,
    isSelectedOperatingSeason,
  };
}
