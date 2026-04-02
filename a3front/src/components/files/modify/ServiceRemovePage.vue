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
      <a-step :title="showMessage(0)" :description="t('files.label.service_penalty')" />
      <a-step
        v-if="filesStore.getFileItinerary.send_communication"
        :title="showMessage(1)"
        :description="t('files.label.communication_to_provider')"
      />
      <a-step :title="showMessage(2)" :description="t('files.label.complete_cancellation')" />
    </a-steps>

    <div v-if="step == 0">
      <div class="d-flex justify-content-between align-items-center pt-5 pb-5">
        <div class="title">
          <font-awesome-icon :icon="['far', 'trash-can']" size="lg" />
          {{ t('global.label.cancel_service') }}
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
      <!-- <a-alert type="error" class="mb-3" style="color: red">
        <template #description>
          <a-row type="flex" justify="start" align="top" style="gap: 10px">
            <a-col>
              <i class="bi bi-exclamation-circle" style="font-size: 18px"></i>
            </a-col>
            <a-col>
              <p class="text-700 mb-1 p-0">Servicio con penalidad</p>
              Realizar el cálculo de penalidades manual: Verifique las políticas<br />
            </a-col>
          </a-row>
        </template>
      </a-alert> -->
      <service-selected @onChangeAsumed="changeAsumed" />

      <div class="my-3">
        <a-row type="flex" justify="end" align="middle">
          <a-col>
            <a-button
              type="default"
              class="mx-2 px-4 text-600"
              v-on:click="returnToProgram()"
              default
              :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              {{ t('global.button.cancel') }}
            </a-button>
            <a-button
              type="primary"
              class="px-4 text-600"
              default
              v-on:click="nextStep()"
              :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              {{ t('global.button.continue') }}
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>

    <div v-if="step == 1">
      <div
        class="d-flex justify-content-between align-items-center pt-5 pb-5"
        v-if="filesStore.getFileItinerary.send_communication"
      >
        <div class="title">
          <font-awesome-icon :icon="['far', 'comments']" />
          {{ t('files.label.cancel_comunication_to_provider') }}
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
        type="cancellation"
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
          <h2 class="text-danger text-800">
            {{ filesStore.getFileItinerary.name }}
            {{ t('global.label.cancelled') }}
          </h2>
          <div class="my-5">
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
                :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
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
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import ServiceSelected from '@/components/files/reusables/ServiceSelected.vue';
  import ServiceMerge from '@/components/files/reusables/ServiceMerge.vue';
  import { useI18n } from 'vue-i18n';
  import { notification } from 'ant-design-vue';

  const { t } = useI18n({
    useScope: 'global',
  });

  const router = useRouter();
  const route = useRoute();
  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();

  const statusesStore = useStatusesStore();
  const haveInvoicesStore = useHaveInvoicesStore();
  const revisionStagesStore = useRevisionStagesStore();

  const service = ref(null);
  const step = ref(0);
  const flag_validate = ref(false);
  const file_id = ref('');
  const executive_id = ref('');
  const asumed_by = ref('');
  const motive = ref('');

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
  });

  const nextStep = async () => {
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
      setTimeout(async () => {
        const data = await service.value.processReservation(true);
        await loadReservation(data, true);
      }, 100);
    }
  };

  const prevStep = () => {
    step.value--;

    if (!filesStore.getFileItinerary.send_communication && step.value == 1) {
      step.value--;
    }
  };

  const changeAsumed = (_data) => {
    flag_validate.value = _data.flag_validate;
    asumed_by.value = _data.asumed_by;
    executive_id.value = _data.executive_id;
    file_id.value = _data.file_id;
    motive.value = _data.motive;
  };

  const loadReservation = async (data) => {
    if (flag_validate.value) {
      data.params.status_reason_id = asumed_by.value.value;
      data.params.penality_executive_id = executive_id.value;
      data.params.penality_file_id = file_id.value;
      data.params.penality_motive = motive.value;
    }

    await filesStore.delete(data.params);
    nextStep();
  };
</script>
