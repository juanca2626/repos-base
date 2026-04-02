<script lang="ts" setup>
  import { ref, watch, computed } from 'vue';
  import { useI18n } from 'vue-i18n';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';

  const props = defineProps({
    visible: Boolean,
    existingCategories: {
      type: Array as () => any[],
      default: () => [],
    },
    availableCategoryLabels: {
      type: Array as () => any[],
      default: () => [],
    },
  });

  const emit = defineEmits(['update:visible', 'confirm', 'cancel']);

  const { t } = useI18n();

  const selectedCategoriesToAdd = ref<number[]>([]);
  const mode = ref('copy'); // 'programacion' or 'copy'
  const selectedSourceCategory = ref<string | number | undefined>(undefined);

  // Filter out categories that are already in the quote
  const availableToAdd = computed(() => {
    const existingIds = props.existingCategories.map((c) => c.type_class_id.toString());
    return props.availableCategoryLabels.filter((c) => !existingIds.includes(c.value.toString()));
  });

  const handleOk = () => {
    if (selectedCategoriesToAdd.value.length === 0) return;

    emit('confirm', {
      categoryIds: selectedCategoriesToAdd.value,
      mode: mode.value,
      sourceId: selectedSourceCategory.value,
    });
    emit('update:visible', false);
  };

  const handleCancel = () => {
    emit('cancel');
    emit('update:visible', false);
  };

  // Helper to get label for a category ID
  const getLabel = (id: number) => {
    const found = props.availableCategoryLabels.find((c) => c.value == id.toString());
    return found ? found.label : `Category ${id}`;
  };

  // Initialize selectedSourceCategory and reset selection
  watch(
    () => props.visible,
    (val) => {
      if (val) {
        selectedCategoriesToAdd.value = [];
        if (props.existingCategories.length > 0) {
          selectedSourceCategory.value = props.existingCategories[0].type_class_id;
          mode.value = 'copy';
        } else {
          mode.value = 'programacion';
        }
      }
    }
  );
</script>

<template>
  <ModalComponent :modalActive="visible" @close="handleCancel" class="modal-add-category">
    <template #body>
      <h2 class="title">
        {{ t('quote.label.add_category_modal_title') }}
      </h2>

      <div class="description">
        <!-- Category Selection Section -->
        <div class="section">
          <div class="section-header">
            <font-awesome-icon :icon="['fas', 'folder-plus']" class="section-icon" />
            <label class="section-label">
              1. {{ t('quote.label.select_categories_to_add') }}
            </label>
          </div>
          <a-select
            v-model:value="selectedCategoriesToAdd"
            mode="multiple"
            :placeholder="t('quote.label.select_categories_placeholder')"
            style="width: 100%"
            class="category-select"
            :maxTagCount="'responsive'"
          >
            <a-select-option
              v-for="cat in availableToAdd"
              :key="cat.value"
              :value="parseInt(cat.value)"
            >
              {{ cat.label }}
            </a-select-option>
          </a-select>
          <!-- div v-if="selectedCategoriesToAdd.length > 0" class="selection-info">
            <font-awesome-icon :icon="['fas', 'check-circle']" class="info-icon" />
            <span
              >{{ selectedCategoriesToAdd.length }} {{ t('quote.label.categories_selected') }}</span
            >
          </div -->
        </div>

        <!-- Data Source Selection Section -->
        <div class="section">
          <div class="section-header">
            <font-awesome-icon :icon="['fas', 'database']" class="section-icon" />
            <label class="section-label"> 2. {{ t('quote.label.how_to_add_data') }} </label>
          </div>
          <a-radio-group v-model:value="mode" style="width: 100%" class="radio-group">
            <!-- Option 1: Copy from existing -->
            <div class="radio-option" v-if="existingCategories.length > 0">
              <a-radio value="copy" class="m-0">
                <div class="radio-content">
                  <font-awesome-icon :icon="['fas', 'copy']" class="radio-icon" />
                  <span>{{ t('quote.label.copy_from_existing') }}</span>
                </div>
              </a-radio>
              <div
                v-if="mode === 'copy' && existingCategories.length > 0"
                class="nested-select p-0"
              >
                <a-select v-model:value="selectedSourceCategory" style="width: 100%">
                  <a-select-option
                    v-for="cat in existingCategories"
                    :key="cat.id"
                    :value="cat.type_class_id"
                  >
                    {{ getLabel(cat.type_class_id) }}
                  </a-select-option>
                </a-select>
              </div>
            </div>

            <!-- Option 2: From Programming -->
            <div class="radio-option">
              <a-radio value="programacion" class="m-0">
                <div class="radio-content">
                  <font-awesome-icon :icon="['fas', 'calendar-days']" class="radio-icon" />
                  <span>{{ t('quote.label.bring_from_programming') }}</span>
                </div>
              </a-radio>
            </div>
          </a-radio-group>
        </div>
      </div>
    </template>

    <template #footer>
      <div class="footer">
        <button :disabled="false" class="cancel" @click="handleCancel">
          {{ t('quote.label.cancel') }}
        </button>
        <button :disabled="selectedCategoriesToAdd.length === 0" class="ok" @click="handleOk">
          {{ t('quote.label.confirm') }}
        </button>
      </div>
    </template>
  </ModalComponent>
