import { computed, onMounted, reactive, ref, watch } from 'vue';
import { storeToRefs } from 'pinia';
import type { FiltersInputsInterface } from '@/modules/negotiations/type-unit-configurator/settings/interfaces';
import { useTypeUnitSettingFilterStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingFilterStore';
import { useTypeUnitConfiguratorStore } from '@/modules/negotiations/type-unit-configurator/store/typeUnitConfiguratorStore';
import { currentYear } from '@/modules/negotiations/type-unit-configurator/helpers/periodHelper';
import { useTypeUnitSettingResourceStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingResourceStore';
import { filterOption } from '@/modules/negotiations/supplier/register/helpers/supplierFormHelper';
import { useTypeUnitSettingStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingStore';
import { emit } from '@/modules/negotiations/api/eventBus';

export function useTypeUnitSettingFilter() {
  const tabLocationKey = ref<number>(0);

  const typeUnitConfiguratorStore = useTypeUnitConfiguratorStore();
  const { periodYears } = storeToRefs(typeUnitConfiguratorStore);
  const { loadPeriodYearsIfEmpty } = typeUnitConfiguratorStore;

  const { setTypeUnitTransportLocationId, setPeriodYear, setTransferId } =
    useTypeUnitSettingFilterStore();

  const { isLoading, typeUnitTransportLocations, transfers } = storeToRefs(
    useTypeUnitSettingResourceStore()
  );

  const { showActionButtons } = storeToRefs(useTypeUnitSettingStore());

  const formState = reactive<FiltersInputsInterface>({
    typeUnitTransportLocationId: null,
    periodYear: currentYear,
    transferId: null,
  });

  const handlePeriodYear = (): void => {
    setPeriodYear(formState.periodYear);
  };

  const handleTransfer = (): void => {
    setTransferId(formState.transferId);
  };

  const isPreviousDisabled = computed(() => tabLocationKey.value <= 0);

  const isNextDisabled = computed(
    () => tabLocationKey.value >= typeUnitTransportLocations.value.length - 1
  );

  const goToPreviousTab = () => {
    if (tabLocationKey.value > 0) {
      tabLocationKey.value--;
      handleTabChangeLocation(tabLocationKey.value);
    }
  };

  const goToNextTab = () => {
    if (tabLocationKey.value < typeUnitTransportLocations.value.length - 1) {
      tabLocationKey.value++;
      handleTabChangeLocation(tabLocationKey.value);
    }
  };

  const handleTabChangeLocation = (key: number) => {
    formState.typeUnitTransportLocationId = typeUnitTransportLocations.value[key].id;
    setTypeUnitTransportLocationId(formState.typeUnitTransportLocationId);
  };

  const handleEditSetting = () => {
    emit('editTypeUnitSetting');
  };

  const handleDeleteSetting = () => {
    emit('deleteTypeUnitSetting');
  };

  onMounted(async () => {
    await loadPeriodYearsIfEmpty();
  });

  watch(
    () => typeUnitTransportLocations.value,
    (data) => {
      if (data.length > 0) {
        handleTabChangeLocation(0);
      }
    }
  );

  return {
    isLoading,
    formState,
    periodYears,
    typeUnitTransportLocations,
    transfers,
    tabLocationKey,
    isPreviousDisabled,
    isNextDisabled,
    showActionButtons,
    goToPreviousTab,
    goToNextTab,
    handleTabChangeLocation,
    handlePeriodYear,
    handleTransfer,
    filterOption,
    handleEditSetting,
    handleDeleteSetting,
  };
}
