import { useTypeUnitConfiguratorStore } from '@/modules/negotiations/type-unit-configurator/store/typeUnitConfiguratorStore';
import { storeToRefs } from 'pinia';
import { useTypeUnitFormStore } from '@/modules/negotiations/type-unit-configurator/type-units/store/typeUnitFormStore';
import { useTypeUnitSettingStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingStore';

export function useTypeUnitConfigurator() {
  const typeUnitSettingStore = useTypeUnitSettingStore();
  const typeUnitFormStore = useTypeUnitFormStore();

  const typeUnitConfiguratorStore = useTypeUnitConfiguratorStore();
  const { setPageActiveTabKey, pageTabsKeys } = typeUnitConfiguratorStore;
  const { pageActiveTabKey } = storeToRefs(typeUnitConfiguratorStore);

  const handleAddTypeUnit = () => {
    setPageActiveTabKey(pageTabsKeys.typeUnit);
    typeUnitFormStore.setShowDrawerForm(true);
  };

  const handleAddSetting = () => {
    setPageActiveTabKey(pageTabsKeys.typeUnitSetting);
    typeUnitSettingStore.setShowDrawerForm(true);
  };

  return {
    pageActiveTabKey,
    pageTabsKeys,
    handleAddTypeUnit,
    handleAddSetting,
  };
}
