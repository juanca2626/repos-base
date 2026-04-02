<template>
  <a-modal
    class="active-extension-notify-modal custom-modal-notify"
    :open="showModal"
    :closable="false"
    @cancel="handleCancel"
    :footer="null"
  >
    <template #title>
      <div class="container-header">
        <span class="header-title custom-primary-font"> Prórroga activa </span>
        <span @click="handleCancel">
          <font-awesome-icon class="custom-close-icon" :icon="['fa', 'xmark']" />
        </span>
      </div>
    </template>
    <div class="container-body">
      <a-spin :spinning="isLoading">
        <span class="body-title custom-primary-font">
          <template v-if="isMultipleExtension">
            ¡Tus solicitudes de prórroga se han realizado exitosamente!
          </template>
          <template v-else>
            ¡Tu solicitud de prórroga se ha realizado <br />
            exitosamente!
          </template>
        </span>
        <div class="container-body-icon">
          <CheckCircleIcon widthCircle="88px" heightCircle="88px" iconSize="50px" />
        </div>
        <template v-if="extensionSummaries.length > 0">
          <span class="body-message custom-primary-font">
            <template v-if="isMultipleExtension">
              <template v-if="isTransportVehicle">
                Otorgado a la unidad <strong>{{ documentExtensionInfo.typeUnitCode }}</strong> con
                placa <strong>{{ documentExtensionInfo.licensePlate }}</strong
                >.
              </template>
              <template v-else>
                Otorgado al conductor <strong>{{ documentExtensionInfo.driverFullName }}</strong
                >.
              </template>

              <table class="mt-3 table-summary">
                <thead>
                  <tr>
                    <th>Usuario</th>
                    <th class="padding-left">Documento</th>
                    <th class="column-reason padding-left">Motivo</th>
                    <th class="padding-left">Vencimiento</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(row, index) in extensionSummaries" :key="index">
                    <td>
                      {{ row.user.code }}
                    </td>
                    <td class="padding-left">
                      {{ row.typeDocument.name }}
                    </td>
                    <td class="padding-left text-justify">
                      {{ row.reason }}
                    </td>
                    <td class="padding-left">
                      {{ row.dateTo }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </template>
            <template v-else>
              Otorgado por <strong>{{ firstExtension?.user.code }}</strong>

              <template v-if="isTransportVehicle">
                a la unidad
                <strong>{{ documentExtensionInfo.typeUnitCode }}</strong> con placa
                <strong>{{ documentExtensionInfo.licensePlate }}</strong>
              </template>
              <template v-else> al conductor {{ documentExtensionInfo.driverFullName }} </template>,
              por el siguiente motivo: <em>“{{ firstExtension?.reason }}”</em>
              <span class="d-block mt-3">
                Vencimiento de la solicitud el <strong>{{ firstExtension?.dateTo }}.</strong>
              </span>
            </template>
          </span>
        </template>
        <template v-else>
          <div class="container-loading"></div>
        </template>
      </a-spin>
    </div>
  </a-modal>
</template>

<script setup lang="ts">
  import CheckCircleIcon from '@/modules/negotiations/supplier/components/custom-icons/CheckCircleIcon.vue';
  import type { ActiveExtensionNotifyProps } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
  import { useActiveExtensionNotify } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/documents/useActiveExtensionNotify';

  const props = defineProps<ActiveExtensionNotifyProps>();

  const emit = defineEmits(['update:showModal']);

  const {
    isMultipleExtension,
    isLoading,
    extensionSummaries,
    firstExtension,
    isTransportVehicle,
    handleCancel,
  } = useActiveExtensionNotify(emit, props);
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .table-summary {
    width: 100%;
    font-size: 13px;
    padding: 0;

    .column-reason {
      min-width: 30%;
      max-width: 40%;
      text-align: left;
    }

    .padding-left {
      padding-left: 10px;
    }

    .text-justify {
      text-align: justify;
    }
  }

  .container-loading {
    min-height: 110px;
  }

  .container-body-icon {
    display: flex;
    justify-content: center;
    align-items: center;
    padding-top: 25px;
    padding-bottom: 25px;
  }
</style>
<style lang="scss">
  @import '@/scss/_variables.scss';

  .active-extension-notify-modal {
    .ant-modal-body {
      padding: 26px !important;
    }

    .ant-modal-footer {
      background-color: $color-white-3;
      height: 70px !important;
      border-bottom-left-radius: 6px !important;
      border-bottom-right-radius: 6px !important;
      padding: 10px 20px !important;
    }
  }
</style>
