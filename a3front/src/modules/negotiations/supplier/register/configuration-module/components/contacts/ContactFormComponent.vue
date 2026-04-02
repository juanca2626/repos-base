<template>
  <a-drawer
    :open="showDrawerForm"
    :title="formState.id ? 'Actualizar contacto' : 'Crear contacto'"
    :width="500"
    :maskClosable="false"
    :keyboard="false"
    @close="showForm = false"
  >
    <p class="form-title">Ingresar los siguientes datos para crear un contacto:</p>

    <a-spin :spinning="isLoading">
      <a-flex gap="middle" vertical>
        <a-form
          layout="vertical"
          :model="formState"
          class="mt-4"
          ref="formRefContact"
          :rules="formRules"
        >
          <a-row :gutter="16">
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Nombres" name="firstname">
                <a-input v-model:value="formState.firstname" placeholder="Ingrese los nombres" />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Apellidos" name="surname">
                <a-input v-model:value="formState.surname" placeholder="Ingrese los apellidos" />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Tipo" name="typeContactId">
                <a-select
                  v-model:value="formState.typeContactId"
                  class="w-100"
                  placeholder="Seleccionar tipo"
                  show-search
                  :filter-option="filterOption"
                  :options="typeContacts"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Cargo" name="departmentId">
                <a-select
                  v-model:value="formState.departmentId"
                  class="w-100"
                  placeholder="Seleccionar cargo"
                  show-search
                  :filter-option="filterOption"
                  :options="departments"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="24">
              <a-form-item label="Lugar de operación" name="supplierBranchOfficeId">
                <a-select
                  v-model:value="formState.supplierBranchOfficeId"
                  class="w-100"
                  placeholder="Seleccionar lugar de operación"
                  show-search
                  :filter-option="filterOption"
                  :options="operationLocations"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Teléfono o celular principal" name="phone">
                <a-input
                  v-model:value="formState.phone"
                  placeholder="Ingrese el teléfono o celular"
                  :maxLength="9"
                  type="tel"
                  :allow-clear="true"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="12">
              <a-form-item label="Correo electrónico:" name="email">
                <a-input
                  v-model:value="formState.email"
                  placeholder="Ingrese el correo"
                  type="email"
                  :allow-clear="true"
                />
              </a-form-item>
            </a-col>
          </a-row>
        </a-form>
      </a-flex>
    </a-spin>
    <template #footer>
      <a-row :gutter="10">
        <a-col :span="12">
          <a-button type="primary" class="ant-btn-md w-100" @click="showForm = false">
            Cancelar
          </a-button>
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
  import { useContactForm } from '@/modules/negotiations/supplier/register/configuration-module/composables/contacts/useContactForm';
  import { useContactsComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/module-configuration/contacts.composable';

  defineProps({
    showDrawerForm: {
      type: Boolean,
      default: false,
    },
  });

  const emit = defineEmits(['update:showDrawerForm']);

  const {
    formRefContact,
    formState,
    isLoading,
    formRules,
    typeContacts,
    departments,
    operationLocations,
    filterOption,
    handleClose,
    handleSubmit,
  } = useContactForm(emit);

  const { showForm } = useContactsComposable();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .form-title {
    font-size: 16px;
    font-weight: 400;
  }
</style>
