<template>
  <div class="base-table container-fluid p-0">
    <div class="row g-0 row-filter">
      <a-form
        ref="formFilterRef"
        class="form-filter"
        :model="formFilter"
        autocomplete="off"
        @finish="onFilter"
        @finishFailed="onFilterFailed"
      >
        <base-input-search
          name="filter"
          :placeholder="t('files.label.input_search')"
          size="large"
          width="400"
          autocomplete="off"
          :allow-clear="false"
          :debounceTime="500"
          :minChars="3"
          v-model="formFilter.filter"
          :isLoading="isLoading"
        />
        <base-select
          name="executiveCode"
          :placeholder="t('global.label.executive')"
          :filter-option="false"
          size="large"
          width="210"
          :fieldNames="{ label: 'name', value: 'code' }"
          :showSearch="true"
          :allowClear="true"
          :options="executivesStore.getExecutives"
          v-model:value="formFilter.executiveCode"
          @search="handleSearchExecutives"
        />
        <base-select
          name="clientId"
          :placeholder="t('global.label.client')"
          :filter-option="false"
          size="large"
          width="210"
          :showSearch="true"
          :allowClear="true"
          :options="clientsStore.getClients"
          v-model:value="formFilter.clientId"
          @search="handleSearchClients"
        />
        <base-range-date-picker
          style="height: 45px"
          name="dateRange"
          valueFormat="YYYY-MM-DD"
          v-model:value="formFilter.dateRange"
        />
        <div>
          <base-button width="60" size="large" type="primary" danger ghost @click="handleRefresh">
            <i class="bi bi-magic"></i>
          </base-button>
          <!-- base-button
            width="119"
            size="large"
            style="display:none;">Crear File</base-button -->
        </div>
      </a-form>
    </div>

    <template v-if="formFilter.flag_stella && data.length > 0 && !isLoading">
      <a-alert>
        <template #description>
          <a-row type="flex" justify="space-between" align="middle">
            <a-col :span="1" class="text-center">
              <font-awesome-icon :icon="['fas', 'circle-info']" size="2xl" />
            </a-col>
            <a-col :span="18">
              <h5 style="color: #2e2b9e" class="mb-1">{{ t('files.label.one_step_file') }}</h5>
              <p style="color: #2e2b9e" class="mb-0">
                {{ t('files.label.one_step_file_detail') }}
              </p>
            </a-col>
            <a-col :span="4" class="text-center">
              <a-button
                type="default"
                v-if="data.length > 1"
                danger
                size="large"
                class="px-4"
                @click="importFilesStella"
              >
                {{ t('global.button.import_all') }}
              </a-button>
            </a-col>
          </a-row>
        </template>
      </a-alert>
    </template>

    <template v-if="isAdmin() && data.length == 0">
      <a-alert type="error" v-if="!formFilter.flag_stella && !isLoading">
        <template #description>
          <a-row type="flex" justify="space-between" align="middle">
            <a-col :span="1" class="text-danger text-center">
              <font-awesome-icon :icon="['fas', 'circle-info']" size="2xl" style="color: #ff3b3b" />
            </a-col>
            <a-col :span="18">
              <h5 style="color: #a40000" class="mb-1">
                {{ t('files.label.files_stella_alert') }}
              </h5>
              <p style="color: #ff3b3b" class="mb-0">
                {{ t('files.label.files_stella_alert_detail') }}
              </p>
            </a-col>
            <a-col :span="1">
              <font-awesome-icon :icon="['fas', 'arrow-right']" size="2xl" style="color: #d80404" />
            </a-col>
            <a-col :span="3" class="text-center">
              <a-button type="primary" danger size="large" class="px-4" @click="handleSearchStella">
                <a-row type="flex" justify="space-between" align="middle" style="gap: 7px">
                  <a-col>
                    <font-awesome-icon :icon="['fas', 'magnifying-glass']" />
                  </a-col>
                  <a-col> {{ t('global.button.search') }} </a-col>
                </a-row>
              </a-button>
            </a-col>
          </a-row>
        </template>
      </a-alert>
    </template>
    <template v-else>
      <div class="row g-0 row-header">
        <div class="col-small" v-for="column in config.columns" :key="column.id">
          <span v-if="!column?.isFiltered" style="font-size: 13px" class="text-uppercase">{{
            column.title
          }}</span>
          <span v-else class="row-header-sort">
            <span
              class="text-uppercase"
              style="font-size: 13px"
              v-if="isDesc && isSelectedFilterBy(column.fieldName)"
              @click="onFilterBy({ filterBy: column.fieldName, filterByType: 'asc' })"
            >
              {{ column.title }}
              <font-awesome-icon
                icon="fa-solid fa-arrow-up-short-wide"
                class="ms-1 text-dark-gray"
              />
            </span>
            <span
              class="text-uppercase"
              style="font-size: 13px"
              v-else
              @click="onFilterBy({ filterBy: column.fieldName, filterByType: 'desc' })"
            >
              {{ column.title }}
              <font-awesome-icon
                icon="fa-solid fa-arrow-down-wide-short"
                class="ms-1 text-dark-gray"
              />
            </span>
          </span>
        </div>
      </div>

      <div v-if="isLoading" class="container-body is-loading">
        <a-spin :tip="t('files.label.loading')" />
      </div>
      <div v-else-if="data?.length === 0" class="container-body is-loading">
        <a-empty>
          <template #description>
            <span>{{ t('global.label.empty') }}</span>
          </template>
        </a-empty>
      </div>

      <div v-else class="container-body">
        <div
          v-bind:class="['row g-0 row-body', showClass(file.dateIn)]"
          v-for="file in data"
          :key="file.id"
        >
          <div class="col-small">{{ file.fileNumber }}</div>
          <div class="col-small col-small-break col-small-200 text-uppercase">
            <a-tooltip>
              <template #title>
                <small class="text-uppercase text-500">{{ file.description }}</small>
              </template>
              {{ file.description }}
            </a-tooltip>
          </div>
          <div class="col-small">
            <component :is="file.statusReason ? 'base-popover' : 'span'" placement="top">
              <base-badge :type="statusByIso(file.status).type">
                <span :class="`status-row-${statusByIso(file.status).iso}`"></span>
                {{ statusByIso(file.status).name }}
              </base-badge>
              <template #content>
                <div class="box-description">{{ file.statusReason }}</div>
              </template>
            </component>
          </div>
          <div class="col-small">
            <files-popover-vip :data="file" @onRefreshCache="handleRefreshCache" />
          </div>
          <div class="col-small">{{ file.paxs }}</div>
          <div class="col-small">
            <base-popover placement="top">
              {{ file.executiveCode }}
              <template #content>
                <div class="box-title">{{ t('global.label.executive') }}</div>
                <div class="box-description">
                  ({{ file.executiveCode }}) {{ file.executiveName }}
                </div>
                <template v-if="file.bossCode && file.bossCode != ''">
                  <br />
                  <div class="box-title">{{ t('global.label.executive_boss') }}:</div>
                  <div class="box-description">({{ file.bossCode }}) {{ file.bossName }}</div>
                </template>
              </template>
            </base-popover>
          </div>
          <div class="col-small">
            <base-popover placement="top">
              {{ file.clientCode }}
              <template #content>
                <div class="box-title">{{ t('global.label.client') }}</div>
                <div class="box-description">({{ file.clientCode }}) {{ file.clientName }}</div>
              </template>
            </base-popover>
          </div>
          <div class="col-small">{{ formatDate(file.dateIn) }}</div>
          <div class="col-small text-uppercase col-small-200 is-col-hidden">
            <span v-if="!file?.haveInvoice">&nbsp;</span>
            <base-badge v-else :type="haveInvoiceByIso(file.haveInvoice)?.type" :is-block="true">
              {{ haveInvoiceByIso(file.haveInvoice).name }}
            </base-badge>
          </div>
          <div class="col-small text-uppercase">
            <base-badge
              :type="getRevisionStageById(file.revisionStages).type + '-' + file.opeAssignStages"
            >
              {{ getRevisionStageById(file.revisionStages).name }}
            </base-badge>
          </div>
          <div class="col-small col-body-options">
            <template v-if="file.origin == 'stela' && file.stela_processing != 2">
              <template v-if="file.origin == 'stela' && file.stela_processing == 0">
                <a-button
                  type="primary"
                  danger
                  size="large"
                  class="px-4"
                  v-bind:disabled="filesStore.isLoadingAsync"
                  @click="filesStore.handleImportStella(file)"
                >
                  <a-row type="flex" justify="space-between" align="middle" style="gap: 7px">
                    <a-col> {{ t('global.button.import') }} </a-col>
                  </a-row>
                </a-button>
              </template>

              <template v-if="file.stela_processing == 1">
                <font-awesome-icon :icon="['fas', 'circle-notch']" />
                <span class="text-danger"> {{ t('global.label.importing') }}..</span>
              </template>

              <template v-if="file.stela_processing == 3"> </template>
            </template>

            <template v-else>
              <popover-hover-and-click
                :buttons="false"
                @onPopoverClick="handlePopoverClickQuotation({ itineraryId: file.id })"
              >
                <font-awesome-icon icon="fa-solid fa-dollar-sign" size="sm" />
                <template #content-hover>{{ t('global.label.see_quote') }}</template>
                <template #content-click>
                  <div class="quotation-content-click">
                    <div class="quotation-content-click-title">
                      {{ t('global.label.quote') }} {{ file.id }}
                    </div>
                    <div class="quotation-content-click-subtitle">
                      {{ t('global.label.this_category_has') }}
                      <span
                        >{{ itineraryStore.getQuotation.length }}
                        {{ t('global.label.products') }}</span
                      >:
                    </div>
                    <div class="quotation-content-click-buttons" style="display: none">
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
                        size="small"
                        :showHeader="false"
                        :columns="quotationColumns"
                        :data-source="itineraryStore.getQuotation"
                        :pagination="false"
                        :loading="itineraryStore.isLoading"
                      >
                        <template #bodyCell="{ column, record }">
                          <template v-if="column.key === 'type'">
                            <strong>{{ record.type }}</strong>
                          </template>
                          <template v-if="column.key === 'name'">
                            <strong>{{ record.name }}</strong>
                          </template>
                          <template v-if="column.key === 'total_amount'">
                            <div
                              v-if="record.total_amount"
                              style="
                                width: 70px;
                                text-align: right;
                                font-weight: 600;
                                color: #eb5757;
                              "
                            >
                              USD {{ record.total_amount }}
                            </div>
                            <div
                              v-else
                              style="
                                width: 70px;
                                text-align: center;
                                font-weight: 600;
                                color: #eb5757;
                              "
                            >
                              -
                            </div>
                          </template>
                        </template>
                      </a-table>
                    </section>
                  </div>
                </template>
              </popover-hover-and-click>

              <popover-hover-and-click :buttons="false">
                <font-awesome-icon icon="fa-solid fa-eye" size="sm" />
                <template #content-hover>{{ t('global.label.see_details') }}</template>
                <template #content-click>
                  <div class="details-content-click">
                    <div class="details-content-click-title">{{ t('files.label.details') }}</div>

                    <div class="details-content-click-subtitle">
                      {{ t('files.label.opening_date') }}:
                    </div>
                    <div class="details-content-click-description">
                      {{ formatDate(file.createdAt) || '-' }}
                    </div>

                    <div class="details-content-click-subtitle">
                      {{ t('files.label.opening_executive') }}:
                    </div>
                    <div class="details-content-click-description">
                      ({{ file.executiveCode || '-' }})
                      {{ file.executiveName || '-' }}
                    </div>

                    <div class="details-content-click-subtitle">
                      {{ t('files.label.deadline') }}:
                    </div>
                    <div class="details-content-click-description">
                      {{ formatDate(file.deadline) || '-' }}
                    </div>

                    <div class="details-content-click-subtitle">{{ t('files.label.order') }}:</div>
                    <div class="details-content-click-description">
                      {{ file.reservationId || '-' }}
                    </div>
                  </div>
                </template>
              </popover-hover-and-click>

              <popover-hover-and-click :buttons="false" v-if="file.fileNumber !== 0">
                <font-awesome-icon :icon="['far', 'file']" size="sm" />
                <template #content-hover>{{ t('files.label.files_document') }}</template>
                <template #content-click>
                  <div class="actions-content-click">
                    <div class="actions-content-click-content">
                      <a-button
                        type="link"
                        style="height: auto; padding: 5px 7px; font-size: 16px"
                        size="small"
                        v-if="file.fileNumber !== 0"
                        :disabled="downloadStore.isFileLoading(file.id)"
                        @click="handleDownloadFileDocuments(file.id, 'passengers', file.lang)"
                      >
                        <div
                          style="
                            display: flex;
                            gap: 5px;
                            justify-content: flex-start;
                            align-items: center;
                          "
                        >
                          <font-awesome-icon
                            :icon="['fas', 'people-group']"
                            v-if="!downloadStore.isFileLoading(file.id)"
                          />
                          <a-spin v-if="downloadStore.isFileLoading(file.id)" />
                          <span style="font-weight: 500">{{ t('global.label.Passengers') }}</span>
                        </div>
                      </a-button>
                      <a-button
                        type="link"
                        style="height: auto; padding: 5px 7px; font-size: 16px"
                        size="small"
                        v-if="file.fileNumber !== 0"
                        :disabled="downloadStore.isFileLoading(file.id)"
                        @click="handleDownloadFileDocuments(file.id, 'flights', file.lang)"
                      >
                        <div
                          style="
                            display: flex;
                            gap: 5px;
                            justify-content: flex-start;
                            align-items: center;
                          "
                        >
                          <font-awesome-icon
                            :icon="['fas', 'plane-departure']"
                            v-if="!downloadStore.isFileLoading(file.id)"
                          />
                          <a-spin v-if="downloadStore.isFileLoading(file.id)" />
                          <span style="font-weight: 500">{{ t('global.label.Flights') }}</span>
                        </div>
                      </a-button>
                      <a-button
                        type="link"
                        style="height: auto; padding: 5px 7px; font-size: 16px"
                        size="small"
                        v-if="file.fileNumber !== 0"
                        :disabled="downloadStore.isFileLoading(file.id)"
                        @click="handleDownloadFileDocuments(file.id, 'invoice', 'pdf', file.lang)"
                      >
                        <div
                          style="
                            display: flex;
                            gap: 5px;
                            justify-content: flex-start;
                            align-items: center;
                          "
                        >
                          <font-awesome-icon
                            :icon="['fas', 'file-invoice']"
                            v-if="!downloadStore.isFileLoading(file.id)"
                          />
                          <a-spin v-if="downloadStore.isFileLoading(file.id)" />
                          <span style="font-weight: 500">{{ t('global.label.Invoice') }}</span>
                        </div>
                      </a-button>
                      <a-button
                        type="link"
                        style="height: auto; padding: 5px 7px; font-size: 16px"
                        size="small"
                        v-if="file.fileNumber !== 0"
                        :disabled="downloadStore.isFileLoading(file.id)"
                        @click="handleDownloadFileDocuments(file.id, 'skeleton', 'pdf', file.lang)"
                      >
                        <div
                          style="
                            display: flex;
                            gap: 5px;
                            justify-content: flex-start;
                            align-items: center;
                          "
                        >
                          <font-awesome-icon
                            :icon="['fas', 'file']"
                            v-if="!downloadStore.isFileLoading(file.id)"
                          />
                          <a-spin v-if="downloadStore.isFileLoading(file.id)" />
                          <span style="font-weight: 500">{{ t('global.label.Skeleton') }}</span>
                        </div>
                      </a-button>
                      <a-button
                        type="link"
                        style="height: auto; padding: 5px 7px; font-size: 16px"
                        size="small"
                        v-if="file.fileNumber !== 0"
                        @click="showItineraryModalDownload(file)"
                      >
                        <div
                          style="
                            display: flex;
                            gap: 5px;
                            justify-content: flex-start;
                            align-items: center;
                          "
                        >
                          <font-awesome-icon
                            :icon="['fas', 'file-lines']"
                            v-if="!downloadStore.isFileLoading(file.id)"
                          />
                          <a-spin v-if="downloadStore.isFileLoading(file.id)" />
                          <span style="font-weight: 500">{{ t('global.label.Itinerary') }}</span>
                        </div>
                      </a-button>
                    </div>
                  </div>
                </template>
              </popover-hover-and-click>

              <popover-hover-and-click :buttons="false" v-if="1 != 1">
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
                          style="
                            display: flex;
                            gap: 12px;
                            justify-content: center;
                            align-items: center;
                          "
                        >
                          {{ t('global.button.go') }}
                          <font-awesome-icon icon="fa-solid fa-arrow-right-long" />
                        </div>
                      </base-button>
                    </div>
                  </div>
                </template>
              </popover-hover-and-click>

              <template v-if="!file.showMasterServices && isAdmin()">
                <base-popover placement="top">
                  <template v-if="!filesStore.isLoadingAsync">
                    <font-awesome-icon
                      icon="fa-solid fa-repeat"
                      @click="handleProcessMasterServices(file)"
                    />
                  </template>
                  <template #content>
                    <div class="box-title">{{ t('files.button.generate_master_services') }}</div>
                  </template>
                </base-popover>
              </template>

              <base-popover placement="top">
                <template v-if="!filesStore.isLoadingAsync">
                  <font-awesome-icon
                    :icon="['far', 'pen-to-square']"
                    @click="handleGoToEdit(file.id)"
                  />
                </template>
                <template #content>
                  <div class="box-title">{{ t('global.label.edit') }}</div>
                </template>
              </base-popover>

              <popover-hover-and-click :buttons="false" v-if="1 != 1">
                <font-awesome-icon icon="fa-solid fa-ellipsis-vertical" size="sm" />
                <template #content-hover>{{ t('files.button.file_actions') }}</template>
                <template #content-click>
                  <div class="actions-content-click">
                    <div class="actions-content-click-content">
                      <a-button
                        type="link"
                        style="height: auto; padding: 5px 7px; font-size: 16px"
                        size="small"
                        @click="handleGoToEdit(file.id)"
                      >
                        <div
                          style="
                            display: flex;
                            gap: 5px;
                            justify-content: flex-start;
                            align-items: center;
                          "
                        >
                          <font-awesome-icon icon="fa-solid fa-edit" />
                          <span style="font-weight: 500">{{ t('global.label.edit') }}</span>
                        </div>
                      </a-button>
                      <a-button
                        type="link"
                        style="height: auto; padding: 5px 7px; font-size: 16px"
                        size="small"
                        v-if="file.fileNumber !== 0"
                        @click="showModalCloneFile(file.id)"
                      >
                        <div
                          style="
                            display: flex;
                            gap: 5px;
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
            </template>
          </div>
        </div>
      </div>

      <div class="row g-0 row-pagination">
        <a-pagination
          v-model:current="currentPageValue"
          v-model:pageSize="currentPageSize"
          :disabled="data?.length === 0"
          :pageSizeOptions="DEFAULT_PAGE_SIZE_OPTIONS"
          :total="total"
          show-size-changer
          show-quick-jumper-off
          @change="onChange"
          @showSizeChange="onShowSizeChange"
        >
          <template #buildOptionText="props">
            <span>{{ props.value }}</span>
          </template>
        </a-pagination>

        <template v-if="!formFilter.flag_stella">
          <a-button
            type="primary"
            danger
            ghost
            size="large"
            :disabled="downloadStore.isLoading"
            :loading="downloadStore.isLoading"
            v-if="data?.length > 0"
            @click="handleDownloadList"
          >
            <template #icon>
              <font-awesome-icon icon="fa-solid fa-arrow-down" />
            </template>
            <span class="ms-2">{{ t('files.label.list_download') }}</span>
          </a-button>
        </template>
      </div>
    </template>

    <CloneFileModal
      v-if="modalCloneIsOpen"
      :is-open="modalCloneIsOpen"
      @update:is-open="modalCloneIsOpen = $event"
      :showFileSelect="false"
      :setFileId="fileId"
    />
    <ItineraryModalComponent
      v-if="modalIsOpenItinerary"
      v-bind:is-open.sync="modalIsOpenItinerary"
      @update:is-open="modalIsOpenItinerary = $event"
    />
  </div>
