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
      <a-step
        :title="showMessage(1)"
        v-if="filesStore.getFileItinerary.send_communication"
        :description="t('files.label.communication_to_provider')"
      />
      <a-step :title="showMessage(2)" :description="t('files.label.complete_modification')" />
    </a-steps>

    <template v-if="step != 2">
      <div v-show="step == 0">
        <div class="d-flex justify-content-between align-items-center pt-5 pb-5">
          <div class="title">
            <font-awesome-icon :icon="['fas', 'pen-to-square']" class="text-dark-gray" />
            {{ t('global.label.modify_service') }}
          </div>
          <div class="actions">
            <a-button
              v-on:click="returnToProgram()"
              class="text-600 btn-default btn-back"
              :disabled="filesStore.isLoading"
              size="large"
            >
              {{ t('global.button.return_to_program') }}
            </a-button>
            <a-button
              v-on:click="goToCreateServiceTemporary()"
              class="text-600 btn-temporary"
              danger
              :disabled="filesStore.isLoading"
              size="large"
              v-if="isAdmin()"
            >
              <IconStopWatch color="#EB5757" width="1.3em" height="1.3em" />
              Crear servicio temporal
            </a-button>
          </div>
        </div>
        <service-selected @onChangeAsumed="changeAsumed" />
        <service-search @onNextStep="nextStep" @onReturnToProgram="returnToProgram" />
      </div>
    </template>

    <div v-if="step == 1">
      <div
        class="d-flex justify-content-between align-items-center pt-5 pb-5"
        v-if="filesStore.getFileItinerary.send_communication"
      >
        <div class="title">
          <font-awesome-icon :icon="['far', 'comments']" />
          {{ t('files.label.communication_to_provider') }}
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

      <service-merge
        :from="filesStore.getFileItinerary"
        :to="filesStore.getFileItinerariesServicesReplace"
        v-show="filesStore.getFileItinerary.send_communication"
        type="modification"
        ref="service"
        :flag_preview="true"
        :flag_simulation="false"
        :show_communication="true"
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
          v-for="(item, index) in reservation.to"
          :key="index"
        >
          <a-row type="flex" align="middle" justify="space-between" class="mx-5">
            <a-col class="me-4">
              <span class="text-danger">
                <b>Detalle del servicio agregado</b>
              </span>
            </a-col>
            <a-col flex="auto" v-if="reservation?.notes_to != '' && reservation?.notes_to != null">
              <span class="bg-white px-3 py-2 bordered w-100 ant-row-middle">
                <span class="d-flex mx-1">
                  <i class="bi bi-pencil"></i>
                </span>
                <span class="text-danger text-600">Notas para el proveedor:</span>
                <span class="mx-2">{{ reservation.notes_to }}</span>
              </span>
            </a-col>
          </a-row>
          <a-row type="flex" align="top" justify="space-between" class="my-3 mx-5">
            <a-col>
              <p class="d-flex" style="gap: 5px">
                <b>Nombre del servicio:</b>
                <span>{{ item.name }}</span>
              </p>
              <!-- p class="d-flex" style="gap: 5px">
                  <b>Proveedor:</b>
                  <span>{{ filesStore.getFile.description }}</span>
                </p -->
              <p class="d-flex" style="gap: 5px">
                <b>Fecha de Reserva:</b>
                <span>{{ dayjs().format('DD/MM/YYYY') }}</span>
              </p>
              <p class="d-flex" style="gap: 5px">
                <b>Cantidad de pax: </b>
                <font-awesome-icon icon="fa-solid fa-user" />
                <span>{{ item.quantity_adults }} {{ t('global.label.adults') }}</span>
                <font-awesome-icon icon="fa-solid fa-child-reaching" />
                <span>{{ item.quantity_child }} {{ t('global.label.children') }}</span>
              </p>
              <p class="d-flex" style="gap: 5px" v-if="item.communications.length > 0">
                <b>Detalles del servicio:</b>
              </p>

              <div class="ms-5" v-for="communication in item.communications">
                <p>
                  <font-awesome-icon :icon="['fas', 'user-check']" class="text-success" />
                  Proveedor:
                  <b>{{ communication.code_request_book }} - {{ communication.supplier_name }}</b>
                </p>
                <template v-if="communication.notas">
                  <p class="mb-0"><b>Notas al proveedor:</b></p>
                  <p>{{ communication.notas }}</p>
                </template>
              </div>
            </a-col>
          </a-row>
          <a-row type="flex" align="middle" class="mx-5">
            <a-col :span="12">
              <p class="mb-0">
                <b>Total Tarifa:</b> <span class="text-danger text-700">USD {{ item.price }}</span>
              </p>
            </a-col>
          </a-row>
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
  <PenaltyAssignmentModal
    :is-open="modalIsOpenPenalty"
    @update:is-open="modalIsOpenPenalty = $event"
  />
</template>

