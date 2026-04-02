<template>
  <div class="quotes-actions">
    <ButtonOutlineContainer
      icon="window-close"
      :text="t('quote.label.close')"
      @click="toggleModalClose"
      :disabled="processing"
      v-if="!quote.file.file_code"
    />
    <ButtonOutlineContainer
      icon="trash-can"
      :text="t('quote.label.detele_quote')"
      @click="toggleModalDelete"
      :disabled="processing"
      v-if="!quote.file.file_code"
    />
    <ButtonOutlineContainer
      icon="floppy-disk"
      :text="t('quote.label.save')"
      @click="modalSave"
      :disabled="processing"
    />
    <ButtonOutlineContainer
      icon="floppy-disk"
      :text="t('quote.label.save_as')"
      @click="toggleModalSaveAs"
      :disabled="processing"
    />
    <template v-if="view === 'table'">
      <ButtonOutlineContainer
        v-if="serviceSelected.length > 0"
        icon="trash-can"
        :text="t('quote.label.detele_service')"
        @click="toggleModalEliminarServicio"
        :disabled="processing"
      />
    </template>
    <DowloadButton :items="downloadItems" :disabled="processing" @selected="selectDownload" />

    <!-- <div class="toggle">
      <div class="container">
        <a-switch v-model:checked="state.checked1" />
        <span>Convertir todos los traslados en premium </span>
      </div>
    </div> -->

    <ModalComponent
      :modal-active="state.showModalClose"
      class="modal-eliminar"
      @close="toggleModalClose"
    >
      <template #body>
        <h3 class="title">{{ t('quote.label.close') }}</h3>
        <div class="description">
          {{ t('quote.label.surely_you_want_to_close_without_saving_changes_to') }}
        </div>
      </template>
      <template #footer>
        <div class="footer">
          <button
            :disabled="processing"
            class="cancel"
            @click="modalDelete"
            style="width: 250px !important"
          >
            {{ t('quote.label.discard_changes') }}
          </button>
          <button
            :disabled="processing"
            class="ok"
            @click="modalCloseSave"
            style="width: 140px !important"
          >
            {{ t('quote.label.save') }}
          </button>
        </div>
      </template>
    </ModalComponent>

    <ModalComponent
      :modal-active="state.showModalEliminar"
      class="modal-eliminar"
      @close="toggleModalDelete"
    >
      <template #body>
        <h3 class="title">{{ t('quote.label.detele_quote') }}</h3>
        <div class="description">
          {{ t('quote.label.eliminating_quote') }}
          {{ t('quote.label.youre_sure') }}
        </div>
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="processing" class="cancel" @click="toggleModalDelete">
            {{ t('quote.label.cancel') }}
          </button>
          <button :disabled="processing" class="ok" @click="modalDelete">
            {{ t('quote.label.yes_continue') }}
          </button>
        </div>
      </template>
    </ModalComponent>

    <ModalComponent
      :modal-active="state.showModalGuardar"
      class="modal-guardar"
      @close="toggleModalSave"
    >
      <template #body>
        <h3 class="title">{{ t('quote.label.quote_saved') }}</h3>
        <div class="description">
          {{ t('quote.label.saved_correctly') }}
        </div>
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="processing" class="ok" @click="toggleModalSave">
            {{ t('quote.label.return') }}
          </button>
        </div>
      </template>
    </ModalComponent>

    <ModalComponent
      :modal-active="state.showModalGuardarComo"
      class="modal-guardarcomo"
      @close="toggleModalSaveAs"
    >
      <template #body>
        <h3 class="title">{{ t('quote.label.save_as') }}</h3>
        <div class="description">
          {{ t('quote.label.enter_name_save') }}
          <input type="text" :placeholder="t('quote.label.quote_name')" v-model="nameQuote" />
        </div>
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="processing" class="cancel" @click="toggleModalSaveAs">
            {{ t('quote.label.return') }}
          </button>
          <button :disabled="processing" class="ok" @click="modalSaveAs">
            {{ t('quote.label.save') }}
          </button>
        </div>
      </template>
    </ModalComponent>

    <service-ramove-confirm
      v-if="state.showModalEliminarServicio"
      :show-modal="state.showModalEliminarServicio"
      @close="toggleModalEliminarServicio"
      @ok="deleteAllServices"
      @cancel="toggleModalEliminarServicio"
    />

    <ModalComponent
      v-if="state.showModalItinerarioDetalle"
      :modal-active="state.showModalItinerarioDetalle"
      class="modal-itinerariodetalle"
      @close="toggleModalIntinerario"
    >
      <template #body>
        <DownloadItinerary />
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="processing" class="cancel" @click="toggleModalIntinerario">
            {{ t('quote.label.return') }}
          </button>
          <button :disabled="processing" class="ok" @click="donwloadIntinerario">
            {{ t('quote.label.yes_continue') }}
          </button>
        </div>
      </template>
    </ModalComponent>

    <ModalComponent
      :modal-active="state.showModalSkeletonDetalle"
      class="modal-Skeletondetalle"
      @close="toggleModalSkeleton"
    >
      <template #body>
        <DownloadSkeleton />
      </template>
      <template #footer>
        <div class="footer">
          <button :disabled="processing" class="cancel" @click="toggleModalSkeleton">
            {{ t('quote.label.return') }}
          </button>
          <button :disabled="processing" class="ok" @click="downloadftSkeleton">
            {{ t('quote.label.yes_continue') }}
          </button>
        </div>
      </template>
    </ModalComponent>
  </div>
