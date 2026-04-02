<template>
  <div class="mt-3">
    <template v-if="canRead">
      <TypeUnitSettingFilterComponent />
      <TypeUnitSettingListComponent />
    </template>
    <template v-if="canCreateOrUpdate">
      <TypeUnitSettingFormComponent />
    </template>
  </div>
</template>

<script setup lang="ts">
  import TypeUnitSettingFilterComponent from '@/modules/negotiations/type-unit-configurator/settings/components/TypeUnitSettingFilterComponent.vue';
  import TypeUnitSettingListComponent from '@/modules/negotiations/type-unit-configurator/settings/components/TypeUnitSettingListComponent.vue';
  import TypeUnitSettingFormComponent from '@/modules/negotiations/type-unit-configurator/settings/components/TypeUnitSettingFormComponent.vue';
  import { useTypeUnitSetting } from '@/modules/negotiations/type-unit-configurator/settings/composables/useTypeUnitSetting';
  import { useTypeUnitConfiguratorPermission } from '@/modules/negotiations/type-unit-configurator/composables/useTypeUnitConfiguratorPermission';

  const { canCreateOrUpdate, canRead } = useTypeUnitConfiguratorPermission();

  useTypeUnitSetting();
</script>
