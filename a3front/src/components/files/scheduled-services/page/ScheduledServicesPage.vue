<template>
  <div>
    <div class="files-edit__sort" style="margin-top: 0">
      <div class="files-edit__sort-col1">
        <base-popover placement="topLeft">
          <a-button
            @click="goBackItineraryPage()"
            class="btn-primary"
            type="primary"
            default
            size="large"
          >
            <font-awesome-icon :icon="['fas', 'arrow-left']" />
          </a-button>
          <template #content>Volver al programa</template>
        </base-popover>
        <div class="title-module">
          <IconBookOpen color="#575757" width="1.2em" height="1.2em" class="icon" />
          Servicios programados
        </div>
      </div>
      <div class="files-edit__sort-col2">
        <a-button
          type="default"
          size="large"
          class="btn-download text-dark-gray"
          @click="showServiceScheduledDownload"
        >
          <font-awesome-icon :icon="['fas', 'cloud-arrow-down']" size="lg" />
        </a-button>
        <a-button type="default" size="large" class="btn-share">
          <font-awesome-icon :icon="['fas', 'share-nodes']" size="lg" />
        </a-button>
      </div>
    </div>

    <div class="files-edit__services">
      <!-- Tabs superiores -->
      <a-tabs v-model:activeKey="activeTab" class="service-schedule-tabs" type="card">
        <a-tab-pane key="programming">
          <template #tab>
            <span class="tab-title">Programación</span>
          </template>
          <ServiceScheduledProgramming />
        </a-tab-pane>
        <!-- Incidencias tab -->
        <a-tab-pane key="incidents">
          <template #tab>
            <span class="tab-title">Incidencias</span>
          </template>
          <div class="tab-content">
            <ServiceScheduledIncidents />
          </div>
        </a-tab-pane>
      </a-tabs>
    </div>
  </div>
  <ServiceScheduledModalProgramming
    v-bind:is-open.sync="modalIsOpenServiceScheduled"
    @update:is-open="modalIsOpenServiceScheduled = $event"
  />
</template>
<script setup lang="ts">
  import BasePopover from '@/components/files/reusables/BasePopover.vue';
  import IconBookOpen from '@/components/icons/IconBookOpen.vue';
  import { defineEmits, ref } from 'vue';
  import ServiceScheduledProgramming from '@/components/files/scheduled-services/components/ServiceScheduledProgramming.vue';
  import ServiceScheduledIncidents from '@/components/files/scheduled-services/components/ServiceScheduledIncidents.vue';
  import ServiceScheduledModalProgramming from '@/components/files/scheduled-services/components/ServiceScheduledModalProgramming.vue';

  const emit = defineEmits(['onBack']);
  const activeTab = ref('programming');
  const modalIsOpenServiceScheduled = ref(false);

  const goBackItineraryPage = () => {
    emit('onBack', false);
  };

  const showServiceScheduledDownload = () => {
    modalIsOpenServiceScheduled.value = true;
  };
</script>

<style scoped lang="scss">
  .title-module {
    margin-left: 10px;
  }

  .btn-download {
    display: flex;
    flex-direction: column;
    align-items: center;
    align-content: center;
    justify-content: center;
    background-color: #fafafa;
    border-color: #fafafa;
    width: 50px;
    height: 45px;
    margin-right: 25px;

    &::before {
      width: 0 !important;
      height: 0 !important;
    }

    &:hover {
      background-color: #e9e9e9;
      color: #575757;
      border-color: #e9e9e9;
    }
  }

  .btn-share {
    display: flex;
    flex-direction: column;
    align-items: center;
    align-content: center;
    justify-content: center;
    background-color: #ffffff;
    color: #eb5757;
    width: 50px;
    height: 45px;
    border-color: #eb5757;

    &:hover {
      background-color: #fff6f6;
      color: #c63838 !important;
      border-color: #c63838;
    }
  }

  /* Tabs */

  .service-schedule-tabs {
    .tab-title {
      text-transform: capitalize;
    }

    :deep(.ant-tabs-nav-list) {
      background-color: #ffffff !important;
    }

    :deep(.ant-tabs-tab-btn) {
      text-transform: capitalize !important;
      color: #ffffff !important;
    }

    :deep(.ant-tabs-tab) {
      background-color: #e9e9e9;
      color: #979797 !important;
      font-weight: 600;
      text-transform: capitalize !important;
      border-top-left-radius: 6px;
      border-top-right-radius: 6px;
      font-size: 14px !important;
      margin-left: 10px !important;
    }

    :deep(.ant-tabs-tab):nth-child(1) {
      margin-left: 15px !important;
    }

    :deep(.ant-tabs-tab-active) {
      background-color: #737373 !important;
      text-transform: capitalize;
      border-top-left-radius: 6px !important;
      border-top-right-radius: 6px !important;
    }

    :deep(.ant-tabs-nav) {
      margin-bottom: 0 !important;

      ::before {
        bottom: 0 !important;
      }
    }
  }
</style>