</template>

<script setup>
  import { formatDate, checkDates } from '@/utils/files.js';
  import { computed, reactive, ref, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import BaseBadge from './BaseBadge.vue';
  import BaseButton from './BaseButton.vue';
  import BasePopover from './BasePopover.vue';
  import BaseRangeDatePicker from './BaseRangeDatePicker.vue';
  import BaseSelect from './BaseSelect.vue';
  import PopoverHoverAndClick from './PopoverHoverAndClick.vue';
  import FilesPopoverVip from '@/components/files/edit/FilesPopoverVip.vue';
  import { isAdmin } from '@/utils/auth';

  import {
    useClientsStore,
    useDownloadStore,
    useExecutivesStore,
    useFilesStore,
    useHaveInvoicesStore,
    useItineraryStore,
    useRevisionStagesStore,
    useStatusesStore,
  } from '@store/files';

  import { notification } from 'ant-design-vue';
  import { debounce } from 'lodash-es';
  import { useI18n } from 'vue-i18n';
  import CloneFileModal from '@/components/files/clone/CloneFileModal.vue';
  import BaseInputSearch from '@/components/files/reusables/BaseInputSearch.vue';
  import ItineraryModalComponent from '@/components/files/itinerary/component/ItineraryModalComponent.vue';

  const { t } = useI18n({
    useScope: 'global',
  });

  const router = useRouter();
  const statusesStore = useStatusesStore();
  const haveInvoicesStore = useHaveInvoicesStore();
  const revisionStagesStore = useRevisionStagesStore();
  const executivesStore = useExecutivesStore();
  const clientsStore = useClientsStore();
  const itineraryStore = useItineraryStore();
  const downloadStore = useDownloadStore();
  const filesStore = useFilesStore();
  const modalCloneIsOpen = ref(false);
  const fileId = ref('');

  const statusByIso = (iso) => statusesStore.getStatusByIso(iso);
  const haveInvoiceByIso = (iso) => haveInvoicesStore.getHaveInvoiceByIso(iso);
  const getRevisionStageById = (id) => revisionStagesStore.getRevisionStageById(id);

  const DEFAULT_FILTER_BY = null;
  const selectedFilter = ref(DEFAULT_FILTER_BY);

  const INIT_CURRENT_PAGE_VALUE = 1;
  const INIT_PAGE_SIZE = 9;
  const DEFAULT_PAGE_SIZE_OPTIONS = [6, 9, 18];

  const modalIsOpenItinerary = ref(false);

  const quotationColumns = ref([
    { name: t('global.column.order'), dataIndex: 'order', key: 'order' },
    { name: t('global.column.type'), dataIndex: 'type', key: 'type' },
    { name: t('global.column.description'), dataIndex: 'name', key: 'name' },
    { name: t('global.column.total'), dataIndex: 'total_amount', key: 'total_amount' },
  ]);

  const DEFAULT_FORM_FILTER = {
    filter: '',
    executiveCode: null,
    clientId: null,
    dateRange: [],
    flag_stella: false,
  };

  const formFilter = reactive(DEFAULT_FORM_FILTER);

  const formFilterRef = ref();

  const props = defineProps({
    config: {
      type: Object,
      default: () => ({}),
    },
    total: {
      type: Number,
      default: 0,
    },
    isLoading: {
      type: Boolean,
      default: true,
    },
    data: {
      type: Array,
      default: () => [],
    },
    currentPage: {
      type: Number,
      default: 0,
    },
    defaultPerPage: {
      type: Number,
      default: 0,
    },
    perPage: {
      type: Number,
      default: 0,
    },
  });

  const emit = defineEmits([
    'onChange',
    'onShowSizeChange',
    'onFilterBy',
    'onFilter',
    'onRefresh',
    'handleRefreshCache',
  ]);

  const currentPageValue = ref(INIT_CURRENT_PAGE_VALUE);
  const currentPageSize = ref(INIT_PAGE_SIZE);

  watch(formFilter, async (newFormFilter) => {
    const form = { ...newFormFilter };
    emit('onFilter', { form });
  });

  watch(
    () => props.perPage,
    (newPerPage) => {
      if (newPerPage) {
        currentPageSize.value = newPerPage;
      }
    }
  );

  watch(
    () => props.currentPage,
    (newCurrentPage) => {
      if (newCurrentPage) {
        currentPageValue.value = newCurrentPage;
      }
    }
  );

  const onFilter = (values) => {
    emit('onFilter', { perPage: props.perPage, form: values });
  };

  const onFilterFailed = (errorInfo) => {
    console.log('Failed:', errorInfo);
    notification.error({
      message: `Filter`,
      description: errorInfo,
      duration: 8,
    });
  };

  const onChange = (page, perSize) => {
    const { filter, clientId, flag_stella } = formFilter;
    emit('onChange', { currentPage: page, perPage: perSize, filter, clientId, flag_stella });
  };

  const onShowSizeChange = (current, size) => {
    const { flag_stella } = formFilter;
    emit('onShowSizeChange', { current, size, flag_stella });
  };

  const isDesc = computed(() => selectedFilter?.value?.filterByType === 'desc');

  const isSelectedFilterBy = (fieldName) => selectedFilter?.value?.filterBy === fieldName;

  const onFilterBy = ({ filterBy, filterByType }) => {
    selectedFilter.value = DEFAULT_FILTER_BY;
    selectedFilter.value = { filterBy, filterByType };

    emit('onFilterBy', selectedFilter.value);
  };

  const handlePopoverClickQuotation = ({ itineraryId }) => {
    itineraryStore.getQuotationById({ itineraryId });
  };

  const handleDownloadList = () => {
    const { filter, executiveCode, clientId, dateRange } = formFilter;
    const perPage = props.perPage;
    downloadStore.download({
      currentPage: currentPageValue.value,
      filter,
      perPage,
      executiveCode,
      clientId,
      dateRange,
    });
  };

  const handleDownloadFileDocuments = (fileId, nameDocument, type_extensions = 'xlsx', lang) => {
    downloadStore.downloadFileDocuments(fileId, nameDocument, type_extensions, lang);
  };

  const handleSearchExecutives = debounce((value) => {
    if (value != '' || (value == '' && executivesStore.getExecutives.length == 0)) {
      executivesStore.fetchAll(value);
    }
  }, 300);

  const handleSearchClients = debounce((value) => {
    if (value != '' || (value == '' && clientsStore.getClients.length == 0)) {
      clientsStore.fetchAll(value);
    }
  }, 300);

  const handleGoToEdit = (id) => {
    if (!id) return;
    router.push({ name: 'files-edit', params: { id } });
  };

  const handleRefresh = () => {
    filesStore.revisionStages = null;
    filesStore.filterNextDays = null;
    filesStore.opeAssignStages = null;
    formFilter.flag_stella = false;
    formFilter.filter = null;
    formFilterRef.value.resetFields();
    currentPageValue.value = 1;
  };

  const handleRefreshCache = () => {
    formFilter.flag_stella = false;
    emit('handleRefreshCache');
  };

  const showModalCloneFile = (id) => {
    fileId.value = id.toString();
    console.log('fileId: ', fileId);
    modalCloneIsOpen.value = true;
  };

  const handleSearchStella = () => {
    formFilter.flag_stella = true;
  };

  const showClass = (_dateIn) => {
    let days = parseInt(checkDates(new Date(), _dateIn));

    if (days >= 0) {
      if (days <= 7) {
        days = 7;
      }

      if (days <= 15 && days > 7) {
        days = 15;
      }

      if (days <= 30 && days > 15) {
        days = 30;
      }

      return `days-color-${days}`;
    } else {
      return '';
    }
  };

  const showItineraryModalDownload = (file) => {
    filesStore.setFile(file);
    modalIsOpenItinerary.value = true;
  };

  const handleProcessMasterServices = async (file) => {
    const fileId = file.id;
    try {
      await filesStore.processMasterServices({ fileId });

      if (!filesStore.getError) {
        file.showMasterServices = true;
      } else {
        file.showMasterServices = false;
        notifyMasterServicesError();
      }
    } catch (err) {
      file.showMasterServices = false;
      console.error('Error inesperado al procesar master services:', err);
      notifyMasterServicesError();
    }
  };

  const notifyMasterServicesError = () => {
    notification.error({
      message: 'Generación de Master Services',
      description:
        'Ocurrió un error al actualizar los master services en algunos servicios del FILE',
    });
  };
</script>

<style scoped lang="scss">
  .base-table {
    .row {
      display: flex;
      align-items: center;
      border-radius: 6px;
      margin-bottom: 15px;
      text-align: center;
      font-size: 0.875rem;
    }

    .row-header {
      background-color: var(--files-background-1);
      color: var(--files-black-4);
      min-height: 50px;
      font-weight: 700;
    }

    .row-header-sort {
      cursor: pointer;
    }

    .container-body {
      min-height: 370px;
      // min-height: 465px;
      &.is-loading {
        background: #fafafa;
        display: flex;
        justify-content: center;
        align-items: center;
      }
    }

    .row-body {
      background-color: var(--files-gray-1);
      border: 1px solid var(--files-main-colorgray-1);
      min-height: 40px;
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
    gap: 1rem;
  }

  .form-vip {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
    margin-bottom: 1.625rem;
  }

  .group-vip {
    cursor: pointer;
    transition: 0.3s ease all;
    color: #c4c4c4;

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
    height: 260px;
    // height: 337px;
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
      margin-top: 5px;

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
    height: auto;
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

  .status-row {
    &-error {
      width: 7px;
      height: 7px;
      background: #c21d3b;
      box-shadow: 0px 0px 0px 2px rgba(194, 29, 59, 0.24);
      border-radius: 100px;
    }

    &-success {
      width: 7px;
      height: 7px;
      background: #1ed790;
      box-shadow: 0px 0px 0px 2px rgba(30, 215, 144, 0.25);
      border-radius: 100px;
    }

    &-trends {
      width: 7px;
      height: 7px;
      background: #9574af;
      box-shadow: 0px 0px 0px 2px #e3d7f2;
      border-radius: 100px;
    }

    &-trends {
      width: 7px;
      height: 7px;
      background: #9574af;
      box-shadow: 0px 0px 0px 2px #e3d7f2;
      border-radius: 100px;
    }
  }

  .is-col-hidden {
    display: none;
  }

  :deep(.ant-form-item.base-select-w210) {
    width: 210px !important;
    min-width: 210px !important;
    max-width: 210px !important;
    flex: 0 0 210px !important;
  }

  :deep(.ant-form-item.base-select-w210[data-v-784362c5]) {
    width: 210px !important;
  }
</style>