</template>

<style lang="scss" scoped>
  .modal-add-category {
    :deep(.modal-inner) {
      max-width: 500px;
    }

    .title {
      font-size: 24px !important;
      font-weight: 700;
      color: #262626;
      margin-bottom: 20px;
      margin-top: 10px;
      text-align: center;
    }

    .description {
      display: flex;
      flex-direction: column;
      gap: 24px;
      margin: 0 !important;

      .section {
        display: flex;
        flex-direction: column;
        gap: 12px;

        .section-header {
          display: flex;
          align-items: center;
          gap: 10px;

          .section-icon {
            color: #c00d0e;
            font-size: 16px;
          }

          .section-label {
            font-size: 15px;
            font-weight: 600;
            color: #595959;
          }
        }

        .selection-info {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 8px 12px;
          background: #f0f9ff;
          border: 1px solid #bae7ff;
          border-radius: 6px;
          font-size: 13px;
          color: #0958d9;
          animation: slideIn 0.3s ease;

          .info-icon {
            color: #52c41a;
            font-size: 14px;
          }
        }

        @keyframes slideIn {
          from {
            opacity: 0;
            transform: translateY(-5px);
          }
          to {
            opacity: 1;
            transform: translateY(0);
          }
        }

        .category-select {
          :deep(.ant-select-selector) {
            border-radius: 6px;
            border-color: #ededff;
            transition: all 0.3s ease;
            padding: 5px;

            &:hover {
              border-color: #ededff;
            }
          }

          :deep(.ant-select-focused .ant-select-selector) {
            border-color: #ededff !important;
          }

          :deep(.ant-select-selection-item) {
            // background: linear-gradient(135deg, #c00d0e 0%, #8b0a0b 100%);
            background-color: #ededff;
            border-color: #ededff;
            color: #4643aa;
            border-radius: 4px;
            font-weight: 500;
          }

          :deep(.ant-select-selection-item-remove) {
            color: #4643aa;

            &:hover {
              color: #4643aa;
            }
          }
        }

        .radio-group {
          display: flex;
          flex-direction: column;
          gap: 12px;

          .radio-option {
            display: flex;
            flex-direction: column;
            gap: 8px;

            .radio-content {
              display: flex;
              align-items: center;
              gap: 8px;

              .radio-icon {
                color: #8c8c8c;
                font-size: 14px;
                transition: color 0.3s ease;
              }
            }
          }
        }

        .nested-select {
          padding-left: 32px;
          margin-top: 4px;
          animation: slideDown 0.3s ease;

          :deep(.ant-select-selector) {
            border-radius: 6px;
            border-color: #d9d9d9;
            transition: all 0.3s ease;

            &:hover {
              border-color: #c00d0e;
            }
          }

          :deep(.ant-select-focused .ant-select-selector) {
            // border-color: #c00d0e !important;
            box-shadow: 0 0 0 2px rgba(192, 13, 14, 0.1);
          }
        }

        @keyframes slideDown {
          from {
            opacity: 0;
            max-height: 0;
          }
          to {
            opacity: 1;
            max-height: 100px;
          }
        }

        :deep(.ant-radio-wrapper) {
          font-size: 14px;
          color: #9e9e9e;
          border-radius: 6px;
          transition: all 0.3s ease;

          &:hover {
            // background: #f5f5f5;
          }

          .ant-radio-checked .ant-radio-inner {
            // border-color: #c00d0e;
            // background-color: #c00d0e;
          }

          &:hover .ant-radio-inner {
            border-color: #c00d0e;
          }

          &.ant-radio-wrapper-checked {
            // background: #fff1f0;

            .radio-icon {
              color: #c00d0e;
            }
          }
        }
      }
    }
  }
</style>
