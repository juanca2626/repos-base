<template>
  <div class="information-commercial-component">
    <div class="container">
      <EmptyStateFormGlobalComponent
        v-if="
          isEditMode && !getShowFormComponent(FormComponentEnum.COMMERCIAL_LANGUAGE) && !spinning
        "
        title="Información comercial"
        :formSpecific="FormComponentEnum.COMMERCIAL_LANGUAGE"
      />

      <!-- ✅ Spinner centrado cuando está cargando pero no hay formulario -->
      <div
        v-else-if="!getShowFormComponent(FormComponentEnum.COMMERCIAL_LANGUAGE) && spinning"
        class="loading-container"
      >
        <SpinGlobalComponent :spinning="true" />
      </div>

      <div v-else>
        <div class="title-form">
          <div>Información comercial</div>
          <div
            v-if="getIsEditFormComponent(FormComponentEnum.COMMERCIAL_LANGUAGE)"
            class="edit-form"
            @click="handleShowForm"
          >
            Editar <font-awesome-icon :icon="['fas', 'pen-to-square']" />
          </div>
        </div>

        <SpinGlobalComponent tip="Guardando..." :spinning="spinning">
          <!-- Modo vista -->
          <template v-if="getIsEditFormComponent(FormComponentEnum.COMMERCIAL_LANGUAGE)">
            <!-- Vista para múltiples idiomas -->
            <!-- Vista para múltiples idiomas -->
            <template v-if="showMultipleLanguages && multipleLanguages.length > 1">
              <div class="multiple-languages-container mt-3">
                <div
                  v-for="(lang, index) in multipleLanguages"
                  :key="index"
                  class="language-item"
                  :class="{ 'border-right': index < multipleLanguages.length - 1 }"
                >
                  <div class="language-header">
                    <span class="language-number">{{ getLanguageName(lang.language) }}</span>
                  </div>
                  <div class="mt-1 mb-3">
                    <span class="mode-label-edit">Nivel: </span>
                    <span class="value-content-edit">
                      {{ getLevelLabel(lang.level) }}
                    </span>
                  </div>
                </div>
              </div>
            </template>

            <!-- Vista para un solo idioma (tanto del formState como del primer elemento del array) -->
            <template v-else>
              <div class="language-details mt-3">
                <div class="language-item">
                  <div class="language-header">
                    <span class="language-number">
                      {{ getLanguageName(currentLanguage) }}
                    </span>
                  </div>
                  <div class="mt-1 mb-3">
                    <span class="mode-label-edit">Nivel: </span>
                    <span class="value-content-edit">
                      {{ getLevelLabel(currentLevel) }}
                    </span>
                  </div>
                </div>
              </div>
            </template>
          </template>

          <!-- ✏️ Modo edición -->
          <template v-else>
            <a-form ref="formRef">
              <!-- Formulario para múltiples idiomas -->
              <template v-if="showMultipleLanguages && multipleLanguages.length > 1">
                <div
                  v-for="(lang, index) in multipleLanguages"
                  :key="index"
                  class="language-form-item"
                  :class="{ 'border-bottom': index < multipleLanguages.length - 1 }"
                >
                  <div class="language-form-header">
                    <span class="language-number">Idioma {{ index + 1 }}</span>
                    <a-button
                      v-if="multipleLanguages.length > 1"
                      type="text"
                      danger
                      size="small"
                      @click="removeLanguage(index)"
                      class="remove-button"
                    >
                      <font-awesome-icon :icon="['fas', 'trash']" />
                    </a-button>
                  </div>

                  <a-row :gutter="16" class="mt-1">
                    <a-col :span="12">
                      <a-form-item
                        :validate-status="getRepeatedLanguages.has(index) ? 'error' : ''"
                        :help="
                          getRepeatedLanguages.has(index) ? 'Este idioma ya está seleccionado' : ''
                        "
                      >
                        <template #label>
                          <div class="form-label">Idioma <span style="color: red">*</span></div>
                        </template>
                        <a-select
                          v-model:value="lang.language"
                          placeholder="Selecciona"
                          style="width: 100%"
                          :status="getRepeatedLanguages.has(index) ? 'error' : ''"
                        >
                          <a-select-option
                            v-for="language in languages"
                            :key="language.id"
                            :value="language.id"
                            :disabled="isLanguageSelected(language.id, index)"
                          >
                            {{ language.name }}
                            <span
                              v-if="isLanguageSelected(language.id, index)"
                              style="color: #999; font-size: 12px"
                            >
                              (Ya seleccionado)
                            </span>
                          </a-select-option>
                        </a-select>
                      </a-form-item>
                    </a-col>

                    <a-col :span="12">
                      <a-form-item>
                        <template #label>
                          <div class="form-label">Nivel <span style="color: red">*</span></div>
                        </template>
                        <a-radio-group v-model:value="lang.level">
                          <a-radio
                            v-for="level in levelOptions"
                            :key="level.value"
                            :value="level.value"
                          >
                            {{ level.label }}
                          </a-radio>
                        </a-radio-group>
                      </a-form-item>
                    </a-col>
                  </a-row>
                </div>

                <div class="mt-2 add-language-section">
                  <a v-if="hasAvailableLanguages" @click="addNewLanguage" class="add-more-language">
                    + Agregar otro idioma
                  </a>
                </div>
              </template>

              <!-- Formulario para un solo idioma (tanto del formState como del primer elemento del array) -->
              <template v-else>
                <a-row :gutter="16">
                  <a-col :span="12">
                    <a-form-item>
                      <template #label>
                        <div class="form-label">Idioma <span style="color: red">*</span></div>
                      </template>
                      <a-select
                        v-model:value="currentLanguage"
                        placeholder="Selecciona"
                        style="width: 100%"
                      >
                        <a-select-option v-for="lang in languages" :key="lang.id" :value="lang.id">
                          {{ lang.name }}
                        </a-select-option>
                      </a-select>
                    </a-form-item>
                  </a-col>

                  <a-col :span="12">
                    <a-form-item>
                      <template #label>
                        <div class="form-label">Nivel <span style="color: red">*</span></div>
                      </template>
                      <a-radio-group v-model:value="currentLevel">
                        <a-radio
                          v-for="level in levelOptions"
                          :key="level.value"
                          :value="level.value"
                        >
                          {{ level.label }}
                        </a-radio>
                      </a-radio-group>
                    </a-form-item>
                  </a-col>
                </a-row>

                <div class="mt-2">
                  <a
                    v-if="hasAvailableLanguages"
                    @click="handleAddLanguage"
                    class="add-more-language"
                  >
                    + Agregar otro idioma
                  </a>
                </div>
              </template>
            </a-form>

            <div class="mt-4 options-button">
              <a-button type="default" @click="handleClose">Cancelar</a-button>
              <a-button type="primary" :disabled="!getIsFormValid" @click="handleSave">
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
  import { useLanguageInformationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/language-information.composable';
  import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';

  defineOptions({ name: 'SupplierLanguageFieldComponent' });

  const {
    multipleLanguages,
    showMultipleLanguages,
    currentLanguage,
    currentLevel,
    languages,
    getLanguageName,
    getShowFormComponent,
    getIsEditFormComponent,
    getLevelLabel,
    getIsFormValid,
    getRepeatedLanguages,
    isLanguageSelected,
    hasAvailableLanguages,
    spinning,
    isEditMode,
    handleClose,
    handleSave,
    handleShowForm,
    handleAddLanguage,
    addNewLanguage,
    removeLanguage,
    levelOptions,
  } = useLanguageInformationComposable();
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
    line-height: 18px;
    color: #7e8285;
  }

  .value-content-edit {
    color: #555;
  }

  .add-more-language {
    color: #007bff;
    cursor: pointer;
    user-select: none;
  }

  .language-item {
    padding: 1px 0;
  }

  .language-details {
    display: flex;
    gap: 32px;
  }

  .language-form-item {
    padding: 12px 0;
  }

  .multiple-languages-container {
    display: flex;
    flex-wrap: wrap;
    gap: 32px;
  }

  .border-right {
    border-right: 1px solid #f0f0f0;
    padding-right: 32px;
  }

  .language-header,
  .language-form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
  }

  .language-number {
    font-weight: 600;
    font-size: 16px;
    color: #2f353a;
  }

  .remove-button {
    color: #ff4d4f;
    padding: 4px 8px;
    height: auto;
  }

  .remove-button:hover {
    background-color: #fff2f0;
  }

  .add-language-section {
    padding: 8px 0;
    border-top: 1px dashed #d9d9d9;
  }

  .add-language-button {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 8px 24px;
    height: auto;
  }

  .me-2 {
    margin-right: 8px;
  }

  .loading-container {
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 200px;
    width: 100%;
    padding: 20px;
  }

  .options-button {
    display: flex;
    gap: 12px;

    :deep(.ant-btn-default) {
      width: 118px;
      height: 48px;
      font-weight: 600 !important;
      font-size: 16px !important;
      line-height: 24px;
      padding: 0 24px;
      color: #2f353a;
      background: #ffffff;
      border-color: #2f353a !important;

      &:hover,
      &:active {
        color: #2f353a !important;
        background: #ffffff !important;
        border-color: #2f353a !important;
      }
    }

    :deep(.ant-btn-primary) {
      width: 159px;
      height: 48px;
      font-weight: 600 !important;
      font-size: 16px !important;
      line-height: 24px;
      padding: 0 24px;
      color: #ffffff;
      background: #2f353a;
      border-color: #2f353a !important;

      &:hover,
      &:active {
        color: #ffffff !important;
        background: #2f353a !important;
        border-color: #2f353a !important;
      }

      &:disabled {
        color: #ffffff !important;
        background: #acaeb0 !important;
        border-color: #acaeb0 !important;
      }
    }
  }
</style>

<style>
  .information-commercial-component .options-button .ant-btn {
    height: 48px !important;
    font-weight: 600 !important;
    font-size: 16px !important;
    line-height: 24px !important;
    padding: 0 24px !important;
  }

  .information-commercial-component .options-button .ant-btn-default {
    color: #2f353a !important;
    background: #ffffff !important;
    border-color: #2f353a !important;
  }

  .information-commercial-component .options-button .ant-btn-primary {
    color: #ffffff !important;
    background: #2f353a !important;
    border-color: #2f353a !important;
  }

  .information-commercial-component .options-button .ant-btn-primary:disabled {
    color: #ffffff !important;
    background: #acaeb0 !important;
    border-color: #acaeb0 !important;
  }
</style>
