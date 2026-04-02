import { reactive, ref, computed } from 'vue';
import { storeToRefs } from 'pinia';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/products/configuration/drawers/base/interfaces';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import type { BaseDrawerProps } from '@/modules/negotiations/products/configuration/drawers/base/interfaces';
import { productConfigurationMultiDayService } from '@/modules/negotiations/products/configuration/drawers/package/services/productConfigurationMultiDayService';
import type { operationalSeasonCodeRequest } from '@/modules/negotiations/products/configuration/drawers/package/interfaces';
import { useNavigationStore } from '@/modules/negotiations/products/configuration/stores/useNavegationStore';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';

export function usePackageEditSeasonDrawer(emit: DrawerEmitTypeInterface, props: BaseDrawerProps) {
  const isLoading = ref<boolean>(false);

  const navigationStore = useNavigationStore();
  const { activeTabKey } = storeToRefs(navigationStore);

  const configurationStore = useConfigurationStore();
  const { items } = storeToRefs(configurationStore);

  const supportResourcesStore = useSupportResourcesStore();
  const { programDurations, operationalSeasons } = storeToRefs(supportResourcesStore);

  const programsToOperate = computed<SelectOption[]>(() => {
    return programDurations.value.map((item: any) => ({
      label: item.name,
      value: item.code,
    }));
  });

  const operatingSeasons = computed<SelectOption[]>(() => {
    return operationalSeasons.value.map((item: any) => ({
      label: item.name,
      value: item.code,
    }));
  });

  const stepNumber = 1;

  const cancelButtonText = 'Cancelar';

  const nextButtonText = 'Guardar';

  const isNextButtonDisabled = computed(() => {
    return formState.operatingSeasonIds.length === 0;
  });

  const availableOperatingSeasons = computed(() => {
    if (!activeTabKey.value || !items.value) {
      return [];
    }

    const itemConfig = items.value.find((item) => item.key === activeTabKey.value);

    if (!itemConfig) return [];

    if (!itemConfig.raw) return [];

    const rawData = itemConfig.raw as Record<string, unknown>;

    if (!rawData.operationalSeasonCodes) return [];

    const operationalSeasonCodes = rawData.operationalSeasonCodes as string[];

    return operatingSeasons.value.filter(
      (season) => !operationalSeasonCodes.includes(String(season.value))
    );
  });

  const getProgramDisplayName = (): string => {
    if (!activeTabKey.value) return '';
    const program = programsToOperate.value.find((p) => p.value === activeTabKey.value);
    return program?.label || activeTabKey.value;
  };

  const selectedProgramValue = computed(() => {
    return formState.programsToOperateIds[0] || activeTabKey.value || null;
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

  const buildMultiDayConfigurationRequest = (): operationalSeasonCodeRequest => {
    const operationalSeasonCodes: string[] = [];

    // Agregar las temporadas seleccionadas en el formulario
    operationalSeasonCodes.push(
      ...formState.operatingSeasonIds.map((seasonCode: string | number) => String(seasonCode))
    );

    // Agregar las temporadas ya configuradas para este programa
    if (items.value && activeTabKey.value) {
      const itemConfig = items.value.find((item) => item.key === activeTabKey.value);

      if (!itemConfig) return { operationalSeasonCodes: [] };

      if (!itemConfig.raw) return { operationalSeasonCodes: [] };

      const rawData = itemConfig.raw as Record<string, unknown>;

      if (!rawData.operationalSeasonCodes) return { operationalSeasonCodes: [] };

      operationalSeasonCodes.push(...(rawData.operationalSeasonCodes as string[]));
    }

    // Eliminar duplicados y convertir a String[]
    const uniqueOperationalSeasonCodes = [...new Set(operationalSeasonCodes)].map((code) =>
      String(code)
    );

    return {
      operationalSeasonCodes: uniqueOperationalSeasonCodes,
    };
  };

  const getBehaviorId = (): string => {
    const behavior = items.value.find((item) => item.key === activeTabKey.value);

    if (!behavior) return '';

    return behavior.id || '';
  };

  const handleGoToConfiguration = async (): Promise<void> => {
    await handleSave();
  };

  const reloadConfigurationData = async () => {
    if (!props.productSupplierId) return;

    try {
      // aqui deberiamos actualizar solo el sidebar
      navigationStore.loadSidebar();
    } catch (error) {
      console.error('Error reloading configuration data:', error);
    }
  };

  const handleSave = async (): Promise<void> => {
    if (!props.productSupplierId) return;

    try {
      isLoading.value = true;

      const request = buildMultiDayConfigurationRequest();

      const behaviorId = getBehaviorId();

      const { success } = await productConfigurationMultiDayService.updateMultiDayConfiguration(
        behaviorId,
        request
      );

      if (success) {
        await reloadConfigurationData();
        clearFormState();
        handleClose();
      }
    } catch (error) {
      console.error('Error saving multi day configuration:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const clearFormState = () => {
    formState.programsToOperateIds = [];
    formState.operatingSeasonIds = [];
  };

  const handleCancelGoBack = (): void => {
    handleClose();
  };

  return {
    isLoading,
    formState,
    programsToOperate,
    operatingSeasons,
    availableOperatingSeasons,
    selectedProgramValue,
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
    getProgramDisplayName,
  };
}
