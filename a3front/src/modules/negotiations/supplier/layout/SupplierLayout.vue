<template>
  <component :is="currentLayout">
    <div class="module-negotiations module-negotiations-supplier-ticket">
      <div class="header-bar-supplier-ticket">
        <div class="justify-header">
          <div>
            <div class="title-form">Lista de proveedores</div>
          </div>
          <div>
            <a-button type="primary" @click="handleCreateSupplier">
              <font-awesome-icon :icon="['fas', 'plus']" />
              Crear nuevo proveedor
            </a-button>
          </div>
        </div>
        <div>
          <a-divider type="horizontal" />
        </div>
      </div>
    </div>
    <router-view></router-view>
    <!-- Drawer para clonar proveedor -->
    <a-drawer
      class="module-negotiations"
      title="Clonar a un proveedor existente"
      :visible="isDrawerVisible"
      @close="closeDrawer"
      :width="480"
    >
      <a-form :model="formModel" @submit.prevent="handleSubmit" layout="vertical">
        <a-form-item label="Seleccionar Proveedor">
          <a-select
            v-model="formModel.provider"
            placeholder="Selecciona un proveedor"
            popupClassName="custom-dropdown-backend"
            :loading="isLoading"
            :filter-option="filterOption"
            show-search
            @change="handleProviderChange"
          >
            <a-select-option
              v-for="provider in transportSuppliers"
              :key="provider.supplier_id"
              :value="provider.supplier_id"
            >
              {{ provider.supplier_name }}
            </a-select-option>
          </a-select>
        </a-form-item>
        <a-alert
          message="Recuerda elegir los formularios del registro y configuración que deseas clonar de este proveedor."
          type="info"
          show-icon
        >
        </a-alert>
        <a-divider />
        <!-- Grupo de checkboxes para el registro del proveedor -->
        <div class="checkbox-group" v-if="supplierRegistration.length">
          <p class="title">Registro del proveedor:</p>
          <a-checkbox-group
            v-model="formModel.selectedModules"
            :options="
              supplierRegistration.map((module) => ({
                label: module.module_name,
                value: module.id,
              }))
            "
          />
        </div>
        <!-- Grupo de checkboxes para "transporte turístico" si la URL contiene "/suppliers/land/tourist-transports" -->
        <div v-if="isTouristTransport">
          <div class="checkbox-group" v-if="touristTransportConfiguration.length">
            <div
              v-for="config in touristTransportConfiguration"
              :key="config.supplier_sub_classification_id"
            >
              <p class="title">Configuración de módulos:</p>
              <a-checkbox-group
                v-model="formModel.selectedConfigModules"
                :options="
                  config.modules.map((module) => ({
                    label: module.module_name,
                    value: module.id,
                  }))
                "
              />
            </div>
          </div>
        </div>

        <!-- Grupo de checkboxes para "museos" si la URL contiene "/negotiations/suppliers/MUSEUM" -->
        <div v-if="isMuseum">
          <div class="checkbox-group" v-if="museumConfiguration.length">
            <div v-for="config in museumConfiguration" :key="config.supplier_sub_classification_id">
              <strong>{{ config.name }}:</strong>
              <br />
              <a-checkbox-group
                v-model="formModel.selectedConfigModules"
                :options="
                  config.modules.map((module) => ({
                    label: module.module_name,
                    value: module.id,
                  }))
                "
              />
            </div>
          </div>
        </div>
      </a-form>
      <template #footer>
        <a-row>
          <a-col :span="24">
            <!-- <a-button type="primary" block @click="handleSaveForm" :loading="isSaving">
              Continuar
            </a-button> -->
          </a-col>
        </a-row>
      </template>
    </a-drawer>
  </component>
</template>

