<template>
  <div class="information-commercial-component">
    <div class="container">
      <EmptyStateFormGlobalComponent
        v-if="!getShowFormComponent(FormComponentEnum.COMMERCIAL_INFORMATION)"
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
        <spin-global-component :tip="spinTip" :spinning="spinning">
          <template v-if="getIsEditFormComponent(FormComponentEnum.COMMERCIAL_INFORMATION)">
            <div class="custom-summary pl-edit-data-summary">
              <p class="text-justify summary-item" v-for="column in commercialInformationSummary">
                <span class="summary-item-title">
                  {{ column.title }}
                </span>
                <span class="summary-item-description description">
                  {{ column.value }}
                </span>
              </p>
            </div>
          </template>
          <template v-else>
            <div>
              <a-form>
                <a-row>
                  <a-col class="gutter-row" :span="12">
                    <a-form-item>
                      <template #label>
                        <div class="form-label">Requisitos</div>
                      </template>
                      <a-textarea
                        v-model:value="formState.requirements"
                        :auto-size="{ minRows: 3 }"
                        placeholder="Ingresa los requisitos"
                      />
                    </a-form-item>
                  </a-col>
                </a-row>
                <a-row>
                  <a-col class="gutter-row" :span="12">
                    <a-form-item>
                      <template #label>
                        <div class="form-label">Restricciones</div>
                      </template>
                      <a-textarea
                        v-model:value="formState.restrictions"
                        :auto-size="{ minRows: 3 }"
                        placeholder="Ingresa las restricciones"
                      />
                    </a-form-item>
                  </a-col>
                </a-row>
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
                      />
                    </a-form-item>
                  </a-col>
                </a-row>
              </a-form>
            </div>

            <div class="mt-4 options-button">
              <a-button size="large" type="default" @click="handleClose"> Cancelar </a-button>
              <a-button size="large" type="primary" :disabled="!getIsFormValid" @click="handleSave">
                Guardar datos
              </a-button>
            </div>
          </template>
        </spin-global-component>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import EmptyStateFormGlobalComponent from '@/modules/negotiations/supplier-new/components/global/empty-state-form-global-component.vue';
  import SpinGlobalComponent from '@/modules/negotiations/supplier-new/components/global/spin-global-component.vue';
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
  import { useCommercialInformationCruiseComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/cruises/commercial-information-cruise.composable';

  defineOptions({
    name: 'CommercialInformationCruiseComponent',
  });

  const {
    formState,
    getShowFormComponent,
    getIsEditFormComponent,
    getIsFormValid,
    spinTip,
    spinning,
    commercialInformationSummary,
    handleClose,
    handleSave,
    handleShowForm,
  } = useCommercialInformationCruiseComposable();
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .custom-summary {
    padding-right: 20px;
  }

  .description {
    margin-left: 10px;
  }
</style>
