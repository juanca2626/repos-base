<template>
  <a-modal
    class="module-negotiations custom-modal"
    v-model:open="showModal"
    :closable="true"
    :title="isEditModal ? 'Editar contacto' : 'Agregar contacto'"
  >
    <a-form layout="vertical">
      <a-form-item label="Ingresa nombre y apellidos:" v-bind="validateInfos.firstname">
        <a-input
          v-model:value="formStateContact.firstname"
          placeholder="Nombre y apellidos del contacto"
          :allow-clear="true"
        />
      </a-form-item>

      <div class="group-inputs">
        <div class="w-50">
          <a-form-item label="Tipo:" v-bind="validateInfos.type_contact_id">
            <a-select
              v-model:value="formStateContact.type_contact_id"
              class="w-100"
              placeholder="Seleccionar tipo"
            >
              <a-select-option
                v-for="item in state.resources.typeContact"
                :key="item.id"
                :value="item.id"
              >
                {{ item.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </div>
        <div class="w-50">
          <a-form-item label="Cargo:" v-bind="validateInfos.department_id">
            <a-select
              v-model:value="formStateContact.department_id"
              class="w-100"
              placeholder="Seleccionar cargo"
            >
              <a-select-option
                v-for="item in state.resources.departments"
                :key="item.id"
                :value="item.id"
              >
                {{ item.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </div>
      </div>

      <div class="group-inputs">
        <div class="w-50">
          <a-form-item label="Ciudad:" v-bind="validateInfos.supplier_branch_office_id">
            <a-select
              v-model:value="formStateContact.supplier_branch_office_id"
              class="w-100"
              placeholder="Seleccionar ciudad"
            >
              <a-select-option
                v-for="item in state.statesResources"
                :key="item.id"
                :value="item.id"
              >
                {{ item.name }}
              </a-select-option>
            </a-select>
          </a-form-item>
        </div>
        <div class="w-50">
          <a-form-item label="Teléfono o celular principal:" v-bind="validateInfos.phone">
            <a-input
              v-model:value="formStateContact.phone"
              placeholder="Ingrese número del celular o..."
              :allow-clear="true"
            />
          </a-form-item>
        </div>
      </div>

      <a-form-item label="Correo electrónico:" v-bind="validateInfos.email">
        <a-input
          v-model:value="formStateContact.email"
          placeholder="Ingresa correo electrónico"
          :allow-clear="true"
        />
      </a-form-item>
    </a-form>

    <template #footer>
      <a-button class="a-button-cancel" @click="handleCancel" size="large"> Cancelar </a-button>
      <a-button
        class="a-button-save"
        type="primary"
        @click="handleOk"
        :loading="state.isLoadingForm"
        :disabled="state.isLoadingForm"
        size="large"
      >
        Guardar
      </a-button>
    </template>
  </a-modal>
</template>

<script lang="ts">
  import { defineComponent } from 'vue';
  import { useNegotiationContacts } from '@/modules/negotiations/supplier/register/configuration-module/composables/useNegotiationContacts';

  export default defineComponent({
    name: 'NegotiationContactsModalComponent',
    setup() {
      const {
        formStateContact,
        handleOk,
        handleCancel,
        validateInfos,
        state,
        showModal,
        isEditModal,
      } = useNegotiationContacts();

      return {
        formStateContact,
        handleOk,
        handleCancel,
        validateInfos,
        state,
        showModal,
        isEditModal,
      };
    },
  });
</script>

<style scoped>
  .group-inputs {
    display: flex;
    justify-content: space-between;
    width: 100%;
    gap: 1rem;

    .w-50 {
      width: 50%;
    }
  }
</style>
