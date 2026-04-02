<template>
  <template v-if="filesStore.isLoading">
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
      <a-step :title="showMessage(0)" description="Servicio y Hoteles con penalidad" />
      <a-step :title="showMessage(1)" description="Comunicaciones" />
      <a-step :title="showMessage(2)" description="Activación completa" />
    </a-steps>

    <div v-if="step == 0">
      <div class="d-flex justify-content-between align-items-center mt-5">
        <h4 class="title">
          <i class="bi bi-arrow-repeat text-danger rotate-120"></i> Activar File
        </h4>
        <div class="actions">
          <a-button
            v-on:click="returnToProgram()"
            class="text-600"
            danger
            :loading="filesStore.isLoading || filesStore.isLoadingAsync"
            size="large"
          >
            <span v-bind:class="[filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : '']">
              {{ t('global.button.return_to_program') }}
            </span>
          </a-button>
          <a-button
            type="primary"
            class="btn-danger mx-2 px-4 text-600"
            v-on:click="nextStep()"
            default
            :loading="filesStore.isLoading || filesStore.isLoadingAsync"
            size="large"
          >
            <span v-bind:class="[filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : '']">
              {{ t('global.button.continue') }}
            </span>
          </a-button>
        </div>
      </div>

      <div class="bg-white bordered p-4 my-4">
        <FileHeader v-bind:data="filesStore.getFile" v-bind:editable="false" />
      </div>

      <simulation-service-list />

      <div
        class="my-3"
        v-if="filesStore.getSimulations.some((simulation) => simulation.type === 'hotel')"
      >
        <a-row type="flex" justify="end" align="middle">
          <a-col>
            <a-button
              type="default"
              class="mx-2 px-4 text-600"
              v-on:click="returnToProgram()"
              default
              :loading="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              <span v-bind:class="[filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : '']"
                >Cancelar</span
              >
            </a-button>
            <a-button
              type="primary"
              default
              class="mx-2 px-4 text-600"
              v-on:click="nextStep()"
              :loading="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              <span v-bind:class="[filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : '']"
                >Continuar</span
              >
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>

    <div v-if="step == 1">
      <div class="d-flex justify-content-between align-items-center mt-5 mb-5">
        <div class="title">
          <font-awesome-icon :icon="['far', 'comments']" />
          {{ t('files.label.communication_to_provider') }}
        </div>
        <div class="actions">
          <a-button
            v-on:click="returnToProgram()"
            class="text-600"
            danger
            :loading="filesStore.isLoading || filesStore.isLoadingAsync"
            size="large"
          >
            <span
              v-bind:class="[filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : '']"
              >{{ t('global.button.return_to_program') }}</span
            >
          </a-button>
        </div>
      </div>

      <template v-if="filesStore.getSimulations.length > 0">
        <template
          v-for="(_simulation, s) in filesStore.getSimulations"
          :key="'item-simulation-' + i"
        >
          <hotel-merge
            v-if="_simulation.type == 'hotel'"
            type="new"
            :filter="_simulation.params"
            :show_communication="true"
            :flag_simulation="true"
            :title="false"
            :to="[_simulation.hotel]"
            :buttons="false"
            ref="items"
          />

          <service-merge
            type="new"
            v-if="_simulation.type == 'service'"
            :filter="_simulation.params"
            :flag_simulation="true"
            :show_communication="true"
            :title="false"
            :to="[_simulation.service]"
            :buttons="false"
            ref="items"
          />
        </template>
      </template>

      <div class="box-buttons mt-5">
        <a-row type="flex" justify="end" align="middle">
          <a-col>
            <a-button
              type="default"
              class="mx-2 px-4 text-600"
              v-on:click="prevStep()"
              default
              :loading="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              <span
                v-bind:class="[filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : '']"
              >
                {{ t('global.button.cancel') }}
              </span>
            </a-button>
            <a-button
              type="primary"
              default
              class="mx-2 px-4 text-600"
              v-on:click="processReservation()"
              :loading="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              <span
                v-bind:class="[filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : '']"
              >
                {{ t('global.button.continue') }}
              </span>
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>

    <div v-if="step == 2">
      <div class="mt-5 pt-5">
        <div class="text-center">
          <h2 class="text-danger text-800">
            Activación de file {{ filesStore.getFile.fileNumber }} exitosa
          </h2>
          <div class="my-5">
            <a-row type="flex" justify="center" align="middle">
              <a-col>
                <svg
                  style="color: #1ed790"
                  class="feather feather-check-circle"
                  xmlns="http://www.w3.org/2000/svg"
                  width="5rem"
                  height="5rem"
                  fill="none"
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  viewBox="0 0 24 24"
                >
                  <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                  <path d="M22 4 12 14.01l-3-3" />
                </svg>
              </a-col>
            </a-row>
          </div>
        </div>

        <div class="box-buttons mt-5">
          <a-row type="flex" justify="center" align="middle">
            <a-col>
              <a-button
                type="primary"
                class="px-4 text-600"
                v-on:click="returnToProgram()"
                default
                :loading="filesStore.isLoading || filesStore.isLoadingAsync"
                size="large"
              >
                <span :class="filesStore.isLoading || filesStore.isLoadingAsync ? 'ms-2' : ''">
                  {{ t('global.button.close') }}
                </span>
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
  } from '@store/files';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import FileHeader from '@/components/files/reusables/FileHeader.vue';
  import HotelMerge from '@/components/files/reusables/HotelMerge.vue';
  import ServiceMerge from '@/components/files/reusables/ServiceMerge.vue';
  import SimulationServiceList from '@/components/files/reusables/SimulationServiceList.vue';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const router = useRouter();
  const route = useRoute();
  const filesStore = useFilesStore();

  const statusesStore = useStatusesStore();
  const haveInvoicesStore = useHaveInvoicesStore();
  const revisionStagesStore = useRevisionStagesStore();

  const step = ref(0);
  // const status_reason_id = ref('');
  const status_reasons = ref([]);

  const items = ref(null);

  const showMessage = (_step) => {
    let message = 'Finalizado';

    if (step.value < _step) {
      message = 'En espera';
    }

    if (step.value == _step) {
      message = 'En proceso';
    }

    return message;
  };

  const returnToProgram = () => {
    router.push({ name: 'files-edit', params: route.params });
  };

  onBeforeMount(async () => {
    const { id } = route.params;

    if (typeof filesStore.getFile.id == 'undefined') {
      await statusesStore.fetchAll();
      await haveInvoicesStore.fetchAll();
      await revisionStagesStore.fetchAll();

      await filesStore.getById({ id });
    } else {
      filesStore.finished();
    }

    await filesStore.fetchStatusReasons();
    filesStore.calculatePenality();

    filesStore.getStatusReasons.forEach((reason) => {
      if (reason.status_iso != 'XL') {
        status_reasons.value.push(reason);
      }
    });
  });

  const prevStep = () => {
    step.value--;
  };

  const nextStep = () => {
    step.value++;
  };

  /*
  const loadReservation = async (data, flag_handler) => {
    console.log('HANDLER: ', flag_handler);

    if (filesStore.getFile.fileNumber == 0) {
      data.params.reservation_add.entity = 'File';
      data.params.reservation_add.object_id = filesStore.getFile.id;
    }

    await filesStore.add_modify(data.params, true);
  };
  */

  const processReservation = async () => {
    let params = {};
    let reservations = [];
    let reservations_services = [];
    let notifications = [];

    for (const item of items.value) {
      const data = await item.processReservation(true);

      if (filesStore.getFile.fileNumber == 0) {
        data.params.reservation_add.entity = 'File';
        data.params.reservation_add.object_id = filesStore.getFile.id;
      }

      notifications.push({
        attachments: data.params.attachments || [],
        notas: data.params.notas || '',
      });

      // Log para depuración
      params = data.params.reservation_add;

      // Agregar reservas dependiendo del tipo
      if (data.params.type === 'hotel') {
        reservations = reservations.concat(data.params.reservation_add.reservations || []);
      }

      if (data.params.type === 'service') {
        reservations_services = reservations_services.concat(
          data.params.reservation_add.reservations_services || []
        );
      }
    }

    // Actualizar `params` con las reservas combinadas
    params = {
      ...params,
      notifications,
      reservations,
      reservations_services,
    };

    await filesStore.activate(filesStore.getFile.id, params);
    nextStep();
  };
</script>
