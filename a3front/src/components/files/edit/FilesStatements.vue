<template>
  <div v-if="!(filesStore.isLoadingStatements || filesStore.isLoadingBasic)" id="files-edit">
    <div class="files-statement-info" v-if="!flagEditStatement">
      <header class="files-statement-info-header">
        <div class="files-statement-info-title text-uppercase">
          <font-awesome-icon :icon="['fas', 'coins']" class="me-2" size="lg" />
          Movimientos Statement
        </div>
        <div class="files-statement-info-buttons" v-if="flag_show_statement">
          <a-tooltip>
            <template #title>{{ t('global.button.download') }}</template>
            <a-button
              type="default"
              size="large"
              class="btn-download"
              @click="showStatementDownload"
            >
              <font-awesome-icon :icon="['fas', 'cloud-arrow-down']" size="lg" />
              <!-- IconDownloadCloud color="#575757" width="22" height="18" / -->
            </a-button>
          </a-tooltip>
          <!--<a-button type="default" size="large" class="btn-reminder">
            <font-awesome-icon icon="stopwatch"></font-awesome-icon>
          </a-button>-->

          <template v-if="!verifyDateInit">
            <base-button
              type="default"
              @click="flagModalDebito = true"
              size="large"
              class="btn-debit"
              :disabled="
                filesStore.getFile.status === 'xl' ||
                filesStore.getFile.status === 'ce' ||
                filesStore.getFile.status === 'bl'
              "
            >
              <span class="text-uppercase">{{ t('global.label.debit_note') }}</span>
            </base-button>
          </template>
          <template v-if="totalStatement > 0 || !verifyDateInit">
            <base-button
              type="default"
              @click="flagModalCredito = true"
              size="large"
              class="btn-credit"
              :disabled="
                filesStore.getFile.status === 'xl' ||
                filesStore.getFile.status === 'ce' ||
                filesStore.getFile.status === 'bl'
              "
            >
              <span class="text-uppercase">{{ t('global.label.credit_note') }}</span>
            </base-button>
          </template>
          <base-button
            v-if="verifyDateInit"
            type="primary"
            size="large"
            @click="toggleStatement"
            :disabled="
              filesStore.getFile.status === 'xl' ||
              filesStore.getFile.status === 'ce' ||
              filesStore.getFile.status === 'bl' ||
              filesStore.getFile.status === 'pf'
            "
          >
            <div style="display: flex; gap: 4px">
              <span class="text-uppercase">
                {{ t('global.button.modify') }}
                statement
              </span>
            </div>
          </base-button>
          <base-button
            v-else
            type="primary"
            size="large"
            @click="toggleStatementHistory"
            :disabled="
              filesStore.getFile.status === 'xl' ||
              filesStore.getFile.status === 'ce' ||
              filesStore.getFile.status === 'bl' ||
              filesStore.getFile.status === 'pf'
            "
          >
            <div style="display: flex; gap: 4px">
              <span class="text-uppercase">
                {{ t('global.button.view') }}
                statement
              </span>
            </div>
          </base-button>
        </div>
      </header>

      <hr class="line" />
      <a-row type="flex" align="middle" justify="space-between" style="gap: 5px">
        <a-col>
          <template v-if="statementDetails.deadline">
            <span class="text-dark-gray me-1">
              <font-awesome-icon
                :icon="['far', 'calendar-xmark']"
                size="lg"
                class="text-dark-gray"
              />
            </span>
            <span class="text-uppercase text-500">{{ t('global.label.deadline') }}</span
            >: <b>{{ statementDetails.deadline }}</b>
          </template>
        </a-col>
        <a-col>
          <a
            :href="goToBilling()"
            target="_blank"
            class="text-danger text-500"
            style="border-bottom: 1px dashed"
          >
            {{ t('global.label.customer_billing') }}
            <font-awesome-icon :icon="['fas', 'arrow-up-right-from-square']" />
          </a>
        </a-col>
      </a-row>
      <hr class="line" />

      <a-row
        :gutter="24"
        type="flex"
        align="top"
        justify="space-between"
        style="gap: 15px"
        class="mt-4"
      >
        <a-col flex="auto">
          <div style="border: 1px solid #e9e9e9; border-radius: 8px" class="p-4">
            <a-row type="flex" align="top" justify="space-between">
              <a-col>
                <p class="m-0 p-0"><b style="font-size: 16px">USD</b></p>
                <p class="m-0 p-0"><span style="font-size: 14px" class="text-gray">USD</span></p>
              </a-col>
              <a-col>
                <p class="m-0 p-0">
                  <b style="font-size: 16px" class="text-gray">
                    {{ t('global.label.payment_received') }}
                  </b>
                </p>
                <p class="m-0 p-0 text-right">
                  <span
                    style="font-size: 24px"
                    v-bind:class="['text-600', totalStatement > 0 ? '' : 'text-gray']"
                  >
                    $ {{ formatNumber({ number: totalStatement, digits: 2 }) }}
                  </span>
                </p>
              </a-col>
            </a-row>

            <div
              class=""
              style="border-top: 2px dashed #e9e9e9"
              v-if="
                filesStore.getReportStatements?.payment_received &&
                filesStore.getReportStatements?.payment_received.length > 0
              "
            >
              <table style="width: 100%" class="mt-3">
                <thead>
                  <tr>
                    <th class="text-center text-gray text-400">
                      <small class="text-uppercase">
                        {{ t('global.column.date') }} - {{ t('global.column.hour') }}
                      </small>
                    </th>
                    <th class="text-center text-gray text-400">
                      <small class="text-uppercase">{{ t('global.column.description') }}</small>
                    </th>
                    <th class="text-center text-gray text-400">
                      <small class="text-uppercase">{{ t('global.column.type') }}</small>
                    </th>
                    <th class="text-center text-gray text-400">
                      <small class="text-uppercase">{{ t('global.column.amount') }}</small>
                    </th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="(payment, p) in filesStore.getReportStatements?.payment_received">
                    <td class="text-center text-dark-gray">
                      <span style="font-size: 14px">
                        {{ formatDate(payment.fecha, 'short') }}
                        <small style="font-size: 8px" v-if="payment.hora">
                          {{ payment.hora }} hrs
                        </small>
                      </span>
                    </td>
                    <td class="text-center">
                      <span class="text-500 text-dark">{{ payment.descripcion }}</span>
                    </td>
                    <td class="text-center">
                      <span class="text-500 text-dark-gray">{{ payment.tipo }}</span>
                    </td>
                    <td class="text-center">
                      <span class="text-600 text-dark"
                        >$ {{ formatNumber({ number: payment.monto, digits: 2 }) }}</span
                      >
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </a-col>
        <a-col flex="auto">
          <div style="border: 1px solid #e9e9e9; border-radius: 8px" class="p-4 mb-4">
            <a-row type="flex" align="top" justify="space-between">
              <a-col>
                <p class="m-0 p-0"><b style="font-size: 16px">USD</b></p>
                <p class="m-0 p-0"><span style="font-size: 14px" class="text-gray">USD</span></p>
              </a-col>
              <a-col>
                <p class="m-0 p-0 text-right">
                  <b style="font-size: 16px" class="text-gray text-capitalize">{{
                    t('global.label.balance')
                  }}</b>
                </p>
                <p class="m-0 p-0 text-right">
                  <span
                    style="font-size: 24px"
                    :class="[
                      'text-600',
                      filesStore.getReportStatements.balance != 0 ? 'text-danger' : 'text-gray',
                    ]"
                    >$
                    {{
                      formatNumber({ number: filesStore.getReportStatements.balance, digits: 2 })
                    }}</span
                  >
                </p>
              </a-col>
            </a-row>
          </div>

          <div style="border: 1px solid #e9e9e9; border-radius: 8px" class="p-4">
            <a-row type="flex" align="top" justify="space-between">
              <a-col>
                <p class="m-0 p-0"><b style="font-size: 16px">USD</b></p>
                <p class="m-0 p-0"><span style="font-size: 14px" class="text-gray">USD</span></p>
              </a-col>
              <a-col>
                <p class="m-0 p-0 text-right">
                  <b style="font-size: 16px" class="text-gray text-capitalize">
                    {{ t('global.label.billed') }}
                  </b>
                </p>
                <p class="m-0 p-0 text-right">
                  <span style="font-size: 24px" class="text-600 text-gray"
                    >$
                    {{
                      formatNumber({ number: filesStore.getReportStatements.statement, digits: 2 })
                    }}</span
                  >
                </p>
              </a-col>
            </a-row>
            <div
              class=""
              style="border-top: 2px dashed #e9e9e9"
              v-if="
                filesStore.getReportStatements.credit_note?.total > 0 ||
                filesStore.getReportStatements.debit_note?.total > 0
              "
            >
              <table style="width: 100%" class="mt-3">
                <tbody>
                  <tr v-if="filesStore.getReportStatements.credit_note?.total > 0">
                    <td class="text-dark-gray">
                      <small class="text-uppercase">{{ t('global.label.credit_note') }}</small>
                    </td>
                    <!-- td class="text-center">
                      <span class="text-500 text-dark">{{
                        filesStore.getReportStatements.credit_note.descri
                      }}</span>
                    </td -->
                    <td class="text-center">
                      <span class="text-500 text-dark-gray">{{
                        formatDate(filesStore.getReportStatements.credit_note.date, 'short')
                      }}</span>
                    </td>
                    <td class="text-center">
                      <span class="text-600 text-danger"
                        >$ {{ filesStore.getReportStatements.credit_note.total }}</span
                      >
                    </td>
                    <td class="text-center">
                      <LoadingOutlined v-if="loadingNoteCredit" />
                      <span
                        class="cursor-pointer text-danger"
                        v-else
                        @click="downloadPdfNoteCredit"
                      >
                        <a-tooltip placement="right">
                          <template #title>{{ t('global.button.download') }}</template>
                          <font-awesome-icon :icon="['fas', 'cloud-arrow-down']" size="lg" fade />
                        </a-tooltip>
                      </span>
                    </td>
                  </tr>

                  <tr v-if="filesStore.getReportStatements.debit_note?.total > 0">
                    <td class="text-dark-gray">
                      <small class="text-uppercase">{{ t('global.label.debit_note') }}</small>
                    </td>
                    <!-- td class="text-center">
                      <span class="text-500 text-dark">{{
                        filesStore.getReportStatements.debit_note.descri
                      }}</span>
                    </td -->
                    <td class="text-center">
                      <span class="text-500 text-dark-gray">{{
                        formatDate(filesStore.getReportStatements.debit_note.date, 'short')
                      }}</span>
                    </td>
                    <td class="text-center">
                      <span class="text-600 text-success"
                        >$ {{ filesStore.getReportStatements.debit_note.total }}</span
                      >
                    </td>
                    <td class="text-center">
                      <LoadingOutlined v-if="loadingNoteDebit" />
                      <span
                        class="cursor-pointer text-success"
                        v-else
                        @click="downloadPdfNoteDebit"
                      >
                        <a-tooltip placement="right">
                          <template #title>{{ t('global.button.download') }}</template>
                          <font-awesome-icon :icon="['fas', 'cloud-arrow-down']" size="lg" />
                        </a-tooltip>
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </a-col>
      </a-row>
    </div>

    <div v-if="flagEditStatement">
      <a-row type="flex" justify="space-between" align="middle" class="my-3">
        <a-col>
          <a-row type="flex" justify="start" align="middle">
            <a-col>
              <a-button type="primary" size="large" @click="handleClose">
                <font-awesome-icon icon="arrow-left"></font-awesome-icon>
              </a-button>
            </a-col>
            <a-col>
              <span class="ms-2 text-500 text-dark-gray text-uppercase" style="font-size: 14px">
                {{ t('global.button.back') }}
              </span>
            </a-col>
          </a-row>
        </a-col>
        <a-col v-if="flag_show_statement">
          <template v-if="statementDetails?.logs.length > 0 && verifyDateInit">
            <a-button danger size="large" @click="flagHistorialStatement = !flagHistorialStatement">
              <a-row type="flex" align="middle" justify="start" style="gap: 4px">
                <a-col>
                  <template v-if="!flagHistorialStatement">
                    <font-awesome-icon :icon="['fas', 'clock-rotate-left']" size="lg" />
                  </template>
                  <template v-else>
                    <font-awesome-icon :icon="['fas', 'xmark']" size="lg" />
                  </template>
                </a-col>
                <a-col>
                  <span v-if="!flagHistorialStatement">{{
                    t('global.label.modification_history')
                  }}</span>
                  <span v-if="flagHistorialStatement">{{ t('global.label.close_history') }}</span>
                </a-col>
              </a-row>
            </a-button>
          </template>
        </a-col>
      </a-row>

      <a-row :gutter="24" style="flex-flow: nowrap">
        <a-col flex="auto">
          <div
            v-bind:class="[!flagChangePrices ? 'py-4 px-5 my-5 bg-light' : '']"
            v-bind:style="!flagChangePrices ? `border-radius: 8px; border: 1px solid #E9E9E9;` : ''"
          >
            <template v-if="flag_show_statement">
              <a-row
                type="flex"
                justify="space-between"
                align="middle"
                v-if="!flagChangePrices"
                class="bg-white px-5 py-2"
                style="border-radius: 12px"
              >
                <a-col>
                  <b>{{ t('global.label.total_changes') }}: </b>
                  <i>{{ statementDetails?.logs.length }} {{ t('global.label.change') }}(s) </i>
                </a-col>
                <a-col v-if="statementDetails?.logs.length > 0 && flagShowStatementLog">
                  <a-row type="flex" align="middle" justify="start" style="gap: 4px">
                    <a-col>
                      <span @click="handleProcess(true)" class="cursor-pointer">
                        <IconHistory color="#ff4d4f" :width="15" :height="15" />
                      </span>
                    </a-col>
                    <a-col>
                      <span
                        class="text-danger text-400 cursor-pointer"
                        @click="handleProcess(true)"
                        >{{ t('global.label.restore_version') }}</span
                      >
                    </a-col>
                  </a-row>
                </a-col>
              </a-row>
            </template>

            <div
              v-bind:class="[!flagChangePrices ? 'bg-white px-5 pt-5 pb-2' : '', 'my-5']"
              style="border-radius: 10px"
            >
              <a-row
                type="flex"
                justify="space-between"
                align="top"
                style="border: 1px solid #e9e9e9; border-radius: 8px"
                class="p-4"
              >
                <a-col :span="17">
                  <b>{{ statementDetails.file_client.name }}</b>
                  <p class="m-0 p-0">
                    {{ statementDetails.file_client.address }},
                    {{ statementDetails.file_client.city }}
                  </p>
                  <p class="m-0 p-0">{{ statementDetails.file_client.country }}</p>
                  <br />
                  <p>VAT/NIF/ID : {{ statementDetails.file_client.ruc }}</p>
                </a-col>
                <a-col :span="7" class="text-right">
                  <p class="m-0 p-0">{{ statementDetails.limatours.address }}</p>
                  <p class="m-0 p-0">{{ statementDetails.limatours.city }}</p>
                  <p class="m-0 p-0">RUC : {{ statementDetails.limatours.ruc }}</p>
                </a-col>
              </a-row>

              <div class="bg-light py-3 px-4 my-3" style="border-radius: 10px">
                <a-row type="flex" justify="end" align="middle" style="gap: 10px">
                  <a-col>
                    <span class="text-dark-gray">
                      <font-awesome-icon :icon="['far', 'clock']" size="lg" />
                    </span>
                  </a-col>
                  <a-col> {{ t('global.label.deadline') }}: </a-col>
                  <a-col>
                    <template v-if="!flag_change_deadline">
                      <template v-if="statementDetails.deadline">
                        <b>{{
                          formatDate(dayjs(new_date_deadline).format('YYYY-MM-DD'), 'short')
                        }}</b>
                      </template>
                      <template v-else> ... </template>
                      <span
                        v-if="verifyDateInit"
                        class="text-danger cursor-pointer ms-2"
                        @click="toggleDateDeadline"
                      >
                        <a-tooltip>
                          <template #title> {{ t('files.label.modify_date') }}</template>
                          <font-awesome-icon :icon="['fas', 'pencil']" size="lg" />
                        </a-tooltip>
                      </span>
                    </template>
                    <template v-else>
                      <a-date-picker
                        v-model:value="new_date_deadline"
                        format="DD/MM/YYYY"
                        :allowClear="false"
                        :disabledDate="disabledDate"
                        @blur="toggleDateDeadline"
                        @change="toggleDateDeadline(false)"
                      />
                      <span
                        class="text-danger cursor-pointer ms-2"
                        @click="toggleDateDeadline(false)"
                      >
                        <font-awesome-icon :icon="['fas', 'check']" size="lg" />
                      </span>
                    </template>
                  </a-col>
                </a-row>
              </div>

              <!-- Table Section -->
              <div class="table-section">
                <div
                  v-bind:class="[
                    'invoice-grid header',
                    flagHistorialStatement ? 'column-4' : 'column-5',
                  ]"
                >
                  <div class="mx-2 text-uppercase">{{ t('global.label.description') }}</div>
                  <div class="text-center text-uppercase">QTY</div>
                  <div class="text-center text-uppercase">{{ t('global.column.unit_price') }}</div>
                  <div class="text-center text-uppercase">{{ t('global.column.amount') }}</div>
                  <div
                    class="text-center text-uppercase"
                    v-if="!flagHistorialStatement && verifyDateInit"
                  >
                    {{ t('global.column.actions') }}
                  </div>
                </div>
                <!-- Rows -->

                <div
                  v-bind:class="[
                    'invoice-grid row',
                    flagHistorialStatement ? 'column-4' : 'column-5',
                  ]"
                  v-for="(_detail, index) in statementDetails.details"
                  :key="`detail-${index}`"
                >
                  <template v-if="!_detail.editable">
                    <div class="mx-2 text-left text-uppercase">
                      <small>{{ _detail.description }}</small>
                    </div>
                    <div class="text-center">
                      {{ _detail.quantity }}
                    </div>
                    <div class="text-center">
                      $ {{ formatNumber({ number: _detail.unit_price, digits: 2 }) }}
                    </div>
                    <div class="text-center">
                      $ {{ formatNumber({ number: _detail.amount, digits: 2 }) }}
                    </div>
                  </template>
                  <template v-else>
                    <div class="mx-2 text-left">
                      <template v-if="_detail?.id > 0">
                        <a-input
                          style="font-size: 12px"
                          v-model:value="_detail.description"
                          :disabled="_detail.locked"
                        />
                      </template>
                      <template v-else>
                        <a-select
                          :allowClear="false"
                          class="w-100"
                          style="font-size: 12px"
                          :showSearch="true"
                          v-model:value="_detail.detail_id"
                          :fieldNames="{ label: 'description', value: 'id' }"
                          :options="options"
                          :disabled="_detail.locked"
                        >
                        </a-select>
                        <template v-if="_detail.detail_id === ''">
                          <a-input
                            v-model:value="_detail.description"
                            placeholder="Ingrese una descripción..."
                            class="mt-2"
                            :disabled="_detail.locked"
                          />
                        </template>
                      </template>
                    </div>
                    <div class="text-center">
                      <a-input
                        type="number"
                        placeholder="0"
                        min="0"
                        v-model:value="_detail.quantity"
                        :disabled="_detail.locked"
                      />
                    </div>
                    <div class="text-center">
                      <a-input
                        type="number"
                        placeholder="0.00"
                        min="0"
                        v-model:value="_detail.unit_price"
                        :disabled="_detail.locked"
                      />
                    </div>
                    <div class="text-center">
                      $
                      {{
                        formatNumber({
                          number: _detail.quantity * _detail.unit_price || 0,
                          digits: 2,
                        })
                      }}
                    </div>
                  </template>
                  <div
                    class="text-center"
                    v-if="!flagHistorialStatement && !_detail.locked && verifyDateInit"
                  >
                    <a-row type="flex" justify="start" align="middle" style="gap: 5px">
                      <template v-if="!(totalStatement > 0)">
                        <template v-if="_detail?.id > 0">
                          <template v-if="!_detail.editable">
                            <a-col>
                              <a-tooltip>
                                <template #title>
                                  <small class="text-uppercase">{{
                                    t('global.label.edit_line')
                                  }}</small>
                                </template>
                                <span
                                  class="cursor-pointer text-dark-gray"
                                  @click="editDetail(_detail)"
                                >
                                  <font-awesome-icon :icon="['fas', 'pencil']" />
                                </span>
                              </a-tooltip>
                            </a-col>
                          </template>
                          <template v-else>
                            <a-col>
                              <a-tooltip>
                                <template #title>
                                  <small class="text-uppercase">{{
                                    t('global.button.save')
                                  }}</small>
                                </template>
                                <span
                                  class="cursor-pointer text-dark-gray"
                                  @click="_detail.editable = false"
                                >
                                  <font-awesome-icon :icon="['fas', 'check']" />
                                </span>
                              </a-tooltip>
                            </a-col>
                          </template>
                          <a-col v-if="!_detail.editable && statementDetails.details.length > 1">
                            <a-tooltip>
                              <template #title>
                                <small class="text-uppercase">{{
                                  t('global.label.delete_line')
                                }}</small>
                              </template>
                              <span
                                class="cursor-pointer text-dark-gray"
                                @click="deleteDetail(index)"
                              >
                                <font-awesome-icon :icon="['fas', 'trash']" />
                              </span>
                            </a-tooltip>
                          </a-col>
                        </template>
                        <template v-else>
                          <a-col v-if="index > 0">
                            <a-tooltip>
                              <template #title>
                                <small class="text-uppercase">{{
                                  t('global.label.delete_line')
                                }}</small>
                              </template>
                              <span
                                class="cursor-pointer text-dark-gray"
                                @click="removeItem(index)"
                              >
                                <font-awesome-icon :icon="['fas', 'minus']" />
                              </span>
                            </a-tooltip>
                          </a-col>
                        </template>
                      </template>

                      <template v-if="index == statementDetails.details.length - 1">
                        <a-col>
                          <a-tooltip>
                            <template #title>
                              <small class="text-uppercase">{{ t('global.label.new_line') }}</small>
                            </template>
                            <template v-if="_detail.quantity * _detail.unit_price > 0">
                              <span class="cursor-pointer text-dark-gray" @click="addItem">
                                <font-awesome-icon :icon="['fas', 'plus']" />
                              </span>
                            </template>
                          </a-tooltip>
                        </a-col>
                      </template>
                    </a-row>
                  </div>
                </div>

                <div
                  v-bind:class="[
                    'invoice-grid-footer',
                    flagHistorialStatement ? 'column-4' : 'column-5',
                  ]"
                >
                  <div></div>
                  <div class="text-center total-label">{{ t('global.column.total') }}:</div>
                  <div class="text-center">&nbsp;</div>
                  <div class="text-center row-total-amount">
                    $ {{ formatNumber({ number: totalAmount, digits: 2 }) }}
                  </div>
                </div>
              </div>

              <div>
                <a-row type="flex" justify="space-between" align="top" style="color: #737373">
                  <a-col flex="auto"></a-col>
                  <a-col flex="auto">
                    <a-row
                      type="flex"
                      justify="space-between"
                      align="middle"
                      style="border-bottom: 1px solid #e9e9e9"
                      class="py-2 my-3"
                    >
                      <a-col>
                        <span style="font-size: 16px" class="text-600"
                          >{{ t('global.label.payment_details') }}:</span
                        >
                      </a-col>
                      <a-col>
                        <a
                          href="https://litopay.pe"
                          style="border-bottom: 1px dashed; padding-bottom: 3px"
                          target="_blank"
                        >
                          <span class="text-700 me-1">LITOPAY</span>
                          <font-awesome-icon :icon="['fas', 'arrow-up-right-from-square']" />
                        </a>
                      </a-col>
                    </a-row>
                    <a-row type="flex" justify="space-between" align="top">
                      <a-col>
                        <p>{{ t('global.label.bank') }}:</p>
                        <p>SWIFT:</p>
                        <p>N°:</p>
                      </a-col>
                      <a-col>
                        <p>
                          <b>BANCO INTERAMERICANO DE FINANZAS</b><br />Rivera Navarrete Av. 600<br />Lima
                          27, Perú
                        </p>
                        <p><b>BIFSPEPL</b></p>
                        <p><b>7000721979</b></p>
                      </a-col>
                    </a-row>
                  </a-col>
                </a-row>
              </div>

              <div v-if="!flagHistorialStatement && totalAmount > 0 && verifyDateInit" class="my-3">
                <a-row
                  type="flex"
                  justify="start"
                  align="middle"
                  style="gap: 4px"
                  v-bind:class="[flagCheckSend ? 'text-danger text-600' : 'text-400']"
                  v-if="!flagShowStatementLog"
                >
                  <a-col>
                    <span class="cursor-pointer" @click="flagCheckSend = !flagCheckSend">
                      <font-awesome-icon
                        :icon="['far', flagCheckSend ? 'square-check' : 'square']"
                      />
                    </span>
                  </a-col>
                  <a-col>
                    <span
                      class="cursor-pointer text-uppercase"
                      style="font-size: 13px"
                      @click="flagCheckSend = !flagCheckSend"
                    >
                      {{ t('global.button.save') }} {{ t('global.label.and') }}
                      {{ t('global.button.download') }}
                    </span>
                  </a-col>
                </a-row>

                <div class="text-center my-3">
                  <a-button
                    class="text-600"
                    @click="handleClose"
                    :disabled="filesStore.isLoadingAsync || filesStore.isLoading"
                    danger
                    size="large"
                  >
                    {{ t('global.button.back') }}
                  </a-button>
                  <a-button
                    v-if="!flagShowStatementLog"
                    v-on:click="handleProcess(false)"
                    :disabled="filesStore.isLoadingAsync || filesStore.isLoading"
                    type="primary"
                    class="btn-danger mx-2 px-4 text-600"
                    default
                    size="large"
                  >
                    {{ t('global.button.continue') }}
                  </a-button>
                </div>
              </div>
            </div>
          </div>
        </a-col>
        <a-col flex="auto" v-if="flagHistorialStatement && statementDetails.logs.length > 0">
          <div class="mt-5">
            <a-card id="historial" style="width: 100%">
              <template #title>{{ t('global.label.history') }}</template>
              <template #extra></template>
              <a-tooltip placement="right">
                <template #title>{{ t('global.label.current_version') }}</template>
                <div
                  @click="resetStatement"
                  v-bind:class="['cursor-pointer p-3', !flagShowStatementLog ? 'bg-light' : '']"
                  style="border-left: 2px solid #ccc"
                >
                  <p style="font-size: 12px" class="mb-2 p-0">
                    {{
                      formatDateTime(
                        statementDetails.logs[statementDetails.logs.length - 1].updated_at,
                        'DD/MM/YYYY',
                        locale
                      )
                    }}
                  </p>
                  <p class="mb-1 p-0" style="color: #737373; font-size: 10px">
                    <i>{{ t('global.label.current_version') }}</i>
                  </p>
                  <p class="mb-0 p-0 text-uppercase" style="color: #4f4b4b; font-size: 10px">
                    <a-badge
                      color="green"
                      :text="
                        statementDetails.logs[statementDetails.logs.length - 1].username || 'Admin'
                      "
                    />
                  </p>
                </div>
              </a-tooltip>
              <template v-for="(_log, l) in sortedLogs" :key="`log-${l}`">
                <a-tooltip placement="right">
                  <template #title>{{ t('global.label.view_version') }}</template>
                  <div
                    @click="setStatement(_log)"
                    v-bind:class="[
                      'cursor-pointer p-3',
                      _log.active && flagShowStatementLog ? 'bg-light' : '',
                    ]"
                    style="border-left: 2px solid #ccc"
                  >
                    <p style="font-size: 12px" class="mb-2 p-0">
                      {{ formatDateTime(_log?.created_at, 'DD/MM/YYYY', locale) }}
                      <template v-if="l === sortedLogs.length - 1">
                        - <i>{{ t('global.label.first_version') }}</i></template
                      >
                    </p>
                    <p
                      class="mb-1 p-0"
                      v-if="_log.active && flagShowStatementLog"
                      style="color: #737373; font-size: 10px"
                    >
                      <i>{{ t('global.label.active_version') }}</i>
                    </p>
                    <p class="mb-0 p-0 text-uppercase" style="color: #4f4b4b; font-size: 10px">
                      <a-badge color="green" :text="_log.username || 'Admin'" />
                    </p>
                  </div>
                </a-tooltip>
              </template>
            </a-card>
          </div>
        </a-col>
      </a-row>
    </div>
  </div>

  <template v-else>
    <loading-skeleton />
  </template>

  <ModalStatementDownload
    v-bind:is-open.sync="modalIsOpenStatementDownload"
    @update:is-open="modalIsOpenStatementDownload = $event"
    ref="modal_statement_download"
  />

  <ModalDebito
    :statement-details="statementDetails"
    v-bind:is-open.sync="flagModalDebito"
    @update:is-open="flagModalDebito = $event"
    @handleSearchStatements="handleClose"
  />

  <ModalCredito
    :statement-details="statementDetails"
    v-bind:is-open.sync="flagModalCredito"
    @update:is-open="flagModalCredito = $event"
    @handleSearchStatements="handleClose"
  />
