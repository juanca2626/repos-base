import { reactive, ref, computed } from 'vue';
import { storeToRefs } from 'pinia';
import type { DrawerEmitTypeInterface } from '@/modules/negotiations/products/configuration/drawers/base/interfaces';
import type { SelectOption } from '@/modules/negotiations/suppliers/interfaces';
import type { BaseDrawerProps } from '@/modules/negotiations/products/configuration/drawers/base/interfaces';
import { productConfigurationMultiDayService } from '@/modules/negotiations/products/configuration/drawers/package/services/productConfigurationMultiDayService';
import type { MultiDayConfigurationRequest } from '@/modules/negotiations/products/configuration/drawers/package/interfaces';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { useSupportResourcesStore } from '@/modules/negotiations/products/configuration/stores/useSupportResourcesStore';

export function usePackageEditDrawer(emit: DrawerEmitTypeInterface, props: BaseDrawerProps) {
  const isLoading = ref<boolean>(false);

  const configurationStore = useConfigurationStore();
  const { items } = storeToRefs(configurationStore);

  const supportResourcesStore = useSupportResourcesStore();
  const { programDurations, operationalSeasons } = storeToRefs(supportResourcesStore);

  const programsToOperate = computed<SelectOption[]>(() => {
    return programDurations.value.map((item) => ({
      label: item.name,
      value: item.code,
    }));
  });

  const operatingSeasons = computed<SelectOption[]>(() => {
    return operationalSeasons.value.map((item) => ({
      label: item.name,
      value: item.code,
    }));
  });

  const stepNumber = 1;

  const cancelButtonText = 'Cancelar';

  const nextButtonText = 'Guardar';

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

  const buildMultiDayConfigurationRequest = (): MultiDayConfigurationRequest => {
    const configurations = [];

    // Para cada programa seleccionado, crear una configuración con todas las temporadas seleccionadas
    for (const programCode of formState.programsToOperateIds) {
      configurations.push({
        programDurationCode: String(programCode),
        operationalSeasonCodes: formState.operatingSeasonIds.map((seasonCode: string | number) =>
          String(seasonCode)
        ),
      });
    }

    return {
      configurations,
    };
  };

  const handleGoToConfiguration = async (): Promise<void> => {
    await handleSave();
  };

  const handleSave = async (): Promise<void> => {
    if (!props.productSupplierId) return;

    try {
      isLoading.value = true;

      const request = buildMultiDayConfigurationRequest();

      const { success } = await productConfigurationMultiDayService.createMultiDayConfiguration(
        props.productSupplierId,
        request
      );

      if (success) {
        await configurationStore.loadConfiguration();
        clearFormState();
        handleClose();
      }
    } catch (error) {
      console.error('Error saving multi day configuration:', error);
    } finally {
      isLoading.value = false;
    }
  };

  // Extraer los códigos de programas ya configurados desde el store
  const configuredProgramCodes = computed(() => {
    if (!items.value || items.value.length === 0) {
      return [];
    }

    // Extraer programDurationCode únicos de behaviorSetting
    const programCodesSet = new Set<string>();

    const programDurationCodes = items.value.map((item) => item.key);

    programDurationCodes.forEach((code) => {
      programCodesSet.add(code);
    });

    return Array.from(programCodesSet);
  });

  const availableProgramsToOperate = computed(() => {
    return programsToOperate.value.filter(
      (program) => !configuredProgramCodes.value.includes(String(program.value))
    );
  });

  const handleCancelGoBack = (): void => {
    handleClose();
  };

  const clearFormState = () => {
    formState.programsToOperateIds = [];
    formState.operatingSeasonIds = [];
  };

  return {
    isLoading,
    formState,
    programsToOperate,
    availableProgramsToOperate,
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
