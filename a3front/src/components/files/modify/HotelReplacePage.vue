<template>
  <template v-if="filesStore.isLoadingItinerary">
    <div class="files-edit">
      <a-skeleton rows="1" active />
    </div>
    <div class="files-edit files-edit__border">
      <a-skeleton rows="1" active />
    </div>
    <div class="files-edit files-edit__border">
      <a-skeleton rows="1" active />
    </div>
  </template>
  <div class="files-edit" v-else>
    <a-steps :current="step" size="large" class="p-5 mb-5">
      <a-step :title="showMessage(0)" :description="t('files.label.choose_modify')" />
      <a-step :title="showMessage(1)" :description="t('files.label.communication_to_the_hotel')" />
      <a-step :title="showMessage(2)" :description="t('files.label.complete_modification')" />
    </a-steps>

    <template v-if="step != 2">
      <div v-show="step == 0">
        <div class="d-flex justify-content-between align-items-center pt-5 pb-5">
          <div class="title">
            <font-awesome-icon :icon="['fas', 'pen-to-square']" class="text-dark-gray" />
            {{ t('global.label.modify_hotel') }}
          </div>
          <div class="actions">
            <a-button
              v-on:click="returnToProgram()"
              class="text-600"
              danger
              :disabled="filesStore.isLoading"
              size="large"
            >
              {{ t('global.button.return_to_program') }}
            </a-button>
          </div>
        </div>
        <hotel-selected @onChangeSelected="changeSelected" @onChangeAsumed="changeAsumed" />
        <hotel-search
          v-if="selected.length > 0 && !loading_selected"
          :items="selected"
          type="modification"
          @onNextStep="nextStep"
          @onReturnToProgram="returnToProgram"
        />
      </div>
    </template>

    <div v-if="step == 1">
      <div class="d-flex justify-content-between align-items-center pt-5 pb-5">
        <div class="title">
          <font-awesome-icon icon="fa-solid fa-comment-alt" class="text-danger" /> Comunicación al
          proveedor
        </div>
        <div class="actions">
          <a-button
            v-on:click="returnToProgram()"
            class="text-600"
            danger
            :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
            size="large"
          >
            {{ t('global.button.return_to_program') }}
          </a-button>
        </div>
      </div>

      <hotel-merge
        :from="filesStore.getFileItinerary"
        :to="filesStore.getFileItinerariesReplace"
        :selected="selected"
        type="modification"
        @onLoadReservation="loadReservation"
        @onPrevStep="prevStep"
        @onNextStep="nextStep"
      />
    </div>

    <div v-if="step == 2">
      <div class="pt-5 pb-5">
        <div class="text-center">
          <h2 class="text-danger text-800">Solicitud de reserva realizada</h2>
          <div class="my-5">
            <font-awesome-icon
              :icon="['far', 'hourglass-half']"
              style="font-size: 5rem"
              class="text-danger"
            />
          </div>
        </div>

        <a-alert type="info" class="mb-0">
          <template #description>
            <a-row type="flex" justify="start" align="top" style="gap: 10px">
              <a-col>
                El sistema está actualizando el itinerario. Esta operación puede tardar unos
                minutos. Por favor, verifica más tarde y espera nuestra confirmación para la
                reserva.
              </a-col>
            </a-row>
          </template>
        </a-alert>

        <div
          class="box-completed bg-light py-3 px-5 my-4"
          v-for="(item, index) in reservation"
          :key="index"
        >
          <a-row type="flex" align="middle" justify="start" class="mx-5 my-3" style="gap: 8px">
            <a-col>
              <font-awesome-icon :icon="['fa-solid', 'fa-hotel']" style="font-size: 18px" />
            </a-col>
            <a-col>
              <span class="text-700" style="font-size: 18px">{{ item.name }}</span>
            </a-col>
            <a-col flex="auto" v-if="item.notesTo != null && item.notesTo != ''">
              <span class="bg-white px-3 py-2 bordered w-100 ant-row-middle">
                <span class="d-flex mx-1">
                  <i class="bi bi-pencil"></i>
                </span>
                <span class="text-danger text-600">Notas para el proveedor:</span>
                <span class="mx-2">{{ item.notesTo }}</span>
              </span>
            </a-col>
          </a-row>
          <p class="my-3 mx-5">
            <span class="text-danger">
              <b>Detalle de la reserva</b>
            </span>
          </p>
          <a-row type="flex" align="top" justify="space-between" class="my-3 mx-5">
            <a-col :span="12">
              <p class="d-flex" style="gap: 5px">
                <b>Número de File:</b>
                <span>{{ filesStore.getFile.fileNumber }}</span>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Nombre del File:</b>
                <span>{{ filesStore.getFile.description }}</span>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Nacionalidad:</b> <span>{{ showLanguage() }}</span>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Fecha de Reserva:</b>
                <span>{{ dayjs().format('DD/MM/YYYY') }}</span>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>E-mail del ejecutivo:</b>
                <span class="text-lowercase"
                  >{{ filesStore.getFile.executiveCode }}@limatours.com.pe</span
                >
              </p>
            </a-col>
            <a-col :span="12">
              <p class="d-flex" style="gap: 5px">
                <span>
                  <font-awesome-icon
                    :icon="['far', 'calendar']"
                    class="text-danger"
                  ></font-awesome-icon>
                  <!-- svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M15.8333 3.95703H4.16667C3.24619 3.95703 2.5 4.70322 2.5 5.6237V17.2904C2.5 18.2108 3.24619 18.957 4.16667 18.957H15.8333C16.7538 18.957 17.5 18.2108 17.5 17.2904V5.6237C17.5 4.70322 16.7538 3.95703 15.8333 3.95703Z" stroke="#EB5757" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M13.333 2.28906V5.6224" stroke="#EB5757" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M6.66699 2.28906V5.6224" stroke="#EB5757" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2.5 8.95703H17.5" stroke="#EB5757" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                  </svg -->
                </span>
                <b class="text-danger">Fechas:</b>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Check-in:</b> {{ formatDate(item.search_parameters.date_from) }}
                <span class="text-danger">|</span> 15:00
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Check-out:</b> {{ formatDate(item.search_parameters.date_to) }}
                <span class="text-danger">|</span> 15:00
              </p>
              <p class="d-flex" style="gap: 5px">
                <b
                  >Cantidad de noches:
                  {{
                    textPad({
                      text: checkDates(
                        item.search_parameters.date_from,
                        item.search_parameters.date_to
                      ),
                      start: 0,
                      length: 2,
                    })
                  }}</b
                >
              </p>
            </a-col>
            <hr class="line-dashed size-1" />
          </a-row>

          <template v-for="(room, r) in item.rooms">
            <a-row type="flex" align="space-between" class="mt-3 mx-5 px-5">
              <hr class="line-dashed size-1 mb-3" v-if="r > 0" />
            </a-row>
            <a-row type="flex" align="space-between" class="mt-3 mx-5 px-5">
              <a-col>
                <p class="d-flex" style="gap: 5px">
                  <span>
                    <font-awesome-icon icon="bed" class="text-danger"></font-awesome-icon>
                  </span>
                  <b class="text-danger">Detalles de Habitación:</b>
                </p>
                <p class="d-flex" style="gap: 5px">
                  <b>Cantidad de Habitaciones:</b>
                  {{ textPad({ text: room.rates[0].quantity_room, start: 0, length: 2 }) }}
                </p>
                <p class="d-flex" style="gap: 5px">
                  <b>Nombre de Habitación:</b> {{ room.description }}
                </p>
                <p class="d-flex" style="gap: 5px">
                  <b>Tipo de Habitación:</b> {{ room.room_type }}
                </p>
                <p class="d-flex" style="gap: 5px">
                  <b>Ocupantes:</b>
                  <span class="text-uppercase">{{
                    room.occupation * room.rates[0].quantity_room
                  }}</span>
                </p>
              </a-col>
              <a-col>
                <p class="d-flex" style="gap: 5px">
                  <span>
                    <font-awesome-icon :icon="['fas', 'dollar-sign']" class="text-danger" />
                  </span>
                  <b class="text-danger">Tarifas</b>
                </p>
                <template v-for="(rate, r) in room.rates" :key="r">
                  <p class="d-flex" style="gap: 5px">
                    <b>Plan Tarifario:</b>
                    {{ rate.name }} <span class="text-danger">|</span>
                    {{ item.code }}
                  </p>
                  <p class="d-flex" style="gap: 5px"><b>Total Tarifa:</b> USD {{ rate.total }}</p>

                  <a-row type="flex" align="middle" justify="start">
                    <a-col>
                      <small><b>Status de la reserva: </b></small>
                    </a-col>
                    <a-col>
                      <template v-if="rate.onRequest == 1">
                        <a-tag
                          class="d-flex ant-row-middle bg-success text-white py-2 mx-2"
                          style="border: transparent"
                        >
                          <font-awesome-icon :icon="['fas', 'circle-check']" size="lg" />
                          <span class="text-600 mx-2">{{ t('global.message.confirmed') }}</span>
                        </a-tag>
                      </template>
                      <template v-else>
                        <a-tag
                          class="d-flex ant-row-middle bg-warning text-white py-2 mx-2"
                          style="border: transparent"
                        >
                          <font-awesome-icon :icon="['fas', 'triangle-exclamation']" size="lg" />
                          <span class="text-600 mx-2">{{ t('global.message.waiting') }}</span>
                        </a-tag>
                      </template>
                    </a-col>
                  </a-row>
                </template>
              </a-col>
            </a-row>
          </template>
        </div>

        <div class="box-buttons mt-5">
          <a-row type="flex" justify="center" align="middle">
            <a-col>
              <a-button
                type="primary"
                class="px-4 text-600"
                v-on:click="returnToProgram()"
                default
                :disabled="filesStore.isLoading"
                size="large"
              >
                {{ t('global.button.close') }}
              </a-button>
            </a-col>
          </a-row>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
  import { onBeforeMount, ref } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import {
    useFilesStore,
    useStatusesStore,
    useHaveInvoicesStore,
    useRevisionStagesStore,
    useItineraryStore,
  } from '@store/files';
  import { formatDate, checkDates, textPad } from '@/utils/files.js';
  import { useLanguagesStore } from '@store/global';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import HotelSelected from '@/components/files/reusables/HotelSelected.vue';
  import HotelMerge from '@/components/files/reusables/HotelMerge.vue';
  import HotelSearch from '@/components/files/reusables/HotelSearch.vue';
  import { useI18n } from 'vue-i18n';
  import dayjs from 'dayjs';
  import { notification } from 'ant-design-vue';

  const { t } = useI18n({
    useScope: 'global',
  });

  const router = useRouter();
  const route = useRoute();
  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();

  const languagesStore = useLanguagesStore();
  const statusesStore = useStatusesStore();
  const haveInvoicesStore = useHaveInvoicesStore();
  const revisionStagesStore = useRevisionStagesStore();
  const selected = ref([]);
  const step = ref(0);
  const reservation = ref({});

  const showLanguage = () => {
    return (
      languagesStore.getLanguages.find((item) => item.value === filesStore.getFile.lang).label ?? ''
    );
  };

  const showMessage = (_step) => {
    let message = t('global.label.finalized');

    if (step.value < _step) {
      message = t('global.label.on_hold');
    }

    if (step.value == _step) {
      message = t('global.label.in_progress');
    }

    return message;
  };

  const returnToProgram = () => {
    router.push({ name: 'files-edit', params: route.params });
  };

  onBeforeMount(async () => {
    const { id, hotel_id } = route.params;

    if (typeof filesStore.getFile.id == 'undefined') {
      await statusesStore.fetchAll();
      await haveInvoicesStore.fetchAll();
      await revisionStagesStore.fetchAll();

      await filesStore.getById({ id });
      await filesStore.getPassengersById({ fileId: id });
      await itineraryStore.getById({ fileId: id, itineraryId: hotel_id });
      const itinerary = itineraryStore.getItinerary;
      filesStore.updateItinerary(itinerary);
    }

    await filesStore.getFileItineraryById({ id, object_id: hotel_id });
    filesStore.finished();

    if (step.value == 0) {
      let itinerariesReplace = filesStore.getFileItinerariesReplace.length;

      for (let i = 0; i < itinerariesReplace; i++) {
        filesStore.removeFileItineraryReplace(i);
      }
    }
  });

  const nextStep = () => {
    if (step.value == 0) {
      if (flag_validate.value && asumed_by.value == '') {
        notification['warning']({
          message: `Error de penalidad`,
          description: 'Complete los datos de quién asume la penalidad',
          duration: 5,
        });

        return false;
      }
    }

    if (step.value < 2) {
      step.value++;
    }
  };

  const prevStep = () => {
    step.value--;
  };

  const loading_selected = ref(false);

  const changeSelected = (data) => {
    loading_selected.value = true;
    selected.value = data;

    setTimeout(() => {
      loading_selected.value = false;
    }, 10);
  };

  const flag_validate = ref(false);
  const asumed_by = ref('');
  const executive_id = ref('');
  const file_id = ref('');
  const motive = ref('');

  const changeAsumed = (_data) => {
    flag_validate.value = _data.flag_validate;
    asumed_by.value = _data.asumed_by;
    executive_id.value = _data.executive_id;
    file_id.value = _data.file_id;
    motive.value = _data.motive;
  };

  import { useSocketsStore } from '@/stores/global';
  const socketsStore = useSocketsStore();

  const loadReservation = async (data) => {
    reservation.value = data.reservation_merged;

    if (flag_validate.value) {
      data.params.status_reason_id = asumed_by.value;
      data.params.penality_executive_id = executive_id.value;
      data.params.penality_file_id = file_id.value;
      data.params.penality_motive = motive.value;
    }

    socketsStore.send({
      type: 'processing-reservation',
      file_id: filesStore.getFile.id,
    });
    await filesStore.add_modify(data.params);
    nextStep();
  };
</script>