</template>

<script setup>
  import BaseButton from '@/components/files/reusables/BaseButton.vue';
  import ModalStatementDownload from '@/components/files/statements/components/ModalStatementDownload.vue';
  import { onBeforeMount, ref, computed } from 'vue';
  import IconHistory from '@/quotes/components/icons/IconHistory.vue';
  import ModalCredito from '../statements/components/ModalCredito.vue';
  import ModalDebito from '../statements/components/ModalDebito.vue';
  import { useFilesStore, useDownloadStore } from '@store/files';
  import { notification } from 'ant-design-vue';
  import dayjs from 'dayjs';
  import { LoadingOutlined } from '@ant-design/icons-vue';
  import { formatNumber, formatDateTime } from '@/utils/files.js';
  import LoadingSkeleton from '@/components/global/LoadingSkeleton.vue';

  import { useI18n } from 'vue-i18n';
  const { t, locale } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();
  const downloadStore = useDownloadStore();

  const loadingNoteCredit = ref(false);
  const loadingNoteDebit = ref(false);
  const modalIsOpenStatementDownload = ref(false);
  const flagEditStatement = ref(false);
  const flagHistorialStatement = ref(false);
  const flagChangePrices = ref(false);
  const flagCheckSend = ref(false);
  const flagModalDebito = ref(false);
  const flagModalCredito = ref(false);
  const flagShowStatementLog = ref(false);
  const modal_statement_download = ref({});
  const flag_show_statement = ref(false);

  const statementCurrent = ref({});

  const setStatement = (log) => {
    statementDetails.value.logs.forEach((_log) => {
      _log.active = false;
    });

    log.active = true;

    flagShowStatementLog.value = true;

    statementDetails.value.details = log.details;
    statementDetails.value.id = log.id;
    statementDetails.value.total = log.total;
  };

  const resetStatement = () => {
    statementDetails.value = JSON.parse(JSON.stringify(statementCurrent.value));
    flagShowStatementLog.value = false;
  };

  const showStatementDownload = () => {
    modalIsOpenStatementDownload.value = true;
  };

  const toggleStatementHistory = () => {
    toggleStatement();
    flagHistorialStatement.value = true;
  };

  const toggleStatement = () => {
    flagShowStatementLog.value = false;
    flagEditStatement.value = !flagEditStatement.value;

    const draftStatement = localStorage.getItem(`draft_statment_${filesStore.getFile.id}`);

    if (draftStatement) {
      statementDetails.value = JSON.parse(draftStatement);
    }
  };

  const statementDetails = ref({
    date: '',
    deadline: '',
    file_number: '',
    file_name: '',
    file_ref: '',
    file_date_in: '',
    file_date_out: '',
    limatours: {
      address: '',
      city: '',
      ruc: '',
    },
    file_client: {
      name: '',
      address: '',
      ruc: '',
    },
    details: [],
    total: 0,
  });

  const flag_change_deadline = ref(false);
  const new_date_deadline = ref('');

  const toggleDateDeadline = (close = true) => {
    if (close) {
      flag_change_deadline.value = !flag_change_deadline.value;
    } else {
      flag_change_deadline.value = close;
    }
  };

  const verifyDateInit = computed(() => {
    return dayjs(filesStore.getFile.dateIn) > dayjs(new Date());
  });

  const sortedLogs = computed(() => {
    return [...statementDetails.value.logs].sort(
      (a, b) => new Date(b.updated_at) - new Date(a.updated_at)
    );
  });

  const disabledDate = (current) => {
    let date1 = dayjs(filesStore.getFile.dateIn).add(-10, 'day');
    return current && (current < dayjs(new Date()) || current > dayjs(date1));
  };

  // Formateador de fechas
  const formatDate = (dateString, format) => {
    if (!dateString) {
      return '';
    }

    const date = new Date(`${dateString} 00:00:00`);

    if (isNaN(date.getTime())) return ''; // Retorna vacío si la fecha no es válida

    switch (format) {
      case 'long': // Ejemplo: November 05 2024
        return date
          .toLocaleDateString('es-ES', {
            month: 'long',
            day: '2-digit',
            year: 'numeric',
          })
          .replace(',', '');

      case 'long-comma': // Ejemplo: December 25, 2024
        return date.toLocaleDateString('es-ES', {
          month: 'long',
          day: '2-digit',
          year: 'numeric',
        });

      case 'short': // Ejemplo: 17/04/2022
        return date.toLocaleDateString('es-ES', {
          day: '2-digit',
          month: '2-digit',
          year: 'numeric',
        });

      default:
        return dateString;
    }
  };

  const options = ref([]);

  const searchStatements = async () => {
    const details = await downloadStore.getStatementByFileId(filesStore.getFile.id);

    if (details.details) {
      flag_show_statement.value = true;
      statementDetails.value = {
        ...details,
        date: formatDate(details.date, 'long'),
        file_date_in: formatDate(details.file_date_in, 'long-comma'),
        file_date_out: formatDate(details.file_date_in, 'long-comma'),
        deadline: formatDate(details.deadline, 'short'),
      };
      localStorage.setItem(
        `draft_statement_${filesStore.getFile.id}`,
        JSON.stringify(statementDetails.value)
      );
    }
  };

  const downloadPdfNoteCredit = async () => {
    loadingNoteCredit.value = true;
    await downloadStore.downloadStatementCreditNote(filesStore.getFile.id);
    loadingNoteCredit.value = false;
  };

  const downloadPdfNoteDebit = async () => {
    loadingNoteDebit.value = true;
    await downloadStore.downloadStatementDebitNote(filesStore.getFile.id);
    loadingNoteDebit.value = false;
  };

  onBeforeMount(async () => {
    filesStore.initedStatements();
    await searchStatements();
    await filesStore.getStatements(filesStore.getFile.id);

    if (flag_show_statement) {
      localStorage.setItem('options', JSON.stringify(statementDetails.value.details));
      options.value = JSON.parse(localStorage.getItem('options'));
    }

    options.value.push({
      description: 'Otro',
      id: '',
    });

    new_date_deadline.value = dayjs(
      JSON.parse(JSON.stringify(statementDetails.value.deadline)),
      'DD/MM/YYYY'
    );
    statementCurrent.value = JSON.parse(JSON.stringify(statementDetails.value));
  });

  const totalStatement = computed(() => {
    const payments = filesStore.getReportStatements.payment_received;

    if (payments) {
      return payments.reduce((sum, payment) => {
        const amount = parseFloat(payment.monto) || 0; // Asegura que sea un número válido
        return sum + amount;
      }, 0);
    } else {
      return 0;
    }
  });

  const goToBilling = () => {
    return `${window.url_front_a2}billing_report?client_id=${filesStore.getFile.clientId}&client_code=${filesStore.getFile.clientCode}&file_code=${filesStore.getFile.fileNumber}&flag_search=true`;
  };

  const editDetail = (_detail) => {
    _detail.editable = true;
  };

  const deleteDetail = (index) => {
    removeItem(index);
  };

  const removeItem = (index) => {
    statementDetails.value.details.splice(index, 1);

    setTimeout(() => {
      if (statementDetails.value.details.length === 0) {
        addItem();
      }
    }, 10);
  };

  const addItem = () => {
    const params = {
      description: '',
      unit_price: '',
      quantity: '',
      amount: '',
      editable: true,
    };
    statementDetails.value.details.push(params);
  };

  const totalAmount = computed(() => {
    const details = statementDetails.value.details;

    const total = details.reduce((sum, item) => {
      const amount = parseFloat(item.quantity * item.unit_price) || 0; // Asegura que sea un número válido
      return sum + amount;
    }, 0);

    return parseFloat(total);
  });

  const handleClose = () => {
    flagEditStatement.value = false;
  };

  const handleProcess = async (restore) => {
    filesStore.initedStatements();

    statementDetails.value.details.forEach((detail) => {
      detail.locked = true;
      if (detail.detail_id != undefined) {
        detail.id = '';

        if (detail.detail_id !== '') {
          detail.description =
            options.value.find((option) => option.id === detail.detail_id)?.description || '';
        }
      }
    });

    const date = dayjs(new_date_deadline.value).format('YYYY-MM-DD');

    await filesStore.updateStatement(
      filesStore.getFile.id,
      date,
      statementDetails.value.details,
      restore
    );

    statementDetails.value.details.forEach((detail) => {
      detail.editable = false;
      detail.locked = false;
    });

    if (filesStore.getError == '' || filesStore.getError == null) {
      localStorage.removeItem(`draft_statement_${filesStore.getFile.id}`);
      handleClose();

      if (flagCheckSend.value) {
        modalIsOpenStatementDownload.value = true;
      }
    } else {
      filesStore.finishedStatements();
      notification.error({
        message: 'Error',
        description: filesStore.getError,
      });
    }
  };
