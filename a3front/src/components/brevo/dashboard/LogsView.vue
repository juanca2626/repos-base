<template>
  <template v-if="brevoStore.isLoading">
    <div class="files-edit">
      <loading-skeleton />
    </div>
  </template>
  <div class="files-edit" v-else>
    <template v-if="brevoStore.getNotifications.length == 0">
      <template v-if="params.type === 'service'">
        <template v-if="filesStore.isLoadingAsync">
          <loading-skeleton />
        </template>
        <template v-else>
          <template v-for="_notification in filesStore.getReservations">
            <a-form class="bg-light p-4 mb-3 resend-form">
              <div class="mt-3">
                <a-form-item :label="t('global.label.recipients')">
                  <a-select
                    mode="tags"
                    v-model:value="emails"
                    placeholder="ejemplo@correo.com"
                    :not-found-content="null"
                    :options="
                      _notification.supplier_emails.map((email) => ({
                        label: email,
                        value: email,
                      }))
                    "
                  >
                  </a-select>
                </a-form-item>
                <a-row align="middle" type="flex" justify="end" class="mx-2">
                  <a-col>
                    <a-button
                      default
                      type="primary"
                      size="large"
                      :disabled="filesStore.isLoadingAsync || communicationsStore.isLoading"
                      class="d-flex ant-row-middle text-600"
                      @click="sendNotificationService()"
                      :loading="filesStore.isLoadingAsync || communicationsStore.isLoading"
                    >
                      <span
                        :class="[
                          filesStore.isLoadingAsync || communicationsStore.isLoading ? 'ms-2' : '',
                          'text-capitalize',
                        ]"
                      >
                        {{ t('global.button.send_communication') }}
                      </span>
                    </a-button>
                  </a-col>
                </a-row>

                <div class="mb-3 mx-2" v-if="showNotesTo">
                  <template v-if="lockedNotesTo">
                    <a-card style="width: 100%" class="mt-3" :headStyle="{ background: black }">
                      <template #title> {{ t('global.label.note_to_provider') }} </template>
                      <template #extra>
                        <a href="javascript:;" @click="lockedNotesTo = false" class="text-danger">
                          <i class="bi bi-pencil"></i>
                        </a>
                      </template>
                      <p class="mb-2">
                        <b>{{ notesTo }}</b>
                      </p>
                      <template v-for="(file, f) in filesTo" :key="f">
                        <a-row align="middle" class="mb-2">
                          <i class="bi bi-paperclip"></i>
                          <a :href="file" target="_blank" class="text-dark mx-1">
                            {{ showName(file) }}
                          </a>
                        </a-row>
                      </template>
                    </a-card>
                  </template>

                  <template v-if="!lockedNotesTo">
                    <p class="text-danger my-2">{{ t('global.label.note_to_provider') }}:</p>
                    <a-row align="top" justify="space-between">
                      <a-col flex="auto">
                        <a-textarea
                          v-model:value="notesTo"
                          :placeholder="t('global.label.placeholder_note_to_provider')"
                          :auto-size="{ minRows: 2 }"
                        />
                      </a-col>
                      <a-col class="mx-2">
                        <file-upload
                          v-bind:folder="'communications'"
                          @onResponseFiles="responseFilesTo"
                        />
                      </a-col>
                      <a-col>
                        <a-button
                          danger
                          type="default"
                          size="large"
                          :disabled="!(notesTo != '' || filesTo.length > 0)"
                          class="d-flex ant-row-middle"
                          @click="lockedNotesTo = true"
                          :loading="
                            communicationsStore.isLoading || communicationsStore.isLoadingAsync
                          "
                        >
                          <i
                            v-bind:class="[
                              'bi bi-floppy',
                              communicationsStore.isLoading || communicationsStore.isLoadingAsync
                                ? 'ms-2'
                                : '',
                            ]"
                          ></i>
                        </a-button>
                      </a-col>
                    </a-row>
                  </template>
                </div>
              </div>
            </a-form>
            <iframe
              style="border: 0"
              :srcdoc="_notification.html"
              width="100%"
              height="1500"
            ></iframe>
          </template>
        </template>
      </template>
      <template v-else>
        <a-alert type="info" class="mb-0">
          <template #description>
            <a-row type="flex" justify="start" align="top" style="gap: 10px">
              <a-col>
                <p class="text-700 mb-1">
                  {{ t('files.message.empty_title_notifications') }}
                </p>
                {{ t('files.message.empty_content_notifications') }}
              </a-col>
            </a-row>
          </template>
        </a-alert>
      </template>
    </template>
    <template v-else>
      <a-collapse accordion v-model:activeKey="activeKey">
        <a-collapse-panel v-for="(_notification, n) in brevoStore.getNotifications" :key="n">
          <template #header>
            <a-row type="flex" justify="space-between">
              <a-col>
                <strong>{{ _notification.subject }}</strong>
              </a-col>
              <a-col>
                <span class="text-capitalize"> {{ t('global.column.date') }} </span>:
                <b>{{ formatDateTime(_notification.date) }}</b>
              </a-col>
            </a-row>
          </template>
          <a-form class="bg-light p-4 my-3 resend-form">
            <a-form-item :label="t('global.label.resend_email_query')" class="mb-0">
              <a-switch v-model:checked="send" size="small" />
            </a-form-item>
            <div v-if="send" class="mt-3">
              <a-form-item :label="t('global.label.add_recipients')">
                <a-select
                  mode="tags"
                  v-model:value="emails"
                  placeholder="ejemplo@correo.com"
                  :not-found-content="null"
                  :options="
                    _notification.emails.map((email) => ({
                      label: email.email,
                      value: email.email,
                    }))
                  "
                >
                </a-select>
              </a-form-item>
              <a-row align="middle" type="flex" justify="end" class="mx-2">
                <a-col>
                  <a-button
                    danger
                    type="primary"
                    size="large"
                    :disabled="filesStore.isLoadingAsync || communicationsStore.isLoading"
                    class="d-flex ant-row-middle text-600"
                    @click="resendNotification(_notification)"
                    :loading="filesStore.isLoadingAsync || communicationsStore.isLoading"
                  >
                    <span
                      :class="
                        filesStore.isLoadingAsync || communicationsStore.isLoading ? 'ms-2' : ''
                      "
                    >
                      {{ t('global.button.resend') }}
                    </span>
                  </a-button>
                </a-col>
              </a-row>

              <div class="mb-3 mx-2" v-if="showNotesTo">
                <template v-if="lockedNotesTo">
                  <a-card style="width: 100%" class="mt-3" :headStyle="{ background: black }">
                    <template #title> {{ t('global.label.note_to_provider') }}: </template>
                    <template #extra>
                      <a href="javascript:;" @click="lockedNotesTo = false" class="text-danger">
                        <i class="bi bi-pencil"></i>
                      </a>
                    </template>
                    <p class="mb-2">
                      <b>{{ notesTo }}</b>
                    </p>
                    <template v-for="(file, f) in filesTo" :key="f">
                      <a-row align="middle" class="mb-2">
                        <i class="bi bi-paperclip"></i>
                        <a :href="file" target="_blank" class="text-dark mx-1">
                          {{ showName(file) }}
                        </a>
                      </a-row>
                    </template>
                  </a-card>
                </template>

                <template v-if="!lockedNotesTo">
                  <p class="text-danger my-2">{{ t('global.label.note_to_provider') }}:</p>
                  <a-row align="top" justify="space-between">
                    <a-col flex="auto">
                      <a-textarea
                        v-model:value="notesTo"
                        :placeholder="t('global.label.placeholder_note_to_provider')"
                        :auto-size="{ minRows: 2 }"
                      />
                    </a-col>
                    <a-col class="mx-2">
                      <file-upload
                        v-bind:folder="'communications'"
                        @onResponseFiles="responseFilesTo"
                      />
                    </a-col>
                    <a-col>
                      <a-button
                        danger
                        type="default"
                        size="large"
                        :disabled="!(notesTo != '' || filesTo.length > 0)"
                        class="d-flex ant-row-middle"
                        @click="lockedNotesTo = true"
                        :loading="
                          communicationsStore.isLoading || communicationsStore.isLoadingAsync
                        "
                      >
                        <i
                          v-bind:class="[
                            'bi bi-floppy',
                            communicationsStore.isLoading || communicationsStore.isLoadingAsync
                              ? 'mx-2'
                              : '',
                          ]"
                        ></i>
                      </a-button>
                    </a-col>
                  </a-row>
                </template>
              </div>
            </div>
          </a-form>
          <a-row
            type="flex"
            class="bg-light"
            justify="space-between"
            style="gap: 10px; padding: 10px"
          >
            <a-col flex="auto">
              <IframeHTML :html="_notification.html" />
            </a-col>
            <a-col :span="6" flex="auto">
              <template v-if="_notification.emails.length > 0">
                <a-collapse>
                  <template v-for="(_email, e) in _notification.emails" :key="`email-${n}-${e}`">
                    <a-collapse-panel>
                      <template #header>
                        <small
                          ><b>{{ _email.email }}</b></small
                        >
                      </template>
                      <a-timeline>
                        <template v-for="(_log, l) in _email.logs" :key="`log-${n}-${e}-${l}`">
                          <a-timeline-item color="red">
                            <small class="text-uppercase">
                              {{ t(`global.label.${_log.event_type}`) }} {{ t('global.label.at') }}
                              {{ dayjs(_log.date * 1000).format('DD/MM/YYYY HH:mm:ss') }}
                            </small>
                          </a-timeline-item>
                        </template>
                      </a-timeline>
                    </a-collapse-panel>
                  </template>
                </a-collapse>
              </template>
            </a-col>
          </a-row>
        </a-collapse-panel>
      </a-collapse>
    </template>
  </div>
