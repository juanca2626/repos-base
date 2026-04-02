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
      <a-step :title="showMessage(2)" description="Anulación completa" />
    </a-steps>

    <div v-if="step == 0">
      <div class="d-flex justify-content-between align-items-center mt-5">
        <h4 class="title">
          <font-awesome-icon :icon="['fas', 'circle-xmark']" class="text-dark-gray" /> Anular Masivo
        </h4>
        <div class="actions">
          <a-button
            v-on:click="returnToProgram()"
            class="text-600"
            danger
            :loading="filesStore.isLoading || filesStore.isLoadingAsync"
            size="large"
          >
            {{ t('global.button.return_to_program') }}
          </a-button>
          <a-button
            type="primary"
            class="btn-danger ms-2 px-4 text-600"
            v-on:click="nextStep()"
            default
            :loading="filesStore.isLoading || filesStore.isLoadingAsync"
            v-if="status_reason_id != ''"
            size="large"
          >
            {{ t('global.button.continue') }}
          </a-button>
        </div>
      </div>

      <div class="bg-light p-4 mt-3">
        <a-alert type="warning" showIcon class="mb-4">
          <template #icon><smile-outlined /></template>
          <template #message>
            <div class="text-dark-warning">
              Debes seleccionar un motivo para la anulación masiva.
            </div>
          </template>
        </a-alert>
        <a-form :model="formState" :label-col="labelCol" :wrapper-col="wrapperCol">
          <a-form-item>
            <template #label>
              <span class="text-600">Motivo para la anulación</span>
              <b class="text-danger px-2">*</b>
            </template>
            <a-select
              :allowClear="false"
              class="w-100"
              v-model:value="status_reason_id"
              :showSearch="true"
              :fieldNames="{ label: 'name', value: 'id' }"
              :options="status_reasons"
            >
            </a-select>
          </a-form-item>
        </a-form>
      </div>

      <div class="bg-white">
        <template
          v-for="(_hotel, h) in filesStore.getItinerariesTrash.filter(
            (itinerary) => itinerary.entity === 'hotel'
          )"
          :key="'penalty-hotel-' + h"
        >
          <hotel-merge
            :show_communication="false"
            type="cancellation"
            :flag_simulation="true"
            :flag_preview="false"
            :title="false"
            :from="_hotel"
            :buttons="false"
          />
        </template>

        <template
          v-for="(_service, s) in filesStore.getItinerariesTrash.filter(
            (itinerary) => itinerary.entity === 'service'
          )"
          :key="'penalty-service-' + s"
        >
          <service-merge
            :show_communication="false"
            type="cancellation"
            :flag_simulation="true"
            :flag_preview="true"
            :title="false"
            :from="_service"
            :buttons="false"
          />
        </template>
      </div>

      <div class="my-3">
        <a-row type="flex" justify="end" align="middle">
          <a-col>
            <a-button
              type="default"
              class="px-4 text-600"
              v-on:click="returnToProgram()"
              default
              :loading="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              {{ t('global.button.back') }}
            </a-button>
            <a-button
              type="primary"
              default
              class="ms-2 px-4 text-600"
              v-on:click="nextStep()"
              :loading="filesStore.isLoading || filesStore.isLoadingAsync"
              v-if="status_reason_id != ''"
              size="large"
            >
              {{ t('global.button.continue') }}
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
            :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
            size="large"
          >
            {{ t('global.button.return_to_program') }}
          </a-button>
        </div>
      </div>

      <template v-if="filesStore.getItinerariesTrash.length > 0">
        <div class="my-5 mx-2 px-4">
          <a-row type="flex" align="middle" justify="start" style="gap: 10px">
            <a-col>
              <font-awesome-icon
                :icon="['fas', 'triangle-exclamation']"
                shake
                size="xl"
                class="text-warning"
              />
            </a-col>
            <a-col>
              <span class="text-dark-warning text-uppercase text-600">Comunicaciones masivas</span>
            </a-col>
          </a-row>
        </div>

        <hotel-merge
          v-for="(itinerary, i) in filesStore.getItinerariesTrash.filter(
            (itinerary) => itinerary.entity === 'hotel'
          )"
          :key="'communication-hotel-' + i"
          :show_communication="true"
          :title="false"
          type="cancellation"
          :flag_simulation="true"
          :flag_preview="false"
          :from="itinerary"
          :buttons="false"
          ref="items"
        />

        <service-merge
          v-for="(itinerary, i) in filesStore.getItinerariesTrash.filter(
            (itinerary) => itinerary.entity === 'service'
          )"
          :key="'communication-service-' + i"
          :show_communication="true"
          :title="true"
          type="cancellation"
          :flag_simulation="true"
          :flag_preview="false"
          :from="itinerary"
          :buttons="false"
          ref="items"
        />
      </template>
      <template v-else>
        <a-alert type="warning">
          <template #message>
            <div class="text-warning">
              No hay comunicaciones disponibles para hoteles y/o servicios, se puede proceder con la
              cancelación.
            </div>
          </template>
        </a-alert>
      </template>

      <div class="my-3">
        <a-row type="flex" justify="end" align="middle">
          <a-col>
            <a-button
              type="default"
              class="px-4 text-600"
              v-on:click="prevStep()"
              default
              :loading="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              {{ t('global.button.back') }}
            </a-button>
            <a-button
              type="primary"
              default
              class="ms-2 px-4 text-600"
              v-on:click="processReservation()"
              :loading="filesStore.isLoading || filesStore.isLoadingAsync"
              size="large"
            >
              {{ t('global.button.continue') }}
            </a-button>
          </a-col>
        </a-row>
      </div>
    </div>

    <div v-if="step == 2">
      <div class="mt-5 pt-5">
        <div class="text-center">
          <h2 class="text-danger text-800">Anulación exitosa</h2>
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
  import { onBeforeMount, ref, watch } from 'vue';
  import { useRouter, useRoute } from 'vue-router';
  import { notification } from 'ant-design-vue';
  import {
    useFilesStore,
    useStatusesStore,
    useHaveInvoicesStore,
    useRevisionStagesStore,
  } from '@store/files';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import HotelMerge from '@/components/files/reusables/HotelMerge.vue';
  import ServiceMerge from '@/components/files/reusables/ServiceMerge.vue';
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

  const items = ref([]);
  const step = ref(0);
  const formState = ref({
    user: '',
    password: '',
  });
  const status_reason_id = ref('');
  const status_reasons = ref([]);

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

  function sleep(ms) {
    return new Promise((resolve) => setTimeout(resolve, ms));
  }

  watch(
    () => items.value.length,
    async () => {
      if (step.value == 1) {
        console.log('SERVICIOS:', items.value);
        for (const refService of items.value) {
          await refService?.handleCancellationType?.();
          if (typeof refService?.handleCancellationType?.() === 'function') {
            await sleep(500); // Aquí sí se espera correctamente
          }
        }
      }
    }
  );

  onBeforeMount(async () => {
    const { id } = route.params;

    if (filesStore.getItinerariesTrash.length == 0) {
      let route = 'files-edit';
      let params = {
        id: filesStore.getFile.id,
      };

      router.push({ name: route, params: params });
      return;
    }

    if (typeof filesStore.getFile.id == 'undefined') {
      await statusesStore.fetchAll();
      await haveInvoicesStore.fetchAll();
      await revisionStagesStore.fetchAll();

      await filesStore.getById({ id });
    }

    let flag_continue = true;

    filesStore.getItinerariesTrash.map((itinerary) => {
      if (!itinerary.confirmation_status && flag_continue) {
        notification.error({
          message: 'Error al anular',
          description: `El ${itinerary.name} del día ${itinerary.date_in} no se encuentra confirmado. Por lo que no podemos anular el File.`,
        });

        flag_continue = false;
      }
    });

    if (!flag_continue) {
      let route = 'files-edit';
      let params = {
        id: filesStore.getFile.id,
      };

      router.push({ name: route, params: params });
    }

    await filesStore.fetchStatusReasons();
    await filesStore.calculatePenality();

    await filesStore.getStatusReasons.forEach(async (reason) => {
      if (reason.status_iso == 'XL') {
        status_reasons.value.push(reason);
      }
    });
    filesStore.finished();
  });

  watch(
    () => step.value,
    (newValue) => {
      if (newValue == 0) {
        filesStore.clearPenality();
      }
    },
    { immediate: true }
  );

  const nextStep = () => {
    step.value++;
  };

  const prevStep = () => {
    step.value--;
  };

  const loadReservation = async (data) => {
    console.log('DATA: ', data);

    if (filesStore.getFile.fileNumber == 0) {
      data.params.reservation_add.entity = 'File';
      data.params.reservation_add.object_id = filesStore.getFile.id;
    }

    await filesStore.delete(data.params);
  };

  const processReservation = async () => {
    filesStore.inited();
    const reservationPromises = items.value.map(async (item) => {
      const data = await item.processReservation(true);
      console.log('ITEM: ', data);
      await loadReservation(data);
    });

    // return false;

    // Espera a que todas las promesas se resuelvan
    await Promise.all(reservationPromises);
    filesStore.finished();

    if (filesStore.getError != '') {
      notification.error({
        message: 'Error',
        description:
          'Los elementos seleccionados no se pudieron anular. Por favor, actualice la página e inténtelo de nuevo.',
      });
    } else {
      filesStore.clearItinerariesTrash();
      nextStep();
    }
  };
</script>
