<template>
  <div
    style="position: relative"
    :class="{
      'action-updated': props.item.isCreated || props.item.isUpdated,
      'action-padding': props.item.isCreated || props.item.isUpdated,
    }"
  >
    <a-badge-ribbon
      :color="'#EB5757'"
      class="cursor-pointer"
      style="position: absolute; z-index: 999; top: -20px; padding: 0 5px"
      v-if="props.item.isCreated || props.item.isUpdated"
      @click="toggleViewStatus()"
    >
      <template #text>
        <span>
          <a-tooltip>
            <template #title v-if="false">
              <small
                >{{
                  props.item.isCreated
                    ? 'Creado'
                    : props.item.isUpdated
                      ? 'Actualizado'
                      : 'Sin cambios'
                }}
                #{{ props.item.id }}</small
              >
            </template>
            <template v-if="item.isCreated">
              <font-awesome-icon :icon="['fas', 'bolt']" />
            </template>
            <template v-if="item.isUpdated">
              <font-awesome-icon :icon="['fas', 'bullseye']" fade />
            </template>
          </a-tooltip>
        </span>
      </template>
    </a-badge-ribbon>
    <div style="display: flex; flex-direction: row; justify-content: space-between">
      <div>
        <div class="info-row">
          <label class="info-label">{{ t('global.label.classification') }}:</label>
          <div class="classification-list">
            <a-tag class="classification-tag">{{ props.item.classification_name }}</a-tag>
          </div>
        </div>

        <div class="info-row">
          <label class="info-label">{{ t('global.label.description') }}:</label>
          <p class="description-label">{{ props.item.description }}</p>
        </div>
      </div>
      <div>
        <a-popover v-model:open="visible" trigger="click">
          <template #content>
            <div class="content-click">
              <a-button
                type="link"
                style="height: auto; padding: 5px 7px; font-size: 16px"
                size="small"
                @click="handleEdit"
              >
                <div
                  style="display: flex; gap: 5px; justify-content: flex-start; align-items: center"
                >
                  <font-awesome-icon icon="fa-solid fa-edit" />
                  <span style="font-weight: 500">{{ t('global.label.edit') }}</span>
                </div> </a-button
              ><br />
              <a-button
                type="link"
                style="height: auto; padding: 5px 7px; font-size: 16px"
                size="small"
                @click="deleteExternalHousing"
              >
                <div
                  style="display: flex; gap: 5px; justify-content: flex-start; align-items: center"
                >
                  <font-awesome-icon :icon="['fas', 'trash-can']" />
                  <span style="font-weight: 500">{{ t('global.button.delete') }}</span>
                </div>
              </a-button>
            </div>
          </template>
          <a-button style="border: none; background-color: inherit; box-shadow: none">
            <font-awesome-icon :icon="['fas', 'ellipsis-vertical']" class="action-icon" />
          </a-button>
        </a-popover>
      </div>
    </div>
  </div>

  <a-modal
    :z-index="1031"
    v-model:visible="openModalDelete"
    :maskClosable="false"
    class="text-center"
    @mousedown.stop
    @click.stop
  >
    <h3>{{ t('global.message.delete_note') }}</h3>
    <p>
      <a-alert
        :description="t('global.message.notification_ope')"
        type="error"
        show-icon
        class="custom-alert"
        v-if="(props.revisionStages || 0) == 2"
      >
        <template #icon>
          <svg
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <g clip-path="url(#clip0_25805_10854)">
              <path
                d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                stroke="#FF3B3B"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M12 8V12"
                stroke="#FF3B3B"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M12 16H12.01"
                stroke="#FF3B3B"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </g>
            <defs>
              <clipPath id="clip0_25805_10854">
                <rect width="24" height="24" fill="white" />
              </clipPath>
            </defs>
          </svg>
        </template>
      </a-alert>
    </p>
    <h5>{{ t('global.message.question_delete_note') }}</h5>
    <template #footer>
      <div class="text-center">
        <a-button
          type="default"
          class="bnt-default"
          default
          @click="cancelModalNote"
          size="large"
          :disabled="serviceNotesStore.isloadingSaveOrUpdateNote"
        >
          {{ t('global.button.cancel') }}
        </a-button>
        <a-button
          type="primary"
          primary
          @click="removeAdditionalInformation"
          size="large"
          :disabled="serviceNotesStore.isloadingSaveOrUpdateNote"
        >
          {{ t('global.button.delete') }}
        </a-button>
      </div>
    </template>
  </a-modal>

  <ModalNotes :open-modal="modalNotesOpen" @close-modal="handleCloseModal" :id="id" type="FILE" />
