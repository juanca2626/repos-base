<template>
  <div class="status-tag-wrapper" @mouseenter="isHovered = true" @mouseleave="isHovered = false">
    <a-flex gap="small" align="center" justify="center" horizontal>
      <div :class="['status-dot-shadow', dotClass]"></div>

      <div style="line-height: 16px; text-align: left">
        <div class="status-title" :style="{ color: color }">
          {{ label }}
        </div>
        <span v-if="order.followUps?.length > 0" :style="{ color: color, 'font-size': '11px' }">
          {{ order.followUps[order.followUps.length - 1].template.name }}
        </span>
        <div
          v-if="order.latestQuotation?.file?.number"
          data-closable="true"
          data-close="false"
          data-color="green"
          data-icon="false"
          style="
            width: 100%;
            height: 100%;
            padding-left: 8px;
            padding-right: 8px;
            padding-top: 1px;
            padding-bottom: 1px;
            background: #dfffe9;
            border-radius: 6px;
            outline: 1px #1ed790 solid;
            outline-offset: -1px;
            justify-content: flex-start;
            align-items: center;
            gap: 3px;
            display: inline-flex;
          "
          class="mt-1"
        >
          <span
            style="
              color: #44b089;
              font-size: 10px;
              font-family: Montserrat;
              font-weight: 500;
              line-height: 17px;
              letter-spacing: 0.15px;
              word-wrap: break-word;
            "
          >
            File: {{ order.latestQuotation?.file?.number }}
          </span>
        </div>
      </div>

      <a-tooltip v-if="isHovered" title="Editar estado">
        <font-awesome-icon
          :icon="['fas', 'pen-to-square']"
          class="edit-icon"
          @click="showUpdateStatusModal(order)"
        />
      </a-tooltip>
    </a-flex>
  </div>
</template>

<script setup>
  import { computed, ref } from 'vue';

  const props = defineProps({
    currentStatus: {
      type: Object,
      required: true,
    },
    order: {
      type: Object,
      default: () => ({}),
    },
  });

  const emit = defineEmits(['update-status']);
  const isHovered = ref(false);
  const showUpdateStatusModal = (order) => emit('update-status', order);

  const statusMap = {
    PE: {
      color: '#FFC107',
      dotClass: 'dot-warning',
    },
    EP: {
      color: '#5C5AB4',
      dotClass: 'dot-processing',
    },
    NC: {
      color: '#D80404',
      dotClass: 'dot-error',
    },
    CO: {
      color: '#1ED790',
      dotClass: 'dot-success',
    },
    EN: {
      color: '#51a5fe',
      dotClass: 'dot-info',
    },
  };

  const currentStatusInfo = computed(() => {
    const statusClass = props.currentStatus?.iso || 'PE';
    return statusMap[statusClass] || {};
  });
  const label = computed(() => props.currentStatus?.name || 'Desconocido');
  const color = computed(() => currentStatusInfo.value.color || '#ccc');
  const dotClass = computed(() => currentStatusInfo.value.dotClass || 'dot-default');
</script>

<style scoped>
  .status-tag-wrapper {
    cursor: pointer;
    padding: 5px;
  }

  .edit-icon {
    margin: 0;
    padding: 0;
    cursor: pointer;
    color: #3d3d3d;
    &:hover {
      color: #d80404;
      outline: none;
    }
  }

  .status-dot-shadow {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    box-shadow: 0 0 0 2px #0000001a;
  }

  .dot-success {
    background-color: #1ed790;
    box-shadow: 0 0 0 2px #1ed79040;
  }

  .dot-error {
    background-color: #d80404;
    box-shadow: 0 0 0 2px #d8040440;
  }

  .dot-processing {
    background-color: #5c5ab4;
    box-shadow: 0 0 0 2px #5c5ab440;
  }

  .dot-warning {
    background-color: #ffc107;
    box-shadow: 0 0 0 2px #ffc10740;
  }

  .dot-info {
    background-color: #51a5fe;
    box-shadow: 0 0 0 2px #b3ddff;
  }

  .status-title {
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.05px;
    line-height: 1;
    text-align: left;
  }
</style>
