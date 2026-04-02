<template>
  <div class="service-configuration-form-container">
    <!-- Modo lectura -->
    <ReadModeComponent v-if="!isEditingContent" title="Configuración" @edit="handleEditMode">
      <div class="read-item">
        <span class="read-item-label">Unidad de medida</span>
        <span class="read-item-value">
          {{ getMeasurementUnitLabel(formState.measurementUnit) || '-' }}
        </span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Cantidad de personas permitida</span>
        <span class="read-item-value">
          De {{ formState.minCapacity || '_' }} a {{ formState.maxCapacity || '_' }} - Cantidad
          incluye niños
        </span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Incluye niños</span>
        <span class="read-item-value">{{ formState.includesChildren ? 'Sí' : 'No' }}</span>
      </div>

      <div class="read-item">
        <span class="read-item-label">Incluye infantes</span>
        <span class="read-item-value">{{ formState.includesInfants ? 'Sí' : 'No' }}</span>
      </div>
    </ReadModeComponent>

    <!-- Modo edición -->
    <template v-else>
      <WizardHeaderComponent title="Configuración" :completed="completedFields" :total="2" />
      <div class="edit-mode-container">
        <a-form :model="formState" ref="formRef" layout="vertical" class="compact-form">
          <a-row :gutter="16">
            <a-col :span="24" :lg="10">
              <a-form-item name="measurementUnit">
                <template #label>
                  <required-label class="form-label" label="Unidad de medida:"
                /></template>
                <a-select
                  v-model:value="formState.measurementUnit"
                  placeholder="Pasajeros"
                  class="custom-select-no-shadow"
                  :options="measurementUnitOptions"
                  :allowClear="true"
                />
              </a-form-item>
            </a-col>
          </a-row>

          <a-row :gutter="16">
            <a-col :span="24" :sm="12" :lg="5">
              <a-form-item name="minCapacity">
                <template #label>
                  <required-label class="form-label" label="Capacidad mínima:" />
                </template>
                <a-input-number
                  v-model:value="formState.minCapacity"
                  placeholder="1"
                  :min="0"
                  style="width: 100%"
                />
              </a-form-item>
            </a-col>

            <a-col :span="24" :sm="12" :lg="5">
              <a-form-item name="maxCapacity">
                <template #label>
                  <required-label class="form-label" label="Capacidad máxima:" />
                </template>
                <a-input-number
                  v-model:value="formState.maxCapacity"
                  placeholder="5"
                  :min="0"
                  style="width: 100%"
                />
              </a-form-item>
            </a-col>
          </a-row>

          <a-row :gutter="16">
            <a-col :span="24" :sm="5">
              <a-form-item name="includesChildren">
                <a-checkbox v-model:checked="formState.includesChildren">
                  Incluye niños
                </a-checkbox>
              </a-form-item>
            </a-col>

            <a-col :span="24" :sm="6">
              <a-form-item name="includesInfants">
                <a-checkbox v-model:checked="formState.includesInfants">
                  Incluye infantes
                </a-checkbox>
              </a-form-item>
            </a-col>
          </a-row>
        </a-form>

        <div class="service-configuration-actions">
          <a-button
            size="large"
            type="primary"
            :disabled="isLoadingButton || !isFormValid"
            @click="handleSaveAndAdvance"
          >
            Guardar Datos
          </a-button>
        </div>
      </div>
    </template>

    <!-- Modal de confirmación de edición -->
    <ModalEditComponent
      :visible="showEditModal"
      @confirm="handleConfirmEdit"
      @cancel="handleCancelEdit"
    />
  </div>
</template>

<script setup lang="ts">
  import ReadModeComponent from '@/modules/negotiations/products/configuration/content/shared/components/ReadModeComponent.vue';
  import WizardHeaderComponent from '@/modules/negotiations/products/configuration/components/WizardHeaderComponent.vue';
  import ModalEditComponent from '@/modules/negotiations/products/configuration/components/ModalEditComponent.vue';
  import RequiredLabel from '@/modules/negotiations/supplier-new/components/required-label.vue';
  import { useServiceConfigurationForm } from '../composables/useServiceConfigurationForm';
  import { useSharedService } from '@/modules/negotiations/products/configuration/composables/useSharedService';

  interface Props {
    currentKey: string;
    currentCode: string;
  }

  const props = defineProps<Props>();

  const { formState, measurementUnitOptions } = useSharedService();

  const {
    isEditingContent,
    isLoadingButton,
    showEditModal,
    completedFields,
    isFormValid,
    getMeasurementUnitLabel,
    handleEditMode,
    handleConfirmEdit,
    handleCancelEdit,
    handleSaveAndAdvance,
  } = useServiceConfigurationForm({
    formState,
    measurementUnitOptions,
    props,
  });
</script>

<style scoped lang="scss">
  @import '@/modules/negotiations/products/configuration/content/shared/styles/configuration/service-configuration-form.styles.scss';
</style>
