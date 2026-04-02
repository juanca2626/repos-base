<script lang="ts" setup>
  import IconLeftArrowTab from '@/quotes/components/icons/IconLeftArrowTab.vue';
  import TabBodyComponent from '@/quotes/components/details/tabs/TabBodyComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import ModalRoomEditDetail from '@/quotes/components/modals/ModalRoomEditDetail.vue';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import { CloseOutlined } from '@ant-design/icons-vue';
  import { useI18n } from 'vue-i18n';
  import { ref } from 'vue';
  import LoadingMaca from '@/components/global/LoadingMaca.vue';

  const { t } = useI18n();

  const { quoteCategories, selectedCategory, updateQuoteCategory, processing } = useQuote();

  const changeTab = (tab: number) => {
    if (processing.value) return;
    selectedCategory.value = tab;
  };

  // Delete category modal state
  const deleteCategoryModal = ref({
    isOpen: false,
    categoryId: null as number | null,
    categoryName: '' as string,
  });

  const handleDeleteCategory = (event: Event, categoryTypeClassId: number) => {
    if (processing.value) return;

    const category = quoteCategories.value.find((c) => c.type_class_id === categoryTypeClassId);
    deleteCategoryModal.value = {
      isOpen: true,
      categoryId: categoryTypeClassId,
      categoryName: category?.type_class.translations[0].value || '',
    };
  };

  const closeDeleteModal = () => {
    deleteCategoryModal.value = {
      isOpen: false,
      categoryId: null,
      categoryName: '',
    };
  };

  const confirmDeleteCategory = async () => {
    if (!deleteCategoryModal.value.categoryId) return;
    const categoryId = deleteCategoryModal.value.categoryId;
    closeDeleteModal();
    if (quoteCategories.value.length > 1 && selectedCategory.value === categoryId) {
      selectedCategory.value =
        quoteCategories.value[quoteCategories.value.length - 2].type_class_id;
    }
    await updateQuoteCategory(categoryId, 'delete');
  };
</script>

<template>
  <a-alert type="warning" v-if="processing" class="mb-4">
    <template #description>
      <a-row type="flex" justify="start" align="middle" style="gap: 15px; flex-flow: row">
        <a-col>
          <LoadingMaca :muted="true" size="small" />
        </a-col>
        <a-col>
          <b class="text-dark-warning d-block">{{ t('quote.label.processing_quote') }}</b>
          <p class="mb-0 d-block">
            {{ t('quote.label.content_processing_quote') }}
          </p>
        </a-col>
      </a-row>
    </template>
  </a-alert>

  <div class="quotes-details">
    <div :class="{ disabled: processing }" class="quotes-tabs">
      <div class="left">
        <icon-left-arrow-tab />
      </div>
      <div class="center">
        <div
          v-for="quoteCategory of quoteCategories"
          :key="`category-tab-${quoteCategory.type_class_id}`"
          :class="{ active: selectedCategory === quoteCategory.type_class_id }"
          class="tab"
          @click="changeTab(quoteCategory.type_class_id)"
        >
          {{ quoteCategory.type_class.translations[0].value }}
          <span
            v-if="quoteCategories.length > 1"
            class="delete-btn"
            @click="handleDeleteCategory($event, quoteCategory.type_class_id)"
          >
            <CloseOutlined />
          </span>
        </div>
      </div>
    </div>
    <div class="quotes-tabs-body">
      <div
        v-for="quoteCategory of quoteCategories"
        :key="`category-tab-body-${quoteCategory.type_class_id}`"
      >
        <tab-body-component
          v-if="selectedCategory === quoteCategory.type_class_id"
          :items="quoteCategory.services"
          :category="quoteCategory.type_class_id"
        />
      </div>
    </div>
  </div>

  <modal-room-edit-detail />

  <ModalComponent :modalActive="deleteCategoryModal.isOpen" @close="closeDeleteModal">
    <template #body>
      <h3 class="title">{{ t('quote.label.confirm_delete_category') }}</h3>
      <div class="description">
        <p>{{ t('quote.label.delete_category_confirmation') }}</p>
        <div class="category-info" style="margin-top: 10px; font-weight: bold">
          <span>{{ t('quote.label.category') }}: </span>
          <span>{{ deleteCategoryModal.categoryName }}</span>
        </div>
      </div>
    </template>
    <template #footer>
      <div class="footer">
        <button :disabled="processing" class="cancel" @click="closeDeleteModal">
          {{ t('quote.label.cancel') }}
        </button>
        <button :disabled="processing" class="ok" @click="confirmDeleteCategory">
          {{ t('quote.label.yes_delete') }}
        </button>
      </div>
    </template>
  </ModalComponent>
</template>

<style lang="scss">
  .quotes-details {
    display: inline-flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0;
    width: 100%;
    margin-bottom: 24px;

    .quotes-tabs {
      display: flex;
      align-items: center;
      gap: 10px;
      align-self: stretch;

      .left {
        display: flex;
        padding: 4px;
        align-items: flex-start;
        gap: 10px;
        border-radius: 6px;
        background: #fafafa;

        svg {
          width: 20px;
          height: 20px;
        }
      }

      .center {
        display: flex;
        gap: 10px;
        justify-content: space-between;
        flex-direction: row;
        align-items: flex-end;

        .tab {
          display: flex;
          width: auto !important;
          padding: 4px 16px;
          flex-direction: row !important;
          justify-content: space-between !important;
          align-items: center;
          border-radius: 6px 6px 0 0;
          background: #e9e9e9;
          color: #979797;
          text-align: center;
          font-size: 14px;
          font-style: normal;
          font-weight: 600;
          line-height: 21px;
          letter-spacing: 0.21px;
          cursor: pointer;
          position: relative;
          gap: 8px; /* Space between text and icon */

          &.active {
            background: #737373;
            color: #fefefe;
            padding: 6px 16px;

            .delete-btn {
              color: rgba(255, 255, 255, 0.7);
              &:hover {
                color: #ff9494; /* Lighter red for dark background */
              }
            }
          }

          .delete-btn {
            font-size: 12px;
            display: flex;
            align-items: center;
            color: rgba(0, 0, 0, 0.45);
            transition: all 0.2s;
            border-radius: 50%;
            padding: 2px;

            &:hover {
              color: #ff4d4f;
              background-color: rgba(0, 0, 0, 0.05);
            }
          }
        }
      }
      &.disabled {
        opacity: 0.7;
        pointer-events: none;
        filter: grayscale(0.5);
      }
    }

    .quotes-tabs-body {
      width: 100%;
    }
  }
</style>
