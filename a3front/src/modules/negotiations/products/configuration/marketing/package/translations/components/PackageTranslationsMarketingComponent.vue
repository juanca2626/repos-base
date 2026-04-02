<template>
  <div class="generic-translations-marketing-component">
    <WizardHeaderComponent
      title="Traducciones"
      :completed="completedFields"
      :total="totalFields"
      v-if="isEditingContent"
    />

    <div class="edit-content-mode-container mb-5" v-if="!isEditingContent">
      <h5 class="section-title mt-2">Traducciones</h5>
      <div class="mt-4 translations-card">
        <a-card size="small" class="card-translations">
          <a-tabs v-model:activeKey="activeLanguageTab" class="translations-tabs">
            <a-tab-pane v-for="lang in languagesOptions" :key="lang.key" :tab="lang.label">
              <template v-if="getTranslationByLang(lang.key)">
                <div class="translation-tab-content edit-content-form">
                  <!-- Comercial name section -->
                  <div class="edit-content-section">
                    <div class="edit-content-section-header">
                      <span class="edit-content-section-title">Nombre comercial del servicio</span>
                      <a-tooltip placement="topLeft">
                        <template #title>
                          <span>Nombre comercial del servicio</span>
                        </template>
                        <IconCircleInfo />
                      </a-tooltip>
                    </div>
                    <div class="edit-content-section-divider"></div>
                    <a-input
                      :value="getTranslationByLang(lang.key)?.translation_name_comercial || ''"
                      @update:value="(val: string) => updateTranslationNameComercial(lang.key, val)"
                      placeholder="Beach Wheel Tour"
                      class="edit-content-input"
                      disabled
                    />
                  </div>

                  <!-- Itinerary section -->
                  <div class="edit-content-section">
                    <div class="edit-content-section-header">
                      <span class="edit-content-section-title">Itinerary</span>
                      <a-tooltip placement="topLeft">
                        <template #title>
                          <span>Texto del itinerario</span>
                        </template>
                        <IconCircleInfo />
                      </a-tooltip>
                    </div>
                    <div class="edit-content-section-divider"></div>
                    <template
                      v-if="getTranslationByLang(lang.key)?.translation_itinerary?.days?.length"
                    >
                      <div
                        v-for="day in getTranslationByLang(lang.key)?.translation_itinerary?.days"
                        :key="day.dayNumber"
                        class="itinerary-day-item-readonly"
                      >
                        <!-- Contenedor principal del día -->
                        <div class="itinerary-day-row">
                          <!-- Parte izquierda: Icono + Día -->
                          <div class="itinerary-day-left">
                            <IconCalendarCheck class="itinerary-day-icon" />
                            <span class="day-title-text">Día {{ day.dayNumber }}</span>
                          </div>

                          <!-- Parte derecha: Texto -->
                          <div class="itinerary-day-right">
                            <div class="itinerary-original-text">{{ day.text }}</div>
                          </div>
                        </div>
                      </div>
                    </template>
                  </div>

                  <!-- Menu section -->
                  <div class="edit-content-section">
                    <div class="edit-content-section-header">
                      <span class="edit-content-section-title">Menú</span>
                      <a-tooltip placement="topLeft">
                        <template #title>
                          <span>Texto del menú</span>
                        </template>
                        <IconCircleInfo />
                      </a-tooltip>
                    </div>
                    <div class="edit-content-section-divider"></div>
                    <div class="itinerary-original-text">
                      {{ getTranslationByLang(lang.key)?.translation_menu || '' }}
                    </div>
                  </div>

                  <!-- Summary section -->
                  <div class="edit-content-section mb-5">
                    <div class="edit-content-section-header">
                      <span class="edit-content-section-title">Summary</span>
                      <a-tooltip placement="topLeft">
                        <template #title>
                          <span>Resumen del servicio</span>
                        </template>
                        <IconCircleInfo />
                      </a-tooltip>
                    </div>
                    <div class="edit-content-section-divider"></div>
                    <div class="summary-items-container">
                      <div class="itinerary-translation-text">
                        {{ getTranslationByLang(lang.key)?.translation_summary }}
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </a-tab-pane>
          </a-tabs>
        </a-card>
      </div>
    </div>
    <div class="edit-mode-container mb-5" v-if="isEditingContent">
      <div v-if="showAiTranslationAlert" class="ai-translation-alert-wrapper">
        <a-alert
          class="ai-translation-alert"
          show-icon
          :closable="true"
          @close="closeAiTranslationAlert"
        >
          <template #icon>
            <IconCircleInfo class="ai-translation-alert-icon" />
          </template>
          <template #message>
            <span class="ai-translation-alert-text">
              Traducción automática por IA completa de los dos idiomas seleccionados
            </span>
          </template>
        </a-alert>
      </div>

      <div class="mt-4 translations-card">
        <a-card size="small" class="card-translations">
          <a-tabs v-model:activeKey="activeLanguageTab" class="translations-tabs">
            <a-tab-pane v-for="lang in languagesOptions" :key="lang.key" :tab="lang.label">
              <template v-if="getTranslationByLang(lang.key)">
                <div class="translation-tab-content">
                  <a-form ref="formRef" layout="vertical" class="compact-form">
                    <a-row :gutter="16">
                      <a-col :span="24" :lg="10">
                        <a-form-item name="nameComercialOriginal">
                          <template #label>
                            <span class="form-label form-label-name-service"
                              >Nombre comercial:</span
                            >
                            <a-tooltip
                              placement="topLeft"
                              overlay-class-name="naming-guidelines-tooltip"
                            >
                              <template #title>
                                <div class="tooltip-content">
                                  <!-- <div class="tooltip-title">Traducciones con IA</div> -->
                                  <div class="tooltip-examples">
                                    <div class="tooltip-example">Texto ingresado por Loading</div>
                                  </div>
                                </div>
                              </template>
                              <IconCircleInfo />
                            </a-tooltip>
                          </template>
                          <a-input
                            :value="formState.nameComercialOriginal"
                            disabled
                            class="translation-input-disabled"
                          />
                        </a-form-item>
                        <a-form-item name="nameComercialTranslation">
                          <template #label>
                            <span class="form-label form-label-name-service">Traducción:</span>
                          </template>
                          <a-input
                            :value="getTranslationByLang(lang.key)?.translation_name_comercial"
                            disabled
                            class="translation-input-disabled"
                          />
                        </a-form-item>
                        <a-form-item name="nameComercialQualification">
                          <template #label>
                            <span class="form-label form-label-name-service"
                              >Calificación de la traducción para el nombre comercial:</span
                            >
                          </template>
                          <a-radio-group
                            :value="getTranslationByLang(lang.key)?.qualification_name_comercial_id"
                            @change="
                              (e: any) =>
                                updateQualificationNameComercial(lang.key, e.target?.value ?? e)
                            "
                            class="translation-radio-group"
                          >
                            <a-radio
                              v-for="opt in qualificationNameComercialOptions"
                              :key="opt.id"
                              :value="opt.id"
                            >
                              {{ opt.name }}
                            </a-radio>
                          </a-radio-group>
                        </a-form-item>
                      </a-col>
                    </a-row>
                  </a-form>

                  <!-- Card Itinerario -->
                  <a-card size="small" class="card-itinerary-translation">
                    <template #title>
                      <div class="card-itinerary-header">
                        <span class="card-text-type-title">Itinerario</span>
                        <a-tooltip placement="topLeft">
                          <template #title>
                            <span>Texto original del itinerario</span>
                          </template>
                          <IconCircleInfo />
                        </a-tooltip>
                      </div>
                    </template>
                    <div class="card-itinerary-content">
                      <template
                        v-if="getTranslationByLang(lang.key)?.translation_itinerary?.days?.length"
                      >
                        <div
                          v-for="day in getTranslationByLang(lang.key)?.translation_itinerary?.days"
                          :key="day.dayNumber"
                          class="itinerary-day-item"
                        >
                          <!-- Contenedor principal del día -->
                          <div class="itinerary-day-row">
                            <!-- Parte izquierda: Icono + Día -->
                            <div class="itinerary-day-left">
                              <IconCalendarCheck class="itinerary-day-icon" />
                              <span class="day-title-text">Día {{ day.dayNumber }}</span>
                            </div>

                            <!-- Parte derecha: Texto, Calificación y Traducción correcta -->
                            <div class="itinerary-day-right">
                              <!-- Texto original del día -->
                              <div class="itinerary-original-text">{{ day.text }}</div>

                              <!-- Calificación -->
                              <div class="translation-field mt-4">
                                <span
                                  class="translation-field-label translation-field-label-section"
                                  >Calificación de la traducción para el día
                                  {{ day.dayNumber }}</span
                                >
                                <a-radio-group
                                  :value="day.qualification_id"
                                  @change="
                                    (e: any) =>
                                      updateQualificationItineraryDay(
                                        lang.key,
                                        day.dayNumber,
                                        e.target?.value ?? e
                                      )
                                  "
                                  class="translation-radio-group"
                                >
                                  <a-radio
                                    v-for="opt in qualificationItineraryOptions"
                                    :key="opt.id"
                                    :value="opt.id"
                                  >
                                    {{ opt.name }}
                                  </a-radio>
                                </a-radio-group>
                              </div>

                              <!-- Ingresa la traducción correcta -->
                              <div v-if="day.qualification_id !== 4" class="translation-field mt-4">
                                <span class="translation-field-label"
                                  >Ingresa la traducción correcta</span
                                >
                                <div class="editor-container">
                                  <EditorQuillComponent
                                    :model-value="day.translation_correct || ''"
                                    @update:model-value="
                                      (val: string) =>
                                        updateTranslationItineraryDayCorrect(
                                          lang.key,
                                          day.dayNumber,
                                          val
                                        )
                                    "
                                    placeholder=""
                                    class="custom-editor"
                                    :max-length="1000"
                                  />
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </template>
                    </div>
                  </a-card>

                  <!-- Card Menú -->
                  <a-card size="small" class="card-itinerary-translation">
                    <template #title>
                      <div class="card-itinerary-header">
                        <span class="card-text-type-title">Menú</span>
                        <a-tooltip placement="topLeft">
                          <template #title>
                            <span>Texto original del menú</span>
                          </template>
                          <IconCircleInfo />
                        </a-tooltip>
                      </div>
                    </template>
                    <div class="card-itinerary-content">
                      <div class="itinerary-original-text">{{ menuOriginal }}</div>

                      <div class="translation-field mt-4">
                        <span class="translation-field-label">Traducción</span>
                        <div class="itinerary-translation-text">
                          {{ getTranslationByLang(lang.key)?.translation_menu }}
                        </div>
                      </div>

                      <div class="translation-field mt-4">
                        <span class="translation-field-label translation-field-label-section"
                          >Calificación de la traducción para el men?</span
                        >
                        <a-radio-group
                          :value="getTranslationByLang(lang.key)?.qualification_menu_id"
                          @change="
                            (e: any) => updateQualificationMenu(lang.key, e.target?.value ?? e)
                          "
                          class="translation-radio-group"
                        >
                          <a-radio
                            v-for="opt in qualificationMenuOptions"
                            :key="opt.id"
                            :value="opt.id"
                          >
                            {{ opt.name }}
                          </a-radio>
                        </a-radio-group>
                      </div>

                      <div
                        v-if="getTranslationByLang(lang.key)?.qualification_menu_id !== 4"
                        class="translation-field mt-4"
                      >
                        <span class="translation-field-label">Ingresa la traducción correcta</span>
                        <div class="editor-container">
                          <EditorQuillComponent
                            :model-value="
                              getTranslationByLang(lang.key)?.translation_menu_correct || ''
                            "
                            @update:model-value="
                              (val: string) => updateTranslationMenuCorrect(lang.key, val)
                            "
                            placeholder=""
                            class="custom-editor"
                            :max-length="1000"
                          />
                        </div>
                      </div>
                    </div>
                  </a-card>

                  <!-- Card Summary -->
                  <a-card size="small" class="card-itinerary-translation">
                    <template #title>
                      <div class="card-itinerary-header">
                        <span class="card-text-type-title">Summary</span>
                        <a-tooltip placement="topLeft">
                          <template #title>
                            <span>Texto original del summary</span>
                          </template>
                          <IconCircleInfo />
                        </a-tooltip>
                      </div>
                    </template>
                    <div class="card-itinerary-content">
                      <div class="itinerary-original-text">{{ summaryOriginal }}</div>

                      <div class="translation-field mt-4">
                        <span class="translation-field-label">Traducción</span>
                        <div class="itinerary-translation-text">
                          {{ getTranslationByLang(lang.key)?.translation_summary }}
                        </div>
                      </div>

                      <div class="translation-field mt-4">
                        <span class="translation-field-label translation-field-label-section"
                          >Calificación de la traducción para el summary</span
                        >
                        <a-radio-group
                          :value="getTranslationByLang(lang.key)?.qualification_summary_id"
                          @change="
                            (e: any) => updateQualificationSummary(lang.key, e.target?.value ?? e)
                          "
                          class="translation-radio-group"
                        >
                          <a-radio
                            v-for="opt in qualificationSummaryOptions"
                            :key="opt.id"
                            :value="opt.id"
                          >
                            {{ opt.name }}
                          </a-radio>
                        </a-radio-group>
                      </div>

                      <div
                        v-if="getTranslationByLang(lang.key)?.qualification_summary_id !== 4"
                        class="translation-field mt-4"
                      >
                        <span class="translation-field-label">Ingresa la traducción correcta</span>
                        <div class="editor-container">
                          <EditorQuillComponent
                            :model-value="
                              getTranslationByLang(lang.key)?.translation_summary_correct || ''
                            "
                            @update:model-value="
                              (val: string) => updateTranslationSummaryCorrect(lang.key, val)
                            "
                            placeholder=""
                            class="custom-editor"
                            :max-length="1000"
                          />
                        </div>
                      </div>
                    </div>
                  </a-card>

                  <!-- boton enviar feedback -->
                  <div class="button-actions">
                    <a-button size="large" type="primary" @click="sendFeedback">
                      Enviar feedback
                    </a-button>
                  </div>
                </div>
              </template>
            </a-tab-pane>
          </a-tabs>
        </a-card>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import WizardHeaderComponent from '@/modules/negotiations/products/configuration/components/WizardHeaderComponent.vue';
  import IconCircleInfo from '@/modules/negotiations/products/configuration/icons/IconCircleInfo.vue';
  import IconCalendarCheck from '@/modules/negotiations/products/configuration/icons/IconCalendarCheck.vue';
  import EditorQuillComponent from '@/modules/negotiations/products/configuration/components/EditorQuillComponent.vue';
  import { usePackageTranslationsMarketingComposable } from '../composables/usePackageTranslationsMarketingComposable';

  const showAiTranslationAlert = ref(true);

  const {
    formState,

    menuOriginal,
    summaryOriginal,
    totalFields,
    completedFields,
    isEditingContent,
    languagesOptions,
    activeLanguageTab,
    qualificationNameComercialOptions,
    qualificationItineraryOptions,
    qualificationMenuOptions,
    qualificationSummaryOptions,
    getTranslationByLang,
    updateQualificationNameComercial,
    updateQualificationItineraryDay,
    updateQualificationMenu,
    updateQualificationSummary,
    updateTranslationItineraryDayCorrect,
    updateTranslationMenuCorrect,
    updateTranslationSummaryCorrect,
    updateTranslationNameComercial,
    closeAiTranslationAlert,
    sendFeedback,
  } = usePackageTranslationsMarketingComposable();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .generic-translations-marketing-component {
    overflow-x: hidden;
    max-width: 100%;
  }

  .translations-card {
    width: 100%;
    max-width: 100%;
  }

  .button-actions {
    margin-bottom: 20px;
  }

  .ai-translation-alert-wrapper {
    margin-bottom: 16px;
  }

  .ai-translation-alert {
    background-color: #e9eaf7;
    border: 1px solid #c5c0e0;
    border-radius: 8px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    padding: 8px 16px;
    display: flex;
    align-items: center;

    :deep(.ant-alert-body) {
      display: flex;
      align-items: center;
      flex: 1;
      gap: 0;
    }

    :deep(.ant-alert-content) {
      display: flex;
      align-items: center;
      flex: 1;
    }

    :deep(.ant-alert-message) {
      color: #2f353a;
      font-size: 14px;
      line-height: 1.4;
      display: flex;
      align-items: center;
    }

    :deep(.ant-alert-close-icon) {
      color: #8c8c8c;
      font-size: 12px;

      &:hover {
        color: #595959;
      }
    }
  }

  .ai-translation-alert :deep(.ant-alert-icon) {
    margin-right: 12px;
    color: #5c5ab4;
    display: flex;
    align-items: center;
  }

  .ai-translation-alert :deep(.ant-alert-icon path),
  .ai-translation-alert :deep(.ant-alert-icon svg path) {
    fill: #5c5ab4 !important;
  }

  .card-translations {
    background-color: $color-white;
    border-color: #e7e7e7;
    border-radius: 8px;

    :deep(.ant-card-head) {
      border-bottom-color: #c5c5c5;
      padding: 12px 16px;
    }

    :deep(.ant-card-body) {
      padding: 0 20px 20px;
    }
  }

  .card-text-type-title {
    font-size: 14px;
    font-weight: 600;
    color: $color-black;
  }

  .translations-tabs {
    :deep(.ant-tabs-nav) {
      margin-bottom: 0;
      padding: 0;
      border-bottom: 1px solid #e7e7e7;
    }

    :deep(.ant-tabs-nav::before) {
      border-bottom: none;
    }

    :deep(.ant-tabs-tab) {
      padding: 12px 20px;
      color: #8c8c8c;
      font-weight: 400;
      font-size: 14px;
    }

    :deep(.ant-tabs-tab-active .ant-tabs-tab-btn) {
      color: #2f353a;
      font-weight: 600;
    }

    :deep(.ant-tabs-ink-bar) {
      background: #2f353a;
      height: 2px;
    }

    :deep(.ant-tabs-tab + .ant-tabs-tab) {
      margin-left: 0;
    }

    :deep(.ant-tabs-content) {
      padding-top: 24px;
    }
  }

  .translation-tab-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
    padding: 0px 20px;
  }

  .translation-field {
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .translation-field-header {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .translation-field-label {
    font-size: 14px;
    font-weight: 600;
    color: $color-black;
  }

  .translation-field-label-section {
    font-size: 14px;
    font-weight: 600;
    color: #2f353a;
  }

  .translation-field-info {
    display: inline-flex;
    cursor: help;
  }

  .translation-input-disabled :deep(.ant-input),
  .translation-input-disabled :deep(.ant-input[disabled]) {
    background-color: #f9f9f9 !important;
    color: #595959;
    cursor: not-allowed;
  }

  .translation-radio-group {
    display: flex;
    flex-direction: column;
    gap: 12px;

    :deep(.ant-radio-wrapper) {
      font-size: 14px;
      color: #2f353a;
    }

    :deep(.ant-radio-checked .ant-radio-inner) {
      border-color: #bd0d12;
      // background-color: #bd0d12;
    }

    :deep(.ant-radio-inner::after) {
      background-color: #fff;
    }
  }

  .card-itinerary-translation {
    background-color: $color-white;
    border-color: #e7e7e7;
    border-radius: 8px;
    box-shadow: none;

    :deep(.ant-card-head) {
      border-bottom-color: #c5c5c5;
      padding: 16px 20px !important;
    }

    :deep(.ant-card-body) {
      padding: 20px 24px 24px 25px !important;
    }
  }

  .card-itinerary-header {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .card-itinerary-content {
    display: flex;
    flex-direction: column;
    gap: 0;
  }

  .itinerary-original-text,
  .itinerary-translation-text {
    font-size: 14px;
    line-height: 1.6;
    color: #595959;
    background-color: #f9f9f9;
    padding: 12px 16px;
    border-radius: 4px;
    white-space: pre-line;
  }

  .itinerary-day-right .itinerary-original-text {
    background-color: #f9f9f9;
    border: 1px solid #e7e7e7;
    border-radius: 4px;
  }

  .mt-4 {
    margin-top: 16px;
  }

  .editor-container {
    :deep(.editor-quill-container) {
      margin-bottom: 0;
      border-radius: 4px;
    }

    :deep(.ql-toolbar.ql-snow) {
      border-radius: 4px 4px 0 0;
    }

    :deep(.ql-container.ql-snow) {
      border-radius: 0 0 4px 4px;
    }
  }

  .editor-container {
    :deep(.editor-quill-container) {
      padding-left: 0;
      padding-right: 0;
    }
  }

  .edit-content-mode-container {
    margin-bottom: 20px;
  }

  .edit-content-form {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .edit-content-section {
    border: 1px solid #e7e7e7;
    border-radius: 8px;
    padding: 20px;
    background-color: $color-white;
  }

  .edit-content-section-header {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 16px;
  }

  .edit-content-section-title {
    font-size: 14px;
    font-weight: 600;
    color: $color-black;
  }

  .edit-content-section-divider {
    width: calc(100% + 40px);
    height: 1px;
    background-color: #e7e7e7;
    margin: 16px -20px;
    display: block;
    box-sizing: border-box;
  }

  .edit-content-input,
  .edit-content-textarea {
    width: 100%;
    border-radius: 4px;
    border-color: #d9d9d9;

    &:hover {
      border-color: #40a9ff;
    }

    &:focus {
      border-color: #40a9ff;
      box-shadow: 0 0 0 2px rgba(24, 144, 255, 0.2);
    }
  }

  .edit-content-textarea {
    :deep(.ant-input) {
      resize: vertical;
    }
  }

  .summary-items-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }

  .summary-item {
    display: flex;
    gap: 12px;
    align-items: flex-start;
  }

  .summary-item-number {
    font-size: 14px;
    font-weight: 600;
    color: $color-black;
    min-width: 20px;
    padding-top: 4px;
  }

  .summary-item-content {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .summary-item-title {
    font-size: 14px;
    font-weight: 600;
    color: $color-black;
  }

  .summary-item-textarea {
    margin-top: 4px;
  }

  .itinerary-day-item {
    padding-bottom: 24px;
    border-bottom: 1px solid #e7e7e7;
    margin-bottom: 24px;

    &:last-child {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }
  }

  .itinerary-day-item-readonly {
    padding-bottom: 20px;
    margin-bottom: 20px;

    &:last-child {
      margin-bottom: 0;
      padding-bottom: 0;
    }
  }

  .itinerary-day-row {
    display: flex;
    gap: 16px;
    align-items: flex-start;
  }

  .itinerary-day-left {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    min-width: 120px;
    flex-shrink: 0;
    padding-top: 2px;
  }

  .itinerary-day-icon {
    width: 20px;
    height: 20px;
    color: #595959;
    flex-shrink: 0;
  }

  .itinerary-day-right {
    flex: 1;
    min-width: 0;
  }

  .itinerary-day-title {
    margin-bottom: 8px;
  }

  .day-title-text {
    font-size: 14px;
    font-weight: 600;
    color: $color-black;
    white-space: nowrap;
  }

  .mt-2 {
    margin-top: 8px;
  }
</style>
