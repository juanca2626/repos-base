<template>
  <div
    :class="{
      'action-updated': item.isCreated || item.isUpdated,
      'action-padding': item.isCreated || item.isUpdated,
    }"
  >
    <a-badge-ribbon
      :color="'#EB5757'"
      class="cursor-pointer"
      style="position: absolute; z-index: 999; top: -20px; padding: 0 5px"
      v-if="item.isCreated || item.isUpdated"
      @click="toggleViewStatus()"
    >
      <template #text>
        <span>
          <a-tooltip>
            <template #title v-if="false">
              <small
                >{{
                  item.isCreated ? 'Creado' : item.isUpdated ? 'Actualizado' : 'Sin cambios'
                }}
                #{{ item.id }}</small
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
    <div class="info-row">
      <div>
        <a-row type="flex" align="middle" justify="start" style="gap: 7px; flex-flow: nowrap">
          <a-col>
            <font-awesome-icon :icon="['fas', 'calendar-day']" />
          </a-col>
          <a-col> {{ t('global.label.day') }} {{ checkDates(dateIn, date) }} </a-col>
          <a-col>
            <a-tag class="day-tag">{{ formatDate(date) }}</a-tag>
          </a-col>
        </a-row>
      </div>
      <div>
        <div style="display: flex; gap: 8px" class="pt-2">
          <div class="info-label">{{ t('global.label.classification') }}:</div>
          <a-tag class="classification-tag">{{ item.classification_name }}</a-tag>
        </div>
        <!-- <div style="display: flex; gap: 8px" class="pt-2">
          <div class="info-label">Tipo de Nota:</div>
          <a-tag class="classification-tag">{{ item.classification_name }}</a-tag>
        </div> -->
        <div style="display: flex; gap: 8px" class="pt-2" v-if="item.assignment_mode === 'ALL_DAY'">
          <div class="info-label">{{ t('global.label.services') }}:</div>
          <div class="description-label">Todos los servicios de este día</div>
        </div>
        <div style="display: flex; gap: 8px" class="pt-2" v-else>
          <div class="info-label">{{ t('global.label.services') }}:</div>
          <div style="display: grid; grid-template-columns: repeat(2, 300px); gap: 10px">
            <div class="tag-service" v-for="service in item.services" :key="service.service_id">
              <font-awesome-icon :icon="['fas', 'file-import']" class="icon" />
              <a-tooltip>
                <template #title v-if="service.service_name.length > 40">
                  <small class="text-uppercase">{{ service.service_name }}</small>
                </template>
                <span class="text">{{ truncateText(service.service_name, 40) }}</span>
              </a-tooltip>
            </div>
          </div>
        </div>

        <div style="display: flex; gap: 8px" class="pt-2">
          <div class="info-label">{{ t('global.label.description') }}:</div>
          <div class="description-label">{{ item.description }}</div>
        </div>
      </div>
      <div class="ultimo">
        <div>
          <a-tag :color="item.type_note === 'REQUIREMENT' ? 'warning' : 'blue'">{{
            item.type_note === 'REQUIREMENT'
              ? t('global.label.requirement')
              : t('global.label.informative')
          }}</a-tag>
          <a-popover v-model:open="visible" trigger="click" v-if="isDateBeforeToday(props.date)">
            <template #content>
              <div class="content-click">
                <a-button
                  type="link"
                  style="height: auto; padding: 5px 7px; font-size: 16px"
                  size="small"
                  @click="handleEditRequirement(props.item.id, props.item.entity)"
                >
                  <div
                    style="
                      display: flex;
                      gap: 5px;
                      justify-content: flex-start;
                      align-items: center;
                    "
                  >
                    <font-awesome-icon icon="fa-solid fa-edit" />
                    <span style="font-weight: 500">{{ t('global.label.edit') }}</span>
                  </div> </a-button
                ><br />
                <a-button
                  type="link"
                  style="height: auto; padding: 5px 7px; font-size: 16px"
                  size="small"
                  @click="deleteCity"
                >
                  <div
                    style="
                      display: flex;
                      gap: 5px;
                      justify-content: flex-start;
                      align-items: center;
                    "
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
  </div>

  <a-modal
    :z-index="1031"
    v-model:visible="openModalDelete"
    :maskClosable="false"
    class="text-center"
    @mousedown.stop
    @click.stop
  >
    <h3>{{ t('files.message.delete_note') }}</h3>
    <p>
      <a-alert
        v-if="(props.revisionStages || 0) == 2 || !isDayValidate"
        :description="t('global.message.notification_ope')"
        type="error"
        show-icon
        class="custom-alert"
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
    <h5>{{ t('files.message.question_delete_note') }}</h5>
    <template #footer>
      <div class="text-center">
        <a-button
          type="default"
          class="bnt-default"
          default
          @click="cancelModalNote"
          size="large"
          :disabled="isLoading"
        >
          {{ t('global.button.cancel') }}
        </a-button>
        <a-button type="primary" primary @click="removeCity" size="large" :disabled="isLoading">
          {{ t('global.button.delete') }}
        </a-button>
      </div>
    </template>
  </a-modal>
  <ModalNotes
    :open-modal="modalNotesOpen"
    @close-modal="handleCloseModal"
    :id="requirement_id"
    :type="requirement_type"
  />

  <ModalNoteForService
    :open-modal="modalNotesOpenForService"
    @close-modal="handleCloseModalForService"
    :id="requirement_id"
    :type="requirement_type"
    :entity="props.item.entity"
  />
