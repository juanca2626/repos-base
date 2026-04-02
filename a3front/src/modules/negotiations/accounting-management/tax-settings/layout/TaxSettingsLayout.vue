<template>
  <div class="module-negotiations">
    <a-row class="header-bar" justify="space-between" align="center">
      <a-col>
        <a-typography-title :level="5">
          <font-awesome-icon :icon="['fas', 'cog']" />
          <span v-if="activeTab !== 'taxSuppliersAssignments'">Configuración de Igv</span>
          <span v-if="activeTab === 'taxSuppliersAssignments'">Asignación de Igv</span>
        </a-typography-title>
      </a-col>
      <a-col>
        <a-button
          v-if="activeTab === 'taxSuppliersAssignments'"
          class="ant-btn-md btn-back"
          primary
          block
          @click="navigateToRouteList"
        >
          <font-awesome-icon :icon="['fas', 'chevron-left']" />
          Regresar al listado
        </a-button>

        <Can :I="PermissionActionEnum.CREATE" :a="ModulePermissionEnum.TAX_GENERAL">
          <a-button
            v-if="activeTab === 'taxGeneral'"
            class="ant-btn-md"
            type="primary"
            @click="handlerShowDrawer"
          >
            <font-awesome-icon :icon="['fas', 'plus']" />
            Añadir
          </a-button>
        </Can>
        <Can :I="PermissionActionEnum.CREATE" :a="ModulePermissionEnum.TAX_SUPPLIER_TYPE">
          <a-dropdown-button
            v-if="activeTab === 'taxSuppliers'"
            class="ant-btn-dropdown"
            type="primary"
          >
            <font-awesome-icon :icon="['fas', 'plus']" />
            Añadir
            <template #overlay>
              <a-menu>
                <a-menu-item key="1" @click="handlerShowDrawerLaw"> Crear nueva ley</a-menu-item>
                <a-menu-item key="2" @click="handlerShowDrawerAssignLawSupplier">
                  Asignar ley a proveedor
                </a-menu-item>
              </a-menu>
            </template>
            <template #icon>
              <font-awesome-icon :icon="['fas', 'angle-down']" />
            </template>
          </a-dropdown-button>
        </Can>
      </a-col>
    </a-row>

    <template v-if="canCreateOrUpdateGeneral">
      <FormGeneralTax
        :showDrawer="showDrawer"
        @handlerShowDrawer="handlerShowDrawer($event)"
        @updateFilters="updateFilters"
      />
    </template>

    <template v-if="canCreateOrUpdateSupplierType">
      <FormLaw
        :showDrawerLaw="showDrawerLaw"
        @handlerShowDrawerLaw="handlerShowDrawerLaw($event)"
      />
      <FormAssignLawSupplier
        :showDrawerLaw="showDrawerAssignLawSupplier"
        @handlerShowDrawerAssignLawSupplier="handlerShowDrawerAssignLawSupplier($event)"
        @updateFilters="updateFilters"
      />
    </template>

    <div>
      <a-tabs
        class="ms-5 me-5 mb-3 mt-5"
        v-model:activeKey="activeTab"
        @change="handleChangeTab"
        v-if="activeTab !== 'taxSuppliersAssignments'"
      >
        <a-tab-pane key="taxGeneral" tab="Configuración de Igv" v-if="canReadTabGeneral" />

        <a-tab-pane
          key="taxSuppliers"
          tab="IGV por tipo de proveedor"
          force-render
          v-if="canReadTabSupplierType"
        />
      </a-tabs>
      <router-view></router-view>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { onMounted } from 'vue';
  import FormLaw from '@/modules/negotiations/accounting-management/tax-settings/suppliers/components/SupplierTaxFormLawComponent.vue';
  import FormGeneralTax from '@/modules/negotiations/accounting-management/tax-settings/general/components/GeneralTaxFormComponent.vue';
  import FormAssignLawSupplier from '@/modules/negotiations/accounting-management/tax-settings/suppliers/components/SupplierTaxFormAssignLawSupplierComponent.vue';
  import { useTaxSettingsLayout } from '@/modules/negotiations/accounting-management/tax-settings/composables/useTaxSettingsLayout';
  import { ModulePermissionEnum } from '@/enums/module-permission.enum';
  import { PermissionActionEnum } from '@/enums/permission-action.enum';
  import { useModulePermission } from '@/composables/useModulePermission';

  const {
    showDrawerLaw,
    showDrawer,
    showDrawerAssignLawSupplier,
    route,
    activeTab,
    handleChangeTab,
    handlerShowDrawerLaw,
    handlerShowDrawer,
    handlerShowDrawerAssignLawSupplier,
    updateFilters,
    navigateToRouteList,
  } = useTaxSettingsLayout();

  const { canCreateOrUpdate, canRead } = useModulePermission();

  const canReadTabGeneral = canRead(ModulePermissionEnum.TAX_GENERAL);

  const canReadTabSupplierType = canRead(ModulePermissionEnum.TAX_SUPPLIER_TYPE);

  const canCreateOrUpdateGeneral = canCreateOrUpdate(ModulePermissionEnum.TAX_GENERAL);

  const canCreateOrUpdateSupplierType = canCreateOrUpdate(ModulePermissionEnum.TAX_SUPPLIER_TYPE);

  onMounted(() => {
    activeTab.value = route.name;
  });
</script>
<style scoped lang="scss"></style>
