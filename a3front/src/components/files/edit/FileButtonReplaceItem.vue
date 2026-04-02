s
<template>
  <files-edit-field-static
    :inline="true"
    :hide-content="false"
    :highlighted="false"
    :link="true"
    @click="handleGoTo"
  >
    <template #label>
      <template v-if="type === 'flight'">
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="feather feather-repeat files-edit-field-static-icon"
          width="16"
          height="16"
          fill="none"
          stroke="currentColor"
          stroke-linecap="round"
          stroke-linejoin="round"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path d="m17 1 4 4-4 4" />
          <path d="M3 11V9a4 4 0 0 1 4-4h14M7 23l-4-4 4-4" />
          <path d="M21 13v2a4 4 0 0 1-4 4H3" />
        </svg>
      </template>
      <template v-else>
        <font-awesome-icon :icon="['far', 'pen-to-square']" />
      </template>
    </template>
    <template #popover-content>
      <span class="text-capitalize">
        {{ t('files.button.replace') }}
        <template v-if="type === 'service'"> {{ t('global.label.service') }}</template>
        <template v-if="type === 'master_service'">
          {{ t('global.label.master_service') }}</template
        >
        <template v-if="type === 'composition'"> {{ t('global.label.composition') }}</template>
        <template v-if="type === 'hotel'"> {{ t('global.label.hotel') }}</template>
        <template v-if="type === 'room'"> {{ t('global.label.unit') }}</template>
      </span>
    </template>
  </files-edit-field-static>

  <a-modal
    v-model:open="modal.open"
    :title="modal.subject"
    :width="modal.type == 'continue' ? 800 : 550"
    :closable="true"
    :maskClosable="false"
  >
    <template #footer>
      <a-row align="middle" justify="center">
        <a-col v-if="modal.type == 'confirmation_status'">
          <a-button
            key="button"
            type="default"
            default
            size="large"
            class="text-600"
            @click="closeModal"
            >{{ t('global.button.cancel') }}</a-button
          >
          <a-button
            key="button"
            type="primary"
            default
            size="large"
            class="text-600"
            @click="moveToReservations"
          >
            {{ t('global.label.goto') }} {{ t('files.button.register') }}
          </a-button>
        </a-col>

        <a-col v-if="modal.type == 'continue'">
          <a-button
            key="button"
            type="default"
            default
            size="large"
            class="text-600"
            @click="closeModal"
            >{{ t('global.button.cancel') }}</a-button
          >
          <a-button
            key="button"
            type="primary"
            default
            size="large"
            class="text-600"
            @click="handleProcess"
            >{{ t('global.button.continue') }}</a-button
          >
        </a-col>
      </a-row>
    </template>
    <div id="files-layout">
      <template v-if="modal.type == 'confirmation_status'">
        <a-alert type="warning">
          <template #message>
            <div
              style="color: #e4b804; font-size: 16px; gap: 10px; padding: 16px"
              class="d-flex text-400"
            >
              <div class="d-inline-block">
                <font-awesome-icon
                  :icon="['fas', 'triangle-exclamation']"
                  style="font-size: 24px"
                />
              </div>
              <div class="d-inline-block">
                {{ t('files.message.hotel_no_confirmed') }}
              </div>
            </div>
          </template>
        </a-alert>

        <div class="bg-pink-stick p-3 mt-3">
          <a-row type="flex" justify="start" align="middle">
            <a-col class="subtitle">
              <span>
                <font-awesome-icon :icon="['fa-solid', 'fa-hotel']" />
              </span>
              <span class="mx-2">
                <b class="text-700" style="font-size: 18px">{{ itinerary.name }}</b>
              </span>
            </a-col>
            <a-col>
              <a-tag color="#c63838">
                {{ itinerary.category }}
              </a-tag>
            </a-col>
            <a-col>
              <a-tag>{{ itinerary.city_in_iso }}</a-tag>
            </a-col>
          </a-row>
          <hr />
          <a-row type="flex" justify="space-between" align="middle">
            <a-col class="text-dark-gray">
              <span class="me-2">
                <CalendarOutlined />
              </span>
              <span style="font-size: 14px">
                <b>{{ formatDate(itinerary.date_in, 'DD/MM/YYYY') }}</b>
              </span>
              <span class="mx-2">
                <b style="font-size: 18px" class="text-danger">|</b>
              </span>
              <span>
                <b>{{ formatDate(itinerary.date_out, 'DD/MM/YYYY') }}</b>
              </span>
            </a-col>
            <a-col>
              <span style="font-size: 12px">
                <b class="text-danger mx-1">{{ itinerary.rooms[0].units[0].nights.length }}</b>
                <b> {{ t('global.label.nights') }}</b>
              </span>
            </a-col>
          </a-row>
          <a-row
            type="flex"
            justify="space-between"
            align="middle"
            v-for="(_room, r) in itinerary.rooms"
            :key="'room-confirmation-' + r"
          >
            <a-col
              class="text-dark-gray d-flex"
              style="gap: 5px"
              v-if="object_id == _room.id || object_id == 0"
            >
              <span>
                <b class="text-danger">{{ _room.units.length }}</b>
                <b> room:</b>
              </span>
              <span>{{ _room.room_type }}</span>
            </a-col>
            <a-col>
              <span v-if="r == data.itinerary.rooms.length - 1">
                <h4
                  :class="[
                    'd-flex',
                    filesStore.calculatePenalityRoomsSale(
                      itinerary.rooms,
                      itinerary.rooms.map((room) => room.id)
                    ) > 0
                      ? 'text-danger'
                      : 'text-success',
                    'mb-0',
                    'text-700',
                  ]"
                  style="font-size: 24px"
                >
                  <template
                    v-if="
                      filesStore.calculatePenalityRoomsSale(
                        itinerary.rooms,
                        itinerary.rooms.map((room) => room.id)
                      ) > 0
                    "
                  >
                    <a-tooltip>
                      <template #title>{{ t('files.label.penalty') }}</template>
                      $
                      {{
                        filesStore.calculatePenalityRoomsSale(
                          itinerary.rooms,
                          itinerary.rooms.map((room) => room.id)
                        )
                      }}
                    </a-tooltip>
                    <a href="javascript:;" class="text-dark-gray ms-1" style="font-size: 0.8rem">
                      <i class="bi bi-question-circle"></i>
                    </a>
                  </template>
                  <template v-else> {{ t('files.label.no_penalty') }} </template>
                </h4>
              </span>
            </a-col>
          </a-row>
        </div>
      </template>

      <template v-if="modal.type == 'continue'">
        <div class="bg-pink-stick p-4">
          <a-row type="flex" justify="start" align="middle">
            <a-col class="subtitle">
              <span>
                <font-awesome-icon :icon="['fa-solid', 'fa-hotel']" />
              </span>
              <span class="mx-2">
                <b>{{ itinerary.name }}</b>
              </span>
            </a-col>
            <a-col>
              <a-tag color="#c63838">
                {{ itinerary.category }}
              </a-tag>
            </a-col>
          </a-row>
          <template v-for="(_room, r) in itinerary.rooms" :key="'room-continue-' + r">
            <a-row type="flex" justify="space-between" align="middle" class="mt-3">
              <a-col>
                <span>
                  <CalendarOutlined />
                </span>
                <span>
                  <b>{{ formatDate(itinerary.date_in, 'DD/MM/YYYY') }}</b>
                </span>
                <span>
                  <b style="font-size: 18px">|</b>
                </span>
                <span>
                  <b>{{ formatDate(itinerary.date_out, 'DD/MM/YYYY') }}</b>
                </span>
              </a-col>
              <a-col>
                <span>
                  <b
                    >{{ t('global.label.nights') }}:
                    <span class="text-danger">{{ _room.units[0].nights.length }}</span></b
                  >
                </span>
              </a-col>
              <a-col>
                <span>
                  <b
                    >{{ t('global.label.units') }}:
                    <span class="text-danger">{{ _room.units.length }}</span></b
                  >
                </span>
              </a-col>
              <a-col>
                <span>
                  <b>{{ t('global.label.room') }}:</b>
                  <span class="text-uppercase">{{ _room.room_name }}</span>
                </span>
              </a-col>
            </a-row>
            <a-row type="flex" justify="space-between" align="middle" class="mt-2">
              <a-col>
                <span>
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 512 512"
                    width="14"
                    height="14"
                    class="svg-danger"
                  >
                    <path
                      d="M256 48a208 208 0 1 1 0 416 208 208 0 1 1 0-416zm0 464A256 256 0 1 0 256 0a256 256 0 1 0 0 512zM175 175c-9.4 9.4-9.4 24.6 0 33.9l47 47-47 47c-9.4 9.4-9.4 24.6 0 33.9s24.6 9.4 33.9 0l47-47 47 47c9.4 9.4 24.6 9.4 33.9 0s9.4-24.6 0-33.9l-47-47 47-47c9.4-9.4 9.4-24.6 0-33.9s-24.6-9.4-33.9 0l-47 47-47-47c-9.4-9.4-24.6-9.4-33.9 0z"
                    />
                  </svg>
                </span>
                <small
                  ><b class="mx-1">{{ _room.rate_plan_name }}</b></small
                >
                <b class="text-danger">$ {{ _room.amount_sale }}</b>
              </a-col>
              <a-col>
                <a-row type="flex" justify="space-between" align="middle">
                  <a-col>
                    <span class="bi bi-check2-circle text-success mb-0"></span>
                  </a-col>
                  <a-col
                    v-if="!(filesStore.calculatePenalityRoomsSale(itinerary.rooms, [_room.id]) > 0)"
                  >
                    <b class="text-success mx-1">
                      <small>{{ t('files.label.no_penalty') }}</small>
                    </b>
                  </a-col>
                  <a-col>
                    <b class="text-danger">
                      <small
                        >$
                        {{
                          filesStore.calculatePenalityRoomsSale(itinerary.rooms, [_room.id])
                        }}</small
                      >
                    </b>
                  </a-col>
                </a-row>
              </a-col>
            </a-row>
          </template>

          <a-row align="middle" type="flex" justify="space-between" class="mt-3">
            <a-col>
              <div
                v-bind:class="[
                  'd-flex cursor-pointer',
                  !showNotesFrom ? 'text-dark-gray' : 'text-danger',
                ]"
                @click="showNotesFrom = !showNotesFrom"
              >
                <template v-if="showNotesFrom">
                  <i
                    class="bi bi-check-square-fill text-danger d-flex"
                    style="font-size: 1.5rem"
                  ></i>
                </template>
                <template v-else>
                  <i class="bi bi-square d-flex" style="font-size: 1.5rem"></i>
                </template>
                <span class="mx-2">Agregar nota al proveedor</span>
              </div>
            </a-col>
            <a-col class="ant-row-end" v-if="lockedNotesFrom || !showNotesFrom">
              <a-button
                danger
                type="default"
                v-bind:class="[
                  'd-flex ant-row-middle text-600',
                  !detail.flag_show ? 'bg-transparent' : '',
                ]"
                @click="showCommunicationFrom()"
                :loading="communicationsStore.isLoading || communicationsStore.isLoadingAsync"
              >
                <i
                  class="bi bi-search"
                  v-if="!(communicationsStore.isLoading || communicationsStore.isLoadingAsync)"
                ></i>
                <span class="ms-2">
                  <template v-if="!detail.flag_show">Ver</template
                  ><template v-else>Ocultar</template> comunicación</span
                >
              </a-button>
            </a-col>
          </a-row>

          <div class="my-2" v-if="showNotesFrom">
            <template v-if="lockedNotesFrom">
              <small class="d-flex text-danger" style="gap: 5px">
                <a href="javascript:;" @click="lockedNotesFrom = false">
                  <i class="bi bi-pencil"></i>
                </a>
                <b>Nota en reserva para el proveedor:</b>
              </small>
              <small class="mb-2">
                {{ notesFrom }}
              </small>
              <template v-for="(file, f) in filesFrom" :key="'files-' + f">
                <a-row align="middle" class="mb-2">
                  <i class="bi bi-paperclip"></i>
                  <a :href="file" target="_blank" class="text-dark mx-1">
                    {{ showName(file) }}
                  </a>
                </a-row>
              </template>
            </template>

            <template v-if="!lockedNotesFrom">
              <small class="text-danger my-2">Nota para el proveedor:</small>
              <a-row align="top" justify="space-between">
                <a-col flex="auto" class="pe-2">
                  <a-textarea
                    v-model:value="notesFrom"
                    placeholder="Escribe una nota para el proveedor que podrás visualizar en la comunicación"
                    :auto-size="{ minRows: 2 }"
                  />
                </a-col>
                <a-col>
                  <file-upload
                    v-bind:folder="'communications'"
                    @onResponseFiles="responseFilesFrom"
                  />
                </a-col>
                <a-col v-if="notesFrom != '' || filesFrom.length > 0">
                  <a-button
                    danger
                    type="default"
                    size="large"
                    class="d-flex ant-row-middle text-600 ms-2"
                    @click="lockedNotesFrom = true"
                    :loading="communicationsStore.isLoading"
                  >
                    <i
                      v-bind:class="[
                        'bi bi-floppy',
                        communicationsStore.isLoading || communicationsStore.isLoadingAsync
                          ? 'ms-2'
                          : '',
                      ]"
                    ></i>
                  </a-button>
                </a-col>
              </a-row>
            </template>
          </div>

          <div v-if="detail.flag_show && (lockedNotesFrom || !showNotesFrom)" class="mt-3">
            <div v-html="detail.html"></div>
          </div>
        </div>
      </template>
    </div>
  </a-modal>
