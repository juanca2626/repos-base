<template>
  <popover-hover-and-click>
    <svg
      xmlns="http://www.w3.org/2000/svg"
      width="20"
      height="20"
      fill="none"
      stroke="currentColor"
      stroke-linecap="round"
      stroke-linejoin="round"
      stroke-width="2"
      class="feather feather-info"
      viewBox="0 0 24 24"
    >
      <circle cx="12" cy="12" r="10" />
      <path d="M12 16v-4M12 8h.01" />
    </svg>
    <template #content-hover
      >Log
      <span class="text-lowercase">
        {{ t('global.label.of') }}
        {{
          data.itinerary.entity == 'service' ? t('global.label.service') : t('global.label.hotel')
        }}
      </span>
    </template>
    <template #content-click>
      <div class="files-toggler-service-log">
        <a-tabs v-model:activeKey="activeKeyTabs" style="min-width: 400px">
          <a-tab-pane key="1" tab="Monto calculado" force-render>
            <div class="calculated-amount">
              <div>
                <h6 class="calculated-amount__title">{{ t('global.label.cost_calculated') }}</h6>
                <div class="calculated-amount__wrapper">
                  <font-awesome-icon
                    class="calculated-amount__currency"
                    icon="fa-solid fa-dollar-sign"
                  />
                  <span class="calculated-amount__total">{{
                    data.itinerary.total_cost_amount
                  }}</span>
                </div>
              </div>
              <div class="calculated-amount__stats">
                <template v-if="data.itinerary.entity == 'service'">
                  <div
                    class="calculated-amount__badges"
                    v-if="data.itinerary.service_amount_logs.length > 0"
                  >
                    <div class="calculated-amount__badge calculated-amount__badge--invoiced">
                      {{ t('files.label.price_modified') }}
                    </div>
                  </div>
                </template>
                <template v-if="data.itinerary.entity == 'hotel'">
                  <div
                    class="calculated-amount__badges"
                    v-if="data.itinerary.room_amount_logs.length > 0"
                  >
                    <div class="calculated-amount__badge calculated-amount__badge--invoiced">
                      {{ t('files.label.price_modified') }}
                    </div>
                  </div>
                </template>
                <div class="calculated-amount__profitability">
                  <!-- div class="calculated-amount__profitability--percentage1">MKP 1%</div -->
                  <div
                    :class="[
                      'calculated-amount__profitability--percentage',
                      data.itinerary.profitability >= 0 ? 'up' : 'down',
                    ]"
                  >
                    {{ data.itinerary.profitability }}%
                    <font-awesome-icon
                      :icon="['fa-solid', 'fa-minus']"
                      v-if="data.itinerary.profitability == 0"
                    />
                    <font-awesome-icon
                      v-else
                      :icon="[
                        'fa-solid',
                        data.itinerary.profitability >= 0
                          ? 'fa-arrow-trend-up'
                          : 'fa-arrow-trend-down',
                      ]"
                    />
                  </div>
                  <div class="calculated-amount__profitability--title">
                    {{ t('global.label.profitability') }}
                  </div>
                </div>
              </div>
            </div>

            <template v-if="data.itinerary.entity == 'service'">
              <template v-if="data.itinerary.service_amount_logs.length > 0">
                <a-collapse v-model:activeKey="activeKey" expand-icon-position="right">
                  <a-collapse-panel key="1" header="Historial de costo en servicios">
                    <a-table
                      :columns="columns"
                      size="small"
                      :data-source="data.itinerary.service_amount_logs"
                    >
                      <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'prev'">
                          <span class="text-dark-gray">
                            <b>$</b> {{ record.amount_previous }}
                          </span>
                        </template>
                        <template v-if="column.key === 'next'">
                          <span :class="`${record.markup >= 0 ? 'text-green' : 'text-danger'}`">
                            <b>$</b> {{ record.amount }}
                          </span>
                        </template>
                        <template v-if="column.key === 'date'">
                          {{ record.date }}
                        </template>
                        <template v-if="column.key === 'markup'">
                          <div
                            :class="[
                              'calculated-amount__profitability--percentage',
                              record.markup >= 0 ? 'up' : 'down',
                            ]"
                          >
                            {{ record.markup }}
                            <font-awesome-icon
                              :icon="['fa-solid', 'fa-minus']"
                              v-if="record.markup == 0"
                            />
                            <font-awesome-icon
                              v-else
                              :icon="[
                                'fa-solid',
                                record.markup >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down',
                              ]"
                            />
                          </div>
                        </template>
                      </template>
                      <!-- template #expandedRowRender="{ record }">
                        <div style="font-size:10px;text-align:center;display:flex;flex-direction:column;">
                          <div
                            v-for="description in record.descriptions"
                            :key="description.key"
                            style="display:flex;justify-content:center;">
                            <div style="margin:0;display:flex;gap:15px;">
                              <span style="color:rgb(46, 43, 158);">{{ description.title }}</span>
                              <span>
                                <span style="color:#333;">
                                  {{ description.subtitle }}:
                                </span> {{ description.content }}
                              </span>
                            </div>
                          </div>
                        </div>
                      </template -->
                    </a-table>
                  </a-collapse-panel>
                </a-collapse>
              </template>
            </template>

            <template v-if="data.itinerary.entity == 'hotel'">
              <template v-if="data.itinerary.room_amount_logs.length > 0">
                <a-collapse v-model:activeKey="activeKey" expand-icon-position="right">
                  <a-collapse-panel key="1" header="Historial de costo en hotel">
                    <a-table
                      :columns="columns"
                      :data-source="data.itinerary.room_amount_logs"
                      size="small"
                    >
                      <template #bodyCell="{ column, record }">
                        <template v-if="column.key === 'prev'">
                          <b>$</b> {{ record.amount_previous }}
                        </template>
                        <template v-if="column.key === 'next'">
                          <b>$</b> {{ record.amount }}
                        </template>
                        <template v-if="column.key === 'date'">
                          {{ record.date }}
                        </template>
                        <template v-if="column.key === 'markup'">
                          <div
                            :class="[
                              'calculated-amount__profitability--percentage',
                              data.itinerary.profitability >= 0 ? 'up' : 'down',
                            ]"
                          >
                            {{ record.markup }}
                            <font-awesome-icon
                              :icon="['fa-solid', 'fa-minus']"
                              v-if="record.markup == 0"
                            />
                            <font-awesome-icon
                              v-else
                              :icon="[
                                'fa-solid',
                                record.markup >= 0 ? 'fa-arrow-trend-up' : 'fa-arrow-trend-down',
                              ]"
                            />
                          </div>
                        </template>
                      </template>
                    </a-table>
                  </a-collapse-panel>
                </a-collapse>
              </template>
            </template>
          </a-tab-pane>

          <a-tab-pane key="2" tab="Log">
            <div
              style="
                display: flex;
                justify-content: space-between;
                background-color: #fafafa;
                border-radius: 6px;
                border: 1px solid #c8c8c8;
                padding: 5px 20px;
                margin-bottom: 10px;
                margin-top: 20px;
              "
            >
              <div>
                <div style="font-size: 11px; color: #999; font-weight: 500">
                  Jenny Kelly Arteaga
                </div>
                <div style="font-size: 14px; color: #333; font-weight: 400">Cambio de horarios</div>
              </div>
              <div>
                <div>
                  <div style="font-size: 11px; color: #eb5757; font-weight: 600; text-align: right">
                    17/11/2022
                  </div>
                  <div style="font-size: 10px; color: #333; font-weight: 400; text-align: right">
                    10:35:00
                  </div>
                </div>
              </div>
            </div>

            <div
              style="
                display: flex;
                justify-content: space-between;
                background-color: #fafafa;
                border-radius: 6px;
                border: 1px solid #c8c8c8;
                padding: 5px 20px;
              "
            >
              <div>
                <div style="font-size: 11px; color: #999; font-weight: 400">
                  Jenny Kelly Arteaga
                </div>
                <div style="font-size: 14px; color: #333; font-weight: 500">
                  Cambio de servicios
                </div>
                <div
                  style="font-size: 11px; color: #999; font-weight: 400; display: flex; gap: 5px"
                >
                  <svg
                    class="feather feather-git-pull-request"
                    style="color: #111"
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    viewBox="0 0 24 24"
                  >
                    <circle cx="18" cy="18" r="3" />
                    <circle cx="6" cy="6" r="3" />
                    <path d="M13 6h3a2 2 0 0 1 2 2v7M6 9v12" />
                  </svg>
                  Nombre del servicio
                </div>
              </div>

              <div style="display: flex; flex-direction: column; justify-content: space-between">
                <div>
                  <div style="font-size: 11px; color: #eb5757; font-weight: 600; text-align: right">
                    17/11/2022
                  </div>
                  <div style="font-size: 10px; color: #333; font-weight: 400; text-align: right">
                    10:35:00
                  </div>
                </div>
                <div
                  style="
                    font-size: 10px;
                    color: #333;
                    font-weight: 400;
                    text-align: right;
                    display: flex;
                    align-items: flex-end;
                    position: relative;
                  "
                >
                  Ver detalle
                  <svg
                    style="position: absolute; right: -3px; color: #eb5757"
                    xmlns="http://www.w3.org/2000/svg"
                    width="16"
                    height="16"
                    fill="none"
                    stroke="currentColor"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    class="feather feather-chevron-down"
                    viewBox="0 0 24 24"
                  >
                    <path d="m6 9 6 6 6-6" />
                  </svg>
                </div>
              </div>
            </div>
          </a-tab-pane>
        </a-tabs>
      </div>
    </template>
    <template #content-buttons>&nbsp;</template>
  </popover-hover-and-click>