</template>

<script lang="ts" setup>
  import { reactive, ref, watchEffect } from 'vue';
  import { useI18n } from 'vue-i18n';
  import ButtonOutlineContainer from '@/quotes/components/global/ButtonOutlineContainer.vue';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import DownloadItinerary from '@/quotes/components/DownloadItinerary.vue';
  import DownloadSkeleton from '@/quotes/components/DownloadSkeleton.vue';
  import DowloadButton from '@/quotes/components/global/DowloadButton.vue';
  import ServiceRamoveConfirm from '@/quotes/components/modals/ServiceDeleteServiceAllConfirmation.vue';
  import useNotification from '@/quotes/composables/useNotification';
  import type { GroupedServices, QuoteService } from '@/quotes/interfaces';
  import { useQuote } from '@/quotes/composables/useQuote';

  interface Props {
    viewBtn?: string;
    processing?: boolean;
  }

  withDefaults(defineProps<Props>(), {
    viewBtn: 'table',
    processing: false,
  });

  const { t } = useI18n();
  const { showErrorNotification } = useNotification();
  const {
    quote,
    saveQuote,
    saveAs,
    closeQuote,
    deleteQuote,
    exportar,
    downloadQuoteItinerary,
    downloadQuoteSkeleton,
    getQuote,
    view,
    downloadItinerary,
    downloadSkeletonUse,
    removeQuoteServices,
    verify_itinerary_errors,
  } = useQuote();

  const serviceSelected = ref<GroupedServices[]>([]);

  interface State {
    checked1: boolean;
    showModalClose: boolean;
    showModalEliminar: boolean;
    showModalGuardar: boolean;
    showModalGuardarComo: boolean;
    showModalEliminarServicio: boolean;
    showModalItinerarioDetalle: boolean;
    showModalSkeletonDetalle: boolean;
    openDownload: boolean;
  }

  const state: State = reactive({
    checked1: true,
    showModalClose: false,
    showModalEliminar: false,
    showModalGuardar: false,
    showModalGuardarComo: false,
    showModalEliminarServicio: false,
    showModalItinerarioDetalle: false,
    showModalSkeletonDetalle: false,
    openDownload: false,
  });

  const nameQuote = ref<string>('');

  const modalSave = async () => {
    const resullt = await saveQuote();
    if (resullt) {
      await getQuote();
      toggleModalSave();
    }
  };
  const toggleModalSave = async () => {
    state.showModalGuardar = !state.showModalGuardar;
  };
  const toggleModalClose = () => {
    state.showModalClose = !state.showModalClose;
  };
  const toggleModalDelete = () => {
    state.showModalEliminar = !state.showModalEliminar;
  };

  const modalCloseSave = async () => {
    await saveQuote();
    await closeQuote();
    toggleModalClose();
  };

  const modalDelete = async () => {
    await deleteQuote();
    toggleModalDelete();
  };

  const toggleModalSaveAs = async () => {
    state.showModalGuardarComo = !state.showModalGuardarComo;
  };

  const modalSaveAs = async () => {
    await saveAs(nameQuote.value);
    await getQuote();
    nameQuote.value = '';
    toggleModalSaveAs();
  };

  const toggleModalEliminarServicio = () => {
    state.showModalEliminarServicio = !state.showModalEliminarServicio;
  };

  const deleteAllServices = async () => {
    let dataToRemove: QuoteService[] = [];

    serviceSelected.value.forEach((row) => {
      if (row.type === 'group_header') {
        row.group.forEach((e) => {
          dataToRemove.push(e);
        });
      } else {
        dataToRemove.push(row.service);
      }
    });

    await removeQuoteServices(dataToRemove);

    toggleModalEliminarServicio();
  };

  const toggleModalIntinerario = () => {
    state.showModalItinerarioDetalle = !state.showModalItinerarioDetalle;
  };

  const toggleModalSkeleton = () => {
    state.showModalSkeletonDetalle = !state.showModalSkeletonDetalle;
  };

  const toggleDownload = () => {
    state.openDownload = !state.openDownload;
  };

  const downloadftSkeleton = async () => {
    const response = await downloadQuoteSkeleton();
    var fileURL = window.URL.createObjectURL(new Blob([response.data]));
    var fileLink = document.createElement('a');
    fileLink.href = fileURL;
    fileLink.setAttribute(
      'download',
      'Skeleton - ' + downloadSkeletonUse.value.nameService + '.docx'
    );
    document.body.appendChild(fileLink);

    fileLink.click();
    state.showModalSkeletonDetalle = !state.showModalSkeletonDetalle;
  };

  const donwloadIntinerario = async () => {
    const response = await downloadQuoteItinerary();

    var fileURL = window.URL.createObjectURL(new Blob([response.data]));
    var fileLink = document.createElement('a');
    fileLink.href = fileURL;
    fileLink.setAttribute(
      'download',
      'Itinerary - ' + downloadItinerary.value.nameServicioItem + '.docx'
    );
    document.body.appendChild(fileLink);

    fileLink.click();

    state.showModalItinerarioDetalle = !state.showModalItinerarioDetalle;
  };

  const selectDownload = (item: string[] | null) => {
    if (item && item.includes('excel')) {
      if (verify_itinerary_errors()) {
        showErrorNotification(t('quote.label.observations_validation_text'));
      } else {
        exportar();
      }
    }

    if (item && item.includes('itinerario')) {
      if (verify_itinerary_errors()) {
        showErrorNotification(t('quote.label.observations_validation_text'));
      } else {
        toggleModalIntinerario();
      }
    }

    if (item && item.includes('programa-dia-dia')) {
      if (verify_itinerary_errors()) {
        showErrorNotification(t('quote.label.observations_validation_text'));
      } else {
        toggleModalSkeleton();
      }
    }
    toggleDownload();
  };

  const downloadItems = [
    {
      label: 'Excel',
      value: 'excel',
    },
    {
      label: t('quote.label.itinerary'),
      value: 'itinerario',
    },
    {
      label: t('quote.label.day_by_day_program_select'),
      value: 'programa-dia-dia',
    },
  ];

  watchEffect(() => {
    serviceSelected.value = [];
    quote.value.categories.forEach((c) => {
      c.services.forEach((s) => {
        if (s.selected == true) {
          serviceSelected.value.push(s);
        }
      });
    });
  });
