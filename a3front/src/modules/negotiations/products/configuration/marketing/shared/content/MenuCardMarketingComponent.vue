<template>
  <div class="mt-4 menu-card-container">
    <a-card size="small" class="card-text-type card-menu">
      <template #title>
        <div class="card-title-with-help">
          <div v-if="isHelpOpen" class="help-inline-content card-help-in-title">
            <div class="help-overlay-title">Pautas para redactar menú</div>
            <ul class="help-overlay-list">
              <li>Aun no definido</li>
            </ul>
          </div>
          <div class="card-title-row">
            <div class="card-title-row-center">
              <span class="card-text-type-title mr-2">Menú</span>
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
              v-if="menu.status === 'PENDING'"
            >
              <span class="mr-2">Marcar como revisado</span>
              <IconCircleCheck />
            </a-button>

            <ReviewStatusBadge v-if="menu.status === 'REVIEWED'" status="reviewed" />
          </div>
        </div>
      </template>
      <div class="editor-container">
        <EditorQuillComponent
          placeholder=""
          class="custom-editor"
          :model-value="menu.text"
          @update:model-value="$emit('update:menuText', $event)"
        />
      </div>
    </a-card>
  </div>
</template>

<script setup lang="ts">
  import IconHelp from '@/modules/negotiations/products/configuration/icons/IconHelp.vue';
  import IconCircleCheck from '@/modules/negotiations/products/configuration/icons/IconCircleCheck.vue';
  import ReviewStatusBadge from '@/modules/negotiations/products/configuration/shared/components/ReviewStatusBadge.vue';
  import EditorQuillComponent from '@/modules/negotiations/products/configuration/components/EditorQuillComponent.vue';

  export interface MenuCardState {
    text: string;
    status: string;
  }

  defineProps<{
    menu: MenuCardState;
    isHelpOpen: boolean;
  }>();

  defineEmits<{
    (e: 'toggleHelp'): void;
    (e: 'markAsReviewed'): void;
    (e: 'update:menuText', value: string): void;
  }>();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .menu-card-container {
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

  .editor-container {
    min-height: 120px;
  }

  .editor-container :deep(.editor-quill-container) {
    padding-left: 0;
    padding-right: 0;
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

  .mr-2 {
    margin-right: 0.5rem;
  }
</style>