</script>
<style lang="scss">
  #files-edit {
    .files-statement-info {
      min-width: auto;

      &-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 20px 0;
      }

      &-title {
        font-style: normal;
        font-weight: 700;
        font-size: 18px;
        line-height: 55px;
        color: #4f4b4b;
      }

      &-title-info {
        color: #3d3d3d;
        font-weight: 700;
        margin-bottom: 20px;
        font-style: normal;
        font-size: 24px;
        line-height: 31px;
      }

      &-buttons {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 25px;

        & :deep(svg) {
          vertical-align: middle;
        }

        .btn-reminder {
          background-color: #ffffff;
          color: #eb5757;
          width: 50px;
          height: 45px;
          border-color: #eb5757;

          &:hover {
            background-color: #fff6f6;
            color: #575757;
            border-color: #c63838;
          }
        }

        .btn-download {
          background-color: #fafafa;
          color: #575757;
          border-color: #575757;

          &:hover {
            background-color: #e9e9e9;
            color: #575757;
            border-color: #e9e9e9;
          }
        }

        .btn-debit,
        .btn-credit,
        .btn-download {
          background-color: #fafafa;
          border-color: #fafafa;

          &:hover {
            background-color: #e9e9e9;
            color: #575757;
            border-color: #e9e9e9;
          }
        }

        base-button {
          display: inline-block;
          padding: 10px 20px;
          font-size: 1rem;
          border-radius: 4px;
          cursor: pointer;
          transition: background-color 0.3s ease;

          & :deep(svg) {
            margin-left: 8px;
          }

          &[danger] {
            border: 2px solid #eb5757;
            color: #eb5757;

            &:hover {
              background-color: #eb5757;
              color: #fff;
            }
          }

          &[type='outline-main'] {
            border: 2px solid #575757;
            color: #575757;

            &:hover {
              background-color: #575757;
              color: #fff;
            }
          }
        }
      }
    }

    .table-section {
      margin-bottom: 20px;

      .invoice-grid {
        display: grid;

        gap: 10px;
        align-items: center;
        padding: 10px 0;
      }

      .column-5 {
        grid-template-columns: 3fr 1fr 1.5fr 1.5fr 1fr;
      }

      .column-4 {
        grid-template-columns: 3fr 1fr 1.5fr 1.5fr;
      }

      .invoice-grid-footer {
        font-family: Montserrat, serif;
        display: grid;
        align-items: center;

        /* Fondo de color a partir de la segunda columna */
        > div:nth-child(2) {
          padding: 10px 12px;
          background-color: #e9e9e9;
        }

        > div:nth-child(3) {
          padding: 10px;
          background-color: #e9e9e9;
        }

        > div:nth-child(2) {
          border-bottom-left-radius: 6px;
          border-top-left-radius: 6px;
        }

        > div:nth-child(4) {
          padding: 7px;
          // background-color: #e9e9e9;
        }

        > div:nth-child(3) {
          border-bottom-right-radius: 6px;
          border-top-right-radius: 6px;
        }

        > div:nth-child(1) {
          padding: 10px 0;
          background-color: transparent; /* La primera columna sin fondo */
        }
      }

      .total-label {
        font-family: Montserrat, serif;
        font-weight: 400;
        font-size: 14px;
      }

      .header {
        font-family: Montserrat, serif;
        font-weight: 700;
        font-size: 14px;
      }

      .row {
        background-color: #fafafa;
        font-family: Montserrat, serif;
        font-weight: 400;
        font-size: 14px;
        padding: 10px 0;
        margin-bottom: 15px;
        border-radius: 6px;
      }

      .row-total-amount {
        font-weight: 700;
        font-size: 18px;
      }
    }

    .ant-badge-status-text {
      font-size: 10px !important;
    }
  }

  #historial {
    .ant-card-head {
      border: 1px solid #e9e9e9 !important;
      border-bottom: 0 !important;
      background-color: #fff !important;
      color: #4f4b4b !important;
    }

    .ant-card-body {
      border: 1px solid #e9e9e9 !important;
    }
  }

  .historial-body {
    padding: 1rem;
  }

  .line {
    border: 1px solid #e9e9e9 !important;
  }
</style>
