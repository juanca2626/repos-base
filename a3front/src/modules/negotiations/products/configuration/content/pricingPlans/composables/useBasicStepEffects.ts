import { watch } from 'vue';
import { useServiceConfigurationSidebar } from '@/modules/negotiations/products/configuration/composables/useServiceConfigurationSidebar';

export function useBasicStepEffects(formState: any) {
  const { setSidebarCollapsed } = useServiceConfigurationSidebar();

  watch(
    () => formState.includeHolidayTariffs,
    (value) => {
      setSidebarCollapsed(value);
    },
    { immediate: true }
  );
}
