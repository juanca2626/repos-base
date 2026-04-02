<template>
  <div class="multi-day-content-marketing-component mb-5">
    <WizardHeaderComponent
      title="Contenido"
      :completed="completedFields"
      :total="totalFields"
      v-if="isEditingContent"
    />

    <div class="read-mode-container" v-if="!isEditingContent">
      <h5 class="section-title mt-2">Contenido</h5>
      <!-- Card: Nombre comercial del servicio -->
      <div class="mt-4 marketing-read-card">
        <a-card size="small" class="card-text-type">
          <template #title>
            <div class="card-title-row">
              <span class="card-text-type-title mr-2">Nombre comercial del servicio</span>
              <a-button type="link" class="edit-button" @click="toggleEditName">
                {{ isEditingName ? 'Guardar' : 'Editar' }}
                <IconEdit v-if="!isEditingName" />
                <IconSave v-else />
              </a-button>
            </div>
          </template>
          <div class="marketing-read-sections">
            <div class="marketing-read-field">
              <a-input
                v-model:value="formState.nameComercial"
                placeholder="Nombre redactado por Marketing"
                class="marketing-read-input"
                :disabled="!isEditingName"
              />
            </div>
          </div>
        </a-card>
      </div>

      <!-- Card: Itinerario -->
      <div class="mt-4 marketing-read-card">
        <a-card size="small" class="card-text-type card-itinerary-read">
          <template #title>
            <div class="card-title-row">
              <span class="card-text-type-title mr-2">Itinerario</span>
              <a-button type="link" class="edit-button" @click="toggleEditItinerary">
                {{ isEditingItinerary ? 'Guardar' : 'Editar' }}
                <IconEdit v-if="!isEditingItinerary" />
                <IconSave v-else />
              </a-button>
            </div>
          </template>
          <div class="marketing-read-sections">
            <div class="marketing-read-field">
              <div v-if="!isEditingItinerary" class="marketing-read-itinerary">
                <template v-if="formState.itinerary.days?.length">
                  <div
                    v-for="row in formState.itinerary.days"
                    :key="row.dayNumber"
                    class="itinerary-day-block"
                  >
                    <div class="itinerary-day-header">
                      <IconCalendarCheck class="itinerary-day-icon" :height="20" :width="20" />
                      <span class="itinerary-day-title">Día {{ row.dayNumber }}</span>
                    </div>
                    <div class="itinerary-day-content" v-html="row.text || ''" />
                  </div>
                </template>
                <div v-else class="itinerary-day-content" v-html="formState.itinerary.text" />
              </div>
              <div v-else class="editor-container">
                <template v-for="row in formState.itinerary.days">
                  <div class="editor-wrapper">
                    <div class="editor-label">
                      <IconCalendarCheck :height="18" :width="18" />
                      <span class="editor-title"> Día {{ row.dayNumber }} </span>
                    </div>
                  </div>
                  <EditorQuillComponent
                    placeholder=""
                    class="custom-editor"
                    :model-value="row.text"
                    @update:model-value="(val: string) => (row.text = val)"
                  />
                </template>
              </div>
            </div>
          </div>
        </a-card>
      </div>

      <!-- Card: Menu -->
      <div class="mt-4 marketing-read-card">
        <a-card size="small" class="card-text-type card-itinerary-read">
          <template #title>
            <div class="card-title-row">
              <span class="card-text-type-title mr-2">Menu</span>
              <a-button type="link" class="edit-button" @click="toggleEditMenu">
                {{ isEditingMenu ? 'Guardar' : 'Editar' }}
                <IconEdit v-if="!isEditingMenu" />
                <IconSave v-else />
              </a-button>
            </div>
          </template>
          <div class="marketing-read-sections">
            <div class="marketing-read-field">
              <div
                v-if="!isEditingMenu"
                class="marketing-read-itinerary"
                v-html="formState.menu.text"
              />
              <div v-else class="editor-container">
                <EditorQuillComponent
                  placeholder=""
                  class="custom-editor"
                  :model-value="formState.menu.text"
                  @update:model-value="(val: string) => (formState.menu.text = val)"
                />
              </div>
            </div>
          </div>
        </a-card>
      </div>

      <!-- Card: Summary -->
      <div class="mt-4 marketing-read-card">
        <a-card size="small" class="card-text-type card-summary-read">
          <template #title>
            <div class="card-title-row">
              <span class="card-text-type-title mr-2">Summary</span>
              <a-button type="link" class="edit-button" @click="toggleEditSummary">
                {{ isEditingSummary ? 'Guardar' : 'Editar' }}
                <IconEdit v-if="!isEditingSummary" />
                <IconSave v-else />
              </a-button>
            </div>
          </template>
          <div class="marketing-read-sections">
            <div class="marketing-read-field">
              <!-- Vista previa del summary cuando no está en edición -->
              <div v-if="!isEditingSummary" class="summary-preview-section">
                <p class="summary-item-text mb-3" v-html="formState.summary.textGeneral" />
                <div class="summary-preview-list">
                  <div
                    v-for="(item, index) in activeCategoriesWithContent"
                    :key="item.id"
                    class="summary-preview-item"
                  >
                    <span class="summary-preview-item-number">{{ index + 1 }}.</span>
                    <div class="summary-preview-item-body">
                      <span class="summary-preview-item-title">{{ item.text }}</span>
                      <br v-if="!stripHtml(item.content)" />
                      <div
                        v-if="stripHtml(item.content)"
                        class="summary-preview-content"
                        v-html="item.content"
                      />
                      <span v-else class="summary-preview-placeholder">Sin contenido</span>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Edición por categorías cuando está en modo edición -->
              <template v-else>
                <div class="editor-container mt-2 mb-2">
                  <div class="marketing-read-summary">
                    <p class="summary-item-text" v-html="formState.summary.textGeneral" />
                  </div>
                </div>
                <div class="summary-content-layout mt-2">
                  <div class="summary-left-panel">
                    <div
                      v-for="(category, index) in categoriesSummary"
                      :key="category.id"
                      class="category-list-item"
                    >
                      <span class="category-number">{{ index + 1 }}.</span>
                      <span class="category-label">{{ category.text }}</span>
                      <a-switch
                        :checked="isCategoryActive(category.id)"
                        @change="toggleCategory(category.id)"
                      />
                    </div>
                  </div>
                  <div class="summary-right-panel">
                    <div
                      v-if="activeCategoriesWithContent.length === 0"
                      class="summary-empty-state"
                    >
                      <IconFileText class="summary-empty-icon" />
                      <p class="summary-empty-message">
                        Activa las categorías del panel izquierdo para comenzar a redactar el
                        contenido
                      </p>
                    </div>
                    <template v-else>
                      <div
                        v-for="category in activeCategoriesWithContent"
                        :key="category.id"
                        class="summary-editor-card mt-4"
                      >
                        <a-card size="small" class="card-category-editor">
                          <template #title>{{ category.text }}</template>
                          <div class="editor-container">
                            <EditorQuillComponent
                              :model-value="categoryContents[category.id] || ''"
                              @update:model-value="
                                (val: string) => updateCategoryContent(category.id, val)
                              "
                              placeholder=""
                              class="custom-editor"
                              :max-length="1000"
                            />
                          </div>
                        </a-card>
                      </div>
                    </template>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </a-card>
      </div>
    </div>

    <div class="edit-mode-container" v-if="isEditingContent">
      <a-form
        :model="formState"
        :rules="rules"
        ref="formRef"
        layout="vertical"
        class="compact-form"
      >
        <a-row :gutter="16">
          <a-col :span="24" :lg="10">
            <a-form-item name="translations">
              <template #label>
                <required-label class="form-label" label="Traducciones:" />
                <a-tooltip placement="topLeft" overlay-class-name="naming-guidelines-tooltip">
                  <template #title>
                    <div class="tooltip-content">
                      <div class="tooltip-title">Traducciones con IA</div>
                      <div class="tooltip-examples">
                        <div class="tooltip-example">
                          Una vez guardados los datos se traducirán todos los textos en los idiomas
                          seleccionados
                        </div>
                      </div>
                    </div>
                  </template>
                  <svg
                    width="16"
                    height="17"
                    viewBox="0 0 16 17"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    style="margin-left: 10px"
                  >
                    <g clip-path="url(#clip0_12682_16615)">
                      <path
                        d="M8 0.414062C3.58125 0.414062 0 3.99531 0 8.41406C0 12.8328 3.58125 16.4141 8 16.4141C12.4187 16.4141 16 12.8328 16 8.41406C16 3.99531 12.4187 0.414062 8 0.414062ZM8 4.41406C8.55219 4.41406 9 4.86188 9 5.41406C9 5.96625 8.55219 6.41406 8 6.41406C7.44781 6.41406 7 5.96719 7 5.41406C7 4.86094 7.44688 4.41406 8 4.41406ZM9.25 12.4141H6.75C6.3375 12.4141 6 12.0797 6 11.6641C6 11.2484 6.33594 10.9141 6.75 10.9141H7.25V8.91406H7C6.58594 8.91406 6.25 8.57812 6.25 8.16406C6.25 7.75 6.5875 7.41406 7 7.41406H8C8.41406 7.41406 8.75 7.75 8.75 8.16406V10.9141H9.25C9.66406 10.9141 10 11.25 10 11.6641C10 12.0781 9.66562 12.4141 9.25 12.4141Z"
                        fill="#2F353A"
                      />
                    </g>
                    <defs>
                      <clipPath id="clip0_12682_16615">
                        <rect
                          width="16"
                          height="16"
                          fill="white"
                          transform="translate(0 0.414062)"
                        />
                      </clipPath>
                    </defs>
                  </svg>
                </a-tooltip>
              </template>
              <a-select
                v-model:value="formState.translations"
                placeholder="Selecciona las traducciones"
                class="custom-select-no-shadow custom-select-translations"
                mode="multiple"
                :options="translationsOptions"
                :allowClear="true"
                :max-tag-count="4"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row>
          <a-col :span="24" :lg="10">
            <a-form-item name="nameService">
              <template #label>
                <span class="form-label form-label-name-service">Nombre del servicio:</span>
                <a-tooltip placement="topLeft" overlay-class-name="naming-guidelines-tooltip">
                  <template #title>
                    <div class="tooltip-content">
                      <div class="tooltip-examples">
                        <div class="tooltip-example">Texto ingresado por Loading</div>
                      </div>
                    </div>
                  </template>
                  <svg
                    width="16"
                    height="17"
                    viewBox="0 0 16 17"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                    style="margin-left: 10px"
                  >
                    <g clip-path="url(#clip0_12682_16615)">
                      <path
                        d="M8 0.414062C3.58125 0.414062 0 3.99531 0 8.41406C0 12.8328 3.58125 16.4141 8 16.4141C12.4187 16.4141 16 12.8328 16 8.41406C16 3.99531 12.4187 0.414062 8 0.414062ZM8 4.41406C8.55219 4.41406 9 4.86188 9 5.41406C9 5.96625 8.55219 6.41406 8 6.41406C7.44781 6.41406 7 5.96719 7 5.41406C7 4.86094 7.44688 4.41406 8 4.41406ZM9.25 12.4141H6.75C6.3375 12.4141 6 12.0797 6 11.6641C6 11.2484 6.33594 10.9141 6.75 10.9141H7.25V8.91406H7C6.58594 8.91406 6.25 8.57812 6.25 8.16406C6.25 7.75 6.5875 7.41406 7 7.41406H8C8.41406 7.41406 8.75 7.75 8.75 8.16406V10.9141H9.25C9.66406 10.9141 10 11.25 10 11.6641C10 12.0781 9.66562 12.4141 9.25 12.4141Z"
                        fill="#2F353A"
                      />
                    </g>
                    <defs>
                      <clipPath id="clip0_12682_16615">
                        <rect
                          width="16"
                          height="16"
                          fill="white"
                          transform="translate(0 0.414062)"
                        />
                      </clipPath>
                    </defs>
                  </svg>
                </a-tooltip>
              </template>
              <a-input
                v-model:value="formState.nameService"
                placeholder="Nombre del servicio"
                class="custom-input-no-shadow"
                :max-length="100"
                :disabled="true"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <a-row v-if="isHelpOverlayOpen" :gutter="16">
          <a-col :span="18">
            <a-form-item class="help-form-item">
              <div class="help-inline-content">
                <div class="help-overlay-title">Pautas para redactar nombre comercial</div>
                <ul class="help-overlay-list">
                  <li>Tipo de servicio + categoría + duración o detalle de la ubicación.</li>
                  <li>No se utiliza la palabra paquete</li>
                </ul>
                <div class="help-overlay-subtitle">Ejemplos:</div>
                <ol class="help-overlay-examples">
                  <li>Tour compartido a la carta de 5 días / 4 noches en Treehouse Lodge.</li>
                  <li>
                    Traslado privado desde la estación de bus de Chachapoyas al hotel en centro con
                    un representante.
                  </li>
                </ol>
              </div>
            </a-form-item>
          </a-col>
        </a-row>

        <a-row>
          <a-col :span="24" :lg="10">
            <a-form-item name="nameComercial">
              <template #label>
                <span class="label-inline">
                  <required-label class="form-label mr-2" label="Nombre comercial:" />
                  <span
                    class="icon-help-trigger"
                    @click="toggleHelpOverlay"
                    role="button"
                    tabindex="0"
                    @keydown.enter="toggleHelpOverlay"
                  >
                    <IconHelp />
                  </span>
                </span>
              </template>
              <a-input
                v-model:value="formState.nameComercial"
                placeholder="Nombre comercial"
                class="custom-input-no-shadow"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <div class="mt-4 itinerary-card-container">
          <a-card size="small" class="card-text-type card-itinerary">
            <template #title>
              <div class="card-title-with-help">
                <div v-if="isItineraryHelpOpen" class="help-inline-content card-help-in-title">
                  <div class="help-overlay-title">Pautas para redactar itinerario</div>
                  <ul class="help-overlay-list">
                    <li>
                      El Español debe ser latino y escrito en futuro simple, tercera persona,
                      directo y operativo.
                    </li>
                    <li>
                      La narrativa debe ser clara, informativa y operativa (no romántica ni poética)
                    </li>
                    <li>
                      El texto debe estar siempre centrado en la experiencia del pasajero, con tono
                      confiable y profesional.
                    </li>
                  </ul>
                  <div class="help-overlay-subtitle">Ejemplos:</div>
                  <div class="help-overlay-examples">
                    <div class="texto-itinerario">
                      Un transporte con un representante lo recogerá desde su hotel en Urubamba para
                      ser trasladado a la estación de Ollantaytambo. Luego, viajará una hora y media
                      en el tren Expedition con vistas a paisajes andinos espectaculares desde la
                      estación de Ollantaytambo hasta Aguas Calientes. Posteriormente, realizará un
                      recorrido en autobús de 25 minutos hasta Machu Picchu, una impresionante
                      hazaña de ingeniería y arquitectura que se cree que sirvió como santuario y
                      retiro para el inca Pachacútec. Designado como Patrimonio de la Humanidad por
                      la UNESCO y una de las Nuevas Siete Maravillas del Mundo moderno, Machu
                      Picchu, que significa "Montaña Vieja", cautiva con su esplendor histórico y su
                      majestuosidad natural.
                    </div>
                  </div>
                </div>
                <div class="card-title-row">
                  <div class="card-title-row-center">
                    <span class="card-text-type-title mr-2">Itinerario</span>
                    <span
                      class="icon-help-trigger"
                      @click="toggleItineraryHelpOverlay"
                      role="button"
                      tabindex="0"
                      @keydown.enter="toggleItineraryHelpOverlay"
                    >
                      <IconHelp />
                    </span>
                  </div>
                  <a-button
                    type="text"
                    class="card-text-type-action"
                    @click="markAsReviewedItinerary"
                    v-if="formState.itinerary.status === 'PENDING'"
                  >
                    <span class="mr-2">Marcar como revisado</span>
                    <IconCircleCheck />
                  </a-button>

                  <ReviewStatusBadge
                    v-if="formState.itinerary.status === 'REVIEWED'"
                    status="reviewed"
                  />
                </div>
              </div>
            </template>
            <template v-for="(row, dayIndex) in formState.itinerary.days">
              <div class="editor-container" :class="{ 'mt-5': Number(dayIndex) > 0 }">
                <div class="editor-wrapper">
                  <div class="editor-label">
                    <IconCalendarCheck :height="18" :width="18" />
                    <span class="editor-title"> Día {{ row.dayNumber }} </span>
                  </div>
                </div>
                <EditorQuillComponent
                  placeholder=""
                  class="custom-editor custom-editor-itinerary"
                  :model-value="row.text"
                />
              </div>
              <!-- <div class="editor-container">
              </div> -->
            </template>
          </a-card>
        </div>

        <MenuCardMarketingComponent
          :menu="formState.menu"
          :is-help-open="isMenuHelpOpen"
          @toggle-help="toggleMenuHelpOverlay"
          @mark-as-reviewed="markAsReviewedMenu"
          @update:menu-text="formState.menu.text = $event"
        />

        <SummaryCardMarketingComponent
          :summary="formState.summary"
          :is-help-open="isSummaryHelpOpen"
          :categories-summary="categoriesSummary"
          :active-category-ids="activeCategoryIds"
          :active-categories-with-content="activeCategoriesWithContent"
          :category-contents="categoryContents"
          @toggle-help="toggleSummaryHelpOverlay"
          @mark-as-reviewed="markAsReviewedSummary"
          @toggle-category="toggleCategory"
          @update:category-content="(id, val) => updateCategoryContent(id, val)"
        />

        <div class="service-details-actions mt-4">
          <a-button
            size="large"
            type="primary"
            @click="handleConfirmSaveModal"
            :disabled="completedFields < totalFields"
          >
            Guardar Datos
          </a-button>
        </div>
      </a-form>
    </div>

    <ModalConfirmComponent
      :title="titleModalConfirm"
      :text="textModalConfirm"
      :visible="showModalConfirm"
      :actionButtonText="actionButtonTextModalConfirm"
      @confirm="handleConfirm"
      @cancel="handleCancel"
    />
  </div>
