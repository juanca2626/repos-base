<template>
  <span class="files-switch-categories" v-if="!isEditing">
    <div class="d-block">
      <a-tooltip>
        <template #title v-if="selectedCategories.length > 0">
          <template v-if="!isEditing">{{ t('files.label.add_category_to_file') }}</template>
          <template v-if="isEditing">{{ t('files.label.update_categories_of_file') }}</template>
        </template>
        <span :class="['text-gray', 'cursor-pointer']" @click="isEditing = !isEditing">
          <font-awesome-icon :icon="['fas', 'tags']" size="xl" />
        </span>
      </a-tooltip>
      <span
        @click="isEditing = !isEditing"
        class="files-switch-categories-label cursor-pointer"
        :class="{ 'opacity-50': !isEditable }"
        v-if="!selectedCategories.length > 0"
      >
        <template v-if="!isEditing">{{ t('files.label.add_category_to_file') }}</template>
        <template v-if="isEditing">{{ t('files.label.update_categories_of_file') }}</template>
      </span>
    </div>
    <div v-if="selectedCategories.length > 0" class="files-switch-categories-selected ms-1">
      <template v-for="(_category, c) in selectedCategories">
        <a-tag class="tag-category">
          {{ showCategory(_category) }}
          <span class="cursor-pointer" @click="removeCategory(c)">
            <i class="bi bi-x-lg"></i>
          </span>
        </a-tag>
      </template>
    </div>
  </span>

  <a-modal v-model:visible="isEditing" @cancel="close">
    <template #title>
      <div class="modal-title">
        <template v-if="!isEditing">{{ t('files.label.add_category_to_file') }}</template>
        <template v-if="isEditing">{{ t('files.label.update_categories_of_file') }}</template>
      </div>
    </template>

    <div id="files-layout">
      <div class="text-right text-dark-gray mb-2" style="font-size: 12px">Max. 3 items</div>

      <a-list size="small" bordered :data-source="filesStore.getFileCategories">
        <template #renderItem="{ item }">
          <a-list-item
            :class="[
              'cursor-pointer',
              selectedCategories.indexOf(item.value) > -1 ? 'text-600' : '',
            ]"
            @click="selectCategory(item.value)"
          >
            <span class="text-danger me-1" v-if="selectedCategories.indexOf(item.value) > -1">
              <font-awesome-icon :icon="['fas', 'square-check']" size="lg" />
            </span>
            <span class="text-dark-gray me-1" v-else>
              <font-awesome-icon :icon="['far', 'square']" size="lg" />
            </span>
            {{ item.label }}
          </a-list-item>
        </template>
      </a-list>
    </div>

    <template #footer>
      <div class="text-center">
        <a-button type="default" default @click="close" size="large">
          <span class="text-500">{{ t('global.button.cancel') }}</span>
        </a-button>
        <a-button type="primary" primary @click="save" size="large">
          <span class="text-500">{{ t('global.button.save') }}</span>
        </a-button>
      </div>
    </template>
  </a-modal>
</template>

<script setup>
  import { ref, onBeforeMount } from 'vue';
  import { debounce } from 'lodash-es';
  import { useFilesStore } from '@store/files';
  import { useI18n } from 'vue-i18n';

  const emit = defineEmits(['onChangeCategoriesFile']);

  const { t } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();

  const isEditing = ref(false);
  const selectedCategories = ref([]);

  const props = defineProps({
    isEditable: true,
    categories: [],
  });

  const close = () => {
    isEditing.value = false;
  };

  const removeCategory = (_category) => {
    selectedCategories.value.splice(_category, 1);
    save();
  };

  const selectCategory = (serieName) => {
    let index = selectedCategories.value.indexOf(serieName);

    if (index > -1) {
      selectedCategories.value.splice(index, 1);
    } else {
      if (selectedCategories.value.length > 2) {
        return false;
      }
      selectedCategories.value.push(serieName);
    }
  };

  const save = debounce(async () => {
    const params = selectedCategories.value;
    emit('onChangeCategoriesFile', params);

    setTimeout(() => {
      close();
    }, 10);
  }, 500);

  const showCategory = (_category) => {
    const category = filesStore.getFileCategories.find((category) => category.value === _category);

    return category.label || '';
  };

  onBeforeMount(async () => {
    if (!filesStore.getFileCategories.length) {
      await filesStore.fetchFileCategories();
    }
    selectedCategories.value = (props.categories || []).map((cat) => cat.category_id);
  });
</script>

<style scoped lang="scss">
  .files-switch-categories {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
    color: #5c5ab4;
    font-style: normal;
    font-weight: 600;
    font-size: 12px;
    line-height: 19px;
    letter-spacing: 0.015em;

    &-label {
      color: #575757;
      padding-left: 5px;
    }

    .ant-switch-handle {
      top: 2px !important;
    }
  }
  .escoger-series {
    position: relative;
    color: #eb5757;
    filter: drop-shadow(0px 4px 8px rgba(16, 24, 40, 0.25));
    z-index: 9999;

    &-select {
      position: absolute;
      top: 0;
      left: 0;
      z-index: 10;
      width: 100%;
      background-color: #fff;
    }

    &-header {
      display: flex;
      justify-content: space-between;
      width: 100%;
      height: 38px;
      background-color: #fff;
      padding: 8px 8px 8px 10px;
      align-items: center;
      font-weight: 700;
      font-size: 10px;
      line-height: 17px;
    }

    &-icon {
      width: 28px;
      height: 28px;
      background: #fff2f2;
      border-radius: 3px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
      margin-right: 5px;
    }

    &-list div {
      padding: 12px 16px 12px 16px;
      font-weight: 600;
      font-size: 12px;
      line-height: 19px;
      letter-spacing: 0.015em;
      color: #212529;
    }

    &-list div:hover,
    &-list div.active {
      background-color: #eb5757;
      color: #fff;
      cursor: pointer;
    }

    &-list {
      overflow: hidden;
      overflow-y: auto;
      max-height: 258px;
    }

    &-list::-webkit-scrollbar {
      width: 0.8em;
    }

    &-list::-webkit-scrollbar-track {
    }

    &-list::-webkit-scrollbar-thumb {
      background-color: #c4c4c4;
      border-radius: 8px;
    }
  }
  .files-switch-categories-selected {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    font-weight: 700;
    font-size: 10px;
    line-height: 17px;
    color: #5c5ab4;
    gap: 7px;
  }
  .opacity-50 {
    opacity: 0.5;
  }

  .tag-category {
    color: #5c5ab4 !important;
    border-color: #5c5ab4 !important;
  }
</style>
