<template>
  <div class="supplier-register-layout">
    <!-- Encabezado con Título y Botones de Acción -->
    <a-row class="header-bar" justify="space-between" align="middle">
      <a-col>
        <HeaderTitleSupplierComponent
          :name="isFormEditMode ? 'Editar' : 'Registrar proveedor'"
          :separator="isFormEditMode ? 'a' : ''"
          :prefixIcon="isFormEditMode ? CustomUserPenIcon : CustomUserIcon"
          :title="headerTitle"
        />
      </a-col>
      <a-col>
        <a-space>
          <a-button type="default" class="btn-secondary ant-btn-md" @click="handleCancel">
            <font-awesome-icon :icon="['fas', 'chevron-left']" />
            <span>Regresar al Listado</span>
          </a-button>
          <a-button type="primary" class="ant-btn-md" @click="handleSave" :disabled="isLoadingForm">
            <font-awesome-icon :icon="['fas', 'floppy-disk']" />
            <span class="">Guardar Cambios</span>
          </a-button>
        </a-space>
      </a-col>
    </a-row>
    <!-- Contenido del formulario (hijo) -->
    <div class="content">
      <router-view></router-view>
    </div>
  </div>
</template>

<script setup lang="ts">
  // import { computed } from 'vue';
  import { emit } from '@/modules/negotiations/api/eventBus';
  import { useSupplierLayout } from '@/modules/negotiations/supplier/composables/useSupplierLayout';
  import { useSupplierForm } from '@/modules/negotiations/supplier/register/composables/useSupplierForm';

  import CustomUserIcon from '@/modules/negotiations/supplier/components/icons/CustomUserIcon.vue';
  import CustomUserPenIcon from '@/modules/negotiations/supplier/components/icons/CustomUserPenIcon.vue';
  import HeaderTitleSupplierComponent from '@/modules/negotiations/supplier/components/HeaderTitleSupplierComponent.vue';

  const { isFormEditMode, headerTitle, isLoadingForm } = useSupplierLayout();
  const { handleCancel } = useSupplierForm();

  // const title = computed(() =>
  //   isFormEditMode.value
  //     ? `${formStateNegotiation.supplierCode || ''} - ${formStateNegotiation.name || ''}`
  //     : ''
  // );

  const handleSave = () => {
    // emitir evento para ejecutar submit en SupplierFormWrapper
    emit('submitSupplierForm');
  };
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  * {
    font-family: $font_general !important;
  }

  .header-title {
    color: $color-black;
    font-size: 18px;
    font-weight: 700;
  }

  .header {
    padding-bottom: 16px;
    border-bottom: 1px solid #f0f0f0;
  }
</style>