</template>

<script setup>
  import { ref, toRefs } from 'vue';
  import FilesEditFieldStatic from '@/components/files/edit/FilesEditFieldStatic.vue';
  import { CalendarOutlined } from '@ant-design/icons-vue';
  import { formatDate } from '@/utils/files.js';
  import { useRouter } from 'vue-router';
  import { useFilesStore } from '@/stores/files';
  import { useCommunicationsStore } from '@/stores/global';

  import { notification } from 'ant-design-vue';
  import dayjs from 'dayjs';

  import FileUpload from '@/components/global/FileUploadComponent.vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n({
    useScope: 'global',
  });

  const props = defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
  });

  const emit = defineEmits(['onHandleGoToReservations']);

  const filesStore = useFilesStore();
  const communicationsStore = useCommunicationsStore();

  const { itinerary, type, params } = toRefs(props.data);

  const router = useRouter();

  const object_id = ref(0);
  const detail = ref({
    flag_show: false,
    html: '',
    subject: '',
  });

  const modal = ref({
    open: false,
    type: '',
    subject: '',
  });

  const showNotesFrom = ref(false);
  const lockedNotesFrom = ref(false);
  const notesFrom = ref('');
  const filesFrom = ref([]);

  const closeModal = () => {
    modal.value.open = false;
    modal.value.type = '';
    modal.value.subject = '';
  };

  const showCommunicationFrom = async () => {
    if (!detail.value.flag_show) {
      let rooms = [];

      itinerary.value.rooms.forEach((room) => {
        let units = [];

        if (room.id == object_id.value || object_id.value == 0) {
          room.units.forEach((unit) => {
            units.push(unit.id);
          });

          if (units.length > 0) {
            rooms.push({
              id: room.id,
              units: units,
            });
          }
        }
      });

      let params = {
        rooms: rooms,
        notas: notesFrom.value,
        attachments: filesFrom.value,
      };

      await communicationsStore.previewCommunication(
        'itineraries/' + itinerary.value.id,
        params,
        'hotel',
        'cancellation'
      );

      detail.value.html = communicationsStore.getHtml;
      detail.value.subject = communicationsStore.getSubject;
      detail.value.flag_show = true;
    } else {
      detail.value.html = '';
      detail.value.subject = '';
      detail.value.flag_show = false;
    }
  };

  const moveToReservations = () => {
    emit('onHandleGoToReservations');
  };

  const handleGoTo = async () => {
    // isLoading.value = true;

    await filesStore.getFileItineraryById({
      id: filesStore.getFile.id,
      object_id: itinerary.value.id,
    });
    itinerary.value = filesStore.getFileItinerary;

    if (itinerary.value.hyperguest) {
      notification.error({
        message: 'Error',
        description: 'El hotel tiene una tarifa Hyperguest, por lo tanto no se puede modificar.',
      });
      return false;
    }

    if (itinerary.value.date_in < dayjs().format('YYYY-MM-DD')) {
      notification.error({
        message: 'Error',
        description: 'La fecha es pasada, no se puede continuar con la solicitud.',
      });
      return false;
    }

    if (type.value === 'service') {
      /*
      const services = itinerary.value.services.map((service) => {
        return {
          id: service.id,
          compositions: service.compositions.map((composition) => composition.id),
        };
      });

      const responseValidate = await filesStore.handleValidateItinerary({
        services: services,
        file_itinerary_id: itinerary.value.id,
        loadingRequest: false,
      });
      if (!responseValidate.success) {
        const masterService = responseValidate?.data?.error?.master_services;
        if (!masterService) {
          // aqui aparecera una notificacion}
          isLoading.value = false;
          message_modal.value =
            responseValidate?.data?.error?.message ||
            responseValidate?.message ||
            'Error interno del servidor.';
          notification.error({
            message: 'Error',
            description: message_modal.value,
          });
        } else {
          // aqui el modal
          isLoading.value = false;
          open.value = true;
          message_modal.value =
            responseValidate?.data?.error?.message || 'Error interno del servidor.';
          master_services.value = masterService;
          type_critical.value =
            masterService.length > 0
              ? masterService.some((service) => service.response?.critical)
              : true;
        }
        return false;
      }
      */
    }

    if (type.value === 'hotel') {
      modal.value.subject = 'Modificar Hotel';

      if (!itinerary.value.confirmation_status) {
        modal.value.open = true;
        modal.value.type = 'confirmation_status';
        return false;
      }

      if (typeof itinerary.value.optional != 'undefined' && !itinerary.value.optional) {
        modal.value.open = true;
        modal.value.type = 'continue';
        return false;
      }
    }

    if (!(type.value === 'hotel' || type.value === 'room')) {
      filesStore.setServiceEdit(props.data);
    }

    const route = `files-replace-${type.value.replace('_', '-')}`;
    router.push({ name: route, params: params.value });
  };
</script>
