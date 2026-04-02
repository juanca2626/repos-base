<template>
  <a-row type="flex" align="middle" justify="center">
    <a-col>
      <a-tag v-if="record.status == 'RQ'" color="error" :bordered="true"> <b>RQ</b> </a-tag>
      <a-tag v-if="record.status == 'OK'" color="success" :bordered="true"> <b>OK</b> </a-tag>
      <a-tag v-if="record.status == 'WL'" color="warning" :bordered="true"> <b>WL</b> </a-tag>
    </a-col>
    <a-col v-if="record.rq_total < record.quantity && record.quantity > 1">
      <a-tag> {{ record.rq_total }} / {{ record.quantity }} </a-tag>
    </a-col>
    <a-col v-else>
      <template v-if="record.status == 'OK'">
        <template v-if="isPromotion(record.date_in)">
          <IconMoneySearch v-if="type == 'hotel'" @click="searchPromotions(record)" />
        </template>
      </template>
      <template v-if="record.status == 'RQ'">
        <span @click="flagModalWL = true" class="cursor-pointer">
          <a-tooltip>
            <template #title>
              <small class="text-uppercase">Waiting List</small>
            </template>
            <font-awesome-icon :icon="['far', 'clock']" fade />
          </a-tooltip>
        </span>
      </template>
      <template v-if="record.status == 'WL'">
        <span class="cursor-pointer" @click="changeStatusWLOK(record)">
          <a-tooltip>
            <template #title>Confirmado</template>
            <font-awesome-icon :icon="['far', 'thumbs-up']" />
          </a-tooltip>
        </span>
      </template>
    </a-col>
  </a-row>

  <a-modal v-model:visible="flagModalWL" :width="520" id="reservations-modal">
    <template #title>
      <a-row type="flex" justify="space-between" align="middle" class="m-3 px-4">
        <a-col>
          <span class="d-block bg-warning text-white p-3 border-2 rounded">WL</span>
        </a-col>
        <a-col>
          <h6 class="m-0" style="font-size: 16px !important">
            Modificación manual de estado a Waiting List
          </h6>
          <span class="text-dark-gray text-400" style="font-size: 14px !important"
            >¿Esta seguro de modificar el estado a Waiting list?</span
          >
        </a-col>
      </a-row>
    </template>
    <div class="bg-light p-4 mx-3">
      <a-row type="flex" justify="space-between" align="middle">
        <a-col>
          <a-row type="flex" justify="start" align="middle" style="gap: 7px">
            <a-col>
              <span>
                <i class="bi bi-building-fill" style="font-size: 16px"></i>
              </span>
            </a-col>
            <a-col>
              <span class="ellipsis d-block">
                <b>{{ record.hotel_name }}</b>
              </span>
            </a-col>
            <a-col>
              <a-tag color="#c63838"> {{ record.city }} </a-tag>
            </a-col>
          </a-row>
          <a-row type="flex" justify="start" align="middle" style="gap: 7px" class="mt-2">
            <a-col>
              <a-tag color="#c63838"> {{ record.status }} </a-tag>
            </a-col>
            <a-col>
              <a-tag> {{ record.quantity }} </a-tag>
            </a-col>
            <a-col>
              <span class="text-dark-gray">{{ record.room_name }}</span>
            </a-col>
          </a-row>
        </a-col>
        <a-col>
          <a-row type="flex" justify="space-between" align="middle" style="gap: 20px">
            <a-col>
              <small class="m-0 text-dark-gray">Salida:</small>
              <h6 class="text-dark m-0">
                <b style="font-size: 18px">10:00</b>
                <small class="text-dark-gray" style="font-size: 12px">HRS</small>
              </h6>
              <small class="m-0" style="font-size: 12px">{{ record.date_in }}</small>
            </a-col>
            <a-col>
              <small class="m-0 text-dark-gray">Llegada:</small>
              <h6 class="text-dark m-0">
                <b style="font-size: 18px">16:00</b>
                <small class="text-dark-gray" style="font-size: 12px">HRS</small>
              </h6>
              <small class="m-0" style="font-size: 12px">{{ record.date_out }}</small>
            </a-col>
          </a-row>
        </a-col>
      </a-row>
      <div
        v-if="record.units.length > 1"
        v-bind:class="['d-flex text-dark-gray text-400 my-2 cursor-pointer']"
        v-on:click="toggleStatus()"
        style="gap: 7px"
      >
        <i
          v-bind:class="['bi', !flag_status ? 'bi-square' : 'bi-check-square-fill text-danger']"
          style="font-size: 16px"
        ></i>
        <span style="font-size: 16px">Estados por habitación</span>
      </div>
    </div>

    <div class="bg-light p-4 m-3" v-if="!flag_status">
      <a-row type="flex" justify="space-between" align="middle">
        <a-col>
          <span class="text-dark-gray text-400"
            >Código WL de
            {{ record.units.length > 1 ? ' habitaciones' : ' habitación' }}
          </span>
        </a-col>
        <a-col>
          <a-input v-model:value="record.confirmation_code" style="width: 120px" />
        </a-col>
      </a-row>
    </div>

    <div class="bg-light p-4 m-3" v-if="flag_status">
      <a-row
        type="flex"
        v-for="(unit, u) in record.units"
        justify="space-between"
        align="middle"
        class="my-3"
      >
        <a-col>
          <a-tag color="#c63838"> {{ unit.status }} </a-tag>
          <i class="bi bi-arrow-right me-2"></i>
          <a-tag color="#FFC107"> WL </a-tag>
        </a-col>
        <a-col>
          <span class="text-dark-gray text-400">{{ unit.room_name }}</span>
        </a-col>
        <a-col>
          <template v-if="unit.status == 'OK'">
            <a-input
              v-model:value="unit.confirmation_code"
              placeholder="Código de confirmación"
              style="width: 120px"
            />
          </template>
          <template v-else>
            <a-input
              v-model:value="unit.waiting_confirmation_code"
              placeholder="Código de confirmación (WL)"
              style="width: 120px"
            />
          </template>
        </a-col>
      </a-row>
    </div>

    <!-- div class="bg-light p-4 m-3">
      <a-form
        layout="horizontal"
        :label-col="{ span: 5, align: 'left' }"
        :wrapper-col="{ span: 19 }"
      >
        <a-form-item label="Motivo">
          <a-select
            size="large"
            :options="motives"
            v-model:value="record.motive"
            showSearch
            label-in-value
            :filter-option="false"
            :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          >
          </a-select>
        </a-form-item>
        <a-form-item label="Especifica">
          <a-textarea :rows="4" v-model:value="record.message_motive" />
        </a-form-item>
        <p class="m-0">
          <small class="text-dark-gray text-400"
            >No esperes más! Cambia el hotel o el tipo de habitación por una con alloment desde el
            siguiente enlace:</small
          >
        </p>
        <p class="m-0">
          <small class="d-block text-danger text-right">
            <a href="javascript:;">Ir a modificar el hotel</a>
          </small>
        </p>
      </a-form>
    </div -->

    <template #footer>
      <div class="text-center">
        <a-button
          type="default"
          default
          @click="handleCancel"
          v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          {{ t('global.button.cancel') }}
        </a-button>
        <a-button
          type="primary"
          primary
          :loading="loading"
          @click="handleProccess"
          v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          {{ t('global.button.modify') }}
        </a-button>
      </div>
    </template>
  </a-modal>

  <a-modal
    :width="1300"
    v-model:visible="showModalPromotionHotels"
    :maskClosable="false"
    class="text-center"
  >
    <template #title>
      <a-row type="flex" justify="start" align="middle" style="gap: 7px" class="px-4 mt-4">
        <a-col>
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 512 512"
            fill="#000"
            width="1rem"
            height="1rem"
            class="d-flex"
          >
            <path
              d="M505 442.7l-99.7-99.7c-4.5-4.5-10.6-7-17-7h-16.3c27.6-35.3 44-79.7 44-128C416 93.1 322.9 0 208 0S0 93.1 0 208s93.1 208 208 208c48.3 0 92.7-16.4 128-44v16.3c0 6.4 2.5 12.5 7 17l99.7 99.7c9.4 9.4 24.6 9.4 33.9 0l28.3-28.3c9.4-9.4 9.4-24.6 .1-34zm-297-90.7c-79.5 0-144-64.3-144-144 0-79.5 64.4-144 144-144 79.5 0 144 64.3 144 144 0 79.5-64.4 144-144 144zm27.1-152.5l-45-13.5c-5.2-1.6-8.8-6.8-8.8-12.7 0-7.3 5.3-13.2 11.8-13.2h28.1c4.6 0 9 1.3 12.8 3.7 3.2 2 7.4 1.9 10.1-.7l11.8-11.2c3.5-3.4 3.3-9.2-.6-12.1-9.1-6.8-20.1-10.8-31.4-11.4V112c0-4.4-3.6-8-8-8h-16c-4.4 0-8 3.6-8 8v16.1c-23.6 .6-42.7 20.6-42.7 45.1 0 20 13 37.8 31.6 43.4l45 13.5c5.2 1.6 8.8 6.8 8.8 12.7 0 7.3-5.3 13.2-11.8 13.2h-28.1c-4.6 0-9-1.3-12.8-3.7-3.2-2-7.4-1.9-10.1 .7l-11.8 11.2c-3.5 3.4-3.3 9.2 .6 12.1 9.1 6.8 20.1 10.8 31.4 11.4V304c0 4.4 3.6 8 8 8h16c4.4 0 8-3.6 8-8v-16.1c23.6-.6 42.7-20.5 42.7-45.1 0-20-13-37.8-31.6-43.4z"
            />
          </svg>
        </a-col>
        <a-col>
          <h5 class="mb-0 text-700" style="font-size: 18px !important">Promociones</h5>
        </a-col>
      </a-row>
    </template>
    <div id="files-layout">
      <div class="files-edit p-0">
        <hotel-search :showHeader="false" :showFilters="false" />
      </div>
    </div>
    <template #footer>
      <span class="d-block" style="margin-top: -24px"></span>
    </template>
  </a-modal>
