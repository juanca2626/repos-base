<template>
  <div class="base-table container-fluid p-0">
    <div class="row g-0 row-filter">filter</div>
    <div class="row g-0 row-header">header</div>
    {{ isLoading }}
    <div class="row g-0 container-body">
      <div class="row g-0 row-body" v-for="file in data" :key="file.id">
        <div class="col-small">{{ file.fileNumber }}</div>
        <div class="col-small col-small-break col-small-200">
          {{ file.description }}
        </div>
        <div class="col-small text-uppercase">
          <base-badge :type="statusByIso(file.status).type">
            {{ statusByIso(file.status).name }}
          </base-badge>
        </div>
        <div class="col-small">
          <files-popover-vip :data="file" @onRefreshCache="handleRefreshCache" />
          <!-- popover-hover-and-click :data="file">
            <template #default="{ isVip }">
              <span class="group-vip">
                <font-awesome-icon v-if="isVip" class="is-vip" icon="fa-solid fa-star" />
                <font-awesome-icon v-else icon="fa-regular fa-star" />
              </span>
            </template>
            <template #content-hover="{ vipReason }">{{ vipReason }}</template>
            <template #content-click="{ fileNumber }">
              <div class="vip-content-click">
                <div class="vip-content-click-title">FILE {{ fileNumber }} - Establecer VIP</div>
                <a-form
                  class="form-vip"
                  :model="formVip"
                  @finish="onFinish"
                  @finishFailed="onFinishFailed">
                  <base-select
                    name="vipReason"
                    label="Escoge un motivo"
                    placeholder="Selecciona"
                    size="large"
                    width="210"
                    :comma="false"
                    :options="vipReasonOptions"
                    v-model:value="formVip.vipReason" />
                  <a-form-item :colon="false" style="margin-bottom:0;">
                    <template #label>
                      <a-checkbox v-model:checked="formVip.vipAnotherReason">Otro motivo&nbsp;&nbsp;</a-checkbox>
                    </template>
                    <base-input
                      name="vipAnotherReason"
                      placeholder="Especificar motivo"
                      size="large"
                      width="210"
                      :disabled="formVip.vipHasAnotherReason"
                      v-model="formFilter.vipAnotherReason" />
                  </a-form-item>
                  <div style="display:flex;justify-content:flex-end;gap:10px">
                    <base-button width="60">Cancelar</base-button>
                    <base-button width="60">Guardar</base-button>
                  </div>
                </a-form>
              </div>
            </template>
          </popover-hover-and-click -->
        </div>
        <div class="col-small">{{ file.paxs }}</div>
        <div class="col-small">
          <base-popover>
            {{ file.executiveCode }}
            <template #content>
              <div class="box-title">{{ t('global.label.executive') }}</div>
              <div class="box-description">({{ file.executiveCode }}) {{ file.executiveName }}</div>
            </template>
          </base-popover>
        </div>
        <div class="col-small">
          <base-popover>
            {{ file.clientCode }}
            <template #content>
              <div class="box-title">{{ t('global.label.client') }}</div>
              <div class="box-description">({{ file.clientCode }}) {{ file.clientName }}</div>
            </template>
          </base-popover>
        </div>
        <div class="col-small">{{ formatDate(file.dateIn) }}</div>
        <div class="col-small text-uppercase col-small-200">
          <span v-if="!file?.haveInvoice">&nbsp;</span>
          <base-badge v-else :type="haveInvoiceByIso(file.haveInvoice)?.iso" :is-block="true">
            {{ haveInvoiceByIso(file.haveInvoice).name }}
          </base-badge>
        </div>
        <div class="col-small text-uppercase">
          <span v-if="!file?.revision_stages">&nbsp;</span>
          <base-badge v-else :type="getRevisionStageById(file.revision_stages).iso">
            {{ getRevisionStageById(file.revision_stages).name }}
          </base-badge>
        </div>
        <div class="col-small col-body-options">
          <popover-hover-and-click>
            <font-awesome-icon icon="fa-solid fa-dollar-sign" size="sm" />
            <template #content-hover>{{ t('global.label.see_quote') }}</template>
            <template #content-click>
              <div class="quotation-content-click">
                <div class="quotation-content-click-title">Quotation 34729</div>
                <div class="quotation-content-click-subtitle">
                  This category has <span>27 Products</span>:
                </div>
                <div class="quotation-content-click-buttons">
                  <base-button size="small" width="89" height="25">
                    <div style="font-size: 12px; line-height: 12px">Standard</div>
                  </base-button>
                  <base-button size="small" width="89" height="25" ghost>
                    <div style="font-size: 12px; line-height: 12px">Luxury</div>
                  </base-button>
                  <base-button size="small" width="89" height="25" ghost>
                    <div style="font-size: 12px; line-height: 12px">First Class</div>
                  </base-button>
                </div>
                <section class="quotation-content-click-table">
                  <a-table
                    :columns="quotationColumns"
                    :data-source="statementStore.getStatements"
                    row-key="id"
                  >
                    <template #bodyCell="{ column, record }">
                      <span v-if="column.key === 'amount'">
                        {{ record.amount | currency }}
                      </span>
                      <span v-else>
                        {{ record[column.dataIndex] }}
                      </span>
                    </template>
                  </a-table>
                </section>
              </div>
            </template>
          </popover-hover-and-click>

          <popover-hover-and-click>
            <font-awesome-icon icon="fa-solid fa-eye" size="sm" />
            <template #content-hover>Ver Detalles</template>
            <template #content-click>
              <div class="details-content-click">
                <div class="details-content-click-title">Detalles de file</div>

                <div class="details-content-click-subtitle">Fecha de apertura:</div>
                <div class="details-content-click-description">10/09/2022</div>

                <div class="details-content-click-subtitle">EC apertura:</div>
                <div class="details-content-click-description">(JKA) Jenny Kelly Arteaga</div>

                <div class="details-content-click-subtitle">Deadlilne:</div>
                <div class="details-content-click-description">10/04/2023</div>

                <div class="details-content-click-subtitle">Pedido:</div>
                <div class="details-content-click-description">13560</div>

                <div class="details-content-click-subtitle">Fecha de apertura 2:</div>
                <div class="details-content-click-description">10/09/2022</div>

                <div class="details-content-click-subtitle">EC apertura 2:</div>
                <div class="details-content-click-description">(JKA) Jenny Kelly Arteaga</div>

                <div class="details-content-click-subtitle">Deadlilne 2:</div>
                <div class="details-content-click-description">10/04/2023</div>

                <div class="details-content-click-subtitle">Pedido 2:</div>
                <div class="details-content-click-description">13560</div>
              </div>
            </template>
          </popover-hover-and-click>

          <popover-hover-and-click>
            <font-awesome-icon icon="fa-regular fa-envelope" size="sm" />
            <template #content-hover>Ver Tareas pendientes</template>
            <template #content-click>
              <div class="pending-tasks-content-click">
                <div class="pending-tasks-content-click-body">
                  <div class="pending-tasks-content-click-title">Bandeja de Pendientes</div>

                  <div class="pending-tasks-content-click-subtitle">Tareas pendientes:</div>
                  <div class="pending-tasks-content-click-description">
                    24 tareas por realizar<br />
                    -Completar informacion para solicitar compra INCs<br />
                    -Registrar datos de paxs<br />
                    -Completar informacion para solicitar compra INCs<br />
                    -Registrar datos de paxs<br />
                    -Completar informacion para solicitar compra INCs<br />
                    -Registrar datos de paxs<br />
                    -Completar informacion para solicitar compra INCs<br />
                    -Registrar datos de paxs<br />
                    -Completar informacion para solicitar compra INCs<br />
                    -Registrar datos de paxs<br />
                    -Completar informacion para solicitar compra INCs<br />
                    -Registrar datos de paxs<br />
                    -Completar informacion para solicitar compra INCs<br />
                    -Registrar datos de paxs
                  </div>
                </div>
                <div class="pending-tasks-content-click-footer">
                  <base-button width="89">
                    <div
                      style="display: flex; gap: 12px; justify-content: center; align-items: center"
                    >
                      Ir
                      <font-awesome-icon icon="fa-solid fa-arrow-right-long" />
                    </div>
                  </base-button>
                </div>
              </div>
            </template>
          </popover-hover-and-click>

          <popover-hover-and-click>
            <font-awesome-icon icon="fa-solid fa-ellipsis-vertical" size="sm" />
            <template #content-hover>{{ t('files.label.actions') }}</template>
            <template #content-click>
              <div class="actions-content-click">
                <div class="actions-content-click-title">{{ t('files.label.actions') }}</div>
                <div class="actions-content-click-content">
                  <a-button type="link" size="small">
                    <div
                      style="
                        display: flex;
                        gap: 10px;
                        justify-content: flex-start;
                        align-items: center;
                      "
                    >
                      <font-awesome-icon icon="fa-solid fa-user-pen" />
                      <span style="font-weight: 500">{{ t('global.label.edit') }}</span>
                    </div>
                  </a-button>
                  <a-button type="link" size="small" v-if="false">
                    <div
                      style="
                        display: flex;
                        gap: 10px;
                        justify-content: flex-start;
                        align-items: center;
                      "
                    >
                      <font-awesome-icon icon="fa-solid fa-clone" />
                      <span style="font-weight: 500">{{ t('global.label.clone') }}</span>
                    </div>
                  </a-button>
                </div>
              </div>
            </template>
          </popover-hover-and-click>

          <font-awesome-icon icon="fa-solid fa-arrow-down-wide-short" v-if="false" />
          <font-awesome-icon icon="fa-solid fa-arrow-up-wide-short" v-if="false" />
        </div>
      </div>
    </div>
    <div class="row g-0 row-pagination">pagination</div>
  </div>
