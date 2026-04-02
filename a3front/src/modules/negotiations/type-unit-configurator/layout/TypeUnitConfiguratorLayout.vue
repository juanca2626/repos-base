<template>
  <div class="module-negotiations">
    <a-row class="header-bar" justify="space-between" align="center">
      <a-col>
        <div class="icon-bus-container">
          <IconTourBus width="20px" height="20px" />
          <a-typography-title :level="5"> Configuración de tipo de unidades </a-typography-title>
        </div>
      </a-col>
      <a-col>
        <template v-if="canCreate">
          <a-dropdown-button class="ant-btn-dropdown" type="primary">
            <font-awesome-icon :icon="['fas', 'plus']" />
            Agregar
            <template #overlay>
              <a-menu>
                <a-menu-item key="1" @click="handleAddTypeUnit">
                  <span class="container-menu-item">
                    <IconTourBus width="15px" height="15px" />
                    Nueva unidad
                  </span>
                </a-menu-item>
                <a-menu-item key="2" @click="handleAddSetting">
                  <span class="container-menu-item">
                    <font-awesome-icon :icon="['fas', 'gears']" />
                    Nueva configuración
                  </span>
                </a-menu-item>
              </a-menu>
            </template>
            <template #icon>
              <font-awesome-icon :icon="['fas', 'angle-down']" />
            </template>
          </a-dropdown-button>
        </template>
      </a-col>
    </a-row>

    <div class="p-5">
      <router-view></router-view>
    </div>
  </div>
</template>

<script setup lang="ts">
  import IconTourBus from '@/components/icons/IconTourBus.vue';
  import { useTypeUnitConfigurator } from '@/modules/negotiations/type-unit-configurator/composables/useTypeUnitConfigurator';
  import { useTypeUnitConfiguratorPermission } from '@/modules/negotiations/type-unit-configurator/composables/useTypeUnitConfiguratorPermission';

  const { canCreate } = useTypeUnitConfiguratorPermission();

  const { handleAddTypeUnit, handleAddSetting } = useTypeUnitConfigurator();
</script>
<style scoped>
  .icon-bus-container {
    display: flex;
    align-items: center;
  }

  .container-menu-item {
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
</style>
