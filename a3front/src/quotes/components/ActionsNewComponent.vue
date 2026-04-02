<template>
  <div class="quotes-actions">
    <ButtonOutlineContainer icon="floppy-disk" :text="t('quote.label.save')" @click="modalSave" />

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
          <button :disabled="false" class="ok" @click="toggleModalSave">
            {{ t('quote.label.return') }}
          </button>
        </div>
      </template>
    </ModalComponent>
  </div>
</template>

<script lang="ts" setup>
  import { reactive } from 'vue';
  import { useI18n } from 'vue-i18n';
  import ButtonOutlineContainer from '@/quotes/components/global/ButtonOutlineContainer.vue';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { notification } from 'ant-design-vue';
  const { t } = useI18n();
  const { quoteNew, saveQuoteNew } = useQuote();

  interface State {
    checked1: boolean;
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
    showModalEliminar: false,
    showModalGuardar: false,
    showModalGuardarComo: false,
    showModalEliminarServicio: false,
    showModalItinerarioDetalle: false,
    showModalSkeletonDetalle: false,
    openDownload: false,
  });

  const modalSave = async () => {
    let mensaje = '';
    if (quoteNew.value.quoteCategoriesSelected.length == 0) {
      mensaje = t('quote.validations.rq_category');
    } else if (quoteNew.value.name == '') {
      mensaje = t('quote.validations.rq_name_quote');
    } else if (quoteNew.value.quoteServiceTypeId == '') {
      mensaje = t('quote.validations.rq_type_of_services');
    }

    if (mensaje != '') {
      notification.error({
        message: `Error`,
        description: mensaje,
      });
      return false;
    }

    const resullt = await saveQuoteNew();
    if (resullt) {
      toggleModalSave();
    }
  };
  const toggleModalSave = async () => {
    state.showModalGuardar = !state.showModalGuardar;
  };
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
