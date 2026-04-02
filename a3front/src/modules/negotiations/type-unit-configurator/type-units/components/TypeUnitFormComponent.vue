<template>
  <a-drawer
    :open="showDrawerForm"
    :title="isEditMode ? 'Editar unidad' : 'Agregar unidad'"
    :width="500"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
  >
    <p class="form-title">
      <template v-if="!isEditMode"> Agrega las unidades según requieras: </template>
      <template v-else> Edita la unidad seleccionada: </template>
    </p>

    <a-spin :spinning="isLoading">
      <template v-for="(row, index) in typeUnits">
        <TypeUnitFormItemComponent :index="index" ref="formRefsTypeUnit" />
      </template>
      <a-row justify="end">
        <a-col>
          <a-button
            v-if="!isEditMode"
            type="link"
            class="custom-primary-font add-extension-button"
            @click="addTypeUnit"
          >
            <font-awesome-icon :icon="['fas', 'plus']" />
            Agregar unidad
          </a-button>
        </a-col>
      </a-row>
    </a-spin>

    <template #footer>
      <a-row :gutter="10">
        <a-col :span="12">
          <a-button type="primary" class="btn-secondary ant-btn-md w-100" @click="handleClose"
            >Cancelar</a-button
          >
        </a-col>
        <a-col :span="12">
          <a-button
            type="primary"
            class="ant-btn-md w-100"
            @click="handleSubmit()"
            :disabled="isLoading"
          >
            Guardar
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
</template>
<script setup lang="ts">
  import { useTypeUnitForm } from '@/modules/negotiations/type-unit-configurator/type-units/composables/useTypeUnitForm';
  import TypeUnitFormItemComponent from '@/modules/negotiations/type-unit-configurator/type-units/components/TypeUnitFormItemComponent.vue';

  const {
    showDrawerForm,
    formRefsTypeUnit,
    typeUnits,
    isLoading,
    isEditMode,
    handleClose,
    handleSubmit,
    addTypeUnit,
  } = useTypeUnitForm();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .form-title {
    font-size: 16px;
    font-weight: 400;
  }

  .add-extension-button {
    font-size: 13px;
    font-weight: 500;
    color: $color-blue;
  }
</style>
