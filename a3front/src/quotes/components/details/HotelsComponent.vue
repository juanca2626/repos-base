<script lang="ts" setup>
  import { onMounted, ref, toRef } from 'vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import type { QuoteService, ServiceRoom } from '@/quotes/interfaces';
  import { useQuote } from '@/quotes/composables/useQuote';
  import ModalRoomAdd from '@/quotes/components/modals/ModalRoomAdd.vue';
  import HotelRoom from '@/quotes/components/details/HotelRoom.vue';
  import HotelRoomRate from '@/quotes/components/details/HotelRoomRate.vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const { setQuoteHotelRoomAccommodation } = useQuote();

  interface Props {
    service: QuoteService;
    serviceGroup: QuoteService[];
  }

  const props = defineProps<Props>();

  const service = toRef(props, 'service');
  const serviceGroup = toRef(props, 'serviceGroup');

  const getRoomsByType = (roomTypeId: number): QuoteService[] => {
    return serviceGroup.value.filter(
      (r) => r.service_rooms[0].rate_plan_room.room.room_type_id === roomTypeId
    );
  };
  const getRoomsGroup = (serviceRoom: ServiceRoom) => {
    return getRoomsByType(serviceRoom.rate_plan_room.room.room_type_id);
  };
  const getRoomTypeId = (serviceRoom: ServiceRoom) => {
    return serviceRoom.rate_plan_room.room.room_type_id;
  };

  interface Emits {
    (e: 'removeService', service: QuoteService): void;

    (e: 'editRoomSearch', service: QuoteService): void;
  }

  const emits = defineEmits<Emits>();
  const removeService = async (service: QuoteService) => {
    emits('removeService', service);
  };

  const editServiceRoom = (service: QuoteService) => {
    emits('editRoomSearch', service);
  };
  const setRemoveByTypeRoom = (serviceRoom: ServiceRoom) => {
    removeService({
      ...service.value,
      type: 'group_type_room',
      type_room_id: getRoomTypeId(serviceRoom),
    });
  };

  const serviceRoomAccommodation = ref<boolean[]>([]);

  onMounted(() => {
    for (let { id } of serviceGroup.value) {
      serviceRoomAccommodation.value[id] = false;
    }
  });

  // add rooms
  const showRoomAddModal = ref<boolean>(false);

  const openRoomAddModal = () => {
    showRoomAddModal.value = true;
  };

  const closeRoomAddModal = () => {
    showRoomAddModal.value = false;
  };
</script>

<template>
  <div class="detail-header">
    <div class="title" @click="openRoomAddModal">
      <font-awesome-icon icon="code-compare" />
      <span>{{ t('quote.label.rooms') }}</span>
    </div>
  </div>
  <div class="detail-body">
    <div class="rooms">
      <hotel-room
        v-for="serviceRoom of service.service_rooms"
        :key="serviceRoom.id"
        :room-type="serviceRoom"
        :rooms="getRoomsGroup(serviceRoom)"
        @remove-room-type="setRemoveByTypeRoom"
      >
        <template #rooms="{ rooms }">
          <hotel-room-rate
            v-for="room of rooms"
            :key="room.id"
            :room="room"
            @remove-room="removeService"
            @edit-room="editServiceRoom"
            @update-room-accommodation="setQuoteHotelRoomAccommodation"
          />
        </template>
      </hotel-room>
    </div>
  </div>

  <modal-room-add v-if="showRoomAddModal" :service="service" @close="closeRoomAddModal" />
</template>

<style lang="scss" scoped>
  .detail-header {
    display: flex;
    height: 24px;
    padding-right: 0;
    flex-direction: column;
    align-items: flex-start;
    align-self: stretch;

    .title {
      display: flex;
      align-items: center;
      gap: 5px;
      flex: 1 0 0;
      cursor: pointer;

      span {
        color: #212529;
        font-size: 12px;
        font-style: normal;
        font-weight: 600;
        line-height: 19px;
        letter-spacing: 0.18px;
        text-decoration: underline;
      }
    }
  }

  .openSideBar {
    .detail-body .rooms .room .top {
      width: 92%;
      margin: 0 auto;
    }
  }

  .detail-body {
    display: flex;
    padding-bottom: 0;
    flex-direction: column;
    align-items: flex-start;
    align-self: stretch;

    .rooms {
      display: flex;
      padding: 10px 0;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      align-self: stretch;
    }
  }
</style>
