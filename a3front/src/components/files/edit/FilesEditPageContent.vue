<template>
  <div class="files-edit p-0 m-0">
    <a-alert v-if="filesStore.getFile.processing" type="warning">
      <template #message>
        <a-row type="flex" justify="space-between" align="middle" style="gap: 15px" class="my-2">
          <a-col>
            <font-awesome-icon
              :icon="['fas', 'spinner']"
              spin
              size="xl"
              class="text-dark-warning"
            />
          </a-col>
          <a-col flex="auto">
            <b class="text-dark-warning">{{ t('files.message.processing_file') }}</b>
          </a-col>
        </a-row>
      </template>
      <template #description>
        <p class="mb-0 d-inline">
          {{ t('files.message.content_processing_file') }}
        </p>
        <p class="mb-0">{{ t('files.message.thanks_for_patience') }}</p>
      </template>
    </a-alert>
  </div>
  <div class="position-relative">
    <div
      class="blur"
      v-if="filesStore.getFile.processing"
      style="
        position: absolute;
        width: 100%;
        height: 100%;
        background-color: #f6f6f6;
        z-index: 1;
        opacity: 0.45;
      "
    >
      <!-- div style="position: absolute; top: 50%; left: 50%; margin-top: -30px; margin-left: -30px;">
        <font-awesome-icon :icon="['fas', 'circle-notch']" spin style="font-size:30px;" />
      </div -->
    </div>

    <a-badge-ribbon
      color="#EB5757"
      class="cursor-pointer"
      style="position: absolute; z-index: 999; top: -7px; padding: 3px 5px"
      v-if="
        socketsStore.getNotifications.filter(
          (item) =>
            (item?.file_id === filesStore.getFile.id ||
              item?.file_number === filesStore.getFile.fileNumber) &&
            item.flag_show &&
            item.type === 'update_file' &&
            item.action !== 'delete' &&
            item.action !== 'new'
        ).length > 0
      "
      @click="toggleViewStatusHeader()"
    >
      <template #text>
        <span>
          <a-popover
            placement="bottomRight"
            v-if="
              socketsStore.getNotifications.filter(
                (item) =>
                  (item?.file_id === filesStore.getFile.id ||
                    item?.file_number === filesStore.getFile.fileNumber) &&
                  item.flag_show &&
                  item.type === 'update_file' &&
                  item.action !== 'delete' &&
                  item.action !== 'new'
              ).length > 0
            "
          >
            <template #title>
              <small class="text-uppercase">
                <font-awesome-icon :icon="['fas', 'arrows-rotate']" spin />
                {{ t('files.notification.update_file') }}</small
              >
            </template>
            <template #content>
              <div class="pe-2" style="max-height: 220px; overflow-y: auto">
                <template
                  v-for="item in socketsStore.getNotifications.filter(
                    (item) =>
                      (item?.file_id === filesStore.getFile.id ||
                        item?.file_number === filesStore.getFile.fileNumber) &&
                      item.flag_show &&
                      item.type === 'update_file' &&
                      item.action !== 'delete' &&
                      item.action !== 'new'
                  )"
                >
                  <p class="mb-0">
                    <font-awesome-icon
                      :icon="['far', item.success ? 'thumbs-up' : 'thumbs-down']"
                      :class="item.success ? 'text-success' : 'text-danger'"
                    />
                    {{ truncateString(t(item.description), 90) }}
                    <small v-if="!item.success" class="text-600 text-uppercase"
                      >({{ t(item.message) }})</small
                    >
                  </p>
                  <p class="mb-0 text-dark-gray" style="font-size: 0.9rem">
                    <a-row type="flex" justify="start" align="middle" style="gap: 5px">
                      <a-col>
                        <small v-if="item.user_code">
                          <font-awesome-icon :icon="['far', 'circle-user']" class="me-1" />
                          <b>{{ item.user_code }}</b>
                        </small>
                        <small v-else>
                          <font-awesome-icon :icon="['fas', 'robot']" class="me-1" />
                          <b>Aurora BOT</b>
                        </small>
                      </a-col>
                      <a-col>
                        <small v-if="item?.date && item?.time">
                          <font-awesome-icon :icon="['far', 'clock']" class="me-1" />
                          <b>{{ formatDate(item.date) }} {{ item.time }}</b>
                        </small>
                      </a-col>
                    </a-row>
                  </p>
                </template>
              </div>
            </template>
            <font-awesome-icon :icon="['fas', 'circle-question']" fade />
          </a-popover>
          <template v-else>
            <span v-if="itinerary.isNew">
              <font-awesome-icon :icon="['fas', 'bolt']" fade />
            </span>
            <span v-else>
              <font-awesome-icon :icon="['fas', 'bullseye']" fade />
            </span>
          </template>
        </span>
      </template>
    </a-badge-ribbon>

    <div
      class="files-edit files-edit__border"
      :style="
        socketsStore.getNotifications.filter(
          (item) =>
            (item?.file_id === filesStore.getFile.id ||
              item?.file_number === filesStore.getFile.fileNumber) &&
            item.flag_show &&
            item.type === 'update_file' &&
            item.action !== 'delete' &&
            item.action !== 'new'
        ).length > 0
          ? `border-color:#EB5757; border-width: 2px;`
          : ''
      "
    >
      <loading-skeleton v-if="filesStore.isLoading || filesStore.isLoadingBasic" />
      <template v-else>
        <FileHeader
          v-if="filesStore.getFile?.id > 0"
          :editable="true"
          @onHandleOpenStatementView="openStatementView"
          @onHandleGoToPaxs="goToPaxs"
          @onRefreshCache="handleRefreshCache"
        />
      </template>
    </div>

    <div class="files-edit files-edit__border">
      <a-skeleton active v-if="filesStore.isLoading" />
      <template v-else>
        <a-tabs v-model:activeKey="activeKey" @change="toggleViewStatus()">
          <a-tab-pane key="1" :class="{ 'block-style': !flagEditable }">
            <template #tab>
              {{ t('files.label.program') }}
            </template>
            <div v-if="parseInt(activeKey) === 1">
              <template v-if="filesStore.isLoadingAsync || itineraryStore.isLoadingAsync">
                <a-progress :percent="showPercentItineraries" status="active" class="pe-4 mb-3" />
                <loading-skeleton />
              </template>
              <template v-else>
                <FilesEditServiceList
                  @onRefreshCache="handleRefreshCache"
                  @onHandleGoToReservations="goToReservationInquiry"
                  :data="{ activeKey: activeKey }"
                  @onHandleGoReservationInquiry="goToReservationInquiry"
                />
              </template>
            </div>
          </a-tab-pane>
          <a-tab-pane
            key="2"
            :tab="t('files.label.passengers')"
            :class="{ 'block-style': !flagEditable }"
          >
            <FilesEditPaxs />
          </a-tab-pane>
          <a-tab-pane key="3" :class="{ 'block-style': !flagEditable }">
            <template #tab>
              {{ t('files.label.flights') }}
            </template>
            <div v-if="parseInt(activeKey) === 3">
              <template v-if="filesStore.isLoadingAsync || itineraryStore.isLoadingAsync">
                <a-progress :percent="showPercentItineraries" status="active" class="pe-4 mb-3" />
                <loading-skeleton />
              </template>
              <div v-show="!(filesStore.isLoadingAsync || itineraryStore.isLoadingAsync)">
                <FilesEditServiceList :data="{ activeKey: activeKey }" />
              </div>
            </div>
          </a-tab-pane>
          <!-- a-tab-pane
            key="4"
            :tab="t('files.label.statement')"
            v-if="filesStore.getFile.generateStatement"
            :class="{ 'block-style': !flagEditable }"
          >
            <FilesEditStatement v-if="parseInt(activeKey) === 4" />
          </a-tab-pane -->
          <!--<a-tab-pane key="5" tab="Cotización" :class="{ 'block-style': !flagEditable }">
            <FilesEditQuote />
          </a-tab-pane> -->
          <a-tab-pane
            key="6"
            force-render
            :tab="t('files.label.statement')"
            :class="{ 'block-style': !flagEditable && !reopen, 'except-block-style': reopen }"
          >
            <template v-if="filesStore.getFile?.id > 0 && filesStore.isLoadingBasic">
              <loading-skeleton />
            </template>
            <template v-else>
              <FilesStatements v-if="filesStore.getFile?.id > 0" />
            </template>
          </a-tab-pane>
          <a-tab-pane
            key="7"
            v-if="filesStore.getFile?.id > 0"
            :class="{ 'block-style': !flagEditable }"
          >
            <template #tab>
              {{ t('files.label.notes') }}
              <font-awesome-icon
                :icon="['fas', 'bullseye']"
                class="text-danger ms-2"
                fade
                v-if="serviceNotesStore.flag_change"
              />
            </template>
            <FilesNotes />
          </a-tab-pane>
          <a-tab-pane
            key="8"
            :tab="t('files.label.reservations')"
            :class="{ 'block-style': !flagEditable }"
          >
            <ReservationsPage />
          </a-tab-pane>
          <!--<a-tab-pane key="9" tab="Tareas" :class="{ 'block-style': !flagEditable }"></a-tab-pane>-->
        </a-tabs>
      </template>
    </div>
  </div>
