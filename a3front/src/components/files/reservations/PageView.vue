<template>
  <template
    v-if="
      filesStore.getFileReports.hotels?.length > 0 ||
      filesStore.getFileReports.cruises?.length > 0 ||
      filesStore.getFileReports.trains?.length > 0 ||
      filesStore.getFileReports.tickets?.length > 0 ||
      filesStore.getFileReports.overflights?.length > 0 ||
      filesStore.getFileReports.restaurants?.length > 0 ||
      filesStore.getFileReports.others?.length > 0
    "
  >
    <a-row
      type="flex"
      justify="space-between"
      align="middle"
      class="px-2 mt-2"
      v-if="!filesStore.isLoadingReports"
    >
      <a-col>
        <span class="text-600">{{ t('global.label.title_reservations_page') }}</span>
      </a-col>
      <a-col>
        <!-- <a-button
          type="primary"
          size="large"
          danger
          class="text-500"
          v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
        >
          <i class="bi bi-arrow-right me-2"></i>
          Confirmación de servicios
        </a-button>-->
      </a-col>
    </a-row>

    <a-row
      type="flex"
      justify="space-between"
      align="middle"
      class="bg-light px-4 py-3 my-4"
      style="border-radius: 6px"
      v-if="!filesStore.isLoadingReports"
    >
      <a-col>
        <a-row type="flex" justify="start" align="middle" style="gap: 10px">
          <template
            v-if="filesStore.getFileReports.total_ws > 0 || filesStore.getFileReports.total_rq > 0"
          >
            <a-col>
              <font-awesome-icon
                :icon="['fas', 'square-poll-vertical']"
                size="xl"
                class="text-gray"
              />
            </a-col>
            <a-col>
              <span class="text-500">{{ t('global.label.reserve_status') }}:</span>
            </a-col>
          </template>
          <a-col v-if="filesStore.getFileReports.total_ws > 0">
            <font-awesome-icon
              :icon="['fas', 'triangle-exclamation']"
              size="lg"
              class="text-warning"
              fade
            />
            <span class="mx-1">
              {{ t('global.message.reservations_on_wl') }}
              <a-badge
                :count="filesStore.getFileReports.total_ws"
                :number-style="{
                  backgroundColor: '#fff',
                  color: '#575757',
                  boxShadow: '0 0 0 1px #E9E9E9 inset',
                }"
              />
            </span>
          </a-col>
          <a-col v-if="filesStore.getFileReports.total_rq > 0">
            <font-awesome-icon
              :icon="['fas', 'circle-exclamation']"
              size="lg"
              class="text-danger"
              fade
            />
            <span class="mx-1">
              {{ t('global.message.reservations_on_rq') }}
              <a-badge
                :count="filesStore.getFileReports.total_rq"
                :number-style="{
                  backgroundColor: '#fff',
                  color: '#575757',
                  boxShadow: '0 0 0 1px #E9E9E9 inset',
                }"
              />
            </span>
          </a-col>
        </a-row>
      </a-col>
      <a-col>
        <a-row type="flex" justify="start" align="middle" style="gap: 15px">
          <a-col>
            <small class="text-danger text-500 text-uppercase">
              <small class="bi bi-circle-fill"></small>
              {{ t('global.message.pending') }}
            </small>
          </a-col>
          <a-col>
            <small class="text-warning text-500 text-uppercase">
              <small class="bi bi-circle-fill"></small>
              {{ t('global.message.waiting') }}
            </small>
          </a-col>
          <a-col>
            <small class="text-success text-500 text-uppercase">
              <small class="bi bi-circle-fill"></small>
              {{ t('global.message.confirmed') }}
            </small>
          </a-col>
        </a-row>
      </a-col>
    </a-row>
  </template>

  <div class="reservations-layout">
    <template v-if="filesStore.isLoadingReports">
      <loading-skeleton />
    </template>

    <div class="mx-2" v-else>
      <template
        v-if="
          filesStore.getFileReports.hotels?.length > 0 ||
          filesStore.getFileReports.cruises?.length > 0 ||
          filesStore.getFileReports.trains?.length > 0 ||
          filesStore.getFileReports.tickets?.length > 0 ||
          filesStore.getFileReports.overflights?.length > 0 ||
          filesStore.getFileReports.restaurants?.length > 0 ||
          filesStore.getFileReports.others?.length > 0
        "
      >
        <template v-if="filesStore.getFileReports.hotels?.length > 0">
          <div class="reservations-title my-4 text-uppercase">
            <h6 class="mb-0 d-flex" style="gap: 5px">
              <font-awesome-icon :icon="['fas', 'hotel']" /> {{ t('global.label.hotels') }}
            </h6>
          </div>

          <div class="reservations-content">
            <div class="mt-3">
              <div class="files-edit__service-row-2" v-show="loading">
                <a-skeleton active />
              </div>

              <table
                v-show="!loading"
                class="ant-table ant-table-small"
                style="width: 100%; border-spacing: 0 10px; border-collapse: separate"
              >
                <thead class="ant-table-thead">
                  <tr>
                    <td
                      class="ant-table-cell"
                      style="color: #3d3d3d"
                      v-for="(column, c) in columnsHotels"
                      v-bind:key="`column-${c}`"
                    >
                      <span class="text-uppercase">{{ column.title }}</span>
                      <template v-if="column.dataIndex === 'confirmation_code'">
                        <font-awesome-icon
                          :icon="['fas', 'clipboard-check']"
                          class="ms-1 text-dark-gray"
                          size="lg"
                        />
                      </template>
                    </td>
                  </tr>
                </thead>
                <tbody class="ant-table-tbody">
                  <template
                    v-for="(record, r) in filesStore.getFileReports.hotels"
                    v-bind:key="`record-${r}`"
                  >
                    <tr
                      class="ant-table-row"
                      :style="`background-color: ${record.flagViewRecord ? '#E9E9E9' : '#FAFAFA'}; color: #4F4B4B;`"
                    >
                      <template v-if="record.isLoading">
                        <td :colspan="columnsHotels.length" scope="row">
                          <loading-skeleton rows="1" class="pt-3 px-3" />
                        </td>
                      </template>
                      <template v-else>
                        <td class="ant-table-cell" v-for="(column, c) in columnsHotels">
                          <template
                            v-if="column.dataIndex === 'date_in' || column.dataIndex === 'date_out'"
                          >
                            {{ formatDate(record[column.dataIndex]) }}
                          </template>
                          <template
                            v-else-if="
                              column.dataIndex === 'hotel_name' || column.dataIndex === 'room_name'
                            "
                          >
                            <a-row type="flex" align="middle" justify="space-between">
                              <a-col>
                                <div class="text-uppercase">
                                  <span class="d-block mb-1">
                                    <a-row type="flex" align="middle" justify="start">
                                      <a-col
                                        ><a-tag class="bg-tag-gray"
                                          ><small class="text-uppercase text-600">{{
                                            record['city']
                                          }}</small></a-tag
                                        ></a-col
                                      >
                                      <a-col class="me-2">
                                        <a-tooltip>
                                          <template #title v-if="record.hotel_name.length > 65">
                                            <small>{{ record.hotel_name }}</small>
                                          </template>
                                          <small
                                            ><b>{{
                                              truncateString(record['hotel_name'], 65)
                                            }}</b></small
                                          >
                                        </a-tooltip>
                                      </a-col>

                                      <a-col>
                                        <a-tag class="tag-bg-dark-gray">
                                          <small class="text-uppercase text-600">{{
                                            record['object_code']
                                          }}</small>
                                        </a-tag>
                                      </a-col>
                                    </a-row>
                                  </span>
                                  <a-tooltip>
                                    <template #title v-if="record.room_name.length > 50">
                                      <small>{{ record.room_name }}</small>
                                    </template>
                                    <small class="d-block text-500">
                                      <font-awesome-icon :icon="['fas', 'bed']" class="me-1" />
                                      <template v-if="record['quantity'] > 1"
                                        ><b>{{ record['quantity'] }}</b> x
                                      </template>
                                      {{ truncateString(record['room_name'], 50) }}
                                    </small>
                                  </a-tooltip>
                                </div>
                              </a-col>
                              <a-col v-if="record.units.length > 1 && !record.flag_editable">
                                <span
                                  class="cursor-pointer text-danger me-3"
                                  @click="toggleRecord(record)"
                                >
                                  <font-awesome-icon
                                    :icon="[
                                      'fas',
                                      record.flagViewRecord ? 'chevron-up' : 'chevron-down',
                                    ]"
                                    size="lg"
                                  />
                                </span>
                              </a-col>
                            </a-row>
                          </template>
                          <template v-else-if="column.dataIndex === 'status'">
                            <Status :record="record" type="hotel" />
                          </template>
                          <template v-else-if="column.dataIndex === ''">
                            <Notification
                              :record="record"
                              type="hotel"
                              @handleModalLogs="onHandleModalLogs"
                              @handleOnLoadReport="onLoadReport"
                              @handleOnSetLoading="setLoading"
                            />
                          </template>
                          <template v-else-if="column.dataIndex === 'confirmation_code'">
                            <a-row
                              type="flex"
                              justify="start"
                              align="middle"
                              style="gap: 7px; flex-flow: nowrap"
                            >
                              <template
                                v-if="
                                  record.flag_editable ||
                                  record.confirmation_code == '' ||
                                  record.confirmation_code == null
                                "
                              >
                                <a-col flex="auto">
                                  <a-input
                                    size="small"
                                    class="d-flex"
                                    v-model:value="record.alt_confirmation_code"
                                    :placeholder="
                                      record.confirmation_code != null &&
                                      record.confirmation_code != ''
                                        ? record.confirmation_code
                                        : t('files.column.code')
                                    "
                                    :disabled="record.blocked"
                                  />
                                </a-col>
                                <a-col>
                                  <span
                                    v-if="!record.blocked"
                                    style="font-size: 15px"
                                    @click="saveRecord('room', record)"
                                    class="cursor-pointer"
                                  >
                                    <a-tooltip>
                                      <template #title>{{ t('global.button.save') }}</template>
                                      <font-awesome-icon :icon="['fas', 'floppy-disk']" />
                                    </a-tooltip>
                                  </span>
                                </a-col>
                              </template>
                              <template v-else>
                                <template v-if="!record.flag_editable">
                                  <a-col flex="auto">
                                    <template v-if="!record.flagViewRecord">
                                      <span
                                        @click="unLockedRecord(record)"
                                        class="cursor-pointer text-danger"
                                      >
                                        <a-tooltip>
                                          <template #title>{{ t('global.label.edit') }}</template>
                                          <font-awesome-icon :icon="['far', 'pen-to-square']" />
                                          {{ record.confirmation_code || ' - ' }}
                                        </a-tooltip>
                                      </span>
                                    </template>
                                    <template v-else>
                                      <b>
                                        {{ record.confirmation_code || ' - ' }}
                                      </b>
                                    </template>
                                  </a-col>
                                </template>
                                <a-col v-else>
                                  <span
                                    v-if="!record.blocked"
                                    style="font-size: 20px"
                                    @click="saveRecordOK('room', record)"
                                    class="cursor-pointer"
                                  >
                                    <a-tooltip>
                                      <template #title>{{ t('global.button.save') }}</template>
                                      <font-awesome-icon :icon="['far', 'circle-check']" />
                                    </a-tooltip>
                                  </span>
                                </a-col>
                              </template>
                              <a-col v-if="false">
                                <a-row type="flex" justify="end" align="middle" style="gap: 7px">
                                  <a-col>
                                    <span
                                      class="cursor-pointer"
                                      style="font-size: 15px"
                                      @click="toggleRecord(record)"
                                    >
                                      <a-tooltip>
                                        <template #title>Desplegar unidades</template>
                                        <template v-if="!record.flagViewRecord">
                                          <font-awesome-icon :icon="['far', 'square-plus']" />
                                        </template>
                                        <template v-else>
                                          <font-awesome-icon :icon="['far', 'square-minus']" />
                                        </template>
                                      </a-tooltip>
                                    </span>
                                  </a-col>
                                </a-row>
                              </a-col>
                            </a-row>
                          </template>
                          <template v-else>
                            <span class="text-uppercase">
                              <small class="text-uppercase text-600">{{
                                record[column.dataIndex]
                              }}</small>
                            </span>
                          </template>
                        </td>
                      </template>
                    </tr>
                    <template
                      v-if="record.units.length > 1 && record.flagViewRecord && !record.isLoading"
                    >
                      <tr v-for="(unit, u) in record.units">
                        <td v-for="(column, c) in columnsHotels">
                          <template v-if="column.dataIndex === 'hotel_name'">
                            <font-awesome-icon
                              :icon="['fas', 'code-merge']"
                              size="lg"
                              class="ms-3 mx-2 text-gray"
                            />
                            <small class="text-uppercase text-500">{{ unit['room_name'] }}</small>
                          </template>
                          <template v-else-if="column.dataIndex === 'status'">
                            <a-tag v-if="unit.status == 'RQ'" color="error">
                              <small>RQ</small>
                            </a-tag>
                            <a-tag v-if="unit.status == 'OK'" color="success">
                              <small>OK</small>
                            </a-tag>
                            <a-tag v-if="unit.status == 'WL'" color="warning">
                              <small>WL</small>
                            </a-tag>
                          </template>
                          <template v-else-if="column.dataIndex === 'confirmation_code'">
                            <a-row
                              type="flex"
                              justify="start"
                              align="middle"
                              class="mx-2"
                              style="gap: 5px; flex-flow: nowrap"
                            >
                              <a-col>
                                <font-awesome-icon
                                  :icon="['fas', 'code-merge']"
                                  size="lg"
                                  class="text-gray"
                                />
                              </a-col>
                              <template
                                v-if="
                                  (typeof unit.flag_editable == 'undefined' &&
                                    unit.confirmation_code == null) ||
                                  unit.flag_editable
                                "
                              >
                                <a-col flex="auto">
                                  <a-input
                                    ref="myInput"
                                    size="small"
                                    :placeholder="unit.confirmation_code ?? t('files.column.code')"
                                    v-model:value="unit.confirmation_code"
                                    @input="
                                      (e) =>
                                        updateStore(
                                          record.id,
                                          unit.id,
                                          'confirmation_code',
                                          e.target.value
                                        )
                                    "
                                    @change="handleChange"
                                    :disabled="unit.blocked"
                                  />
                                </a-col>
                              </template>
                              <template
                                v-if="
                                  (typeof unit.flag_editable == 'undefined' &&
                                    unit.confirmation_code == null) ||
                                  unit.flag_editable
                                "
                              >
                                <a-col>
                                  <span
                                    v-if="!unit.blocked"
                                    @click="saveRecordOK('room-unit', unit, record)"
                                    class="cursor-pointer"
                                  >
                                    <a-tooltip>
                                      <template #title>{{ t('global.button.save') }}</template>
                                      <font-awesome-icon :icon="['far', 'circle-check']" />
                                    </a-tooltip>
                                  </span>
                                </a-col>
                                <!-- <a-col>
                                    <IconPlusCircle @click="toggleRecord(unit)" />
                                  </a-col> -->
                              </template>
                              <template v-else>
                                <a-col>
                                  <span
                                    @click="unit.flag_editable = true"
                                    class="cursor-pointer text-danger"
                                  >
                                    <a-tooltip>
                                      <template #title>{{ t('global.label.edit') }}</template>
                                      {{ unit.confirmation_code || ' - ' }}
                                    </a-tooltip>
                                  </span>
                                </a-col>
                              </template>
                              <!-- <template v-if="!unit.flag_editable">
                                <a-col>
                                  <span>{{ unit.confirmation_code || ' - ' }}</span>
                                </a-col>
                                <a-col>
                                  <IconEdit @click="unit.flag_editable = true" />
                                </a-col>
                              </template>
                              <a-col v-else>
                                <IconSaveAlt
                                  v-if="!unit.blocked"
                                  @click="saveRecordOK('room-unit', unit, record)"
                                  color="#3D3D3D"
                                  width="19"
                                  height="20"
                                />
                              </a-col> -->
                            </a-row>
                          </template>
                        </td>
                      </tr>
                    </template>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </template>

        <template v-if="filesStore.getFileReports.cruises?.length > 0">
          <div class="reservations-title my-4 text-uppercase">
            <h6 class="mb-0 d-flex" style="gap: 5px">
              <font-awesome-icon :icon="['fas', 'anchor']" />
              {{ t('global.label.cruises') }}
            </h6>
          </div>

          <div class="reservations-content">
            <div class="mt-3">
              <div class="files-edit__service-row-2" v-show="loading">
                <a-skeleton active />
              </div>

              <table
                v-show="!loading"
                class="ant-table ant-table-small"
                style="width: 100%; border-spacing: 0 10px; border-collapse: separate"
              >
                <thead class="ant-table-thead">
                  <tr>
                    <td
                      class="ant-table-cell"
                      style="color: #3d3d3d"
                      v-for="(column, c) in columnsLodges"
                      v-bind:key="`column-${c}`"
                    >
                      <span class="text-uppercase">{{ column.title }}</span>
                      <template v-if="column.dataIndex === 'confirmation_code'">
                        <font-awesome-icon
                          :icon="['fas', 'clipboard-check']"
                          class="ms-1 text-dark-gray"
                          size="lg"
                        />
                      </template>
                    </td>
                  </tr>
                </thead>
                <tbody class="ant-table-tbody">
                  <template
                    v-for="(record, r) in filesStore.getFileReports.cruises"
                    v-bind:key="`record-${r}`"
                  >
                    <template v-if="record.isLoading">
                      <td :colspan="columnsLodges.length" scope="row">
                        <loading-skeleton rows="1" class="pt-3 px-3" />
                      </td>
                    </template>
                    <template v-else>
                      <tr
                        class="ant-table-row"
                        :style="`background-color: ${record.flagViewRecord ? '#E9E9E9' : '#FAFAFA'}; color: #4F4B4B;`"
                      >
                        <td class="ant-table-cell" v-for="(column, c) in columnsLodges">
                          <template
                            v-if="column.dataIndex === 'date_in' || column.dataIndex === 'date_out'"
                          >
                            {{ formatDate(record[column.dataIndex]) }}
                          </template>
                          <template v-else-if="column.dataIndex === 'name'">
                            <a-row type="flex" align="middle" justify="start">
                              <a-col
                                ><a-tag class="bg-tag-gray"
                                  ><small class="text-uppercase text-600">{{
                                    record['city']
                                  }}</small></a-tag
                                ></a-col
                              >
                              <a-col>
                                <a-tooltip>
                                  <template #title v-if="record.name.length > 50">
                                    <small>{{ record.name }}</small>
                                  </template>
                                  <small
                                    ><b>{{ truncateString(record['name'], 50) }}</b></small
                                  >
                                </a-tooltip>
                              </a-col>
                            </a-row>
                          </template>
                          <template v-else-if="column.dataIndex === 'status'">
                            <Status :record="record" />
                          </template>
                          <template v-else-if="column.dataIndex === ''">
                            <Notification
                              :record="record"
                              @handleModalLogs="onHandleModalLogs"
                              @handleOnLoadReport="onLoadReport"
                              @handleOnSetLoading="setLoading"
                            />
                            <!-- a-tag color="processing" :bordered="false">Apertura</a-tag>
                            <a-tag color="success" :bordered="false">Respondido</a-tag -->
                          </template>
                          <template v-else-if="column.dataIndex === 'confirmation_code'">
                            <ConfirmationCode :record="record" />
                          </template>
                          <template v-else>
                            {{ record[column.dataIndex] }}
                          </template>
                        </td>
                      </tr>
                    </template>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </template>

        <template v-if="filesStore.getFileReports.trains?.length > 0">
          <div class="reservations-title my-4 text-uppercase">
            <h6 class="mb-0 d-flex" style="gap: 5px">
              <font-awesome-icon :icon="['fas', 'train']" />
              {{ t('global.label.trains') }}
            </h6>
          </div>

          <div class="reservations-content">
            <div class="mt-3">
              <div class="files-edit__service-row-2" v-show="loading">
                <a-skeleton active />
              </div>

              <table
                v-show="!loading"
                class="ant-table ant-table-small"
                style="width: 100%; border-spacing: 0 10px; border-collapse: separate"
              >
                <thead class="ant-table-thead">
                  <tr>
                    <td
                      class="ant-table-cell"
                      style="color: #3d3d3d"
                      v-for="(column, c) in columnsTrains"
                      v-bind:key="`column-${c}`"
                    >
                      <span class="text-uppercase">{{ column.title }}</span>
                      <template v-if="column.dataIndex === 'confirmation_code'">
                        <font-awesome-icon
                          :icon="['fas', 'clipboard-check']"
                          class="ms-1 text-dark-gray"
                          size="lg"
                        />
                      </template>
                    </td>
                  </tr>
                </thead>
                <tbody class="ant-table-tbody">
                  <template
                    v-for="(record, r) in filesStore.getFileReports.trains"
                    v-bind:key="`record-${r}`"
                  >
                    <template v-if="record.isLoading">
                      <td :colspan="columnsTrains.length" scope="row">
                        <loading-skeleton rows="1" class="pt-3 px-3" />
                      </td>
                    </template>
                    <template v-else>
                      <tr
                        class="ant-table-row"
                        :style="`background-color: ${record.flagViewRecord ? '#E9E9E9' : '#FAFAFA'}; color: #4F4B4B;`"
                      >
                        <td class="ant-table-cell" v-for="(column, c) in columnsTrains">
                          <template
                            v-if="column.dataIndex === 'date_in' || column.dataIndex === 'date_out'"
                          >
                            {{ formatDate(record[column.dataIndex]) }}
                          </template>
                          <template v-else-if="column.dataIndex === 'name'">
                            <a-row type="flex" align="middle" justify="start">
                              <a-col
                                ><a-tag class="bg-tag-gray"
                                  ><small class="text-uppercase text-600">{{
                                    record['city']
                                  }}</small></a-tag
                                ></a-col
                              >
                              <a-col>
                                <a-tooltip>
                                  <template #title v-if="record.name.length > 50">
                                    <small>{{ record.name }}</small>
                                  </template>
                                  <small
                                    ><b>{{ truncateString(record['name'], 50) }}</b></small
                                  >
                                </a-tooltip>
                              </a-col>
                            </a-row>
                          </template>
                          <template v-else-if="column.dataIndex === 'status'">
                            <Status :record="record" />
                          </template>
                          <template v-else-if="column.dataIndex === ''">
                            <Notification
                              :record="record"
                              @handleModalLogs="onHandleModalLogs"
                              @handleOnLoadReport="onLoadReport"
                              @handleOnSetLoading="setLoading"
                            />
                            <!-- a-tag color="processing" :bordered="false">Apertura</a-tag>
                            <a-tag color="success" :bordered="false">Respondido</a-tag -->
                          </template>
                          <template v-else-if="column.dataIndex === 'confirmation_code'">
                            <ConfirmationCode :record="record" />
                          </template>
                          <template v-else>
                            <div class="text-center">
                              {{ record[column.dataIndex] }}
                            </div>
                          </template>
                        </td>
                      </tr>
                    </template>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </template>

        <template v-if="filesStore.getFileReports.tickets?.length > 0">
          <div class="reservations-title my-4 text-uppercase">
            <h6 class="mb-0 d-flex" style="gap: 5px">
              <font-awesome-icon :icon="['fas', 'ticket']" />
              {{ t('global.label.tickets') }}
            </h6>
          </div>

          <div class="reservations-content">
            <div class="mt-3">
              <div class="files-edit__service-row-2" v-show="loading">
                <a-skeleton active />
              </div>

              <table
                v-show="!loading"
                class="ant-table ant-table-small"
                style="width: 100%; border-spacing: 0 10px; border-collapse: separate"
              >
                <thead class="ant-table-thead">
                  <tr>
                    <td
                      class="ant-table-cell"
                      style="color: #3d3d3d"
                      v-for="(column, c) in columnsTickets"
                      v-bind:key="`column-${c}`"
                    >
                      <span class="text-uppercase">{{ column.title }}</span>
                      <template v-if="column.dataIndex === 'confirmation_code'">
                        <font-awesome-icon
                          :icon="['fas', 'clipboard-check']"
                          class="ms-1 text-dark-gray"
                          size="lg"
                        />
                      </template>
                    </td>
                  </tr>
                </thead>
                <tbody class="ant-table-tbody">
                  <template
                    v-for="(record, r) in filesStore.getFileReports.tickets"
                    v-bind:key="`record-${r}`"
                  >
                    <template v-if="record.isLoading">
                      <td :colspan="columnsTickets.length" scope="row">
                        <loading-skeleton rows="1" class="pt-3 px-3" />
                      </td>
                    </template>
                    <template v-else>
                      <tr
                        class="ant-table-row"
                        :style="`background-color: ${record.flagViewRecord ? '#E9E9E9' : '#FAFAFA'}; color: #4F4B4B;`"
                      >
                        <td class="ant-table-cell" v-for="(column, c) in columnsTickets">
                          <template
                            v-if="column.dataIndex === 'date_in' || column.dataIndex === 'date_out'"
                          >
                            {{ formatDate(record[column.dataIndex]) }}
                          </template>
                          <template v-else-if="column.dataIndex === 'name'">
                            <a-row type="flex" align="middle" justify="start">
                              <a-col
                                ><a-tag class="bg-tag-gray"
                                  ><small class="text-uppercase text-600">{{
                                    record['city']
                                  }}</small></a-tag
                                ></a-col
                              >
                              <a-col>
                                <a-tooltip>
                                  <template #title v-if="record.name.length > 50">
                                    <small>{{ record.name }}</small>
                                  </template>
                                  <small
                                    ><b>{{ truncateString(record['name'], 50) }}</b></small
                                  >
                                </a-tooltip>
                              </a-col>
                            </a-row>
                          </template>
                          <template v-else-if="column.dataIndex === 'status'">
                            <StatusTicket :record="record" />
                          </template>
                          <template v-else-if="column.dataIndex === ''">
                            <Notification
                              :record="record"
                              @handleModalLogs="onHandleModalLogs"
                              @handleOnLoadReport="onLoadReport"
                              @handleOnSetLoading="setLoading"
                            />
                            <!-- a-tag color="processing" :bordered="false">Apertura</a-tag>
                            <a-tag color="success" :bordered="false">Respondido</a-tag -->
                          </template>
                          <template v-else-if="column.dataIndex === 'confirmation_code'">
                            <ConfirmationCode :record="record" @handleOnLoadReport="onLoadReport" />
                          </template>
                          <template v-else>
                            {{ record[column.dataIndex] }}
                          </template>
                        </td>
                      </tr>
                    </template>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </template>

        <template v-if="filesStore.getFileReports.overflights?.length > 0">
          <div class="reservations-title my-4 text-uppercase">
            <h6 class="mb-0 d-flex" style="gap: 5px">
              <font-awesome-icon :icon="['fas', 'helicopter']" />
              {{ t('global.label.overflights') }}
            </h6>
          </div>

          <div class="reservations-content">
            <div class="mt-3">
              <div class="files-edit__service-row-2" v-show="loading">
                <a-skeleton active />
              </div>

              <table
                v-show="!loading"
                class="ant-table ant-table-small"
                style="width: 100%; border-spacing: 0 10px; border-collapse: separate"
              >
                <thead class="ant-table-thead">
                  <tr>
                    <td
                      class="ant-table-cell"
                      style="color: #3d3d3d"
                      v-for="(column, c) in columnsOverflights"
                      v-bind:key="`column-${c}`"
                    >
                      <span class="text-uppercase">{{ column.title }}</span>
                      <template v-if="column.dataIndex === 'confirmation_code'">
                        <font-awesome-icon
                          :icon="['fas', 'clipboard-check']"
                          class="ms-1 text-dark-gray"
                          size="lg"
                        />
                      </template>
                    </td>
                  </tr>
                </thead>
                <tbody class="ant-table-tbody">
                  <template
                    v-for="(record, r) in filesStore.getFileReports.overflights"
                    v-bind:key="`record-${r}`"
                  >
                    <template v-if="record.isLoading">
                      <td :colspan="columnsOverflights.length" scope="row">
                        <loading-skeleton rows="1" class="pt-3 px-3" />
                      </td>
                    </template>
                    <template v-else>
                      <tr
                        class="ant-table-row"
                        :style="`background-color: ${record.flagViewRecord ? '#E9E9E9' : '#FAFAFA'}; color: #4F4B4B;`"
                      >
                        <td class="ant-table-cell" v-for="(column, c) in columnsOverflights">
                          <template
                            v-if="column.dataIndex === 'date_in' || column.dataIndex === 'date_out'"
                          >
                            {{ formatDate(record[column.dataIndex]) }}
                          </template>
                          <template v-else-if="column.dataIndex === 'name'">
                            <a-row type="flex" align="middle" justify="start">
                              <a-col
                                ><a-tag class="bg-tag-gray"
                                  ><small class="text-uppercase text-600">{{
                                    record['city']
                                  }}</small></a-tag
                                ></a-col
                              >
                              <a-col>
                                <a-tooltip>
                                  <template #title v-if="record.name.length > 50">
                                    <small>{{ record.name }}</small>
                                  </template>
                                  <small
                                    ><b>{{ truncateString(record['name'], 50) }}</b></small
                                  >
                                </a-tooltip>
                              </a-col>
                            </a-row>
                          </template>
                          <template v-else-if="column.dataIndex === 'status'">
                            <Status :record="record" />
                          </template>
                          <template v-else-if="column.dataIndex === ''">
                            <Notification
                              :record="record"
                              @handleModalLogs="onHandleModalLogs"
                              @handleOnLoadReport="onLoadReport"
                              @handleOnSetLoading="setLoading"
                            />
                            <!-- a-tag color="processing" :bordered="false">Apertura</a-tag>
                            <a-tag color="success" :bordered="false">Respondido</a-tag -->
                          </template>
                          <template v-else-if="column.dataIndex === 'confirmation_code'">
                            <ConfirmationCode :record="record" />
                          </template>
                          <template v-else>
                            {{ record[column.dataIndex] }}
                          </template>
                        </td>
                      </tr>
                    </template>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </template>

        <template v-if="filesStore.getFileReports.restaurants?.length > 0">
          <div class="reservations-title my-4 text-uppercase">
            <h6 class="mb-0 d-flex" style="gap: 5px">
              <font-awesome-icon :icon="['fas', 'utensils']" />
              {{ t('global.label.restaurants') }}
            </h6>
          </div>

          <div class="reservations-content">
            <div class="mt-3">
              <div class="files-edit__service-row-2" v-show="loading">
                <a-skeleton active />
              </div>

              <table
                v-show="!loading"
                class="ant-table ant-table-small"
                style="width: 100%; border-spacing: 0 10px; border-collapse: separate"
              >
                <thead class="ant-table-thead">
                  <tr>
                    <td
                      class="ant-table-cell"
                      style="color: #3d3d3d"
                      v-for="(column, c) in columnsRestaurants"
                      v-bind:key="`column-${c}`"
                    >
                      <span class="text-uppercase">{{ column.title }}</span>
                      <template v-if="column.dataIndex === 'confirmation_code'">
                        <font-awesome-icon
                          :icon="['fas', 'clipboard-check']"
                          class="ms-1 text-dark-gray"
                          size="lg"
                        />
                      </template>
                    </td>
                  </tr>
                </thead>
                <tbody class="ant-table-tbody">
                  <template
                    v-for="(record, r) in filesStore.getFileReports.restaurants"
                    v-bind:key="`record-${r}`"
                  >
                    <template v-if="record.isLoading">
                      <td :colspan="columnsRestaurants.length" scope="row">
                        <loading-skeleton rows="1" class="pt-3 px-3" />
                      </td>
                    </template>
                    <template v-else>
                      <tr
                        class="ant-table-row"
                        :style="`background-color: ${record.flagViewRecord ? '#E9E9E9' : '#FAFAFA'}; color: #4F4B4B;`"
                      >
                        <td class="ant-table-cell" v-for="(column, c) in columnsRestaurants">
                          <template
                            v-if="column.dataIndex === 'date_in' || column.dataIndex === 'date_out'"
                          >
                            {{ formatDate(record[column.dataIndex]) }}
                          </template>
                          <template v-else-if="column.dataIndex === 'name'">
                            <a-row type="flex" align="middle" justify="start">
                              <a-col
                                ><a-tag class="bg-tag-gray"
                                  ><small class="text-uppercase text-600">{{
                                    record['city']
                                  }}</small></a-tag
                                ></a-col
                              >
                              <a-col>
                                <a-tooltip>
                                  <template #title v-if="record.name.length > 50">
                                    <small>{{ record.name }}</small>
                                  </template>
                                  <small
                                    ><b>{{ truncateString(record['name'], 50) }}</b></small
                                  >
                                </a-tooltip>
                              </a-col>
                            </a-row>
                          </template>
                          <template v-else-if="column.dataIndex === 'status'">
                            <Status :record="record" />
                          </template>
                          <template v-else-if="column.dataIndex === ''">
                            <Notification
                              :record="record"
                              @handleModalLogs="onHandleModalLogs"
                              @handleOnLoadReport="onLoadReport"
                              @handleOnSetLoading="setLoading"
                            />
                          </template>
                          <template v-else-if="column.dataIndex === 'confirmation_code'">
                            <ConfirmationCode :record="record" />
                          </template>
                          <template v-else>
                            {{ record[column.dataIndex] }}
                          </template>
                        </td>
                      </tr>
                    </template>
                  </template>
                </tbody>
              </table>
            </div>
          </div>
        </template>

        <template v-if="filesStore.getFileReports.others?.length > 0">
          <div class="reservations-title my-4 text-uppercase">
            <h6 class="mb-0 d-flex" style="gap: 5px">
              <font-awesome-icon :icon="['fas', 'tags']" />
              {{ t('global.label.others_services') }}
            </h6>
          </div>

          <div class="reservations-content">
            <div class="mt-3">
              <div class="files-edit__service-row-2" v-if="loading">
                <a-skeleton active />
              </div>
              <template v-else>
                <table
                  class="ant-table ant-table-small"
                  style="width: 100%; border-spacing: 0 10px; border-collapse: separate"
                >
                  <thead class="ant-table-thead">
                    <tr>
                      <td
                        class="ant-table-cell"
                        style="color: #3d3d3d"
                        v-for="(column, c) in columnsOthers"
                        v-bind:key="`column-${c}`"
                      >
                        <span class="text-uppercase">{{ column.title }}</span>
                        <template v-if="column.dataIndex === 'confirmation_code'">
                          <font-awesome-icon
                            :icon="['fas', 'clipboard-check']"
                            class="ms-1 text-dark-gray"
                            size="lg"
                          />
                        </template>
                      </td>
                    </tr>
                  </thead>
                  <tbody class="ant-table-tbody">
                    <template
                      v-for="(record, r) in filesStore.getFileReports.others"
                      v-bind:key="`record-${r}`"
                    >
                      <tr
                        class="ant-table-row"
                        :style="`background-color: ${record.flagViewRecord ? '#E9E9E9' : '#FAFAFA'}; color: #4F4B4B;`"
                      >
                        <template v-if="record.isLoading">
                          <td :colspan="columnsOthers.length" scope="row">
                            <loading-skeleton rows="1" class="pt-3 px-3" />
                          </td>
                        </template>
                        <template v-else>
                          <td class="ant-table-cell" v-for="(column, c) in columnsOthers">
                            <template
                              v-if="
                                column.dataIndex === 'date_in' || column.dataIndex === 'date_out'
                              "
                            >
                              {{ formatDate(record[column.dataIndex]) }}
                            </template>
                            <template v-else-if="column.dataIndex === 'name'">
                              <a-row type="flex" align="middle" justify="start" style="gap: 5px">
                                <a-col
                                  ><a-tag class="bg-tag-gray"
                                    ><small class="text-uppercase text-600">{{
                                      record['city']
                                    }}</small></a-tag
                                  ></a-col
                                >
                                <a-col>
                                  <a-tooltip>
                                    <template #title v-if="record.name.length > 50">
                                      <small>{{ record.name }}</small>
                                    </template>
                                    <small
                                      ><b>{{ truncateString(record['name'], 50) }}</b></small
                                    >
                                  </a-tooltip>
                                </a-col>
                                <a-col>
                                  <a-tag class="tag-bg-dark-gray">
                                    <small>
                                      <font-awesome-icon :icon="['fas', 'user-check']" />
                                      <span class="text-600 ms-1">
                                        {{ record['supplier']['code_request_book'] }}
                                      </span>
                                    </small>
                                  </a-tag>
                                </a-col>
                              </a-row>
                            </template>
                            <template v-else-if="column.dataIndex === 'status'">
                              <Status :record="record" />
                            </template>
                            <template v-else-if="column.dataIndex === ''">
                              <Notification
                                :record="record"
                                @handleModalLogs="onHandleModalLogs"
                                @handleOnLoadReport="onLoadReport"
                                @handleOnSetLoading="setLoading"
                              />
                              <!-- a-tag color="processing" :bordered="false">Apertura</a-tag>
                              <a-tag color="success" :bordered="false">Respondido</a-tag -->
                            </template>
                            <template v-else-if="column.dataIndex === 'confirmation_code'">
                              <ConfirmationCode :record="record" />
                            </template>
                            <template v-else>
                              {{ record[column.dataIndex] }}
                            </template>
                          </td>
                        </template>
                      </tr>
                    </template>
                  </tbody>
                </table>
              </template>
            </div>
          </div>
        </template>
      </template>
      <template v-else>
        <a-empty :description="null" class="py-3 my-3 bg-light" />
      </template>
    </div>
  </div>

  <a-modal
    v-model:open="flagModalLogs"
    :width="1240"
    :closable="true"
    :maskClosable="false"
    :title="t('global.label.notifications_reserve')"
  >
    <LogsView type="modal" :params="paramsModalLogs" v-if="flagModalLogs" />
    <template #footer>
      <div class="text-center">
        <a-button
          type="primary"
          default
          class="text-600"
          :loading="loading"
          @click="flagModalLogs = false"
          v-bind:disabled="filesStore.isLoading || filesStore.isLoadingAsync"
          size="large"
        >
          {{ t('global.button.close') }}
        </a-button>
      </div>
    </template>
  </a-modal>
