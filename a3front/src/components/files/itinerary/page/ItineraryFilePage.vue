<template>
  <div>
    <div class="files-edit__sort">
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
          <template #content>Volver a notas</template>
        </base-popover>
        <div class="title-module">Itinerario</div>
      </div>
      <div class="files-edit__sort-col2">
        <a-button type="default" size="large" class="btn-download">
          <font-awesome-icon :icon="['fas', 'clock-rotate-left']" />
        </a-button>
        <a-button
          type="default"
          size="large"
          class="btn-download"
          @click="showItineraryModalDownload"
        >
          <IconDownloadCloud color="#EB5757" width="22px" height="22px" />
        </a-button>
        <a-button type="primary" size="large" ghost class="btn-file">
          Reemplazar itinerario
        </a-button>
      </div>
    </div>

    <div class="files-edit__services mt-5">
      <!-- Tabs superiores -->
      <!-- Tabs dinámicos -->
      <a-tabs
        v-model:activeKey="activeTab"
        class="service-schedule-tabs"
        type="card"
        @change="handleTabChange"
      >
        <a-tab-pane v-for="option in languageOptions" :key="option.value" :tab="option.label">
          <div v-if="activeTab === option.value">
            <!-- Contenido dinámico basado en el idioma -->
            <ItineraryListComponent :language="option.value" :itineraries="itineraries" />
          </div>
        </a-tab-pane>
      </a-tabs>
    </div>
  </div>
  <ItineraryModalComponent
    v-bind:is-open.sync="modalIsOpenItinerary"
    @update:is-open="modalIsOpenItinerary = $event"
  />
</template>
<script setup lang="ts">
  import BasePopover from '@/components/files/reusables/BasePopover.vue';
  import { defineEmits, onMounted, ref } from 'vue';
  import IconDownloadCloud from '@/components/icons/IconDownloadCloud.vue';
  import ItineraryListComponent from '@/components/files/itinerary/component/ItineraryListComponent.vue';
  import ItineraryModalComponent from '@/components/files/itinerary/component/ItineraryModalComponent.vue';
  import { useFilesStore } from '@/stores/files';
  import { useRouter } from 'vue-router';

  const emit = defineEmits(['onBack']);
  const activeTab = ref('es'); // Idioma predeterminado (Español)
  const modalIsOpenItinerary = ref(false);
  const router = useRouter();
  const fileId = router.currentRoute.value.params.id;
  const fileStore = useFilesStore();
  const itineraries = ref([]);
  const goBackItineraryPage = () => {
    emit('onBack', false);
  };

  const languageOptions = ref([
    { label: 'Español', value: 'es' },
    { label: 'Inglés', value: 'en' },
    { label: 'Portugués', value: 'pt' },
  ]);

  const showItineraryModalDownload = () => {
    modalIsOpenItinerary.value = true;
  };

  const handleTabChange = (key: string) => {
    getItineraryDetails(key); // Llama a la función con la clave del tab activo
  };

  const getItineraryDetails = async (key: string) => {
    await fileStore.fetchItineraryDetails(fileId, key);
    itineraries.value = fileStore.getItineraryDetails;
  };

  onMounted(async () => {
    await fileStore.fetchItineraryDetails(fileId, 'es');
    itineraries.value = fileStore.getItineraryDetails;
  });
</script>

<style scoped lang="scss">
  .title-module {
    font-family: Montserrat, serif;
    font-size: 24px !important;
    margin-left: 10px;
  }

  .btn-download {
    display: flex;
    flex-direction: column;
    align-items: center;
    align-content: center;
    justify-content: center;
    background-color: #ffffff;
    color: #eb5757;
    width: 50px;
    height: 45px;
    margin-right: 25px;
    border-color: #eb5757;

    &::before {
      width: 0 !important;
      height: 0 !important;
    }

    &:hover {
      background-color: #fff6f6;
      color: #c63838 !important;
      border-color: #c63838;
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

  .btn-file {
    display: flex;
    flex-direction: column;
    align-items: center;
    align-content: center;
    justify-content: center;
    background-color: #ffffff;
    border-color: #eb5757;
    width: auto;
    height: 45px;
    margin-right: 10px;

    &::before {
      width: 0 !important;
      height: 0 !important;
    }

    &:hover {
      background-color: #fff6f6 !important;
      color: #c63838 !important;
      border-color: #c63838 !important;
    }
  }

  /* Tabs */

  .service-schedule-tabs {
    .tab-title {
      text-transform: capitalize !important;
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
