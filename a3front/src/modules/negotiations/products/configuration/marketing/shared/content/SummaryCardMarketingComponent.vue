<template>
  <div class="mt-4 summary-card-container">
    <a-card size="small" class="card-text-type card-summary">
      <template #title>
        <div class="card-title-with-help">
          <div v-if="isHelpOpen" class="help-inline-content card-help-in-title">
            <div class="help-overlay-title">Pautas para redactar Summary</div>
            <ul class="help-overlay-list">
              <li>
                Según lo ingresado por Loading categorizar en el orden descrito en el detalle del
              </li>
            </ul>
          </div>
          <div class="card-title-row">
            <div class="card-title-row-center">
              <span class="card-text-type-title mr-2">Summary</span>
              <span
                class="icon-help-trigger"
                @click="$emit('toggleHelp')"
                role="button"
                tabindex="0"
                @keydown.enter="$emit('toggleHelp')"
              >
                <IconHelp />
              </span>
            </div>
            <a-button
              type="text"
              class="card-text-type-action"
              @click="$emit('markAsReviewed')"
              v-if="summary.status === 'PENDING'"
            >
              <span class="mr-2">Marcar como revisado</span>
              <IconCircleCheck />
            </a-button>

            <ReviewStatusBadge v-if="summary.status === 'REVIEWED'" status="reviewed" />
          </div>
        </div>
      </template>
      <div class="editor-container mt-2 mb-2">
        <div class="marketing-read-summary">
          <p class="summary-item-text" v-html="summary.textGeneral" />
        </div>
      </div>
      <div class="summary-content-layout">
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
              @change="$emit('toggleCategory', category.id)"
            />
          </div>
        </div>
        <div class="summary-right-panel">
          <div v-if="activeCategoryIds.length === 0" class="summary-empty-state">
            <IconFileText class="summary-empty-icon" />
            <p class="summary-empty-message">
              Activa las categorías del panel izquierdo para comenzar a redactar el contenido
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
                    @update:model-value="$emit('update:categoryContent', category.id, $event)"
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
      <div class="summary-preview-section mt-4" v-if="activeCategoriesWithContent.length > 0">
        <h4 class="summary-preview-title">Vista previa del Summary</h4>
        <div class="summary-preview-divider"></div>
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
    </a-card>
  </div>
</template>

<script setup lang="ts">
  import IconHelp from '@/modules/negotiations/products/configuration/icons/IconHelp.vue';
  import IconCircleCheck from '@/modules/negotiations/products/configuration/icons/IconCircleCheck.vue';
  import IconFileText from '@/modules/negotiations/products/configuration/icons/IconFileText.vue';
  import ReviewStatusBadge from '@/modules/negotiations/products/configuration/shared/components/ReviewStatusBadge.vue';
  import EditorQuillComponent from '@/modules/negotiations/products/configuration/components/EditorQuillComponent.vue';

  export interface SummaryState {
    textGeneral: string;
    status: string;
  }

  export interface CategorySummaryItem {
    id: number;
    text: string;
  }

  export interface ActiveCategoryWithContent extends CategorySummaryItem {
    content: string;
  }

  const props = defineProps<{
    summary: SummaryState;
    isHelpOpen: boolean;
    categoriesSummary: CategorySummaryItem[];
    activeCategoryIds: number[];
    activeCategoriesWithContent: ActiveCategoryWithContent[];
    categoryContents: Record<number, string>;
  }>();

  defineEmits<{
    (e: 'toggleHelp'): void;
    (e: 'markAsReviewed'): void;
    (e: 'toggleCategory', categoryId: number): void;
    (e: 'update:categoryContent', categoryId: number, value: string): void;
  }>();

  const isCategoryActive = (categoryId: number) => props.activeCategoryIds.includes(categoryId);

  const stripHtml = (html: string) => {
    if (!html) return '';
    return html.replace(/<[^>]*>/g, '').trim();
  };
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .summary-card-container {
    width: 100%;
    max-width: 100%;
    min-width: 0;
    overflow: hidden;
  }

  :deep(.card-text-type.ant-card) {
    box-shadow: none !important;
  }

  .card-text-type {
    background-color: white;
    border-color: #e7e7e7;
    border-radius: 8px;

    :deep(.ant-card-head-wrapper) {
      padding: 8px 12px;
    }

    :deep(.ant-card-body) {
      padding-left: 20px !important;
      padding-right: 19px !important;
      padding-bottom: 20px !important;
    }

    :deep(.ant-card-head) {
      border-bottom-color: #c5c5c5 !important;
      height: auto;
      min-height: auto;
      padding: 12px 16px;
      overflow: hidden;
      min-width: 0;
    }

    :deep(.ant-card-head-title) {
      width: 100% !important;
      max-width: 100% !important;
      min-width: 0 !important;
      padding: 0;
      overflow: hidden;
      flex: 1 1 auto;
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
      height: 32px;
      border: 1px solid $color-black;
      border-radius: 4px;
      background-color: $color-white;
      transition: all 0.2s ease;

      &:hover {
        color: inherit;
        border-color: inherit;
        background-color: $color-white;

        svg {
          color: inherit;
        }
      }

      svg {
        transition: all 0.2s ease;
      }
    }
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

  .help-inline-content {
    background: #212121;
    border-radius: 8px;
    padding: 24px 32px;
    width: 100%;
    max-width: 100%;
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

  .mr-2 {
    margin-right: 0.5rem;
  }

  .marketing-read-summary {
    border-radius: 4px;
    background-color: #f5f5f5;
    padding: 10px 12px;
  }

  .summary-item-text {
    font-size: 14px;
    color: #575b5f;
    margin: 0;
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

  .summary-editor-card {
    .editor-container {
      margin-top: 8px;
    }

    .editor-container :deep(.editor-quill-container) {
      margin-bottom: 0;
      border-radius: 4px;
      overflow: hidden;
    }

    .editor-container :deep(.ql-toolbar.ql-snow) {
      border-radius: 4px 4px 0 0;
    }

    .editor-container :deep(.ql-container.ql-snow) {
      border-radius: 0 0 4px 4px;
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
  }

  .editor-container :deep(.editor-quill-container) {
    padding-left: 0;
    padding-right: 0;
    margin-bottom: 0;
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

  .mt-4 {
    margin-top: 16px;
  }

  .mb-2 {
    margin-bottom: 8px;
  }
</style>