<script setup lang="ts">
  import { ref, computed, onMounted } from 'vue';
  import { useRoute } from 'vue-router';
  import SupplierRegisterLayout from '@/modules/negotiations/supplier/register/layout/SupplierRegisterLayout.vue';
  import { useCloneSupplierStore } from '@/modules/negotiations/supplier/store/supplier-clone.store';
  import { useSupplierLayout } from '@/modules/negotiations/supplier/composables/useSupplierLayout';
  import { useSupplierRouteAction } from '@/modules/negotiations/supplier/composables/useSupplierRouteAction';

  const { setSupplierSubClassification, isTicketSubClassification } = useSupplierLayout();
  const { handleCreateSupplier } = useSupplierRouteAction();

  const isDrawerVisible = ref(false);
  const route = useRoute();

  const store = useCloneSupplierStore();
  const supplierRegistration = computed(() => store.supplierRegistration);
  const moduleConfiguration = computed(() => store.moduleConfiguration);
  const transportSuppliers = computed(() => store.transportSuppliers); // Lista de proveedores
  const isLoading = computed(() => store.isLoading);

  const formModel = ref({
    provider: null, // Para almacenar el proveedor seleccionado
    selectedModules: [] as number[], // Para almacenar los módulos seleccionados del proveedor
    selectedConfigModules: [] as number[], // Para almacenar los módulos seleccionados de la configuración
  });

  const closeDrawer = () => {
    isDrawerVisible.value = false;
  };

  const currentLayout = computed(() => {
    return route.meta.layout === 'SupplierRegisterLayout' ? SupplierRegisterLayout : 'div';
  });

  const filterOption = (input: any, option: any) => {
    return (
      option.label.toLowerCase().indexOf(input.toLowerCase()) >= 0 ||
      option.value.toString().toLowerCase().indexOf(input.toLowerCase()) >= 0
    );
  };

  const handleCloneSupplier = () => {
    isDrawerVisible.value = true;
  };

  const handleSubmit = () => {
    if (!formModel.value.provider) {
      console.log('Por favor, selecciona un proveedor.');
      return;
    }
    console.log('Proveedor seleccionado:', formModel.value.provider);
    // Aquí podrías agregar más lógica para clonar el proveedor según la selección
  };

  const isTouristTransport = computed(() => {
    return route.path.includes('tourist-transports');
  });

  const touristTransportConfiguration = computed(() => {
    return moduleConfiguration.value.filter((config) =>
      config.name.toLowerCase().includes('transporte turístico')
    );
  });

  const isMuseum = computed(() => {
    return route.path.includes('museum');
  });

  const museumConfiguration = computed(() => {
    return moduleConfiguration.value.filter((config) =>
      config.name.toLowerCase().includes('museo')
    );
  });

  const handleProviderChange = (value: any) => {
    console.log('Proveedor seleccionado:', value);
    // Aquí puedes actualizar los módulos del proveedor según la selección
    // store.fetchModuleConfigurations(value); // Este método debe ser creado para actualizar los módulos del proveedor seleccionado.
  };

  onMounted(() => {
    store.fetchSupplierData(); // Asegúrate de que los datos se carguen al montar el componente
    // store.fetchTransportSuppliers(); // Obtener los proveedores de transporte
    setSupplierSubClassification(Number(route.meta.supplierSubClassification));
  });
</script>

<style lang="scss">
  .module-negotiations-supplier-ticket {
    padding-top: 1rem;

    .header-bar-supplier-ticket {
      background: #ffffff;
      border-bottom-color: white;
      padding: 0 20px;

      .justify-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
      }

      .title-form {
        font-weight: 700;
        font-size: 24px;
        line-height: 32px;
        color: #2f353a;
      }

      .ant-divider-horizontal {
        margin-top: 1rem;
        margin-bottom: 0;
      }

      button {
        width: 254px;
        height: 48px;
        gap: 8px;
        border-radius: 5px;
        padding: 12px 24px;
        background: #2f353a;
        border: 1px solid #2f353a;

        &:hover {
          background: #3c4349;
          border: 1px solid #2f353a;
        }
      }
    }
  }
</style>