</script>

<style lang="scss">
  @import '@/scss/variables';

  .quotes-actions {
    display: inline-flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 27px;
    border: none;
    background: transparent;

    .toggle {
      display: flex;
      width: 256px;
      height: 21px;
      padding-left: 0;
      justify-content: center;
      align-items: center;

      .container {
        display: flex;
        height: 21px;
        justify-content: center;
        align-items: center;
        gap: 8px;

        .ant-switch {
          background-color: #fff;
          transition: background-color 700ms linear;
          border: 1px solid #eb5757;
          width: 36px;

          &-checked {
            background-color: #eb5757;
            transition: background-color 700ms linear;

            .ant-switch-handle::before {
              background-color: #fff;
            }
          }
        }

        .ant-switch-handle {
          top: 1px;
        }

        .ant-switch-handle::before {
          background-color: #eb5757;
        }

        :deep(.ant-switch-handle) {
          bottom: 1px;
          height: auto;
          top: 1px;
          width: 16px;
        }

        :deep(.ant-switch-handle::before) {
          background-color: #eb5757;
        }

        span {
          color: #eb5757;
          font-size: 14px;
          font-style: normal;
          font-weight: 500;
          letter-spacing: 0.21px;
          width: 210px;
        }
      }
    }

    .modal-eliminar .modal-inner {
      max-width: 435px;

      .modal-body {
        .title {
          padding: 0 30px;
        }
      }
    }

    .modal-guardar .modal-inner {
      max-width: 520px;
    }

    .modal-guardarcomo .modal-inner {
      max-width: 435px;

      input {
        margin-top: 16px;
        border-radius: 4px;
        border: 1px solid #bbbdbf;
        background: #ffffff;
        width: 340px;
        height: 45px;
        padding: 5px 10px;
        align-items: center;

        &::placeholder {
          overflow: hidden;
          color: #bbbdbf;
          text-overflow: ellipsis;
          font-family: Montserrat;
          font-size: 14px;
          font-style: normal;
          font-weight: 400;
          line-height: 21px;
          letter-spacing: 0.21px;
        }

        &:focus {
          outline: 2px solid #55a3ff;
          box-shadow: 0 0 15px rgba(128, 186, 255, 1);
        }
      }
    }

    .modal-eliminarservicio .modal-inner {
      max-width: 435px;
    }

    .button-download-container {
      position: relative;

      .button-component {
        font-size: 14px;
        font-weight: 600;
        gap: 10px;
        padding: 12px 0;
      }

      .box {
        position: absolute;
        display: none;

        &.openDownload {
          display: block;
        }
      }
    }
  }
</style>
