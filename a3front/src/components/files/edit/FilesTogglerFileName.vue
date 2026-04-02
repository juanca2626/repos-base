<template>
  <div class="files-toggler-file-name">
    <files-edit-field-static v-if="!isEditing" :hideContent="false">
      <template #label>{{ t('files.label.file_name') }}</template>
      <template #content>
        <span
          style="max-width: 150px"
          v-bind:class="['files-toggler-file-name-content text-uppercase truncate']"
          >{{ file.description }}</span
        >
        <template v-if="editable">
          <span
            @click="toggleIsEditing"
            style="display: inline-flex; margin-left: 11px; cursor: pointer"
          >
            <font-awesome-icon :icon="['far', 'pen-to-square']" />
          </span>
        </template>
      </template>
      <template #popover-content>
        <span class="text-uppercase">{{ file.description }}</span>
      </template>
    </files-edit-field-static>
    <a-input
      v-else
      v-model:value="file.description"
      placeholder="Basic usage"
      size="large"
      @change="handleUpdate"
      style="height: 45px; width: 157px"
    >
      <template #suffix>
        <span
          @click="save"
          style="cursor: pointer; display: flex"
          class="files-toggler-file-name-save"
        >
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
    </a-input>
  </div>
</template>

<script setup>
  import { ref, watch } from 'vue';
  import FilesEditFieldStatic from '@/components/files/edit/FilesEditFieldStatic.vue';
  import { useInputsMontadosStore, useFilesStore } from '@store/files';
  import { createFilePassengerAdapter } from '../../../stores/files/adapters/files';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n({
    useScope: 'global',
  });

  const isEditing = ref(false);

  const inputsMontadosStore = useInputsMontadosStore();
  const filesStore = useFilesStore();
  const file = filesStore.getFile;
  const flagUpdate = ref(false);

  const toggleIsEditing = () => {
    isEditing.value = !isEditing.value;
    inputsMontadosStore.currentInput = 'file-name';
  };

  watch(
    () => inputsMontadosStore.currentInput,
    (value) => {
      if (value !== 'file-name') isEditing.value = false;
    }
  );

  defineProps({
    data: {
      type: String,
      default: () => '',
    },
    editable: {
      type: Boolean,
      default: () => true,
    },
  });

  const handleUpdate = () => {
    flagUpdate.value = true;
  };

  const save = async () => {
    if (flagUpdate.value) {
      const data = {
        id: file.id,
        description: file.description,
        dateIn: file.dateIn,
        passengers: filesStore.getFilePassengers.map((s) => createFilePassengerAdapter(s)),
        lang: file.lang,
      };

      await filesStore.update(data);
    }

    isEditing.value = !isEditing.value;
    inputsMontadosStore.currentInput = '';
  };
</script>

<style scoped lang="scss">
  .files-toggler-file-name {
    position: relative;
    &-content {
      display: inline-block;
      font-size: 16px;
      overflow: hidden;
      text-overflow: clip;
      white-space: nowrap;
      &.ellipsis {
        text-overflow: ellipsis !important;
      }
    }
    &-save:hover {
      color: #eb5757;
    }
  }
</style>
