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
      <a-step :title="showMessage(0)" :description="t('files.label.hotel_penalty')" />
      <a-step :title="showMessage(1)" :description="t('files.label.communication_to_provider')" />
      <a-step :title="showMessage(2)" :description="t('files.label.complete_cancellation')" />
    </a-steps>

    <div v-if="step == 0">
      <div class="d-flex justify-content-between align-items-center pt-5 pb-5">
        <div class="title">
          <font-awesome-icon :icon="['far', 'trash-can']" size="lg" />
          {{ t('global.label.cancel_room') }}
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

      <hotel-selected
        @onChangeSelected="changeSelected"
        @onChangeAsumed="changeAsumed"
        :type="'room'"
        :editable="false"
      />

      <div class="my-3">
        <a-row type="flex" justify="end" align="middle">
          <a-col>
            <a-button
              type="default"
              class="mx-2 px-4 text-600"
              v-on:click="returnToProgram()"
              default
              :disabled="filesStore.isLoading"
              size="large"
            >
              {{ t('global.button.cancel') }}
            </a-button>
            <a-button
              type="primary"
              v-if="selected.length > 0"
              class="px-4 text-600"
              default
              v-on:click="nextStep()"
              :disabled="filesStore.isLoading"
              size="large"
            >
              {{ t('global.button.continue') }}
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>

    <div v-if="step == 1">
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

      <hotel-merge
        :from="filesStore.getFileItinerary"
        :selected="selected"
        type="cancellation"
        @onLoadReservation="loadReservation"
        @onPrevStep="prevStep"
        @onNextStep="nextStep"
      />
    </div>

    <div v-if="step == 2">
      <div class="pt-5 pb-5">
        <div class="text-center">
          <h2 class="text-danger text-800">
            {{ t('global.label.hotel') }} {{ filesStore.getFileItinerary.name }}
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
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import HotelSelected from '@/components/files/reusables/HotelSelected.vue';
  import HotelMerge from '@/components/files/reusables/HotelMerge.vue';
  import { notification } from 'ant-design-vue';
  import { useI18n } from 'vue-i18n';

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

  const step = ref(0);
  const selected = ref([]);

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
    const { id, hotel_id, room_id } = route.params;

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

    await filesStore.getFileItineraryByRoomId({ object_id: room_id });
    filesStore.finished();
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

    step.value++;
  };

  const prevStep = () => {
    step.value--;
  };

  const flag_validate = ref(false);
  const asumed_by = ref('');
  const motive = ref('');

  const changeAsumed = (_data) => {
    flag_validate.value = _data.flag_validate;
    asumed_by.value = _data.asumed_by;
    motive.value = _data.motive;
  };

  const changeSelected = (_data) => {
    selected.value = _data;
  };

  const loadReservation = async (data) => {
    await filesStore.delete(data.params);
    nextStep();
  };
</script>