</template>

<script setup>
  import { onBeforeMount, ref } from 'vue';
  import dayjs from 'dayjs';
  import { useRoute } from 'vue-router';
  import { useBrevoStore } from '@store/brevo';
  import { useFilesStore } from '@store/files';
  import { notification } from 'ant-design-vue';
  import { useCommunicationsStore } from '@store/global';
  import FileUpload from '@/components/global/FileUploadComponent.vue';
  import { getUserCode } from '@/utils/auth';
  import IframeHTML from '@/components/files/reusables/IframeHTML.vue';
  import { formatDateTime } from '@/utils/files.js';
  import LoadingSkeleton from '@/components/global/LoadingSkeleton.vue';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const activeKey = ref(['0']);

  const send = ref(false);

  const showNotesTo = ref(false);
  const lockedNotesTo = ref(false);
  const notesTo = ref('');
  const filesTo = ref([]);
  const emails = ref([]);

  const route = useRoute();
  const brevoStore = useBrevoStore();
  const communicationsStore = useCommunicationsStore();
  const filesStore = useFilesStore();

  const props = defineProps({
    type: {
      type: String,
      default: () => 'web',
    },
    params: {
      type: Object,
      default: () => ({}),
    },
  });

  onBeforeMount(async () => {
    if (props.params.type === 'hotel') {
      await handleSearchNotifications();
    }

    if (props.params.type === 'service') {
      const { record } = props.params;

      console.log(record.send_notification);

      if (parseInt(record.send_notification) === 1) {
        await handleSearchNotifications();
      } else {
        brevoStore.clearNotifications();
        await filesStore.processNotificationService({
          itinerary_id: record.file_itinerary_id,
          composition_code: record.code,
          composition_id: record.id,
        });

        emails.value = filesStore.getReservations.flatMap(
          (notification) => notification.supplier_emails || []
        );

        console.log(filesStore.getReservations, emails.value);
      }
    }
  });

  const handleSearchNotifications = async () => {
    emails.value = [];
    send.value = false;
    // Notifications searching..
    if (props.type == 'web') {
      const { object_id, object_code = '', type = '', timestamp } = route.params;
      await brevoStore.searchNotifications({ object_id, object_code, type, timestamp });
    }

    if (props.type == 'modal') {
      const { object_id, object_code, type, timestamp } = props.params;
      await brevoStore.searchNotifications({ object_id, object_code, type, timestamp });
    }
  };

  const sendNotificationService = async () => {
    if (emails.value.length === 0) {
      notification.error({
        message: `Error`,
        description: t('global.message.invalid_emails'),
        duration: 5,
      });

      return false;
    }

    brevoStore.inited();
    const { record } = props.params;

    for (const reservation of filesStore.getReservations) {
      const params = {
        destination: 'A3 Files - Informix',
        user: getUserCode(),
        flag_send: true,
        template: 'notification-service',
        data: {
          mail_type: 'confirmation',
          mail_config_to: 'service',
          hotel_code: record.code,
          hotels: [],
          services: [record.code],
        },
        module: 'ms_files',
        submodule: 'reservation',
        object_id: filesStore.getFile.fileNumber,
        to: emails.value,
        cc:
          typeof reservation.executive_email === 'string'
            ? [reservation.executive_email]
            : reservation.executive_email,
        bcc: [],
        body: reservation.html,
        subject: `Notificación - ${reservation.supplier_name}`,
        attachments: reservation.attachments,
      };
      await filesStore.handleResendNotification(params);
    }

    // Actualizando el estado de la notificación..
    await filesStore.updateNotificationService({
      composition_id: record.id,
    });

    if (filesStore.getError === '') {
      record.send_notification = 1;

      notification.success({
        message: t('global.message.title_communication_notification'),
        description: t('global.message.content_communication_notification'),
        duration: 5,
      });
    } else {
      notification.error({
        message: `Ocurrió un Error`,
        description: filesStore.getError,
        duration: 5,
      });
    }

    setTimeout(() => {
      handleSearchNotifications();
    }, 1500);
  };

  const resendNotification = async (notification) => {
    if (emails.value.length === 0) {
      notification.error({
        message: `Error`,
        description: t('global.message.invalid_emails'),
        duration: 5,
      });

      return false;
    }

    brevoStore.inited();
    const params = {
      destination: 'A3 Files',
      user: getUserCode(),
      flag_send: true,
      template: notification.template,
      data: JSON.parse(notification.data),
      module: notification.module,
      submodule: notification.submodule,
      object_id: notification.object_id,
      to: emails.value,
      cc: [],
      bcc: [],
      body: notification.html,
      subject: notification.subject,
      attachments: filesTo.value,
      notas: notesTo.value,
    };
    await filesStore.handleResendNotification(params);

    setTimeout(() => {
      handleSearchNotifications();
    }, 1500);
  };
</script>
