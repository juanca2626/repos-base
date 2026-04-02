<template>
  <!-- :color="itinerary.isNew || itinerary.isUpdated ? '#EB5757' : '#737373'" -->
  <div>
    <div v-if="props.amount === 1">
      <div
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
        <div class="reservation-header pr-3">
          <div class="header-section">
            <a-tag color="gold">
              <small class="text-uppercase">
                {{ props.item.city }}
              </small>
            </a-tag>
            <div class="date-group">
              <div>
                <font-awesome-icon :icon="['fas', 'arrow-right-to-bracket']" />
                <label style="color: #575757">{{ t('global.label.in') }}:</label>
                <span style="font-weight: 500; font-size: 12px">{{
                  formatDate(props.item.date_check_in)
                }}</span>
              </div>
              <div>
                <font-awesome-icon :icon="['fas', 'arrow-right-from-bracket']" />
                <label style="color: #575757">{{ t('global.label.out') }}:</label>
                <span style="font-weight: 500; font-size: 12px">{{
                  formatDate(props.item.date_check_out)
                }}</span>
              </div>
            </div>
          </div>

          <div class="header-section">
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
                      style="
                        display: flex;
                        gap: 5px;
                        justify-content: flex-start;
                        align-items: center;
                      "
                    >
                      <font-awesome-icon icon="fa-solid fa-edit" />
                      <span class="text-500">{{ t('global.label.edit') }}</span>
                    </div> </a-button
                  ><br />
                  <a-button
                    type="link"
                    style="height: auto; padding: 5px 7px; font-size: 16px"
                    size="small"
                    @click="deleteExternalHousing"
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
                      <span class="text-5500">{{ t('global.button.delete') }}</span>
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

        <div class="reservation-content pt-2">
          <!-- Lista de pasajeros -->
          <div class="info-row">
            <label class="info-label">{{ t('global.label.passengers_capital_letters') }}:</label>
            <div class="passengers-list">
              <a-tag
                v-for="item in passengers"
                :key="item.id"
                :bordered="false"
                class="passenger-tag"
              >
                {{ item.label }}
              </a-tag>
            </div>
          </div>

          <div class="info-row">
            <label class="info-label">{{ t('global.label.housing') }}:</label>
            <div class="info-value">{{ props.item.name_housing }}</div>
          </div>

          <div class="info-row">
            <label class="info-label">{{ t('global.label.address') }}:</label>
            <div @click="openMaps" class="info-value-blue" style="cursor: pointer">
              {{ props.item.address }}
            </div>
          </div>

          <div class="info-row">
            <label class="info-label">{{ t('global.label.contact') }}:</label>
            <div @click="makeCall" class="info-value-blue" style="cursor: pointer">
              {{ props.item.code_phone + ' ' + props.item.phone }}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div v-else>
      <div
        :class="{
          'action-updated': props.item.isCreated || props.item.isUpdated,
        }"
      >
        <a-badge-ribbon
          :color="'#EB5757'"
          class="cursor-pointer"
          style="position: absolute; z-index: 999; top: -7px; padding: 0 5px"
          v-if="props.item.isCreated || props.item.isUpdated"
          @click="toggleViewStatus()"
        >
          <template #text>
            <span>
              <a-tooltip>
                <template #title>
                  <small
                    >{{
                      props.item.isCreated
                        ? 'Creado'
                        : props.item.isUpdated
                          ? 'Actualizado'
                          : 'Sin cambios'
                    }}
                    {{ '#' + props.item.id }}</small
                  >
                </template>
                <template v-if="true">
                  <font-awesome-icon :icon="['far', 'circle-check']" />
                </template>
              </a-tooltip>
            </span>
          </template>
        </a-badge-ribbon>
        <a-card class="reservation-card override-card" :class="{ border: toogleBool }">
          <template #title>
            <div class="reservation-header">
              <div class="header-section">
                <a-tag class="location-tag">{{ props.item.city }}</a-tag>
                <div class="date-group">
                  <div>
                    <font-awesome-icon :icon="['fas', 'arrow-right-to-bracket']" />
                    <label style="color: #575757">{{ t('global.label.in') }}:</label>
                    <span style="font-weight: 500; font-size: 12px">{{
                      formatDate(props.item.date_check_in)
                    }}</span>
                  </div>
                  <div>
                    <font-awesome-icon :icon="['fas', 'arrow-right-from-bracket']" />
                    <label style="color: #575757">{{ t('global.label.out') }}:</label>
                    <span style="font-weight: 500; font-size: 12px">{{
                      formatDate(props.item.date_check_out)
                    }}</span>
                  </div>
                </div>
              </div>

              <div class="header-section">
                <div class="nights-info">
                  <font-awesome-icon :icon="['fas', 'moon']" />
                  <span
                    >{{ nightsCount }}
                    {{
                      nightsCount === 1 ? t('global.label.night') : t('global.label.nights')
                    }}</span
                  >
                </div>
                <font-awesome-icon
                  :icon="['fas', toogleBool ? 'chevron-up' : 'chevron-down']"
                  class="action-icon"
                  @click="toggleActive"
                />
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
                        @click="deleteExternalHousing"
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
          </template>
          <hr class="custom-divider" v-if="toogleBool" />
          <div class="reservation-content pt-2" v-if="toogleBool">
            <div class="info-row">
              <label class="info-label">{{ t('global.label.passengers_capital_letters') }}:</label>
              <div class="passengers-list">
                <a-tag
                  v-for="item in passengers"
                  :key="item.id"
                  :bordered="false"
                  class="passenger-tag"
                >
                  {{ item.label }}
                </a-tag>
              </div>
            </div>

            <div class="info-row">
              <label class="info-label">{{ t('global.label.housing') }}:</label>
              <div class="info-value">{{ props.item.name_housing }}</div>
            </div>

            <div class="info-row">
              <label class="info-label">{{ t('global.label.address') }}:</label>
              <div @click="openMaps" class="info-value-blue" style="cursor: pointer">
                {{ props.item.address }}
              </div>
            </div>

            <div class="info-row">
              <label class="info-label">{{ t('global.label.contact') }}:</label>
              <div @click="makeCall" class="info-value-blue" style="cursor: pointer">
                {{ props.item.code_phone + ' ' + props.item.phone }}
              </div>
            </div>
          </div>
        </a-card>
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
    <h3>{{ t('global.message.delete_external_housing') }}</h3>
    <p>
      <a-alert
        description="Operaciones recibirá una notificación de la eliminación"
        type="error"
        show-icon
        class="custom-alert"
        v-if="(filesStore.getFile.revisionStages || 0) == 2"
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
    <h5>{{ t('global.message.question_delete_external_housing') }}</h5>
    <template #footer>
      <div class="text-center">
        <a-button
          type="default"
          class="bnt-default"
          default
          @click="cancelModalNote"
          size="large"
          :disabled="serviceNotesStore.isLoadingSaveOrUpdateExternalHousing"
        >
          {{ t('global.button.cancel') }}
        </a-button>
        <a-button
          type="primary"
          primary
          @click="removeExternalHousing"
          size="large"
          :disabled="serviceNotesStore.isLoadingSaveOrUpdateExternalHousing"
        >
          {{ t('global.button.delete') }}
        </a-button>
      </div>
    </template>
  </a-modal>

  <ModalNotes
    :open-modal="modalNotesOpen"
    @close-modal="handleCloseModal"
    :id="id"
    type="EXTERNAL_HOUSING"
  />