</template>

<style lang="scss">
  .ant-table-cell {
    padding: 7px 5px;
  }

  .ant-table {
    table-layout: auto;
    width: 100%;
    border-spacing: 0px 7px !important;
    border-collapse: separate !important;
  }

  .ant-table-thead {
    .ant-table-cell {
      font-size: 12px;
      font-weight: 600;

      &:last-child {
        padding-right: 80px;
        text-align: right !important;
      }
    }
  }

  .ant-table-tbody {
    .ant-table-cell {
      white-space: nowrap;
    }
  }
</style>

<script setup>
  import { onBeforeMount, ref, computed } from 'vue';
  import { debounce } from 'lodash-es';
  import { notification } from 'ant-design-vue';
  import { useFilesStore } from '@/stores/files';
  import { formatDate } from '@/utils/files.js';
  import { useI18n } from 'vue-i18n';
  import ConfirmationCode from './ConfirmationCode.vue';
  import Status from './Status.vue';
  import StatusTicket from './StatusTicket.vue';
  import LoadingSkeleton from '@/components/global/LoadingSkeleton.vue';

  import LogsView from '@/components/brevo/dashboard/LogsView.vue';
  import Notification from './Notification.vue';
  import { truncateString } from '@/utils/files.js';

  const { t } = useI18n({
    useScope: 'global',
  });

  const loading = ref(false);
  const filesStore = useFilesStore();

  const toggleRecord = (record) => {
    record.flagViewRecord = !record.flagViewRecord;
    // record.flag_editable = !record.flag_editable;
    console.log('Record: ', record);
  };

  const myInput = ref(null);

  const columnsHotels = computed(() => [
    {
      title: t('files.column.date_in'),
      dataIndex: 'date_in',
      sorter: false,
    },
    {
      title: t('files.column.date_out'),
      dataIndex: 'date_out',
      sorter: false,
    },
    {
      title: t('files.column.hotel'),
      dataIndex: 'hotel_name',
      sorter: false,
      ellipsis: true,
      width: 180,
    },
    {
      title: t('files.column.status'),
      dataIndex: 'status',
      sorter: false,
    },
    {
      title: t('files.column.actions'),
      dataIndex: '',
      sorter: false,
    },
    {
      title: t('files.column.code'),
      dataIndex: 'confirmation_code',
      width: 170,
      sorter: false,
    },
  ]);

  const columnsLodges = computed(() => [
    {
      title: t('files.column.date_in'),
      dataIndex: 'date_in',
      sorter: false,
    },
    {
      title: t('files.column.date_out'),
      dataIndex: 'date_out',
      sorter: false,
    },
    {
      title: t('files.column.service'),
      dataIndex: 'name',
      sorter: false,
    },
    {
      title: t('files.column.status'),
      dataIndex: 'status',
      sorter: false,
    },
    {
      title: t('files.column.actions'),
      dataIndex: '',
      sorter: false,
    },
    {
      title: t('files.column.code'),
      dataIndex: 'confirmation_code',
      sorter: false,
      width: 170,
    },
  ]);

  const columnsTrains = computed(() => [
    {
      title: t('files.column.service'),
      dataIndex: 'name',
      sorter: false,
    },
    {
      title: '#',
      dataIndex: 'quantity',
      sorter: false,
    },
    {
      title: t('files.column.date_in'),
      dataIndex: 'date_in',
      sorter: false,
    },
    {
      title: t('files.column.hour'),
      dataIndex: 'start_time',
      sorter: false,
    },
    {
      title: t('files.column.date_out'),
      dataIndex: 'date_out',
      sorter: false,
    },
    {
      title: t('files.column.hour'),
      dataIndex: 'departure_time',
      sorter: false,
    },
    {
      title: t('files.column.status'),
      dataIndex: 'status',
      sorter: false,
    },
    {
      title: t('files.column.actions'),
      dataIndex: '',
      sorter: false,
    },
    {
      title: t('files.column.code'),
      dataIndex: 'confirmation_code',
      sorter: false,
      width: 170,
    },
  ]);

  const columnsTickets = computed(() => [
    {
      title: t('files.column.date'),
      dataIndex: 'date_in',
      sorter: false,
    },
    {
      title: t('files.column.detail'),
      dataIndex: 'name',
      sorter: false,
    },
    {
      title: t('files.column.status'),
      dataIndex: 'status',
      sorter: false,
    },
    {
      title: t('files.column.actions'),
      dataIndex: '',
      sorter: false,
    },
    {
      title: t('files.column.code'),
      dataIndex: 'confirmation_code',
      sorter: false,
      width: 170,
    },
  ]);

  const columnsOverflights = computed(() => [
    {
      title: t('files.column.date'),
      dataIndex: 'date_in',
      sorter: false,
    },
    {
      title: '#',
      dataIndex: 'quantity',
      sorter: true,
    },
    {
      title: t('files.column.detail'),
      dataIndex: 'name',
      sorter: true,
    },
    {
      title: t('files.column.status'),
      dataIndex: 'status',
      sorter: true,
    },
    {
      title: t('files.column.actions'),
      dataIndex: '',
      sorter: true,
    },
    {
      title: t('files.column.code'),
      dataIndex: 'confirmation_code',
      sorter: true,
    },
  ]);

  const columnsRestaurants = computed(() => [
    {
      title: t('files.column.date'),
      dataIndex: 'date_in',
      sorter: false,
    },
    {
      title: '#',
      dataIndex: 'quantity',
      sorter: true,
    },
    {
      title: t('files.column.detail'),
      dataIndex: 'name',
      sorter: true,
    },
    {
      title: t('files.column.status'),
      dataIndex: 'status',
      sorter: true,
    },
    {
      title: t('files.column.actions'),
      dataIndex: '',
      sorter: true,
    },
    {
      title: t('files.column.code'),
      dataIndex: 'confirmation_code',
      sorter: true,
    },
  ]);

  const columnsOthers = computed(() => [
    {
      title: t('files.column.date'),
      dataIndex: 'date_in',
      sorter: false,
    },
    {
      title: '#',
      dataIndex: 'quantity',
      sorter: false,
      width: 80,
    },
    {
      title: t('files.column.detail'),
      dataIndex: 'name',
      sorter: false,
      ellipsis: true,
      width: 400,
    },
    {
      title: t('files.column.status'),
      dataIndex: 'status',
      sorter: false,
    },
    {
      title: t('files.column.actions'),
      dataIndex: '',
      sorter: false,
    },
    {
      title: t('files.column.code'),
      dataIndex: 'confirmation_code',
      sorter: false,
      width: 170,
    },
  ]);

  onBeforeMount(async () => {
    await filesStore.fetchFileReports(filesStore.getFile.id);
  });

  const onLoadReport = async () => {
    console.log('CARGANDO..');
  };

  const setLoading = async (status = false) => {
    console.log('Estado Loading.. ', status);
  };

  const saveRecord = debounce(async (type, record) => {
    record.flag_editable = false;

    if (
      typeof record.alt_confirmation_code == 'undefined' ||
      record.alt_confirmation_code === null ||
      record.alt_confirmation_code === ''
    ) {
      return false;
    }

    localStorage.setItem('confirmation_code', record.alt_confirmation_code);
    record.blocked = true;
    record.confirmation_code = localStorage.getItem('confirmation_code');

    setTimeout(async () => {
      localStorage.removeItem('confirmation_code');
      await filesStore.saveConfirmationCode({
        type,
        id: record.id,
        confirmation_code: record.confirmation_code,
        itinerary_id: record.file_itinerary_id,
        file_number: filesStore.getFile.fileNumber,
      });
      record.blocked = false;
      record.isLoading = true;

      if (filesStore.getError != '') {
        notification['error']({
          message: `Ocurrió un Error`,
          description: filesStore.getError,
          duration: 5,
        });
      } else {
        record.flag_editable = false;
      }
    }, 10);
  }, 350);

  const saveRecordOK = debounce(async (type, record, _record) => {
    // console.log("RECORD: ", record);
    // console.log("RECORD: ", _record);

    if (record.confirmation_code != null && record.confirmation_code != '') {
      record.blocked = true;
      await filesStore.saveConfirmationCode({
        type,
        id: record.id,
        confirmation_code: record.confirmation_code,
        itinerary_id: _record.file_itinerary_id,
        file_number: filesStore.getFile.fileNumber,
      });
      record.blocked = false;
      _record.isLoading = true;

      if (filesStore.getError != '') {
        notification['error']({
          message: `Ocurrió un Error`,
          description: filesStore.getError,
          duration: 5,
        });
      } else {
        record.flag_editable = false;
      }
    } else {
      record.flag_editable = false;
    }
  }, 350);

  const unLockedRecord = (record) => {
    record.flag_editable = true;
    record.blocked = false;

    console.log('Record: ', record);
  };

  const flagModalLogs = ref(false);
  const paramsModalLogs = ref({});

  const showModalLogs = async (record, type = 'service') => {
    flagModalLogs.value = false;

    const params = {
      object_id: filesStore.getFile.fileNumber,
      object_code: type === 'hotel' ? record.object_code : record.code,
      type: type,
      timestamp: record.created_at ?? '',
      record: record,
    };

    paramsModalLogs.value = params;

    setTimeout(() => {
      flagModalLogs.value = true;
    }, 100);
  };

  const onHandleModalLogs = (data) => {
    showModalLogs(data.record, data.type);
  };

  const updateStore = (reportId, unitId, key, value) => {
    filesStore.addNewProperty(reportId, unitId, 'flag_editable', true);
    filesStore.updateHotelsUnitProperty(reportId, unitId, key, value);

    // console.log(filesStore.getFileReports.hotels);
  };

  const handleChange = (e) => {
    if (!e || !e.target) return; // Previene el error si el evento es inválido
    // console.log(e.target.value);
  };
</script>