</template>

<script setup>
  import { ref } from 'vue';
  import dayjs from 'dayjs';
  import HotelSearch from '@/components/files/reusables/HotelSearch.vue';
  import { useI18n } from 'vue-i18n';
  import IconMoneySearch from '@/components/icons/IconMoneySearch.vue';
  import { useFilesStore } from '@/stores/files';

  const filesStore = useFilesStore();

  const { t } = useI18n({
    useScope: 'global',
  });

  const flagModalWL = ref(false);
  const flag_status = ref(false);
  const showModalPromotionHotels = ref(false);

  const isPromotion = (dateIn) => {
    return false;
    return dateIn > dayjs().format('YYYY-MM-DD');
  };

  const props = defineProps({
    record: {
      type: String,
      default: () => {},
    },
    type: {
      type: String,
      default: () => `service`,
    },
  });

  const toggleStatus = () => {
    flag_status.value = !flag_status.value;
  };

  const handleCancel = () => {
    flagModalWL.value = false;
  };

  const handleProccess = async () => {
    const params = {
      waiting_reason_id: record.motive,
      waiting_reason_other_message: record.message_motive,
      waiting_confirmation_code: record.waiting_confirmation_code,
    };

    await filesStore.updateStatusRQWL(props.type, record.id, params);
    flagModalWL.value = false;
  };

  const changeStatusWLOK = async (record) => {
    await filesStore.updateStatusWLOK(props.type, record.id, record.confirmation_code);
  };

  const searchPromotions = async (_itinerary) => {
    console.log('ITINERARY: ', _itinerary);
    if (!filesStore.isLoadingAsync) {
      showModalPromotionHotels.value = true;

      let client_id = localStorage.getItem('client_id');
      let lang = localStorage.getItem('lang');

      if (client_id == '' || client_id == null) {
        client_id = 15766;
      }

      let params = {
        client_id: client_id,
        date_from: _itinerary.date_in,
        date_to: _itinerary.date_out,
        quantity_persons_rooms: [],
        quantity_rooms: _itinerary.unit_total,
        zero_rates: true,
        hotels_search_code: '',
        allWords: false,
        lang: lang,
        price_range: {
          min: 0,
          max: 950,
        },
        destiny: {
          code: `PE,${_itinerary.city}`,
          label: '',
        },
      };

      await filesStore.fetchHotels(params, true);
    } else {
      notification['warning']({
        message: `Búsqueda de hoteles con promoción`,
        description: 'Se está realizando la búsqueda, espere un momento..',
        duration: 5,
      });
    }
  };
</script>

<style lang="scss">
  #reservations-modal {
    .ant-modal-body {
      padding-right: 0 !important;
      padding-left: 0 !important;
    }
  }
</style>