</template>

<script setup lang="ts">
  import WizardHeaderComponent from '@/modules/negotiations/products/configuration/components/WizardHeaderComponent.vue';
  import RequiredLabel from '@/modules/negotiations/supplier-new/components/required-label.vue';
  import IconHelp from '@/modules/negotiations/products/configuration/icons/IconHelp.vue';
  import ReviewStatusBadge from '@/modules/negotiations/products/configuration/shared/components/ReviewStatusBadge.vue';
  import EditorQuillComponent from '@/modules/negotiations/products/configuration/components/EditorQuillComponent.vue';
  import IconCircleCheck from '@/modules/negotiations/products/configuration/icons/IconCircleCheck.vue';
  import IconEdit from '@/modules/negotiations/products/configuration/icons/IconEdit.vue';
  import IconSave from '@/modules/negotiations/products/configuration/icons/IconSave.vue';
  import IconFileText from '@/modules/negotiations/products/configuration/icons/IconFileText.vue';
  import IconCalendarCheck from '@/modules/negotiations/products/configuration/icons/IconCalendarCheck.vue';
  import ModalConfirmComponent from '@/modules/negotiations/products/configuration/components/ModalConfirmComponent.vue';
  import MenuCardMarketingComponent from '@/modules/negotiations/products/configuration/marketing/shared/content/MenuCardMarketingComponent.vue';
  import SummaryCardMarketingComponent from '@/modules/negotiations/products/configuration/marketing/shared/content/SummaryCardMarketingComponent.vue';

  import { usePackageContentMarketingComposable } from '../composables/usePackageContentMarketingComposable';

  const {
    formState,
    rules,
    totalFields,
    completedFields,
    isItineraryHelpOpen,
    isMenuHelpOpen,
    isSummaryHelpOpen,
    activeCategoryIds,
    categoryContents,

    isEditingContent,
    categoriesSummary,
    translationsOptions,
    isHelpOverlayOpen,
    activeCategoriesWithContent,
    titleModalConfirm,
    textModalConfirm,
    actionButtonTextModalConfirm,
    showModalConfirm,

    isEditingName,
    isEditingItinerary,
    isEditingSummary,
    isEditingMenu,

    toggleEditName,
    toggleEditItinerary,
    toggleEditSummary,
    toggleEditMenu,

    toggleHelpOverlay,
    toggleItineraryHelpOverlay,
    toggleMenuHelpOverlay,
    markAsReviewedMenu,
    markAsReviewedItinerary,
    toggleSummaryHelpOverlay,
    markAsReviewedSummary,
    stripHtml,
    isCategoryActive,
    toggleCategory,
    updateCategoryContent,
    handleConfirmSaveModal,
    handleConfirm,
    handleCancel,
  } = usePackageContentMarketingComposable();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .multi-day-content-marketing-component {
    overflow-x: hidden;
    max-width: 100%;
  }

  .marketing-read-sections {
    margin-top: 8px;
    padding-right: 16px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .marketing-read-field {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }

  .marketing-read-label {
    font-size: 14px;
    font-weight: 600;
    color: $color-black-3;
  }

  .marketing-read-input :deep(.ant-input[disabled]),
  .marketing-read-input :deep(.ant-input-disabled) {
    background-color: #f9f9f9 !important;
    color: #7e8285;
    cursor: not-allowed;
  }

  .marketing-read-itinerary {
    padding: 0;
    font-size: 14px;
    line-height: 1.6;
    color: #575b5f;
    max-height: none;
    overflow-y: visible;
    background-color: transparent;
  }

  .itinerary-day-block {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;

    &:last-child {
      margin-bottom: 0;
    }
  }

  .itinerary-day-header {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-shrink: 0;
    min-width: fit-content;
    padding-top: 5px;
  }

  .itinerary-day-icon {
    flex-shrink: 0;
    color: #2f353a;

    :deep(svg) {
      display: block;
    }
  }

  .itinerary-day-title {
    font-size: 14px;
    font-weight: 700;
    color: #2f353a;
    line-height: 1.4;
    white-space: nowrap;
  }

  .itinerary-day-content {
    flex: 1;
    font-size: 14px;
    font-weight: 400;
    color: #575b5f;
    line-height: 1.6;
    background-color: #f9f9f9;
    border-radius: 4px;
    padding: 10px 12px;
    min-width: 0;

    :deep(p) {
      margin: 0 0 8px 0;

      &:last-child {
        margin-bottom: 0;
      }
    }

    :deep(p:first-of-type) {
      margin-top: 0;
    }
  }

  .marketing-read-summary {
    border-radius: 4px;
    background-color: #f5f5f5;
    padding: 10px 12px;
  }

  .edit-button {
    display: flex;
    align-items: center;
    gap: 6px;
    color: $color-blue;
    font-size: 16px;
    line-height: 24px;
    font-weight: 500;
    padding: 4px 16px;

    &:hover {
      color: $color-blue;
    }
  }

  .summary-item + .summary-item {
    margin-top: 12px;
  }

  .summary-item-title {
    font-size: 14px;
    font-weight: 600;
    color: #2f353a;
    margin-bottom: 4px;
  }

  .summary-item-text {
    font-size: 14px;
    color: #575b5f;
    margin: 0;
  }

  .edit-mode-container {
    max-width: 100%;
    overflow-x: hidden;
  }

  .naming-guidelines-tooltip {
    max-width: 600px !important;

    .ant-tooltip-inner {
      padding: 16px;
      background-color: #212121;
      color: #babcbd;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
  }

  :deep(.card-text-type.ant-card) {
    box-shadow: none !important;
  }

  .card-text-type {
    background-color: white;
    border-color: #e7e7e7;
    border-radius: 8px;

    :deep(.ant-card-head-wrapper) {
      padding: 8px 12px 8px 12px;
    }

    :deep(.ant-card-body) {
      padding-left: 20px !important;
      padding-right: 19px !important;
      padding-bottom: 20px !important;
    }

    :deep(.ant-card-head) {
      border-bottom-color: #c5c5c5 !important;
      height: 66px;
    }

    &-title {
      font-size: 14px;
      font-weight: 600;
      color: $color-black;
    }

    &-action {
      display: flex;
      align-items: center;
      font-size: 14px;
      font-weight: 500;
      color: $color-black;
      // padding: 4px 12px;
      height: 32px;
      border: 1px solid $color-black;
      border-radius: 4px;
      background-color: $color-white;
      transition: all 0.2s ease;

      &:hover {
        color: none;
        border-color: none;
        background-color: $color-white;

        svg {
          color: none;
        }
      }

      svg {
        transition: all 0.2s ease;
      }
    }
  }

  .card-category-editor {
    background: #f9f9f9 !important;
    border-radius: 6px;
    border: none !important;

    :deep(.ant-card-head) {
      min-height: auto;
      height: auto;
      padding: 12px 16px 8px;
      border-bottom: none !important;
    }

    :deep(.ant-card-head-wrapper) {
      padding: 0;
    }

    :deep(.ant-card-body) {
      padding: 0 16px 16px !important;
    }

    :deep(.ant-card-head-title) {
      font-weight: 600;
      font-size: 14px;
      color: #575b5f;
      padding: 12px 0 0 16px;
    }

    .editor-container {
      margin-top: 8px;

      :deep(.editor-quill-container) {
        margin-bottom: 0;
        border-radius: 4px;
        overflow: hidden;
      }

      :deep(.ql-toolbar.ql-snow) {
        border-radius: 4px 4px 0 0;
      }

      :deep(.ql-container.ql-snow) {
        border-radius: 0 0 4px 4px;
      }
    }
  }

  .editor-container :deep(.editor-quill-container) {
    padding-left: 0;
    padding-right: 0;
    margin-bottom: 0;
  }

  .label-inline {
    display: inline-flex;
    align-items: center;
    gap: 1px;
  }

  .icon-help-trigger {
    display: inline-flex;
    align-items: center;
    cursor: pointer;
    padding: 2px;
    border-radius: 4px;
    transition: opacity 0.2s;
    margin: 0;

    &:hover {
      opacity: 0.7;
    }

    &:hover :deep(svg path) {
      stroke: #575b5f;
    }
  }

  .help-form-item {
    margin-bottom: 16px;

    :deep(.ant-form-item-control-input) {
      min-height: auto;
    }

    :deep(.ant-form-item-control-input-content) {
      line-height: 1;
    }
  }

  .help-inline-content {
    background: #212121;
    border-radius: 8px;
    padding: 24px 32px;
    width: 100%;
    max-width: 100%;
  }

  .itinerary-card-container,
  .menu-card-container,
  .summary-card-container {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow: hidden;
  }

  .card-itinerary.ant-card,
  .card-menu.ant-card,
  .card-summary.ant-card {
    width: 100% !important;
    max-width: 100% !important;
    overflow: hidden;
  }

  .card-itinerary :deep(.ant-card-head),
  .card-menu :deep(.ant-card-head),
  .card-summary :deep(.ant-card-head) {
    height: auto;
    min-height: auto;
    padding: 12px 16px;
    overflow: hidden;
    min-width: 0;
  }

  .card-itinerary :deep(.ant-card-head-wrapper) {
    flex-direction: column;
    align-items: stretch;
    width: 100%;
    min-width: 0;
    overflow: hidden;
  }

  .card-itinerary :deep(.ant-card-head-title),
  .card-menu :deep(.ant-card-head-title),
  .card-summary :deep(.ant-card-head-title) {
    width: 100% !important;
    max-width: 100% !important;
    min-width: 0 !important;
    padding: 0;
    overflow: hidden;
    flex: 1 1 auto;
  }

  .card-title-with-help {
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow: hidden;
  }

  .card-title-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
  }

  .card-title-row-center {
    display: flex;
    align-items: center;
    flex-shrink: 0;
  }

  .card-help-in-title {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    margin: 0;
    padding: 20px 16px;
    border-radius: 6px;
    box-sizing: border-box;
    overflow-wrap: break-word;
    word-wrap: break-word;
    word-break: break-word;
    overflow: hidden;
  }

  .help-overlay-example {
    color: #babcbd;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;

    p {
      margin: 0;
    }
  }

  .help-overlay-example p {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;
    word-wrap: break-word;
    overflow-wrap: break-word;
  }

  .help-overlay-title {
    color: #f9f9f9;
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 16px 0;
  }

  .help-overlay-list {
    color: #babcbd;
    font-size: 14px;
    line-height: 1.6;
    margin: 0 0 16px 0;
    padding-left: 20px;
    max-width: 100%;
    overflow-wrap: anywhere;
    word-break: break-word;

    li {
      overflow-wrap: anywhere;
      word-break: break-word;
    }
  }

  .help-overlay-subtitle {
    color: #f9f9f9;
    font-size: 14px;
    font-weight: 600;
    margin: 0 0 8px 0;
  }

  .help-overlay-examples {
    color: #babcbd;
    font-size: 14px;
    line-height: 1.6;
    margin: 0;
    text-align: justify;
  }

  .texto-itinerario {
    width: 100%;
    max-width: 100%;
    box-sizing: border-box;

    white-space: pre-line;
    line-height: 1.6;
  }

  .form-label-name-service {
    color: #7e8285;
  }

  .summary-textarea {
    :deep(textarea) {
      font-weight: 400;
      font-size: 16px;
    }
  }

  .tag-close-option {
    background-color: $color-gray-ligth-4;

    span {
      font-size: 14px;
      font-weight: 400;
      color: $color-black;
    }

    :deep(.ant-tag-close-icon) {
      color: $color-black-3 !important;
    }
  }

  :deep(.custom-select-translations.ant-select-multiple .ant-select-selection-item) {
    background: #dcdcdc !important;
    color: #2f353a !important;
    border-radius: 4px;
  }

  :deep(.custom-select-translations .ant-select-selection-item-remove) {
    border-radius: 2px;
    color: #7e8285 !important;
  }

  .mr-2 {
    margin-right: 0.5rem;
  }

  .summary-content-layout {
    display: flex;
    gap: 24px;
    min-height: 300px;
  }

  .summary-left-panel {
    flex: 0 0 290px;
    padding-right: 24px;
  }

  .category-list-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 0;

    &:last-child {
      border-bottom: none;
    }
  }

  .category-number {
    font-weight: 500;
    font-size: 14px;
    color: $color-black-3;
    min-width: 24px;
  }

  .category-label {
    font-weight: 500;
    flex: 1;
    font-size: 14px;
    color: $color-black-3;
  }

  .summary-left-panel :deep(.ant-switch-checked) {
    background-color: #c63838 !important;
  }

  .summary-right-panel {
    flex: 1;
    min-width: 0;
  }

  .summary-empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 280px;
    color: #7e8285;
    background-color: #f9f9f9;
    text-align: center;
    padding: 24px;
  }

  .summary-empty-icon {
    width: 64px;
    height: 64px;
    stroke: #d9d9d9;
    margin-bottom: 16px;
  }

  .summary-empty-message {
    font-size: 14px;
    margin: 0;
    max-width: 320px;
    line-height: 1.5;
  }

  .summary-preview-section {
    background-color: #f9f9f9;
    border-radius: 8px;
    padding: 12px 20px 16px 20px;
  }

  .summary-preview-title {
    font-size: 16px;
    font-weight: 600;
    color: $color-black;
    padding: 12px 20px 0 20px;
  }

  .summary-preview-divider {
    width: 100%;
    height: 0.5px;
    background-color: #c5c5c5;
    margin: 8px 0 12px 0;
  }

  .summary-preview-list {
    margin: 0;
    padding: 0;
  }

  .summary-preview-item {
    display: flex;
    gap: 8px;
    padding: 12px 0 0 0;
    font-size: 14px;
    line-height: 1.5;
  }

  .summary-preview-item-number {
    color: #575b5f;
    font-weight: 600;
    font-size: 14px;
    flex-shrink: 0;
  }

  .summary-preview-item-body {
    flex: 1;
    min-width: 0;
  }

  .summary-preview-item-title {
    color: #575b5f;
    font-weight: 600;
    font-size: 14px;
  }

  .summary-preview-content {
    padding-top: 8px;
    font-weight: 400;
    color: #595959;
    margin-left: 0;

    :deep(p) {
      margin: 0 0 4px 0;
    }
  }

  .summary-preview-placeholder {
    font-style: italic;
    color: #8c8c8c;
  }

  .summary-preview-empty {
    color: #8c8c8c;
    font-size: 14px;
    margin: 0;
  }

  .custom-editor-itinerary {
    :deep(.ql-toolbar) {
      padding-left: 80px;
      border-bottom: none;
    }
  }

  .editor-container {
    width: 100%;

    .editor-wrapper {
      position: relative;
    }

    .editor-label {
      position: absolute;
      top: 12px;
      left: 15px;
      display: flex;
      align-items: center;
      gap: 6px;
      z-index: 2;
    }

    .editor-title {
      font-size: 14px;
      font-weight: 600;
      color: $color-black;
    }
  }

  .custom-editor {
    :deep(.ql-toolbar) {
      padding-left: 80px;
      border-bottom: none;
    }

    :deep(.ql-container) {
      border-radius: 4px;
    }

    :deep(.ql-editor) {
      border-radius: 4px;
      border-top: 1px solid #ccc;
    }
  }
</style>
