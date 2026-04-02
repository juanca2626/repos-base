<template>
  <div>
    <div v-if="!viewItineraryFile">
      <div class="files-edit__sort">
        <div class="files-edit__sort-col1">
          <div class="title-module">{{ t('global.label.file_information') }}</div>
          <!--<a-button type="primary" size="large" ghost class="btn-file">
            <IconFilePlus color="#EB5757" width="1.2em" height="1.2em" />
          </a-button>-->
          <base-popover placement="topLeft">
            <a-button type="primary" size="large" ghost class="btn-file" @click="showSkeletonFile">
              <IconClipBoard color="#EB5757" width="1.2em" height="1.2em" />
            </a-button>
            <template #content>Skeleton</template>
          </base-popover>
          <base-popover placement="topLeft">
            <a-button
              type="primary"
              size="large"
              ghost
              class="btn-file"
              @click="showListHotelsDownload()"
            >
              <font-awesome-icon :icon="['fas', 'hotel']" />
            </a-button>
            <template #content>{{ t('files.label.hotel_list') }}</template>
          </base-popover>
          <a-button type="primary" size="large" ghost class="btn-file" @click="showItineraryFile">
            {{ t('files.label.itinerary') }}
          </a-button>
          <!-- COMENTADO PARA LA SIGUIENTE FASE -->
          <!-- <a-button type="link" primary class="link-notes-client" @click="toggleNoteClient">
            <div class="link-notes">
              {{ showNoteClient ? 'Ocultar nota del cliente' : 'Mostrar nota del cliente' }}
            </div>
          </a-button> -->
        </div>
        <div class="files-edit__sort-col2">
          <!-- COMENTADO PARA LA SIGUIENTE FASE -->
          <base-button type="primary" size="large" @click="openModalNotes">
            <div>
              <span>{{ t('files.button.add_notes') }}</span>
            </div>
          </base-button>
          <ModalNotes :open-modal="modalNotesOpen" @close-modal="handleCloseModal" />
        </div>
      </div>

      <div class="files-edit__services">
        <!-- Tabs superiores -->
        <FileInformationGeneral :show-note-client="showNoteClient" />
      </div>
    </div>
    <div v-if="viewItineraryFile">
      <ItineraryFilePage @onBack="goBackNotesPage" />
    </div>
  </div>
  <ModalListHotelsDownload
    v-bind:is-open.sync="modalIsOpenListHotels"
    @update:is-open="modalIsOpenListHotels = $event"
  />
  <SkeletonModal
    v-bind:is-open.sync="modalIsOpenSkeleton"
    @update:is-open="modalIsOpenSkeleton = $event"
  />
</template>
<script setup lang="ts">
  import { ref } from 'vue';
  import SkeletonModal from '@/components/files/skeleton/components/SkeletonModal.vue';
  import BaseButton from '@/components/files/reusables/BaseButton.vue';
  //import IconFilePlus from '@/components/icons/IconFilePlus.vue';
  import IconClipBoard from '@/components/icons/IconClipBoard.vue';
  import ItineraryFilePage from '@/components/files/itinerary/page/ItineraryFilePage.vue';
  import ModalListHotelsDownload from '@/components/files/hotels/components/ModalListHotelsDownload.vue';
  import BasePopover from '@/components/files/reusables/BasePopover.vue';
  import FileInformationGeneral from '@/components/files/edit/FileInformationGeneral.vue';
  import ModalNotes from '@/components/files/notes/ModalNotes.vue';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const modalIsOpenListHotels = ref(false);
  const modalIsOpenSkeleton = ref(false);
  const viewItineraryFile = ref(false);
  const showNoteClient = ref(false);
  const modalNotesOpen = ref(false);

  const showListHotelsDownload = () => {
    modalIsOpenListHotels.value = true;
  };

  const showSkeletonFile = () => {
    modalIsOpenSkeleton.value = true;
  };

  const goBackNotesPage = () => {
    viewItineraryFile.value = false;
  };

  const showItineraryFile = () => {
    viewItineraryFile.value = true;
  };

  const openModalNotes = () => {
    modalNotesOpen.value = true;
  };

  const handleCloseModal = (newValue: boolean) => {
    modalNotesOpen.value = newValue;
  };
  // const toggleNoteClient = () => {
  //   showNoteClient.value = !showNoteClient.value;
  // };
</script>

<style scoped lang="scss">
  .title-module {
    margin-left: 10px;
    margin-right: 58px;
  }

  .link-notes-client {
    font-family: Montserrat, serif;
    font-weight: 500;
    font-size: 12px;
    color: #c63838;

    &:hover {
      color: #eb5757;
    }

    .link-notes {
      text-decoration: underline;
      text-underline-position: under;
      text-underline-offset: 2px;
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
</style>
