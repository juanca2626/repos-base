<template>
  <template v-if="cash.paxCount !== departure.totalPax">
    <a-alert type="warning">
      <template #description>
        La salida tuvo una modificación en la cantidad de PAX. El requerimiento se realizó con
        <b>{{ cash.paxCount }} PAX(s)</b> originalmente.
      </template>
    </a-alert>
  </template>
  <a-timeline :class="cash.paxCount !== departure.totalPax ? '' : 'mt-3'">
    <template v-for="(history, _h) in cash.statusHistory" :key="_h">
      <a-timeline-item>
        <a-row type="flex" justify="space-between" align="middle">
          <a-col>
            <span>
              <b class="text-uppercase">{{ getStatus(history.status) }}</b> el
              {{ moment(history.timestamp).format('DD/MM/YYYY HH:mm') }} por
              <b>{{ history.user }}</b>
            </span>
          </a-col>
          <a-col v-if="history.status === 'REQUESTED' || history.status === 'DELIVERED'">
            <a href="javascript:;" @click="handleResendNotification(cash)">
              <SendOutlined /> <small class="text-uppercase"><i>Reenviar Notificación</i></small>
            </a>
          </a-col>
        </a-row>
      </a-timeline-item>
    </template>
  </a-timeline>
</template>

<script setup>
  import moment from 'moment';
  import { defineProps } from 'vue';
  import { useDepartures } from '@/composables/adventure';
  import { SendOutlined } from '@ant-design/icons-vue';

  const { departure, getStatus } = useDepartures();

  const emit = defineEmits(['resendNotification']);

  const handleResendNotification = (cash) => {
    emit('resendNotification', cash);
  };

  defineProps({
    cash: {
      type: Object,
      required: true,
    },
  });
</script>
