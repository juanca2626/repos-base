<template>
  <div
    :class="['files-toggler-language', isEditing ? 'pt-2' : '']"
    v-if="!filesStore.isLoading && !languagesStore.isLoading"
  >
    <files-edit-field-static v-if="!isEditing" hideContent>
      <template #label>{{ t('global.label.language') }}</template>
      <template #content>
        <span v-if="filesStore.getFile?.lang">{{
          languagesStore.getLanguages.find((item) => item.value === filesStore.getFile?.lang)?.label
        }}</span>
        <template v-if="editable">
          <span
            @click="toggleIsEditing(isEditable)"
            style="margin-left: 11px; line-height: 21px; vertical-align: middle; cursor: pointer"
          >
            <font-awesome-icon :icon="['far', 'pen-to-square']" />
          </span>
        </template>
      </template>
    </files-edit-field-static>
    <a-select
      v-else
      style="min-height: 45px; width: 157px"
      class="language-select"
      name="languageValue"
      placeholder="Lenguajes"
      :filter-option="false"
      size="large"
      width="210"
      label-in-value
      :showSearch="true"
      :allowClear="false"
      :options="languagesStore.getAllLanguages"
      v-model:value="filesStore.getFile.lang"
      @change="handleChange"
    >
      <template #suffixIcon>
        <span class="save-btn" @click="save">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="24"
            height="24"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            class="feather feather-save"
          >
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z" />
            <path d="M17 21v-8H7v8M7 3v5h8" />
          </svg>
        </span>
      </template>
    </a-select>
  </div>
</template>

<script setup>
  import { ref, watch } from 'vue';
  import FilesEditFieldStatic from '@/components/files/edit/FilesEditFieldStatic.vue';
  import { useFilesStore, useInputsMontadosStore } from '@store/files';
  import { useLanguagesStore } from '@store/global';
  //import { debounce } from 'lodash-es';
  import { createFilePassengerAdapter } from '@/stores/files/adapters/files';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n({
    useScope: 'global',
  });

  const languagesStore = useLanguagesStore();
  const inputsMontadosStore = useInputsMontadosStore();
  const filesStore = useFilesStore();
  const flagUpdate = ref(false);

  const isEditing = ref(false);

  const toggleIsEditing = (isEditable) => {
    if (!isEditable) {
      return;
    }
    isEditing.value = !isEditing.value;
    inputsMontadosStore.currentInput = 'language';
  };

  defineProps({
    data: {
      type: String,
      default: () => '',
    },
    editable: {
      type: Boolean,
      default: () => true,
    },
    isEditable: true,
  });

  const save = async () => {
    isEditing.value = !isEditing.value;
    inputsMontadosStore.currentInput = '';

    if (flagUpdate.value) {
      const data = {
        id: filesStore.getFile.id,
        description: filesStore.getFile.description,
        dateIn: filesStore.getFile.dateIn,
        passengers: filesStore.getFilePassengers.map((s) => createFilePassengerAdapter(s)),
        lang: filesStore.getFile.lang,
      };

      await filesStore.update(data);
    }
  };

  const handleChange = (value) => {
    filesStore.getFile.lang = value.value.toUpperCase();
    flagUpdate.value = true;
  };

  watch(
    () => inputsMontadosStore.currentInput,
    (value) => {
      if (value !== 'language') isEditing.value = false;
    }
  );
</script>
