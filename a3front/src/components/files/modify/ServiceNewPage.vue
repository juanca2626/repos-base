<template>
  <div>
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
        <a-step :title="showMessage(1)" :description="t('files.label.communication_to_provider')" />
        <a-step :title="showMessage(2)" :description="t('files.label.complete_reservation')" />
      </a-steps>
      <template v-if="step !== 2">
        <div v-show="step == 0">
          <div class="d-flex justify-content-between align-items-center pt-5 pb-5">
            <div class="title">
              <font-awesome-icon :icon="['far', 'square-plus']" class="text-dark-gray" />
              {{ t('files.button.add') }} {{ t('files.button.service') }}
            </div>
            <div class="actions">
              <a-button
                danger
                :class="['text-600', filesStore.isLoadingAsync ? 'ps-2' : '']"
                type="default"
                v-on:click="returnToProgram()"
                :loading="filesStore.isLoadingAsync"
                size="large"
              >
                <span :class="[filesStore.isLoadingAsync ? 'ps-2' : '']">
                  {{ t('global.button.return_to_program') }}
                </span>
              </a-button>
            </div>
          </div>
          <service-search @onReturnToProgram="returnToProgram" @onNextStep="nextStep" />
        </div>
      </template>

      <template v-if="step == 1">
        <div class="d-flex justify-content-between align-items-center pt-5 pb-5">
          <div class="title">
            <font-awesome-icon :icon="['far', 'comments']" />
            {{ t('files.label.communication_to_provider') }}
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

        <service-merge
          :to="filesStore.getFileItinerariesServicesReplace"
          type="new"
          @onLoadReservation="loadReservation"
          @onPrevStep="prevStep"
          @onNextStep="nextStep"
        />
      </template>

      <template v-if="step == 2">
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
              <a-col
                flex="auto"
                v-if="reservation?.notes_to != '' && reservation?.notes_to != null"
              >
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
                  <b>Fecha de Servicio:</b>
                  <span>{{ dayjs(item.date_from).format('DD/MM/YYYY') }}</span>
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
                  <b>Total Tarifa:</b> <span class="text-danger">USD {{ item.price }}</span>
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
      </template>
    </div>
  </div>
</template>

<script setup>
  import { onBeforeMount, ref } from 'vue';
  import {
    useFilesStore,
    useStatusesStore,
    useHaveInvoicesStore,
    useRevisionStagesStore,
  } from '@/stores/files';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import ServiceSearch from '@/components/files/reusables/ServiceSearch.vue';
  import ServiceMerge from '@/components/files/reusables/ServiceMerge.vue';
  import { useRouter, useRoute } from 'vue-router';

  import { useI18n } from 'vue-i18n';
  import dayjs from 'dayjs';
  const { t } = useI18n({
    useScope: 'global',
  });

  const returnToProgram = () => {
    router.push({ name: 'files-edit', params: router.params });
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

  const step = ref(0); //Step number
  const reservation = ref({});

  const router = useRouter();
  const route = useRoute();
  const filesStore = useFilesStore();
  // const languagesStore = useLanguagesStore();

  const statusesStore = useStatusesStore();
  const haveInvoicesStore = useHaveInvoicesStore();
  const revisionStagesStore = useRevisionStagesStore();

  onBeforeMount(async () => {
    const { id } = route.params;

    if (typeof filesStore.getFile.id == 'undefined') {
      await statusesStore.fetchAll();
      await haveInvoicesStore.fetchAll();
      await revisionStagesStore.fetchAll();

      await filesStore.getById({ id });
      await filesStore.getPassengersById({ fileId: id });
    }

    filesStore.finished();
  });

  const nextStep = () => {
    if (step.value === 2) {
      return false;
    }

    step.value++;
  };

  const prevStep = () => {
    step.value--;
  };

  import { useSocketsStore } from '@/stores/global';
  const socketsStore = useSocketsStore();

  const loadReservation = async (data) => {
    reservation.value = data.reservation;
    if (filesStore.getFile.fileNumber == 0) {
      data.params.reservation_add.entity = 'File';
      data.params.reservation_add.object_id = filesStore.getFile.id;
    }

    socketsStore.send({
      type: 'processing-reservation',
      file_id: filesStore.getFile.id,
    });
    await filesStore.add_modify(data.params);
    nextStep();
  };
</script>
