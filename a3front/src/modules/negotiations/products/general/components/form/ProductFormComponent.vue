<template>
  <div class="form-inputs">
    <a-form layout="vertical" :model="formState" ref="formRefProduct" :rules="formRules">
      <a-row>
        <a-col class="gutter-row" :span="14">
          <a-form-item name="serviceTypeId">
            <template #label>
              <RequiredLabel class="form-label" label="Tipo de servicio:" />
            </template>
            <a-select
              v-model:value="formState.serviceTypeId"
              placeholder="Selecciona"
              :options="serviceTypes"
              show-search
              :filter-option="filterOption"
              @change="handleChangeServiceType"
            />
          </a-form-item>
        </a-col>
      </a-row>
      <a-row>
        <a-col class="gutter-row" :span="7">
          <a-form-item name="code">
            <template #label>
              <RequiredLabel class="form-label" label="Código genérico" />
            </template>
            <a-input
              v-model:value="formState.code"
              placeholder="Ingrese el código"
              :maxlength="6"
              @input="handleInputCode"
              autocomplete="off"
            >
              <template #suffix>
                <template v-if="fieldsAvailability.code.isLoading">
                  <LoadingOutlined />
                </template>
              </template>
            </a-input>

            <FadeTransition>
              <ErrorMessageComponent
                v-show="!fieldsAvailability.code.isAvailable"
                class="mt-2"
                errorMessage="El código ya existe"
              />
            </FadeTransition>
            <!-- <SuggestionMessageComponent class="mt-1" suggestionMessage="Disponible: EXT01" /> -->
          </a-form-item>
        </a-col>
      </a-row>
      <a-row>
        <a-col class="gutter-row" :span="14">
          <a-form-item name="name">
            <template #label>
              <RequiredLabel class="form-label" label="Nombre genérico" />
            </template>
            <a-input
              v-model:value="formState.name"
              placeholder="Ingrese el nombre"
              @input="handleInputName"
              autocomplete="off"
            >
              <template #suffix>
                <template v-if="fieldsAvailability.name.isLoading">
                  <LoadingOutlined />
                </template>
              </template>
            </a-input>

            <FadeTransition>
              <ErrorMessageComponent
                v-show="!fieldsAvailability.name.isAvailable"
                class="mt-2"
                errorMessage="El nombre ya existe"
              />
            </FadeTransition>
            <!-- <SuggestionMessageComponent class="mt-1" suggestionMessage="Disponible: City tour" /> -->
          </a-form-item>
        </a-col>
      </a-row>

      <div class="mt-1 mb-1">
        <a-button
          size="large"
          type="primary"
          class="button-action-primary-strong btn-save"
          @click="handleSave"
          :disabled="disabledSaveButton"
        >
          Guardar datos
        </a-button>
      </div>
    </a-form>
  </div>
</template>
<script setup lang="ts">
  import { LoadingOutlined } from '@ant-design/icons-vue';
  import RequiredLabel from '@/modules/negotiations/supplier-new/components/required-label.vue';
  import { useProductForm } from '@/modules/negotiations/products/general/composables/form/useProductForm';
  import ErrorMessageComponent from '@/modules/negotiations/products/general/components/partials/ErrorMessageComponent.vue';
  // import SuggestionMessageComponent from '@/modules/negotiations/products/general/components/partials/SuggestionMessageComponent.vue';
  import FadeTransition from '@/modules/negotiations/products/general/components/partials/FadeTransition.vue';

  const {
    formState,
    formRules,
    formRefProduct,
    serviceTypes,
    fieldsAvailability,
    disabledSaveButton,
    handleInputName,
    filterOption,
    handleSave,
    handleChangeServiceType,
    handleInputCode,
  } = useProductForm();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .btn-save {
    min-width: 160px;

    &:disabled {
      color: $color-white-4;
      background: $color-black-5;
      border-color: $color-black-5 !important;
    }
  }

  .form-inputs {
    margin-top: 20px;
  }
</style>