</template>
<script setup>
  import { ref, computed } from 'vue';
  import { useServiceNotesStore } from '@/stores/files';
  import { useSocketsStore } from '@/stores/global';
  import ModalNotes from '@/components/files/notes/ModalNotes.vue';
  import { getUserId, getUserName, getUserCode } from '@/utils/auth';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const messages = computed(() => {
    return {
      removed_note: t('files.message.removed_note'),
      removed_successfully: t('files.message.removed_successfully'),
    };
  });
  const serviceNotesStore = useServiceNotesStore();
  const socketsStore = useSocketsStore();
  const id = ref(0);

  const props = defineProps({
    item: {
      required: true,
      type: Array,
      default: [],
    },
    fileId: {
      required: true,
    },
    fileNumber: {
      required: true,
    },
    revisionStages: {
      required: true,
    },
  });

  const openModalDelete = ref(false);
  const modalNotesOpen = ref(false);

  const visible = ref(false);

  const hide = () => {
    visible.value = false;
  };

  const deleteExternalHousing = () => {
    hide();
    openModalDelete.value = true;
  };

  const cancelModalNote = () => {
    openModalDelete.value = false;
  };

  const removeAdditionalInformation = async () => {
    const response = await serviceNotesStore.deleteNote({
      file_id: props.fileId,
      id: props.item.id,
      data: {
        type_note: 'mask',
        created_by: getUserId(),
        created_by_code: getUserCode(),
        created_by_name: getUserName(),
      },
    });

    const type = 'delete_note';
    const message = messages.value.removed_note;
    const description = messages.value.removed_successfully;

    sendSocketMessage(response.success, type, message, description, {
      id: props.item.id,
      type_note: props.item.type_note,
      record_type: props.item.record_type,
    });
    // await serviceNotesStore.fetchAllFileNotes({ file_id: props.fileId });
  };

  const sendSocketMessage = (success, type, message, description, data = {}) => {
    socketsStore.send({
      success,
      type: type,
      file_number: props.fileNumber,
      file_id: props.fileId,
      user_code: getUserCode(),
      user_id: getUserId(),
      message: message,
      description: description,
      id: data?.id || 0,
      type_note: data?.type_note || '',
      record_type: data?.record_type || '',
    });
  };
  // Función para emitir el evento de edición
  const handleEdit = () => {
    // props.item = { ...props.item };

    id.value = props.item.id;
    console.log('ID: ', id.value);
    openModalNotes();
    hide();
  };

  const openModalNotes = () => {
    modalNotesOpen.value = true;
  };

  const handleCloseModal = () => {
    modalNotesOpen.value = false;
  };

  const toggleViewStatus = () => {
    props.item.isCreated = false;
    props.item.isUpdated = false;
    serviceNotesStore.changeFlagChange();
  };
</script>
<style scoped>
  .info-row {
    display: flex;
    gap: 10px;
    margin-top: 8px;
  }

  .info-label {
    font-weight: 600;
    min-width: 90px;
    color: #575757;
    font-size: 12px;
  }

  .info-value {
    color: #979797;
    font-weight: 500;
    font-size: 12px;
  }

  .info-value-blue {
    color: #55a3ff;
    font-weight: 500;
    font-size: 12px;
  }

  .classification-tag {
    font-weight: 400;
    size: 12px;
    color: #fafafa;
    background: #4ba3b2 !important;
    border-color: rgba(255, 255, 255, 0.3);
  }
  .description-label {
    color: #979797;
    font-size: 12px;
    font-weight: 500;
  }

  .action-icon {
    cursor: pointer;
    color: #979797;
    opacity: 0.8;
    transition: opacity 0.2s;
    font-size: 12px;
  }

  .action-icon:hover {
    opacity: 1;
  }

  .actions-content-click-content {
    display: flex;
    flex-direction: column;
  }

  .content-click {
    overflow-y: auto;
    overflow-x: hidden;
  }

  .action-updated {
    position: relative;
    border: 2px solid #eb5757;
    border-radius: 6px;
  }

  .action-padding {
    padding: 10px 0 10px 15px;
  }
</style>