</template>
<script setup>
  // import { uniqBy } from 'lodash';
  import { ref, computed } from 'vue';
  import { useFilesStore, useServiceNotesStore } from '@/stores/files';
  import ModalNotes from '@/components/files/notes/ModalNotes.vue';
  import { getUserId, getUserName, getUserCode } from '@/utils/auth';
  import { useSocketsStore } from '@/stores/global';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();
  const serviceNotesStore = useServiceNotesStore();
  const socketsStore = useSocketsStore();

  const openModalDelete = ref(false);
  const modalNotesOpen = ref(false);
  const visible = ref(false);
  const id = ref(0);

  const hide = () => {
    visible.value = false;
  };

  const props = defineProps({
    amount: {
      required: true,
      type: Number,
      default: 1,
    },
    item: {
      required: true,
      type: Array,
      default: [],
    },
    fileId: {
      required: true,
    },
  });

  const passengers = computed(() => {
    const storePassengers = filesStore.getFilePassengers || [];
    const itemPassengers = props.item.passengers || [];

    // Filtrar y combinar según necesites
    return storePassengers.filter((passenger) => {
      // Ejemplo: solo pasajeros que existan en ambos lugares
      return itemPassengers.some((storePass) => storePass.passengers_id === passenger.id);
    });
  });

  const nightsCount = computed(() => {
    try {
      const checkInDate = new Date(props.item.date_check_in);
      const checkOutDate = new Date(props.item.date_check_out);

      // Validar fechas
      if (isNaN(checkInDate) || isNaN(checkOutDate)) {
        console.error('Fechas inválidas');
        return 0;
      }

      // Asegurarse que check_out es posterior a check_in
      if (checkOutDate <= checkInDate) {
        return 0;
      }

      // Calcular diferencia en milisegundos
      const diffTime = checkOutDate - checkInDate;

      // Convertir a días (1 noche = 1 día completo)
      const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));

      return diffDays;
    } catch (error) {
      console.error('Error calculando noches:', error);
      return 0;
    }
  });

  const toogleBool = ref(false);

  const toggleActive = () => {
    toogleBool.value = !toogleBool.value;
  };

  const formatDate = (value) => {
    const date = value.split('-');
    return date[2] + '/' + date[1] + '/' + date[0];
  };

  const openMaps = () => {
    const url = `https://www.google.com/maps/search/?api=1&query=${props.item.lat},${props.item.lng}`;
    window.open(url, '_blank', 'noopener,noreferrer');
  };

  const makeCall = () => {
    const number = props.item.code_phone + ' ' + props.item.phone;
    const cleaned = number.replace(/\D/g, '');
    window.open(`https://wa.me/${cleaned}`, '_blank');
  };

  const deleteExternalHousing = () => {
    hide();
    openModalDelete.value = true;
  };

  const cancelModalNote = () => {
    openModalDelete.value = false;
  };

  const removeExternalHousing = async () => {
    const { success } = await serviceNotesStore.deleteExternalHousing({
      file_id: filesStore.file.id,
      id: props.item.id,
      data: {
        created_by: getUserId(),
        created_by_code: getUserCode(),
        created_by_name: getUserName(),
      },
    });

    if (success) {
      cancelModalNote();
      const type = 'delete_external_housing';
      const message = 'Alojamiento externo';
      const description = 'Se elimino un alojamiento externo';
      sendSocketMessage(success, type, message, description, { id: props.item.id });
    }
  };

  const handleEdit = () => {
    id.value = props.item.id;
    hide();
    openModalNotes();
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

  const sendSocketMessage = (success, type, message, description, data = {}) => {
    socketsStore.send({
      success,
      type: type,
      file_number: filesStore.getFile.fileNumber,
      file_id: filesStore.getFile.id,
      user_code: getUserCode(),
      user_id: getUserId(),
      message: message,
      description: description,
      id: data?.id || 0,
    });
  };
</script>
<style scoped>
  /* Estilos del card */
  .reservation-card {
    border-radius: 8px;
    background-color: #fafafa;
    border: 1px solid #e9e9e9 !important;
  }

  /* Header styles */
  .reservation-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    width: 100%;
  }

  .pr-3 {
    padding: 0px 30px 0 0;
  }

  .header-section {
    display: flex;
    align-items: center;
    gap: 12px;
  }

  .location-tag {
    color: #979797;
    background: #fafafa !important;
    border-color: rgba(255, 255, 255, 0.3);
    font-weight: 400;
    size: 12px;
  }

  .date-group,
  .nights-info {
    display: flex;
    align-items: center;
    gap: 12px;
    color: #979797;
    font-weight: 600;
    font-size: 12px;
  }

  .date-group div {
    display: flex;
    align-items: center;
    gap: 8px;
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

  /* Content styles */
  .reservation-content {
    padding: 0px 0;
  }

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

  :where(.files-layout) .ant-card-head {
    all: unset;
  }

  /* Estilos específicos para tu card */
  .reservation-card.override-card :deep(.ant-card-head) {
    background: linear-gradient(135deg, #fafafa, #fafafa) !important;
    border-radius: 8px 8px 8px 8px !important;
    padding: 0 16px !important;
    border: 1px solid #e9e9e9 !important;
  }

  .reservation-card.override-card.border :deep(.ant-card-head) {
    border: 0 !important;
  }

  .reservation-card.override-card :deep(.ant-card-head-title) {
    color: #979797 !important;
    padding: 12px 0 !important;
  }

  .reservation-card.override-card :deep(.ant-card-body) {
    padding-top: 0px !important;
  }

  /* Estilos para los elementos internos */
  .reservation-card :deep(.ant-card-head) .ant-tag {
    background: #fafafa !important;
    border-color: #e9e9e9 !important;
    color: #4f4b4b !important;
    font-weight: 400;
    size: 12px;
  }

  .reservation-card :deep(.ant-card-head) .location-tag {
    color: #979797;
    background: #fafafa !important;
    border-color: rgba(255, 255, 255, 0.3);
    font-weight: 400;
    size: 12px;
  }
  .custom-divider {
    border: 0;
    height: 1px;
    background: #e9e9e9;
    margin: 0px 0;
    width: 100%;
  }
  .actions-content-click-content {
    display: flex;
    flex-direction: column;
  }

  .tag-city {
    background-color: #fafafa !important;
    border: 1px solid #e9e9e9 !important;
  }

  .action-updated {
    position: relative;
    border: 2px solid #eb5757;
    border-radius: 6px;
  }

  .action-padding {
    padding: 10px 0 15px 15px;
  }
</style>
