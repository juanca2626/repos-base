<template>
  <div class="module-negotiations">
    <a-tabs v-model:activeKey="pageActiveTabKey">
      <a-tab-pane :key="pageTabsKeys.typeUnit" tab="Tipos de unidad">
        <div class="mt-3">
          <template v-if="canRead">
            <TypeUnitFilterComponent />
            <TypeUnitListComponent />
          </template>
          <template v-if="canCreateOrUpdate">
            <TypeUnitFormComponent />
          </template>
        </div>
      </a-tab-pane>
      <a-tab-pane :key="pageTabsKeys.typeUnitSetting" tab="Configuración">
        <div class="mt-3">
          <TypeUnitSettingComponent />
        </div>
      </a-tab-pane>
    </a-tabs>
  </div>
</template>

<script lang="ts" setup>
  import { onMounted, onBeforeUnmount } from 'vue';
  import TypeUnitFilterComponent from '@/modules/negotiations/type-unit-configurator/type-units/components/TypeUnitFilterComponent.vue';
  import TypeUnitListComponent from '@/modules/negotiations/type-unit-configurator/type-units/components/TypeUnitListComponent.vue';
  import TypeUnitFormComponent from '@/modules/negotiations/type-unit-configurator/type-units/components/TypeUnitFormComponent.vue';
  import TypeUnitSettingComponent from '@/modules/negotiations/type-unit-configurator/settings/components/TypeUnitSettingComponent.vue';
  import { useTypeUnitConfigurator } from '@/modules/negotiations/type-unit-configurator/composables/useTypeUnitConfigurator';
  import { useTypeUnitConfiguratorPermission } from '@/modules/negotiations/type-unit-configurator/composables/useTypeUnitConfiguratorPermission';

  const { canCreateOrUpdate, canRead } = useTypeUnitConfiguratorPermission();

  const { pageActiveTabKey, pageTabsKeys } = useTypeUnitConfigurator();

  onMounted(() => {
    document.body.classList.add('module-negotiations');
  });

  onBeforeUnmount(() => {
    document.body.classList.remove('module-negotiations');
  });
</script>
