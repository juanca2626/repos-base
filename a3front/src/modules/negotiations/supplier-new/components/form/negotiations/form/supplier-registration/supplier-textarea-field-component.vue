<template>
  <div class="information-commercial-component">
    <div class="container">
      <EmptyStateFormGlobalComponent
        v-if="
          isEditMode &&
          !hasData &&
          (!getShowFormComponent(FormComponentEnum.COMMERCIAL_INFORMATION) ||
            getIsEditFormComponent(FormComponentEnum.COMMERCIAL_INFORMATION))
        "
        title="Información comercial"
        :formSpecific="FormComponentEnum.COMMERCIAL_INFORMATION"
      />
      <div v-else>
        <div class="title-form">
          <div>Información comercial</div>
          <div
            v-if="getIsEditFormComponent(FormComponentEnum.COMMERCIAL_INFORMATION)"
            class="edit-form"
            @click="handleShowForm"
          >
            Editar <font-awesome-icon :icon="['fas', 'pen-to-square']" />
          </div>
        </div>

        <SpinGlobalComponent :tip="spinTip" :spinning="spinning">
          <template v-if="getIsEditFormComponent(FormComponentEnum.COMMERCIAL_INFORMATION)">
            <!-- Modo vista -->
            <div class="mt-3">
              <span class="mode-label-edit">Data adicional: </span>
              <span class="value-content-edit">
                {{ formState.additionalInformation || 'Sin información' }}
              </span>
            </div>
          </template>

          <template v-else>
            <!-- Modo edición -->
            <a-form>
              <a-row>
                <a-col class="gutter-row" :span="12">
                  <a-form-item>
                    <template #label>
                      <div class="form-label">Data adicional</div>
                    </template>
                    <a-textarea
                      v-model:value="formState.additionalInformation"
                      :auto-size="{ minRows: 3 }"
                      placeholder="Ingresa data adicional"
                      style="max-width: 500px"
                    />
                  </a-form-item>
                </a-col>
              </a-row>
            </a-form>

            <div class="mt-4 options-button">
              <a-button size="large" type="default" @click="handleClose"> Cancelar </a-button>
              <a-button size="large" type="primary" :disabled="!getIsFormValid" @click="handleSave">
                Guardar datos
              </a-button>
            </div>
          </template>
        </SpinGlobalComponent>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import EmptyStateFormGlobalComponent from '@/modules/negotiations/supplier-new/components/global/empty-state-form-global-component.vue';
  import SpinGlobalComponent from '@/modules/negotiations/supplier-new/components/global/spin-global-component.vue';
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
  import { useCommercialInformationCruiseComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/cruises/commercial-information-cruise.composable';
  import { computed } from 'vue';

  defineOptions({
    name: 'SupplierTextareaFieldComponent',
  });

  const hasData = computed(() => {
    return Boolean(formState.additionalInformation);
  });

  const {
    formState,
    getShowFormComponent,
    getIsEditFormComponent,
    getIsFormValid,
    spinTip,
    spinning,
    isEditMode,
    handleClose,
    handleSave,
    handleShowForm,
  } = useCommercialInformationCruiseComposable();
</script>

<style scoped>
  .form-label {
    font-weight: 700;
    font-size: 14px;
    line-height: 20px;
    color: #2f353a;
  }
  .mode-label-edit {
    font-weight: 600;
    font-size: 14px;
    line-height: 24px;
    color: #7e8285;
  }
  .value-content-edit {
    color: #555;
  }
</style>
