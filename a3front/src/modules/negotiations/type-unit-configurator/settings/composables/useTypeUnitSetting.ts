import { onMounted, ref } from 'vue';
import { useTypeUnitSettingResourceStore } from '@/modules/negotiations/type-unit-configurator/settings/store/typeUnitSettingResourceStore';

export function useTypeUnitSetting() {
  const showActionButtons = ref<boolean>(false);

  const typeUnitSettingResourceStore = useTypeUnitSettingResourceStore();

  const { fetchResources } = typeUnitSettingResourceStore;

  onMounted(async () => {
    await fetchResources();
  });

  return {
    showActionButtons,
  };
}
