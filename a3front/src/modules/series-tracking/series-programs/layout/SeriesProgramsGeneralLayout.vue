<template>
  <div class="module-negotiations">
    <Can :I="PermissionActionEnum.CREATE" :a="ModulePermissionEnum.SERIES_FACILE">
      <a-title-section
        title="Listado de registro de pasajeros"
        icon="user"
        :btn="{ title: 'Añadir', action: 'showDrawer', icon: 'plus' }"
        @handlerShowDrawer="handlerShowDrawer($event)"
      />
    </Can>
    <template v-if="canCreateOrUpdate">
      <SeriesProgramsFormComponent
        :showDrawer="showDrawer"
        @handlerShowDrawer="handlerShowDrawer($event)"
        @updateFilters="updateFilters"
      />
    </template>

    <div class="p-5">
      <router-view></router-view>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { Can, useAbility } from '@casl/vue';
  import { useSeriesProgramsLayout } from '../composables/useSeriesProgramsLayout';
  import ATitleSection from '@/components/backend/ATitleSection.vue';
  import SeriesProgramsFormComponent from '@/modules/series-tracking/series-programs/components/SeriesProgramsFormComponent.vue';
  import { computed } from 'vue';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';

  const { showDrawer, handlerShowDrawer } = useSeriesProgramsLayout();
  const ability = useAbility();
  const canCreateOrUpdate = computed(() => {
    return (
      ability.can(PermissionActionEnum.CREATE, ModulePermissionEnum.SERIES_FACILE) ||
      ability.can(PermissionActionEnum.UPDATE, ModulePermissionEnum.SERIES_FACILE)
    );
  });
</script>