</template>

<script setup>
  import FilesPopoverVip from '@/components/files/edit/FilesPopoverVip.vue';
  import BaseBadge from '@/components/files/reusables/BaseBadge.vue';

  import { useStatusesStore } from '@store/files';

  const statusesStore = useStatusesStore();

  const statusByIso = (iso) => statusesStore.getStatusByIso(iso);

  defineProps({
    isLoading: {
      type: Boolean,
      default: false,
    },
    data: {
      type: Array,
      default: () => [],
    },
    currentPage: {
      type: Number,
      default: 1,
    },
  });

  const handleRefreshCache = async () => {
    //
  };
</script>

<style scoped lang="scss">
  .base-table {
    .row {
      display: flex;
      align-items: center;
      border-radius: 6px;
      margin-bottom: 5px;
      text-align: center;
      font-size: 0.875rem;
      padding-right: 10px;
    }
    .row-header {
      background-color: var(--files-black-2);
      color: var(--files-background-1);
      min-height: 50px;
      font-weight: 500;
    }

    .row-header-sort {
      cursor: pointer;
    }
    .container-body {
      min-height: 370px;
      // min-height: 465px;
    }
    .row-body {
      background-color: var(--files-background-1);
      border: 1px solid var(--files-main-color);
      min-height: 56px;
      font-weight: 400;
    }
    .col-small {
      flex: 1 0 0%;
      &-break {
        word-break: break-all;
        text-align: left;

        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
      }
      &-200 {
        width: 200px;
      }
    }
    .col-body-options {
      display: flex;
      justify-content: center;
      align-items: center;
      color: var(--files-main-color);
      gap: 8px;

      svg {
        cursor: pointer;
        padding: 1px;
        width: 16px;
        height: 16px;
      }
      svg:focus {
        outline: none;
      }
    }
  }
  .box-title {
    font-weight: 500;
    font-size: 12px;
    line-height: 18px;
    color: #2f353a;
  }
  .box-description {
    font-weight: 400;
    font-size: 12px;
    line-height: 18px;
  }
  .form-filter {
    display: flex;
    gap: 7.125px;
  }
  .form-vip {
    display: flex;
    flex-direction: column;
    gap: 2.125rem;
    margin-bottom: 1.625rem;
  }
  .group-vip {
    cursor: pointer;
    transition: 0.3s ease all;
    .is-vip {
      color: var(--files-exclusives);
    }

    &:hover {
      color: var(--files-exclusives);
    }
  }
  .row-pagination {
    display: flex;
    justify-content: center;
    gap: 60px;
    padding-top: 20px;
  }
  .text-uppercase {
    text-transform: uppercase;
  }
  .vip-content-click {
    font-family: var(--files-font-basic);
    width: 410px;
    height: 337px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;
    &-title {
      font-weight: 700;
      font-size: 1rem;
      line-height: 23px;
      padding: 20px 12px 23px;
      text-align: center;
      letter-spacing: -0.015em;
      color: #3d3d3d;
      border-bottom: 1px solid #e9e9e9;
      margin-bottom: 1.8125rem;
    }
  }
  .quotation-content-click {
    font-family: var(--files-font-basic);
    width: 320px;
    height: 220px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    &-title {
      font-weight: 900;
      font-size: 1.2rem;
      line-height: 1.2;
    }
    &-subtitle {
      font-weight: 400;
      font-size: 0.8rem;
      span {
        text-decoration: underline;
        font-weight: 500;
      }
    }
    &-buttons {
      display: flex;
      gap: 5px;
      padding-bottom: 10px;
    }
    &-table {
      width: 310px;
      box-shadow:
        0 3px 6px -2px rgb(0 0 0 / 20%),
        0 6px 12px rgb(0 0 0 / 10%);
      & :deep(.ant-table-cell) {
        font-size: 0.7rem;
      }
    }
  }
  .details-content-click {
    font-family: var(--files-font-basic);
    width: 275px;
    height: 220px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;
    color: var(--files-black-2);

    &-title {
      font-weight: 700;
      text-align: center;
      text-transform: uppercase;
    }
    &-subtitle {
      font-weight: 600;
    }
    &-description {
      font-weight: 400;
    }
  }

  .pending-tasks-content-click {
    font-family: var(--files-font-basic);
    width: 320px;
    height: 202px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;

    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;

    color: var(--files-black-2);

    &-body {
      overflow-y: auto;
    }
    &-footer {
      height: 100px;
      overflow-y: auto;
      display: flex;
      justify-content: end;
    }
    &-title {
      color: var(--files-black-4);
      font-weight: 700;
      text-align: center;
    }
    &-subtitle {
      font-weight: 600;
    }
    &-description {
      font-weight: 400;
      font-size: 0.75rem;
      line-height: 1.1875;
    }
  }
  .actions-content-click {
    font-family: var(--files-font-basic);
    width: 185px;
    height: 110px;
    display: flex;
    flex-direction: column;
    padding: 3px;

    font-size: 0.875rem;
    line-height: 1.3125;
    letter-spacing: 0.015em;

    &-title {
      color: var(--files-black-4);
      font-weight: 700;
      text-align: center;
    }
    &-content {
      display: flex;
      flex-direction: column;
    }
  }
</style>