</template>

<script setup>
  import { ref, onBeforeMount, computed } from 'vue';
  import FilesEditServiceList from '@/components/files/edit/FilesEditServiceList.vue';
  import FilesEditPaxs from '@/components/files/edit/FilesEditPaxs.vue';
  //import FilesEditQuote from '@/components/files/edit/FilesEditQuote.vue';
  import FilesStatements from '@/components/files/edit/FilesStatements.vue';
  import FilesNotes from '@/components/files/edit/FilesNotes.vue';
  import FileHeader from '@/components/files/reusables/FileHeader.vue';
  import LoadingSkeleton from '@/components/global/LoadingSkeleton.vue';
  import ReservationsPage from '@/components/files/reservations/PageView.vue';
  import { formatDate, truncateString } from '@/utils/files.js';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  import { useFilesStore, useItineraryStore, useServiceNotesStore } from '@store/files';
  import { useSocketsStore } from '@/stores/global';

  const socketsStore = useSocketsStore();
  // import { isAdmin } from '@/utils/auth';

  const filesStore = useFilesStore();
  const itineraryStore = useItineraryStore();
  const serviceNotesStore = useServiceNotesStore();

  const activeKey = ref('1');
  const reopen = ref(false);

  const openStatementView = () => {
    activeKey.value = '6';
  };

  const goToReservationInquiry = () => {
    activeKey.value = '8';
  };

  const goToPaxs = () => {
    activeKey.value = '2';
  };

  const handleRefreshCache = () => {
    /*
    socketsStore.send({
      type: 'processing-reservation',
      file_id: filesStore.getFile.id,
    });
    */
    /*
    notification.open({
      message: 'Solicitud Aceptada',
      description:
        'El proceso se está ejecutando en segundo plano. En un momento se actualizará el FILE. Por favor, espere..',
    });
    */
  };

  const flagEditable = ref(true);

  const isEditable = () => {
    if (!filesStore.getFile.statusReason) {
      return;
    }

    reopen.value =
      // filesStore.getFile.statusReason.toLowerCase().includes('reaperturado') ||
      filesStore.getFile.statusReason.toLowerCase().includes('reaperturado') &&
      filesStore.getFile.statusReasonId != 4;

    flagEditable.value = filesStore.getFile.status.toLowerCase() === 'ok' && !reopen.value;
  };

  onBeforeMount(() => {
    isEditable();
  });

  const showPercentItineraries = computed(() => {
    const count_success = filesStore.getFileItineraries.filter(
      (itinerary) => !itinerary.isLoading
    ).length;
    const total = filesStore.getFileItineraries.length;
    if (total === 0) return 0;
    const percent = parseFloat((count_success / total) * 100);
    return parseFloat(percent).toFixed(2);
  });

  const toggleViewStatus = () => {
    if (activeKey.value == 7) {
      serviceNotesStore.changeFlagChange();
    }
  };

  const toggleViewStatusHeader = () => {
    console.log('BORRANDO..');
    socketsStore.readNotificationsHeader(filesStore.getFile.id, filesStore.getFile.fileNumber);
  };
</script>
<style scoped lang="scss">
  .block-style * {
    opacity: 0.9;
    pointer-events: none;
  }

  /* Excluye los elementos con la clase 'except-block-style' */
  .block-style .except-block-style * {
    opacity: 1 !important;
    pointer-events: auto;
  }
</style>
