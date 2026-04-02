<template>
  <a-dropdown
    trigger="hover"
    placement="bottomRight"
    @click="socketsStore.clearTotal()"
    :disabled="socketsStore.getNotifications?.length === 0"
  >
    <a-badge :count="socketsStore.getTotal" offset="[10, 0]" class="cursor-pointer text-danger">
      <font-awesome-icon
        :icon="['far', 'bell']"
        size="xl"
        :shake="socketsStore.getTotal > 0 ? true : false"
      />
    </a-badge>

    <template #overlay>
      <a-list
        :data-source="socketsStore.getNotifications || []"
        bordered
        size="small"
        item-layout="horizontal"
        style="width: 450px; max-height: 400px; overflow-y: auto"
        class="pb-0"
      >
        <template #renderItem="{ item, index }">
          <a-alert
            :key="index"
            :class="['mb-1', !item.flag_show ? 'bg-light border-0 px-3 py-3' : '']"
            :type="
              !item.flag_show
                ? 'warning'
                : item.show_icon === undefined || item.show_icon
                  ? item.success
                    ? `success`
                    : `error`
                  : `warning`
            "
          >
            <template #message>
              <a-row
                type="flex"
                justify="start"
                align="middle"
                style="gap: 15px; flex-flow: nowrap"
              >
                <a-col v-if="item.show_icon === undefined || item.show_icon">
                  <template v-if="item.success">
                    <template v-if="item.flag_show">
                      <font-awesome-icon
                        :icon="['fas', 'thumbs-up']"
                        class="text-success"
                        size="xl"
                        bounce
                      />
                    </template>
                    <template v-else>
                      <font-awesome-icon
                        :icon="['fas', 'check-double']"
                        class="text-success"
                        size="xl"
                      />
                    </template>
                  </template>
                  <template v-else>
                    <font-awesome-icon
                      :icon="['fas', 'thumbs-down']"
                      class="text-danger"
                      size="xl"
                    />
                  </template>
                </a-col>
                <a-col flex="auto">
                  <small
                    ><b class="text-uppercase"
                      >{{ t(item.message) }}
                      <a-tag
                        color="red"
                        class="ms-1"
                        v-if="
                          item?.itinerary_id &&
                          filesStore.getFileItineraries.find(
                            (itinerary) => itinerary.id === item?.itinerary_id
                          )?.object_code
                        "
                      >
                        {{
                          filesStore.getFileItineraries.find(
                            (itinerary) => itinerary.id === item?.itinerary_id
                          )?.object_code
                        }}
                      </a-tag>
                      <a-tag color="orange" class="ms-1" v-if="item?.quote_service_id">
                        <a-tooltip>
                          <template #title>Service ID</template>
                          #{{ item.quote_service_id }}
                        </a-tooltip>
                      </a-tag></b
                    ></small
                  >
                  <p class="mb-0 me-1" style="font-size: 13px">
                    <i class="d-block">{{ t(item.description) }}</i>
                  </p>
                  <a-row type="flex" justify="start" align="middle" style="gap: 5px">
                    <a-col>
                      <small v-if="item.user_code">
                        <font-awesome-icon :icon="['far', 'circle-user']" class="me-1" />
                        <b>{{ item.user_code }}</b>
                      </small>
                      <small v-else>
                        <font-awesome-icon :icon="['fas', 'robot']" class="me-1" />
                        <b>Aurora BOT</b>
                      </small>
                    </a-col>
                    <a-col>
                      <small v-if="item?.date && item?.time">
                        <font-awesome-icon :icon="['far', 'clock']" class="me-1" />
                        <b>{{ formatDate(item.date) }} {{ item.time }}</b>
                      </small>
                    </a-col>
                  </a-row>
                </a-col>
                <a-col v-if="!item.success && !item.remove">
                  <a-button
                    size="small"
                    :disabled="brevoStore.isLoading"
                    @click="reportError(item)"
                    type="primary"
                  >
                    <a-tooltip>
                      <template #title><small>REPORTAR</small></template>
                      <font-awesome-icon :icon="['fas', 'bullhorn']" shake />
                    </a-tooltip>
                  </a-button>
                </a-col>
              </a-row>
            </template>
          </a-alert>
        </template>
      </a-list>
    </template>
  </a-dropdown>
</template>

<script setup>
  import { notification } from 'ant-design-vue';
  import { useSocketsStore } from '@/stores/global';
  import { useBrevoStore } from '@store/brevo';
  import { formatDate } from '@/utils/files.js';
  import { getUserCode } from '@/utils/auth';
  import { useFilesStore } from '@/stores/files';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const socketsStore = useSocketsStore();
  const brevoStore = useBrevoStore();
  const filesStore = useFilesStore();

  const STAGE = import.meta.env.VITE_APP_ENV;

  const reportError = async (item) => {
    const params = {
      destination: 'error',
      user: getUserCode(),
      flag_send: true,
      template: 'notification-error',
      data: [],
      to: ['lsv@limatours.com.pe'],
      subject: `Error en A3 FILES (${STAGE})`,
      body: `<p>Error en el siguiente LOG (AMAZON): <b>${item.stream_log}</b></p><p>${item.description}</p>`,
    };
    await brevoStore.putNotification(params);
    item.remove = true;

    notification.success({
      message: 'Observación Reportada',
      description:
        'El equipo de Sistemas lo estará revisando y se comunicará con usted para confirmar la solución del percance.',
    });
  };
</script>