</template>
<script setup>
  import { ref, computed } from 'vue';
  import { checkDates } from '@/utils/files.js';
  import ModalNotes from '@/components/files/notes/ModalNotes.vue';
  import ModalNoteForService from '@/components/files/notes/ModalNoteForService.vue';
  import { useServiceNotesStore } from '@/stores/files';
  import { useSocketsStore } from '@/stores/global';
  import { getUserId, getUserName, getUserCode } from '@/utils/auth';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const serviceNotesStore = useServiceNotesStore();
  const socketsStore = useSocketsStore();

  const props = defineProps({
    item: {
      required: true,
      type: Array,
      default: [],
    },
    dateIn: {
      required: true,
      type: String,
      default: '',
    },
    date: {
      required: true,
      type: String,
      default: '',
    },
    fileId: {
      required: true,
    },
  });

  const messages = computed(() => {
    return {
      delete_note: t('global.message.delete_note'),
      removed_successfully: t('global.message.removed_successfully'),
    };
  });

  const openModalDelete = ref(false);
  const modalNotesOpen = ref(false);
  const visible = ref(false);
  const isLoading = ref(false);
  const modalNotesOpenForService = ref(false);
  const requirement_id = ref(0);
  const requirement_type = ref('');
  const isDayValidate = computed(() => {
    if (!props.dateIn) return false;

    const inputDate = new Date(props.dateIn);
    if (isNaN(inputDate.getTime())) return false; // Fecha inválida

    const dateMinusTwoDays = new Date(inputDate);
    dateMinusTwoDays.setDate(dateMinusTwoDays.getDate() - 2);

    const today = new Date();
    today.setHours(0, 0, 0, 0);

    return dateMinusTwoDays > today;
  });

  const hide = () => {
    visible.value = false;
  };

  const deleteCity = () => {
    hide();
    openModalDelete.value = true;
  };

  const openModalNotes = () => {
    modalNotesOpen.value = true;
  };

  const openModalNotesForService = () => {
    modalNotesOpenForService.value = true;
  };

  const handleCloseModal = () => {
    requirement_id.value = 0;
    requirement_type.value = '';
    modalNotesOpen.value = false;
  };

  const cancelModalNote = () => {
    openModalDelete.value = false;
  };

  const removeCity = async () => {
    isLoading.value = true;
    const response = await serviceNotesStore.deleteNote({
      file_id: props.fileId,
      id: props.item.id,
      data: {
        type_note: props.item.entity,
        created_by: getUserId(),
        created_by_code: getUserCode(),
        created_by_name: getUserName(),
      },
    });
    openModalDelete.value = false;
    isLoading.value = false;
    const message = messages.value.delete_note;
    const description = messages.value.removed_successfully;

    sendSocketMessage(response.success, 'delete_note', message, description, {
      id: props.item.id,
      type_note: props.item.type_note,
      record_type: props.item.record_type,
    });
  };

  const handleCloseModalForService = () => {
    requirement_id.value = 0;
    requirement_type.value = '';
    modalNotesOpenForService.value = false;
  };

  const handleEditRequirement = (id, entity) => {
    requirement_id.value = id;
    requirement_type.value = props.item.type_note;
    hide();

    console.log(id, entity);

    if (entity == 'mask') {
      openModalNotes();
    } else {
      openModalNotesForService();
    }
  };

  const formatDate = (dateString) => {
    if (!dateString) return '';

    // Dividir la fecha por guiones, espacios o cualquier separador común
    const dateParts = dateString.split(/[-/ ]/); // Admite separadores: - / o espacio
    let month, day;

    // Extraer mes y día según el formato de entrada
    if (dateParts.length >= 3) {
      // Formato YYYY-MM-DD o similar
      [, month, day] = dateParts; // Ignoramos el año (primer elemento)
    } else if (dateParts.length === 2) {
      // Si ya viene solo MM-DD
      [month, day] = dateParts;
    } else {
      return dateString; // Devuelve el original si no se puede parsear
    }

    // Asegurar 2 dígitos para mes y día
    month = month.padStart(2, '0');
    day = day.padStart(2, '0');

    return `${day}/${month}`;
  };

  const truncateText = (text, length = 25) => {
    if (!text) return '';
    return text.length > length ? text.substring(0, length) + '...' : text;
  };

  const isDateBeforeToday = (date) => {
    const inputDate = new Date(date);
    const today = new Date();

    // Reset time components to compare only dates (not time)
    today.setHours(0, 0, 0, 0);
    inputDate.setHours(0, 0, 0, 0);

    return inputDate > today;
  };

  const toggleViewStatus = () => {
    props.item.isCreated = false;
    props.item.isUpdated = false;
    serviceNotesStore.changeFlagChange();
  };

  const sendSocketMessage = (success, type, message, description, data = {}) => {
    socketsStore.send({
      success,
      type: type,
      file_number: props.fileId,
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
</script>
<style scoped>
  .info-row {
    display: flex;
    gap: 20px;
    margin: 20px 35px;
  }

  .ultimo {
    margin-left: auto;
    display: flex;
    flex-direction: row;
  }

  .info-row .day-tag {
    color: #4f4b4b !important;
    background: #ededff !important;
    font-weight: 400;
    size: 12px;
  }

  .classification-tag {
    font-weight: 400;
    size: 12px;
    color: #fafafa;
    background: #4ba3b2 !important;
    border-color: rgba(255, 255, 255, 0.3);
  }

  .info-label {
    font-weight: 600;
    min-width: 90px;
    color: #575757;
    font-size: 12px;
  }

  .description-label {
    color: #737373;
    font-weight: 500;
    font-size: 12px;
    text-align: justify;
  }

  .info-value-blue {
    color: #55a3ff;
    font-weight: 500;
    font-size: 12px;
  }

  .passengers-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }

  .passenger-tag {
    margin: 0;
    background-color: #ededff;
    color: #4f4b4b;
    font-size: 12px;
    font-weight: 400;
  }

  .description-label {
    color: #737373;
    font-weight: 500;
    font-size: 12px;
    text-align: justify;
  }

  .info-value-blue {
    color: #55a3ff;
    font-weight: 500;
    font-size: 12px;
  }

  .passengers-list {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
  }

  .passenger-tag {
    margin: 0;
    background-color: #ededff;
    color: #4f4b4b;
    font-size: 12px;
    font-weight: 400;
  }

  .services-tags {
    display: flex;
    flex-direction: row;
    gap: 12px;
  }

  .tag-service {
    /* max-width: 250px; */
    background-color: #ffffff;
    border-radius: 8px;
    padding: 10px 15px;
    display: flex;
    flex-direction: row;
    gap: 10px;
    align-items: center;
  }

  .tag-service .icon {
    font-size: 20px;
  }

  .tag-service .text {
    color: #2e2e2e !important;
    font-weight: 500;
    size: 12px;
  }

  .action-icon {
    cursor: pointer;
    color: #979797;
    opacity: 0.8;
    transition: opacity 0.2s;
    font-size: 18px;
  }

  .action-updated {
    position: relative;
    border: 2px solid #eb5757;
    border-radius: 6px;
  }

  .action-padding {
    padding: 10px 0 10px 15px;
    margin: 1rem;
  }
</style>