<script setup>
  import { onBeforeMount, ref } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import {
    useFilesStore,
    useHaveInvoicesStore,
    useRevisionStagesStore,
    useStatusesStore,
    useItineraryStore,
  } from '@store/files';
  // import { formatDate } from '@/utils/files.js';
  // import { useLanguagesStore } from '@store/global';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import ServiceSelected from '@/components/files/reusables/ServiceSelected.vue';
  import ServiceMerge from '@/components/files/reusables/ServiceMerge.vue';
  import ServiceSearch from '@/components/files/reusables/ServiceSearch.vue';
  import { useI18n } from 'vue-i18n';
  //import IconStopWatch from '@/components/icons/IconStopWatch.vue';
  import dayjs from 'dayjs';
  import PenaltyAssignmentModal from '@/components/files/temporary/components/PenaltyAssignmentModal.vue';
  import { notification } from 'ant-design-vue';
  import { isAdmin } from '@/utils/auth';

  const { t } = useI18n({
    useScope: 'global',
  });

  const router = useRouter();
  const route = useRoute();
  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();

  // const languagesStore = useLanguagesStore();
  const statusesStore = useStatusesStore();
  const haveInvoicesStore = useHaveInvoicesStore();
  const revisionStagesStore = useRevisionStagesStore();

  const service = ref(null);
  const step = ref(0);
  const reservation = ref({});
  const modalIsOpenPenalty = ref(false);

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

  /* const goToCreateServiceTemporary = () => {
    if (filesStore.getFileItinerary.penality === 0) {
      const currentId = router.currentRoute.value.params.id; // Obtenemos el :id desde la URL
      const currentServiceId = router.currentRoute.value.params.service_id; // Obtenemos el :service_id desde la URL
      localStorage.setItem('currentStep', '0');
      router.push({
        name: 'files-add-service-temporary',
        params: {
          id: currentId, // Usamos el id actual
          service_id: currentServiceId, // Usamos el service_id actual
        },
      });
    } else {
      showModalServiceHasPenalty();
    }
  }; */

  onBeforeMount(async () => {
    const { id, service_id } = route.params;

    if (typeof filesStore.getFile.id == 'undefined') {
      await statusesStore.fetchAll();
      await haveInvoicesStore.fetchAll();
      await revisionStagesStore.fetchAll();

      await filesStore.getById({ id });
      await filesStore.getPassengersById({ fileId: id });
      await itineraryStore.getById({ fileId: id, itineraryId: service_id });
      const itinerary = itineraryStore.getItinerary;
      filesStore.updateItinerary(itinerary);
    }

    await filesStore.getFileItineraryById({ id, object_id: service_id });
    filesStore.finished();

    if (step.value == 0) {
      let itinerariesReplace = filesStore.getFileItinerariesServicesReplace.length;

      for (let i = 0; i < itinerariesReplace; i++) {
        filesStore.removeFileItineraryServiceReplace(i);
      }
    }
  });

  const nextStep = () => {
    if (step.value === 2) {
      return false;
    }

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

    step.value++;

    if (!filesStore.getFileItinerary.send_communication && step.value == 1) {
      return false;
    }

    setTimeout(async () => {
      if (!filesStore.getFileItinerary.send_communication && step.value === 1) {
        const data = await service.value.processReservation(true);
        await loadReservation(data, true);
      }
    }, 100);
  };

  const prevStep = () => {
    step.value--;

    if (!filesStore.getFileItinerary.send_communication && step.value == 1) {
      step.value--;
    }
  };

  const flag_validate = ref(false);
  const asumed_by = ref('');
  const motive = ref('');

  const changeAsumed = (_data) => {
    flag_validate.value = _data.flag_validate;
    asumed_by.value = _data.asumed_by;
    motive.value = _data.motive;
  };

  import { useSocketsStore } from '@/stores/global';
  const socketsStore = useSocketsStore();

  const loadReservation = async (data) => {
    reservation.value = data.reservation;

    if (data.type === 'new') {
      socketsStore.send({
        type: 'processing-reservation',
        file_id: filesStore.getFile.id,
      });
      await filesStore.add_modify(data.params);
    }

    if (data.type === 'cancellation') {
      await filesStore.delete(data.params);
    }

    nextStep();
  };

  /*const showModalServiceHasPenalty = () => {
    modalIsOpenPenalty.value = true;
  };*/
</script>
<style scoped lang="scss">
  .actions {
    display: flex;
    justify-content: flex-end;
    gap: 25px;
  }

  .btn-temporary {
    width: auto;
    height: 45px;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 14px;
    font-weight: 600 !important;
    color: #eb5757 !important;
    border: 1px solid #eb5757 !important;

    svg {
      margin-right: 10px;
    }

    &:hover {
      color: #eb5757 !important;
      background-color: #fff6f6 !important;
      border: 1px solid #eb5757 !important;
    }
  }

  .btn-back {
    width: auto;
    height: 45px;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 14px;
    font-weight: 600 !important;
    color: #575757 !important;
    border: 1px solid #fafafa !important;

    &:hover {
      color: #575757 !important;
      background-color: #e9e9e9 !important;
      border: 1px solid #e9e9e9 !important;
    }
  }
</style>
