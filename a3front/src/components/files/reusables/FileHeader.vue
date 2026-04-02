<template>
  <template v-if="filesStore.isLoadingBasic">
    <a-skeleton rows="3" active :title="false" />
  </template>
  <div v-show="!filesStore.isLoadingBasic">
    <a-row
      type="flex"
      align="middle"
      justify="space-between"
      v-bind:class="['files-edit__header', !isEditable() ? 'mb-3' : '']"
    >
      <a-col>
        <div class="files-edit__fileinfo">
          <div class="files-edit__fileinfo-left">
            <template v-if="flag_protected">
              <a-tooltip>
                <template #title>
                  <small class="text-uppercase">{{ t('files.label.protected_rate') }}</small>
                </template>
                <font-awesome-icon
                  :icon="['fas', 'shield-halved']"
                  class="text-dark-warning"
                  size="2xl"
                  beat
                />
              </a-tooltip>
            </template>
            <files-popover-info :data="filesStore.getFile" />
            <span class="files-edit__filelabel">{{ t('files.column.file') }} N°</span>
            <span class="files-edit__filenum"> {{ filesStore.getFile.fileNumber }} </span>
          </div>

          <div
            class="custom-box"
            v-if="
              filesStore.getFile.status == 'xl' &&
              filesStore.getFile.statusReason != '' &&
              filesStore.getFile.statusReason != null
            "
          >
            {{ t('files.label.delete_motive') }}:
            <strong>{{ filesStore.getFile.statusReason.replace('Anulado - ', '') }}</strong>
          </div>

          <div class="files-edit__fileinfo-center"></div>
          <div class="files-edit__fileinfo-right" v-if="!editable">
            <div class="files-edit__statuses">
              <template v-if="!editable">
                <base-popover placement="bottom">
                  <base-badge
                    v-if="filesStore.getFile?.status"
                    :type="statusByIso(filesStore.getFile.status).type"
                  >
                    {{ statusByIso(filesStore.getFile.status).name }}
                  </base-badge>
                  <template #content>
                    <div class="box-description">{{ filesStore.getFile.statusReason }}</div>
                  </template>
                </base-popover>

                <template v-if="haveInvoicesStore.getHaveInvoices.length > 0">
                  <base-badge :type="filesStore.getFile.haveInvoice ? 'billed' : 'unbilled'">
                    {{ haveInvoiceByIso(filesStore.getFile.haveInvoice).name }}
                  </base-badge>
                </template>

                <template v-if="revisionStagesStore.getRevisionStages.length > 0">
                  <base-badge
                    :type="`${filesStore.getFile.opeAssignStages === 2 ? 'success' : getRevisionStageById(filesStore.getFile.revisionStages).type}`"
                  >
                    {{ getRevisionStageById(filesStore.getFile.revisionStages).name }}
                  </base-badge>
                </template>
              </template>
              <template v-else>
                <template v-if="filesStore.getFile.status == 'xl'">
                  <a-alert
                    type="error"
                    v-if="
                      filesStore.getFile.statusReason != '' &&
                      filesStore.getFile.statusReason != null
                    "
                  >
                    <template #message>
                      <div class="text-dark">
                        {{ t('files.label.delete_motive') }}:
                        <b>{{ filesStore.getFile.statusReason.replace('Anulado - ', '') }}</b>
                      </div>
                    </template>
                  </a-alert>
                </template>
              </template>
            </div>
          </div>
        </div>
      </a-col>
      <a-col flex="auto">
        <div class="text-right px-2 in-charge">
          <p class="mb-0">
            <small>{{ t('files.label.main_executive') }}</small>
          </p>
          <p class="mb-0">
            <font-awesome-icon :icon="['fas', 'user-tie']" class="me-1" />
            <component :is="hasBoss ? 'base-popover' : 'span'" placement="top" class="d-inline">
              <b>{{ executiveName }}</b>

              <template #content v-if="hasBoss">
                <div class="box-title text-dark-gray">{{ t('global.label.executive_boss') }}:</div>
                <div class="box-description">
                  ({{ filesStore.getFile.bossCode }}) {{ filesStore.getFile.bossName }}
                </div>
              </template>
            </component>
          </p>
        </div>
      </a-col>
      <a-col>
        <template v-if="socketsStore.getConnections.length > 0">
          <a-row type="flex" align="middle" style="gap: 7px">
            <!-- a-col>
              <font-awesome-icon :icon="['fas', 'podcast']" size="lg" class="text-dark-gray" />
            </a-col -->
            <a-col>
              <div
                class="files-edit__avatars"
                v-if="editable && filesStore.getFile.fileNumber !== 0"
              >
                <template v-for="(connection, c) in socketsStore.getConnections">
                  <files-popover-avatar
                    @onRemoveUserView="removeUserView"
                    @click="
                      connection.userCode !== getUserCode()
                        ? handleConnectTeams(`${connection.userCode}@limatours.com.pe`)
                        : undefined
                    "
                    :token="connection.token"
                    :ip="connection.ip"
                    :label="`${connection.userName} (${connection.userCode})`"
                    :photo="baseURLPhoto(connection.userPhoto)"
                    :active="connection.token === socketsStore.getToken"
                  />
                </template>
              </div>
            </a-col>
          </a-row>
        </template>
      </a-col>
    </a-row>
    <div class="files-edit__subheader" v-if="editable">
      <div class="files-edit__subheader-col1" style="height: auto; min-height: 28px">
        <span class="files-edit__filealert" v-if="filesStore.getFile.fileNumber == 0">
          <a-alert type="warning">
            <template #message>
              <WarningOutlined :style="{ fontSize: '18px', color: '#FFCC00' }" /> Para crear el file
              y el sistema te asigne un número de file, debes agregar un servicio.
            </template>
          </a-alert>
        </span>
        <template v-else>
          <div class="files-edit__statuses text-uppercase">
            <base-popover placement="bottom">
              <base-badge
                v-if="filesStore.getFile?.status"
                :type="statusByIso(filesStore.getFile.status).type"
              >
                {{ statusByIso(filesStore.getFile.status).name }}
              </base-badge>
              <template #content>
                <div class="box-description">{{ filesStore.getFile.statusReason }}</div>
              </template>
            </base-popover>

            <base-badge :type="filesStore.getFile.haveInvoice ? 'billed' : 'unbilled'">
              {{ haveInvoiceByIso(filesStore.getFile.haveInvoice).name }}
            </base-badge>

            <template v-if="getRevisionStageById(filesStore.getFile.revisionStages)">
              <base-badge
                :type="
                  filesStore.getFile.opeAssignStages
                    ? 'success'
                    : `${getRevisionStageById(filesStore.getFile.revisionStages).type}`
                "
              >
                {{ getRevisionStageById(filesStore.getFile.revisionStages).name }}
              </base-badge>
            </template>
          </div>
          <files-switch-serie
            :isEditable="isEditable()"
            :label-unchecked="`${t('files.label.asociate_serie')}`"
            :label-checked="`${t('files.label.label_select_serie')}`"
            v-if="
              filesStore.getFile.fileNumber !== 0 &&
              !filesStore.isLoading &&
              !filesStore.isLoadingBasic
            "
          />
          <files-switch-categories
            :isEditable="isEditable()"
            :categories="filesStore.getFile.categories"
            @onChangeCategoriesFile="changeCategoriesFile"
            v-if="
              filesStore.getFile.fileNumber !== 0 &&
              !filesStore.isLoading &&
              !filesStore.isLoadingBasic
            "
          />
        </template>
      </div>

      <div class="files-edit__subheader-col2">
        <template v-if="filesStore.getFile.fileNumber !== 0">
          <a-dropdown class="except-block-style">
            <a-button
              style="height: auto"
              class="text-600 except-block-style"
              size="large"
              @click.prevent
              overlayClassName="btn-create-file-item"
              :disabled="
                filesStore.isLoadingAsync ||
                filesStore.isLoading ||
                itineraryStore.isLoadingAsync ||
                filesStore.getFilePendingProcesses
              "
              >{{ t('files.button.new') }}
            </a-button>
            <template #overlay>
              <a-menu class="file-dropdown-menu">
                <a-menu-item key="1" class="file-menu-item" @click="showModalCreateFile">
                  <div class="menu-item-content">
                    <IconDocPlus color="#EB5757" width="1.5em" height="1.5em" />
                    <span class="menu-text">{{ t('global.label.from_zero') }}</span>
                  </div>
                </a-menu-item>
                <a-menu-item key="2" class="file-menu-item" @click="showModalCloneFile">
                  <div class="menu-item-content">
                    <IconCloneSolid color="#EB5757" width="1.2em" height="1.2em" />
                    <span class="menu-text">{{ t('global.label.clone') }}</span>
                  </div>
                </a-menu-item>
              </a-menu>
            </template>
          </a-dropdown>
          <a-button
            class="ms-3 text-600"
            type="primary"
            ghost
            @click="handleCancel"
            :disabled="
              filesStore.isLoadingAsync ||
              filesStore.isLoading ||
              itineraryStore.isLoadingAsync ||
              filesStore.getFilePendingProcesses
            "
            size="large"
            v-if="filesStore.getFile.status == 'ok'"
          >
            {{ t('global.button.destroy') }}
          </a-button>
        </template>
        <template v-if="filesStore.getFile.status == 'xl'">
          <a-button
            v-if="flag_activate"
            type="primary"
            default
            @click="showModalActivate"
            size="large"
            :disabled="
              filesStore.isLoadingAsync ||
              filesStore.isLoading ||
              itineraryStore.isLoadingAsync ||
              filesStore.getFilePendingProcesses
            "
            class="ms-3 text-600"
          >
            {{ t('global.button.activate') }}
          </a-button>
        </template>
        <a-tooltip placement="right">
          <template #title>{{ t('global.button.close') }} FILE</template>
          <a-button
            @click="goTo('files')"
            :disabled="
              filesStore.isLoadingAsync || filesStore.isLoading || itineraryStore.isLoadingAsync
            "
            class="ms-3 text-600"
            style="z-index: 10"
            type="dashed"
            size="large"
          >
            <font-awesome-icon :icon="['fas', 'right-from-bracket']" />
          </a-button>
        </a-tooltip>
      </div>
    </div>
    <div class="position-relative" :class="{ 'block-style': !isEditable() }">
      <div class="locked" v-if="filesStore.getFile.status == 'xl'"></div>
      <div class="files-edit__information">
        <div class="files-edit__wrap">
          <files-popover-vip
            v-if="filesStore.getFile.id > 0"
            :editable="isEditable()"
            :data="filesStore.getFile"
            @onRefreshCache="handleRefreshCache"
          />
          <files-toggler-file-name
            :data="filesStore.getFile.description"
            :editable="isEditable()"
          />
        </div>

        <files-toggler-start-day :editable="isEditable()">
          <span style="text-transform: capitalize">
            {{ formatDate(filesStore.getFile.dateIn, 'MMM DD, YYYY') }}
          </span>
        </files-toggler-start-day>

        <files-edit-field-static :editable="isEditable()" :hide-content="true">
          <template #label>{{ t('files.label.final_date') }}</template>
          <template #content>
            <span style="text-transform: capitalize" v-if="filesStore.getFile.fileNumber !== 0">
              {{ formatDate(filesStore.getFile.dateOut, 'MMM DD, YYYY') }}
            </span>
            <span v-else>&nbsp;</span>
          </template>
        </files-edit-field-static>

        <files-edit-field-static :editable="isEditable()" :hide-content="false">
          <template #label>{{ t('global.label.client_name') }}</template>
          <template #content>
            {{ filesStore.getFile.clientCode }}
          </template>
          <template #popover-content>
            <div class="files-edit-field-static-wrap">
              <div class="d-flex" style="gap: 5px">
                <strong>MKP:</strong>
                <span style="font-weight: normal">{{ t('global.label.client') }}</span>
                <span class="lt-red">{{ filesStore.getFile.markupClient }}{{ '%' }}</span>
              </div>
              <div class="d-flex" style="gap: 5px">
                <strong>{{ t('global.label.name') }}:</strong>
                <span class="lt-red"
                  >({{ filesStore.getFile.clientCode }}) {{ filesStore.getFile.clientName }}</span
                >
              </div>
            </div>
          </template>
        </files-edit-field-static>

        <files-edit-field-static :editable="isEditable()" :hide-content="false">
          <template #label
            ><span class="text-capitalize">{{ t('global.label.passengers') }}</span></template
          >
          <template #content>
            <div style="display: flex; gap: 3px; cursor: pointer" @click="goToPaxs">
              <font-awesome-icon style="width: 15.31px; height: 17.5px" icon="fa-solid fa-user" />
              {{
                filesStore.getFile?.adults
                  ? String(filesStore.getFile.adults).padStart(2, '0')
                  : '00'
              }}
              <font-awesome-icon
                style="width: 13.75px; height: 18.33px"
                icon="fa-solid fa-child-reaching"
                v-if="filesStore.getFile.children > 0"
              />
              {{
                filesStore.getFile?.children
                  ? String(filesStore.getFile.children).padStart(2, '0')
                  : ''
              }}
              <font-awesome-icon
                style="width: 20px; height: 20px"
                icon="fa-solid fa-baby-carriage"
                v-if="filesStore.getFile.infants > 0"
              />
              {{
                filesStore.getFile?.infants
                  ? String(filesStore.getFile.infants).padStart(2, '0')
                  : ''
              }}
            </div>
          </template>
          <template #popover-content
            >{{ t('global.label.goto') }} {{ t('global.label.passengers') }}
          </template>
        </files-edit-field-static>

        <files-edit-field-static :editable="isEditable()" :hide-content="true">
          <template #label>
            <span class="text-capitalize">{{ t('global.label.accommodation') }}</span>
          </template>
          <template #content>
            <BaseSelectAccommodations v-model="accommodation" :editable="false" :box="false" />
          </template>
        </files-edit-field-static>

        <files-toggler-language :editable="isEditable()" :isEditable="isEditable()" />

        <files-edit-field-static
          :editable="isEditable()"
          :hide-content="false"
          v-if="
            filesStore.getFile.generateStatement ||
            filesStore.getFile.amountSale > 0 ||
            filesStore.getFile?.statement?.total > 0
          "
        >
          <template #label>{{ t('files.label.statement') }}</template>
          <template #content>
            <div class="files-edit-field-statement" style="gap: 3px" @click="openStatementView">
              <template v-if="filesStore.getFile?.statement?.total > 0">
                <font-awesome-icon icon="fa-solid fa-dollar-sign" class="text-dark-gray" />
                {{
                  filesStore.getFile?.statement
                    ? formatNumber({ number: filesStore.getFile?.statement?.total, digits: 2 })
                    : '0.00'
                }}
              </template>
              <template v-else-if="filesStore.getFile.amountSale > 0">
                <font-awesome-icon
                  style="width: 11.25px; height: 18px"
                  icon="fa-solid fa-dollar-sign"
                />
                {{
                  filesStore.getFile.amountSale
                    ? formatNumber({ number: filesStore.getFile.amountSale, digits: 2 })
                    : '0.00'
                }}
              </template>
            </div>
          </template>
          <template #popover-content>
            <div class="files-edit-field-static-wrap d-flex" style="gap: 5px">
              <strong>{{ t('files.label.profitability') }}:</strong>
              <span :class="[filesStore.getFile.profitability > 0 ? 'lt-green' : 'lt-red']">
                {{ filesStore.getFile.profitability }}%
              </span>
            </div>
          </template>
        </files-edit-field-static>
      </div>
      <CreateFileModal
        v-if="modalIsOpen"
        :is-open="modalIsOpen"
        @update:is-open="modalIsOpen = $event"
      />
      <CloneFileModal
        v-if="modalCloneIsOpen"
        :is-open="modalCloneIsOpen"
        @update:is-open="modalCloneIsOpen = $event"
        :showFileSelect="false"
        :setFileId="''"
      />
    </div>
  </div>

  <a-modal v-model:visible="modalActivate" :width="400">
    <template #title> {{ t('global.button.activate') }} {{ t('files.column.file') }} </template>
    <a-alert class="text-warning" type="warning" show-icon v-if="checked == 1 || checked == 2">
      <template #icon> </template>
      <template #description>
        <template v-if="checked == 1">
          {{ t('files.message.title_activate') }}
        </template>
        <template v-if="checked == 2">
          {{ t('files.message.title_to_quote') }}
        </template>
      </template>
    </a-alert>
    <p class="text-center mt-3 mx-2 font-490">{{ t('files.message.preferred_activate') }}:</p>
    <p class="text-center">
      <a-radio-group v-model:value="checked">
        <a-radio :value="1" v-if="!lockedOriginalDate">
          <a-tooltip>
            <template #title>{{ t('files.label.original_date_tooltip') }}</template>
            {{ t('files.label.original_date') }}
          </a-tooltip>
        </a-radio>
        <a-radio :value="2">{{ t('files.label.new_date') }}</a-radio>
      </a-radio-group>
    </p>
    <div
      v-if="checked == 2"
      :style="{ width: '300px', border: '1px solid #d9d9d9', borderRadius: '6px' }"
    >
      <a-calendar
        :disabledDate="disabledDate"
        v-model:value="newDateActivate"
        :fullscreen="false"
      ></a-calendar>
    </div>
    <template #footer>
      <div class="text-center">
        <a-button
          type="default"
          class="bnt-default"
          default
          @click="hideModalActivate"
          size="large"
        >
          {{ t('global.button.cancel') }}
        </a-button>
        <a-button
          type="primary"
          primary
          :loading="loading"
          v-if="checked == 1 || checked == 2"
          @click="handleActivate"
          size="large"
        >
          <template v-if="checked == 1 && !flag_quote">
            {{ t('global.button.continue') }}
          </template>
          <template v-if="checked == 2 || flag_quote">
            {{ t('global.label.goto') }} {{ t('global.label.quote') }}
          </template>
        </a-button>
      </div>
    </template>
  </a-modal>

  <a-modal v-model:visible="modalProtected" title="Servicio Actualizado" :width="620">
    <a-alert type="error" show-icon>
      <template #icon>
        <font-awesome-icon :icon="['fas', 'circle-exclamation']" />
      </template>
      <template #description>
        <div class="text-danger">
          {{ t('files.message.service_protected') }}
        </div>
      </template>
    </a-alert>

    <template v-for="(itinerary, i) in filesStore.getFileItinerariesProtected">
      <template v-if="itinerary.entity === 'service'">
        <template v-if="itinerary.service_amount_logs && itinerary.service_amount_logs.length > 0">
          <a-row type="flex" justify="space-between" align="middle" class="my-3" style="gap: 5px">
            <a-col>
              <font-awesome-icon
                :icon="
                  filesStore.showServiceIcon(
                    itinerary.service_category_id,
                    itinerary.service_sub_category_id,
                    itinerary.service_type_id
                  )
                "
                style="font-size: 1.2rem"
              />
            </a-col>
            <a-col flex="auto">
              <a-tooltip>
                <template v-if="itinerary.name.length > 70" #title>{{ itinerary.name }}</template>
                {{ truncateString(itinerary.name, 75) }}
              </a-tooltip>
            </a-col>
          </a-row>

          <a-row type="flex" justify="space-between" align="middle">
            <a-col flex="auto">
              <a-card style="width: 100%">
                <a-row type="flex" justify="space-between" align="middle">
                  <a-col>
                    <font-awesome-icon :icon="['fas', 'shield-halved']" class="text-warning" />
                    {{ t('files.label.initial_cost') }}
                  </a-col>
                  <a-col>
                    <b>MKP {{ itinerary.service_amount_logs[0].markup_previous }}%</b>
                  </a-col>
                </a-row>
                <a-row type="flex" justify="space-between" align="middle">
                  <a-col>
                    <span class="text-warning text-700" style="font-size: 24px">
                      $ {{ itinerary.service_amount_logs[0].amount_previous }}
                    </span>
                  </a-col>
                  <a-col class="text-right">
                    <p class="mb-0 text-warning">
                      {{ itinerary.service_amount_logs[0].markup_previous }}%
                      <font-awesome-icon
                        :icon="[
                          'fas',
                          itinerary.service_amount_logs[0].markup_previous >= 0
                            ? 'arrow-trend-up'
                            : 'arrow-trend-down',
                        ]"
                      />
                    </p>
                    <small class="mb-0" style="font-size: 6px">{{
                      t('files.label.service_profitability')
                    }}</small>
                  </a-col>
                </a-row>
              </a-card>
            </a-col>
            <a-col class="mx-3">
              <font-awesome-icon :icon="['fas', 'arrow-right-long']" />
            </a-col>
            <a-col flex="auto">
              <a-card style="width: 100%">
                <a-row type="flex" justify="space-between" align="middle">
                  <a-col>
                    <font-awesome-icon class="text-danger" :icon="['fas', 'circle-exclamation']" />
                    {{ t('files.label.updated_cost') }}
                  </a-col>
                  <a-col>
                    <b>MKP {{ itinerary.service_amount_logs[0].markup }}%</b>
                  </a-col>
                </a-row>
                <a-row type="flex" justify="space-between" align="middle">
                  <a-col>
                    <span class="text-danger text-700" style="font-size: 24px">
                      $ {{ itinerary.service_amount_logs[0].amount }}
                    </span>
                  </a-col>
                  <a-col class="text-right">
                    <p class="mb-0 text-danger">
                      {{ itinerary.service_amount_logs[0].markup }}%
                      <font-awesome-icon
                        :icon="[
                          'fas',
                          itinerary.service_amount_logs[0].markup_previous >= 0
                            ? 'arrow-trend-up'
                            : 'arrow-trend-down',
                        ]"
                      />
                    </p>
                    <small class="mb-0" style="font-size: 6px">{{
                      t('files.label.service_profitability')
                    }}</small>
                  </a-col>
                </a-row>
              </a-card>
            </a-col>
          </a-row>
        </template>
      </template>

      <template v-if="itinerary.entity === 'hotel'">
        <template v-if="itinerary.service_amount_logs && itinerary.service_amount_logs.length > 0">
          <a-row type="flex" justify="space-between" align="middle" class="my-3" style="gap: 5px">
            <a-col>
              <i class="bi bi-building-fill" style="font-size: 1.2rem"></i>
            </a-col>
            <a-col flex="auto">
              <a-tooltip>
                <template v-if="itinerary.name.length > 70" #title>{{ itinerary.name }}</template>
                {{ truncateString(itinerary.name, 75) }}
              </a-tooltip>
            </a-col>
          </a-row>

          <a-row type="flex" justify="space-between" align="middle">
            <a-col flex="auto">
              <a-card style="width: 100%">
                <a-row type="flex" justify="space-between" align="middle">
                  <a-col>
                    <font-awesome-icon :icon="['fas', 'shield-halved']" class="text-warning" />
                    {{ t('files.label.initial_cost') }}
                  </a-col>
                  <a-col>
                    <b>MKP {{ itinerary.room_amount_logs[0].markup_previous }}%</b>
                  </a-col>
                </a-row>
                <a-row type="flex" justify="space-between" align="middle">
                  <a-col>
                    <span class="text-warning text-700" style="font-size: 24px">
                      $ {{ itinerary.room_amount_logs[0].amount_previous }}
                    </span>
                  </a-col>
                  <a-col class="text-right">
                    <p class="mb-0 text-warning">
                      {{ itinerary.room_amount_logs[0].markup_previous }}%
                      <font-awesome-icon
                        :icon="[
                          'fas',
                          itinerary.room_amount_logs[0].markup_previous >= 0
                            ? 'arrow-trend-up'
                            : 'arrow-trend-down',
                        ]"
                      />
                    </p>
                    <small class="mb-0" style="font-size: 6px">{{
                      t('files.label.service_profitability')
                    }}</small>
                  </a-col>
                </a-row>
              </a-card>
            </a-col>
            <a-col class="mx-3">
              <font-awesome-icon :icon="['fas', 'arrow-right-long']" />
            </a-col>
            <a-col flex="auto">
              <a-card style="width: 100%">
                <a-row type="flex" justify="space-between" align="middle">
                  <a-col>
                    <font-awesome-icon class="text-danger" :icon="['fas', 'circle-exclamation']" />
                    {{ t('files.label.updated_cost') }}
                  </a-col>
                  <a-col>
                    <b>MKP {{ itinerary.room_amount_logs[0].markup }}%</b>
                  </a-col>
                </a-row>
                <a-row type="flex" justify="space-between" align="middle">
                  <a-col>
                    <span class="text-danger text-700" style="font-size: 24px">
                      $ {{ itinerary.room_amount_logs[0].amount }}
                    </span>
                  </a-col>
                  <a-col class="text-right">
                    <p class="mb-0 text-danger">
                      {{ itinerary.room_amount_logs[0].markup }}%
                      <font-awesome-icon
                        :icon="[
                          'fas',
                          itinerary.room_amount_logs[0].markup_previous >= 0
                            ? 'arrow-trend-up'
                            : 'arrow-trend-down',
                        ]"
                      />
                    </p>
                    <small class="mb-0" style="font-size: 6px">{{
                      t('files.label.service_profitability')
                    }}</small>
                  </a-col>
                </a-row>
              </a-card>
            </a-col>
          </a-row>
        </template>
      </template>
    </template>
    <template #footer>
      <div class="text-center">
        <a-button type="primary" default @click="closeModal" size="large"> Cerrar </a-button>
      </div>
    </template>
  </a-modal>

  <a-modal v-model:visible="modalFileProtected" title="File actualizado" :width="620">
    <a-alert type="info" show-icon>
      <template #icon>
        <font-awesome-icon :icon="['fas', 'circle-exclamation']" />
      </template>
      <template #description>
        <div>
          {{ t('files.label.file_protected') }}
        </div>
      </template>
    </a-alert>

    <template
      v-if="
        filesStore.getFile.itinerary_amount_logs &&
        filesStore.getFile.itinerary_amount_logs.length > 0
      "
    >
      <a-row type="flex" justify="space-between" align="middle">
        <a-col flex="auto">
          <a-card style="width: 100%">
            <a-row type="flex" justify="space-between" align="middle">
              <a-col>
                <font-awesome-icon :icon="['fas', 'shield-halved']" class="text-warning" />
                {{ t('files.label.initial_cost') }}
              </a-col>
              <a-col>
                <b>MKP {{ filesStore.getFile.itinerary_amount_logs[0].markup_previous }}%</b>
              </a-col>
            </a-row>
            <a-row type="flex" justify="space-between" align="middle">
              <a-col>
                <span class="text-warning text-700" style="font-size: 24px">
                  $ {{ filesStore.getFile.itinerary_amount_logs[0].amount_previous }}
                </span>
              </a-col>
              <a-col class="text-right">
                <p class="mb-0 text-warning">
                  {{ filesStore.getFile.itinerary_amount_logs[0].markup_previous }}%
                  <font-awesome-icon
                    :icon="[
                      'fas',
                      filesStore.getFile.itinerary_amount_logs[0].markup_previous >= 0
                        ? 'arrow-trend-up'
                        : 'arrow-trend-down',
                    ]"
                  />
                </p>
                <small class="mb-0" style="font-size: 6px">{{
                  t('files.label.profitability')
                }}</small>
              </a-col>
            </a-row>
          </a-card>
        </a-col>
        <a-col class="mx-3">
          <font-awesome-icon :icon="['fas', 'arrow-right-long']" />
        </a-col>
        <a-col flex="auto">
          <a-card style="width: 100%">
            <a-row type="flex" justify="space-between" align="middle">
              <a-col>
                <font-awesome-icon :icon="['fas', 'repeat']" class="text-success" />
                {{ t('files.label.updated_cost') }}
              </a-col>
              <a-col>
                <b>MKP {{ filesStore.getFile.itinerary_amount_logs[0].markup }}%</b>
              </a-col>
            </a-row>
            <a-row type="flex" justify="space-between" align="middle">
              <a-col>
                <span class="text-success text-700" style="font-size: 24px">
                  $ {{ filesStore.getFile.itinerary_amount_logs[0].amount }}
                </span>
              </a-col>
              <a-col class="text-right">
                <p class="mb-0 text-success">
                  {{ filesStore.getFile.itinerary_amount_logs[0].markup }}%
                  <font-awesome-icon
                    :icon="[
                      'fas',
                      filesStore.getFile.itinerary_amount_logs[0].markup_previous >= 0
                        ? 'arrow-trend-up'
                        : 'arrow-trend-down',
                    ]"
                  />
                </p>
                <small class="mb-0" style="font-size: 6px">{{
                  t('files.label.profitability')
                }}</small>
              </a-col>
            </a-row>
          </a-card>
        </a-col>
      </a-row>
    </template>
    <template #footer>
      <div class="text-center">
        <a-button type="primary" default @click="closeModal" size="large">
          {{ t('global.button.close') }}
        </a-button>
      </div>
    </template>
  </a-modal>

  <template
    v-if="
      1 != 1 &&
      filesStore.getStatementChanges != null &&
      !filesStore.isLoading &&
      isEditable() &&
      isValidUserCodeChanges()
    "
  >
    <a-modal v-model:open="openStatement" :width="550" :closable="false" :maskClosable="false">
      <template #footer>
        <a-row align="middle" justify="center">
          <a-col>
            <a-button
              key="button"
              type="default"
              default
              size="large"
              class="text-600"
              :disabled="loading_update_statement"
              @click="closeModal"
              >{{ t('global.button.close') }}</a-button
            >
            <a-button
              key="button"
              type="primary"
              default
              size="large"
              class="text-600"
              :disabled="loading_update_statement"
              @click="handleProcess"
              >{{ t('global.button.continue') }}</a-button
            >
          </a-col>
        </a-row>
      </template>

      <template #title>
        <span class="text-700 text-uppercase">
          {{ t('files.label.modified_service') }}
        </span>
      </template>

      <div id="files-layout">
        <a-alert type="warning">
          <template #message>
            <a-row type="flex" style="gap: 7px">
              <a-col>
                <font-awesome-icon
                  :icon="['fas', 'triangle-exclamation']"
                  class="text-dark-warning"
                  size="lg"
                />
              </a-col>
              <a-col>
                <span class="text-dark-warning">
                  {{ t('files.message.alert_change_statement') }}
                </span>
              </a-col>
            </a-row>
          </template>
        </a-alert>

        <span style="font-size: 14px; line-height: 19px" class="d-block text-center">
          {{ t('files.message.confirm_statement') }}
        </span>

        <div class="bg-light mt-4" style="border-radius: 8px; padding: 20px">
          <span class="text-500 text-uppercase" style="font-size: 12px; line-height: 19px">
            {{ t('files.label.statement') }}
          </span>

          <a-row
            type="flex"
            justify="space-between"
            align="top"
            style="border-bottom: 1px dashed #e9e9e9; padding-top: 4px; padding-bottom: 8px"
          >
            <a-col>
              <a-row type="flex" justify="start" align="middle" style="gap: 7px">
                <a-col>
                  <p class="mb-0">
                    <span
                      class="text-400 text-uppercase text-dark-gray"
                      style="font-size: 10px; line-height: 17px"
                    >
                      {{ t('global.label.before') }}
                    </span>
                  </p>
                  <p class="mb-0">
                    <strong class="text-700 text-gray" style="font-size: 24px; line-height: 31px">
                      $
                      {{
                        formatNumber({
                          number: filesStore.getStatementChanges?.table_changes?.before,
                          digits: 2,
                        })
                      }}
                    </strong>
                  </p>
                </a-col>
                <a-col style="padding-top: 25px">
                  <font-awesome-icon
                    :icon="['fas', 'long-arrow-alt-right']"
                    class="text-dark-gray"
                  />
                </a-col>
                <a-col>
                  <p class="mb-0">
                    <span
                      class="text-400 text-uppercase"
                      style="font-size: 10px; line-height: 17px; color: #1ed790"
                      >{{ t('global.label.after') }}</span
                    >
                  </p>
                  <p class="mb-0">
                    <strong
                      class="text-700"
                      style="font-size: 24px; line-height: 31px; color: #1ed790"
                    >
                      $
                      {{
                        formatNumber({
                          number: filesStore.getStatementChanges?.table_changes?.after,
                          digits: 2,
                        })
                      }}
                    </strong>
                  </p>
                </a-col>
              </a-row>
            </a-col>
            <a-col class="text-right">
              <p class="mb-0 text-700" style="font-size: 12px; line-height: 19px">
                MKP: {{ filesStore.getStatementChanges?.table_changes?.mkp }}%
              </p>
              <p class="mb-0 text-700" style="font-size: 12px; line-height: 19px">
                {{ filesStore.getStatementChanges?.table_changes?.mkp }}%
                <font-awesome-icon :icon="['fas', 'arrow-trend-up']" />
              </p>
              <p class="mb-0 text-600" style="font-size: 9px">
                {{ t('global.label.profitability') }}
              </p>
            </a-col>
          </a-row>

          <p class="mt-3 text-500 text-uppercase" style="font-size: 12px; line-height: 19px">
            {{ t('files.message.price_by_room_type_and_pax') }}
          </p>
          <table style="width: 100%">
            <thead>
              <tr>
                <td style="font-size: 10px; line-height: 17px">
                  <span class="text-uppercase text-dark-gray">
                    {{ t('global.label.before') }}
                  </span>
                </td>
                <td></td>
                <td class="text-center" style="font-size: 10px; line-height: 17px">
                  <span class="text-uppercase text-dark-gray">
                    {{ t('global.label.after') }}
                  </span>
                </td>
                <td class="text-center" style="font-size: 10px; line-height: 17px">
                  <span class="text-uppercase text-dark-gray">
                    {{ t('global.label.unit') }}
                  </span>
                </td>
                <td class="text-right" style="font-size: 10px; line-height: 17px">
                  <span class="text-uppercase text-dark-gray">
                    {{ t('global.label.pax_type') }}
                  </span>
                </td>
              </tr>
            </thead>
            <tbody>
              <tr v-for="(item, i) in filesStore.getStatementChanges?.table_changes?.table">
                <td class="font-600" style="font-size: 12px; color: #979797">
                  $ {{ formatNumber({ number: item.before, digits: 2 }) }}
                </td>
                <td>
                  <font-awesome-icon
                    :icon="['fas', 'long-arrow-alt-right']"
                    class="text-dark-gray"
                  />
                </td>
                <td class="text-600 text-center" style="font-size: 12px; color: #28a745">
                  $ {{ formatNumber({ number: item.after, digits: 2 }) }}
                </td>
                <td class="text-center" style="font-size: 12px">{{ item.room }}</td>
                <td class="text-right" style="font-size: 12px">{{ item.type_pax }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <div
          :class="[flagModify ? 'mt-3' : '']"
          :style="`${flagModify ? 'border:1px solid #E9E9E9;' : ''}border-radius: 8px; padding: 20px;`"
        >
          <div v-bind:class="['d-flex cursor-pointer']" @click="flagModify = !flagModify">
            <template v-if="flagModify">
              <i class="bi bi-check-square-fill text-danger d-flex" style="font-size: 1rem"></i>
            </template>
            <template v-else>
              <i class="bi bi-square d-flex" style="font-size: 1rem; color: #c4c4c4"></i>
            </template>
            <span class="mx-2 text-500" style="color: #909090; font-size: 15px; line-height: 1rem">
              {{ t('files.label.modify_it_manually') }}
            </span>
          </div>

          <div v-if="flagModify" class="my-3">
            <a-form>
              <a-form-item>
                <template #label>
                  <span class="text-600">{{ t('files.label.reason_for_modification') }}</span>
                  <b class="text-danger px-2">*</b>
                </template>
                <a-select
                  :allowClear="true"
                  class="w-100"
                  v-model:value="status_reason_id"
                  :showSearch="true"
                  notFoundContent=""
                  :fieldNames="{ label: 'name', value: 'id' }"
                  :options="filesStore.getStatementModificationReasons"
                >
                </a-select>
              </a-form-item>
              <a-form-item v-if="parseInt(status_reason_id) === 2">
                <a-input
                  v-model:value="other_reason"
                  :placeholder="t('files.label.enter_a_custom_motif')"
                ></a-input>
              </a-form-item>
            </a-form>
          </div>
        </div>
      </div>
    </a-modal>
  </template>
</template>

<script setup>
  import { useSocketsStore } from '@/stores/global';
  import { formatDate, formatNumber, truncateString } from '@/utils/files.js';
  import { getUserName, getUserId, getUserCode } from '@/utils/auth';
  import { notification } from 'ant-design-vue';
  import dayjs from 'dayjs';
  import FilesPopoverInfo from '@/components/files/edit/FilesPopoverInfo.vue';
  import FilesPopoverAvatar from '@/components/files/edit/FilesPopoverAvatar.vue';
  import FilesPopoverVip from '@/components/files/edit/FilesPopoverVip.vue';
  import FilesEditFieldStatic from '@/components/files/edit/FilesEditFieldStatic.vue';
  import FilesTogglerFileName from '@/components/files/edit/FilesTogglerFileName.vue';
  import FilesTogglerLanguage from '@/components/files/edit/FilesTogglerLanguage.vue';
  import FilesTogglerStartDay from '@/components/files/edit/FilesTogglerStartDay.vue';
  import FilesSwitchSerie from '@/components/files/edit/FilesSwitchSerie.vue';
  import FilesSwitchCategories from '@/components/files/edit/FilesSwitchCategories.vue';
  import BaseSelectAccommodations from '@/components/files/reusables/BaseSelectAccommodations.vue';
  import BaseBadge from '@/components/files/reusables/BaseBadge.vue';
  import BasePopover from './BasePopover.vue';
  import { WarningOutlined } from '@ant-design/icons-vue';
  import { useRouter } from 'vue-router';
  import { useI18n } from 'vue-i18n';
  import {
    useExecutivesStore,
    useFilesStore,
    useHaveInvoicesStore,
    useRevisionStagesStore,
    useStatusesStore,
    useItineraryStore,
    useVipsStore,
  } from '@store/files';

  import { onBeforeMount, ref, watch, computed } from 'vue';
  import CreateFileModal from '@/components/files/create/CreateFileModal.vue';
  import CloneFileModal from '@/components/files/clone/CloneFileModal.vue';
  import IconDocPlus from '@/components/icons/IconDocPlus.vue';
  import IconCloneSolid from '@/components/icons/IconCloneSolid.vue';

  const { t } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();
  const vipsStore = useVipsStore();
  const itineraryStore = useItineraryStore();
  const statusesStore = useStatusesStore();
  const haveInvoicesStore = useHaveInvoicesStore();
  const revisionStagesStore = useRevisionStagesStore();
  const executivesStore = useExecutivesStore();

  const socketsStore = useSocketsStore();

  const statusByIso = (iso) => statusesStore.getStatusByIso(iso);
  const haveInvoiceByIso = (iso) => haveInvoicesStore.getHaveInvoiceByIso(iso);
  const getRevisionStageById = (id) => revisionStagesStore.getRevisionStageById(id);

  const emit = defineEmits(['onHandleOpenStatementView', 'onHandleGoToPaxs', 'onRefreshCache']);

  const router = useRouter();
  const modalIsOpen = ref(false);
  const modalCloneIsOpen = ref(false);
  const accommodation = ref({});

  const removeUserView = ({ token }) => {
    socketsStore.send({
      success: true,
      type: 'user_disconnected_in_file',
      token: token,
      file_number: filesStore.getFile.fileNumber,
      user_name: getUserName(),
      user_code: getUserCode(),
      user_id: getUserId(),
    });

    setTimeout(() => {
      socketsStore.send({
        success: true,
        type: 'ping',
      });
    }, 100);
  };

  const openStatementView = () => {
    emit('onHandleOpenStatementView');
  };

  const props = defineProps({
    editable: {
      type: Boolean,
      default: () => true,
    },
  });

  const modalActivate = ref(false);
  const lockedOriginalDate = ref(false);
  const modalProtected = ref(false);
  const modalFileProtected = ref(false);
  const flag_activate = ref(false);

  const disabledDate = (current) => {
    return (
      current &&
      current < dayjs().startOf('day') &&
      dayjs(current).format('YYYY-MM-DD') < dayjs().format('YYYY-MM-DD')
    );
  };

  const goToPaxs = () => {
    emit('onHandleGoToPaxs');
  };

  const goTo = (link, params = []) => {
    router.push({ name: link, params });
  };

  const handleRefreshCache = async () => {
    emit('onRefreshCache');
  };

  const closeModal = () => {
    modalProtected.value = false;
    modalFileProtected.value = false;
    openStatement.value = false;
  };

  const flagModify = ref(false);
  const openStatement = ref(true);
  const status_reason_id = ref(null);
  const other_reason = ref('');

  const loading_update_statement = ref(false);

  const handleProcess = async () => {
    if (flagModify.value) {
      if (!status_reason_id.value) {
        notification.error({
          message: 'Campos incompletos',
          description: 'Ingrese un motivo para continuar',
        });
        return false;
      }

      if (parseInt(status_reason_id.value) === 2 && other_reason.value.trim() === '') {
        notification.error({
          message: 'Campos incompletos',
          description: 'Ingrese un motivo personalizado para continuar',
        });
        return false;
      }
    }

    loading_update_statement.value = true;

    const itineraries = filesStore.getStatementChanges?.update_itineraries ?? [];
    const params = {
      fileId: filesStore.getFile.id,
      reasonId: status_reason_id.value,
      otherReason: other_reason.value,
      itineraries,
    };
    await filesStore.putUpdateStatementItineraries(params);

    if (!flagModify.value) {
      const details = filesStore.getStatementChanges?.update_statement ?? [];
      const deadline = filesStore.getStatementChanges?.deadline ?? '';
      await filesStore.updateStatement(filesStore.getFile.id, deadline, details, false);
    } else {
      openStatementView();

      socketsStore.send({
        success: true,
        type: 'update_statement',
        message: 'Modificación de FILE',
        user_code: getUserCode(),
        user_id: getUserId(),
        file_number: filesStore.getFile.fileNumber,
        description: `El statement del FILE ha sido actualizado.`,
      });
    }

    openStatement.value = false;
    loading_update_statement.value = false;
  };

  const hasBoss = computed(() => !!filesStore.getFile?.bossCode);
  const executiveName = computed(() => filesStore.getFile?.executiveName ?? '');

  const isValidUserCodeChanges = () => {
    const userCode = localStorage.getItem('user_search_statement_changes');
    return userCode === getUserCode();
  };

  const handleConnectTeams = (email) => {
    const url = `https://teams.microsoft.com/l/chat/0/0?users=${email}`;
    window.open(url, '_blank');
  };

  const baseURLPhoto = (photo) => {
    return photo ? `${window.url_front_a2}images/users/${photo}` : false;
  };

  const handleCancel = async () => {
    const date1 = dayjs(filesStore.getFile.dateIn);
    const date2 = dayjs(new Date());

    let flag_continue = true;

    if (date1 <= date2) {
      notification.error({
        message: 'Error al anular',
        description: `La fecha del itinerario es pasada, por lo que no podemos anular el File.`,
      });

      flag_continue = false;
    }

    filesStore.getFileItineraries.map((itinerary) => {
      if (!itinerary.confirmation_status && flag_continue) {
        notification.error({
          message: 'Error al anular',
          description: `El ${itinerary.name} del día ${formatDate(itinerary.date_in)} no se encuentra confirmado. Por lo que no podemos anular el File.`,
        });

        flag_continue = false;
      }
    });

    if (flag_continue) {
      let route = 'files-cancel';
      let params = {
        id: filesStore.getFile.id,
      };

      router.push({ name: route, params: params });
    }
  };

  const checked = ref(1);
  const newDateActivate = ref('');

  const verifyOriginalDate = () => {
    let date1 = dayjs(filesStore.getFile.dateIn);
    let date2 = dayjs(new Date());
    let days = date1.diff(date2, 'day');
    lockedOriginalDate.value = days <= 7;

    if (days >= 7 || date1.format('YYYY') == date2.format('YYYY')) {
      flag_activate.value = true;

      if (days <= 7) {
        checked.value = 2;
      }
    }
  };

  const hideModalActivate = () => {
    modalActivate.value = false;
  };

  const showModalActivate = () => {
    verifyOriginalDate();
    modalActivate.value = true;
  };

  const handleActivate = async () => {
    modalActivate.value = false;
    filesStore.inited();

    if (checked.value == 2 || flag_activate.value) {
      let new_date = '';

      if (checked.value == 2) {
        new_date = dayjs(newDateActivate.value).format('YYYY-MM-DD');
      }

      if (flag_activate.value) {
        new_date = dayjs(filesStore.getFile.dateIn).format('YYYY-MM-DD');
      }

      let params = {
        force: true,
        date_init: new_date,
        activate: true,
      };

      await filesStore.sendQuote(filesStore.getFile.id, params);

      const error = filesStore.getError;

      if (error != '' && error != null) {
        notification.error({
          message: 'Error al activar',
          description: `${error.message}`,
        });

        filesStore.finished();
        return;
      }

      localStorage.setItem('a3_file_id', filesStore.getFile.id);
      window.location.href = `${window.url_app}quotes`;
    } else {
      let route = 'files-activate';
      let params = {
        id: filesStore.getFile.id,
      };

      router.push({ name: route, params: params });
    }
  };

  const showModalCreateFile = () => {
    modalIsOpen.value = true;
  };

  const showModalCloneFile = () => {
    modalCloneIsOpen.value = true;
  };

  const flag_quote = ref(false);
  const flag_protected = ref(false);

  watch(
    () => filesStore.getFileItinerariesProtected,
    async (newItineraries) => {
      if (newItineraries.length > 0) {
        modalProtected.value = true;

        for (const itinerary of newItineraries) {
          await filesStore.updateFlagRateProtected(filesStore.getFile.id, itinerary.id);
        }
      } else {
        modalFileProtected.value =
          filesStore.getFile.viewRateProtected && !filesStore.getFile.protectedRate;

        if (modalFileProtected.value) {
          await filesStore.updateFlagFileRateProtected(filesStore.getFile.id);
        }
      }
    },
    { immediate: true, deep: true } // Opcional: para ejecutar al montar y observar cambios profundos
  );

  onBeforeMount(async () => {
    const file = filesStore.getFile;
    const itineraries = filesStore.getFileItineraries;

    verifyOriginalDate();

    if (vipsStore.getVips.length === 0) {
      await vipsStore.fetchAll();
    }

    flag_protected.value = itineraries.some((itinerary) => itinerary.protected_rate);

    flag_quote.value = !itineraries.some((itinerary) => itinerary.entity === 'hotel');

    accommodation.value = {
      SGL: file.suggested_accommodation_sgl,
      DBL: file.suggested_accommodation_dbl,
      TPL: file.suggested_accommodation_tpl,
    };

    setTimeout(async () => {
      const allExecutives = executivesStore.getAllExecutives;
      const allBoss = executivesStore.getBoss;

      if (allExecutives[file.executiveCode]?.name) {
        const bossCode = allBoss.executives_boss[file.executiveCode] ?? '';

        file.bossCode = bossCode;
        file.bossName = allExecutives[file.bossCode]?.name || '';
        file.executiveName = allExecutives[file.executiveCode]?.name || '';

        return false;
      }

      if (!file.executiveName) {
        const { executiveCode } = file;
        let codeList = executiveCode?.split(',') || [];

        await executivesStore.fetchAllBoss(executiveCode);

        const bossCode = executivesStore.getBoss.executives_boss[executiveCode] ?? '';

        if (bossCode) {
          file.bossCode = bossCode;
          codeList.push(bossCode);
        }

        await executivesStore.findAll(codeList.join(','));

        const allExecutives = executivesStore.getAllExecutives;
        file.bossName = allExecutives[file.bossCode]?.name || '';
        file.executiveName = allExecutives[file.executiveCode]?.name || '';
      }
    }, 350);

    if (props.editable) {
      await filesStore.searchStatementChanges(filesStore.getFile.id);
    }
  });

  const isEditable = () => {
    if (!filesStore.getFile.statusReason) {
      return true;
    }
    let reopen =
      !filesStore.getFile.statusReason.toLowerCase().includes('reaperturado') ||
      (filesStore.getFile.statusReason.toLowerCase().includes('reaperturado') &&
        filesStore.getFile.statusReasonId === 4);

    return props.editable && filesStore.getFile.status === 'ok' && reopen;
  };

  const changeCategoriesFile = async (params) => {
    await filesStore.saveCategoriesFile(filesStore.getFile.id, params);
    localStorage.setItem('change_categories', 1);

    socketsStore.send({
      success: true,
      type: 'update_statement',
      action: 'categories',
      file_number: parseInt(filesStore.getFile.fileNumber),
      file_id: parseInt(filesStore.getFile.id),
      message: 'Modificación del FILE',
      user_code: getUserCode(),
      user_id: getUserId(),
      date: dayjs().format('YYYY-MM-DD'),
      time: dayjs().format('HH:mm:ss'),
      description: 'La categorización del FILE fue modificada.',
    });
  };
</script>
<style scoped lang="scss">
  .file-dropdown-menu {
    background-color: white;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
    padding: 4px 0;

    :deep(.ant-dropdown-menu-item) {
      padding: 10px; /* Ajusta el padding de los items */
      .menu-item-content {
        display: flex;
        align-items: center;
        gap: 10px;

        svg {
          color: #eb5757;
          margin-left: 5px;
        }

        /* Estilos para el texto del item */
        span {
          font-family: 'Montserrat', sans-serif;
          font-weight: 500;
          font-size: 16px;
          color: #eb5757;
          margin-right: 15px;
        }
      }

      /* Ajustes cuando un item está activo (hover o selección) */
      &:hover {
        background-color: #ffffff; /* Color de fondo del ítem cuando está activo */
      }

      /* Ajustes para ítems seleccionados */
      &.ant-dropdown-menu-item-selected {
        background-color: #ffffff; /* Color de fondo del ítem seleccionado */
      }
    }
  }

  .block-style > div {
    opacity: 0.5;
    pointer-events: none;
    isolation: isolate;
  }

  /* Excluye los elementos con la clase 'except-block-style' */
  .block-style .except-block-style {
    opacity: 1 !important;
    pointer-events: auto;
    isolation: isolate;
  }

  .custom-box {
    border: 1px solid #f5a89a; /* Borde de color tenue (rosa claro) */
    border-radius: 8px; /* Bordes redondeados */
    padding: 12px 16px; /* Espacio interno */
    background-color: #fafafa; /* Fondo blanco */
    font-size: 14px; /* Tamaño de texto */
    color: #595959; /* Color del texto principal */
  }

  .custom-box strong {
    font-weight: 600; /* Texto en negrita para resaltar */
    color: #262626; /* Color más oscuro para el texto resaltado */
  }
</style>