</template>

<script setup>
  import { ref } from 'vue';
  import PopoverHoverAndClick from '@/components/files/reusables/PopoverHoverAndClick.vue';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const activeKeyTabs = ref('1');
  const activeKey = ref([]);

  defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
  });

  const columns = [
    { title: 'Antes', dataIndex: 'prev', key: 'prev' },
    { title: 'Después', dataIndex: 'next', key: 'next' },
    { title: 'Fecha', dataIndex: 'date', key: 'date' },
    { title: 'Markup', dataIndex: 'markup', key: 'markup' },
  ];
</script>

<style lang="scss" scoped>
  .files-edit {
    &__service-header {
      display: flex;
      gap: 20px;
    }
  }
  .files-toggler-service-log {
    margin: 15px;
    display: inline-flex;
  }
  .calculated-amount {
    display: flex;
    justify-content: space-between;
    margin-bottom: 18px;

    &__title {
      color: #333;
      font-size: 14px;
      font-weight: 600;
      line-height: 21px;
      letter-spacing: 0.015em;
      color: #575757;
    }
    &__wrapper {
      display: flex;
      align-items: center;
    }
    &__currency {
      width: 16px;
      height: 30px;
    }
    &__total {
      font-size: 2rem;
      font-weight: 700;
      line-height: 43px;
      letter-spacing: -0.01em;
    }
    &__stats {
      display: flex;
      flex-direction: column;
      align-items: flex-end;
      gap: 2px;
    }
    &__badges {
      display: flex;
      gap: 10px;
    }
    &__badge {
      font-size: 12px;
      font-weight: 700;
      line-height: 19px;
      color: #c4c4c4;
      position: relative;
      padding-left: 10px;

      &::before {
        content: '';
        position: absolute;
        left: 0;
        top: 6px;
        display: inline-block;
        width: 6px;
        height: 6px;
        border-radius: 100px;
      }
      &--invoiced::before {
        background: #5c5ab4;
        box-shadow: 0px 0px 0px 2px #e3d7f2;
      }
      &--default::before {
        background: #4f4b4b;
        box-shadow: 0px 0px 0px 2px #e3d7f2;
      }
    }
    &__profitability {
      font-weight: 600;
      font-size: 6px;
      text-align: right;
      &--percentage1 {
        font-weight: 700;
        font-size: 12px;
        line-height: 19px;
        letter-spacing: 0.015em;
        color: #2b4597;
      }
      &--percentage {
        font-weight: 700;
        font-size: 12px;
        line-height: 19px;
      }
      &--title {
        font-weight: 600;
        font-size: 6px;
        color: #737373;
      }
    }
  }

  .up {
    color: #1ed790;
  }

  .down {
    color: #c63838;
  }
</style>
