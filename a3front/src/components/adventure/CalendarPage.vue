<template>
  <a-row type="flex" justify="space-between" align="middle" class="bg-light header-bar">
    <a-col>
      <h1 class="page-title">Calendario</h1>
    </a-col>
  </a-row>

  <div class="content-page">
    <a-calendar
      v-model:value="value"
      :events="calendarDepartures"
      @panelChange="onPanelChange"
      :dateCellRender="dateCellRender"
    />
  </div>

  <a-modal v-model:open="showModal" title="SERVICIOS" :footer="null" :centered="true" width="700px">
    <div v-if="selectedEvent" class="modal-content">
      <p class="text-center mb-0">
        <b>{{ selectedEvent.title ?? '' }}</b>
      </p>
      <p class="text-center">
        <small>{{ selectedEvent.file }}</small>
      </p>

      <backend-table-component
        :items="services"
        :columns="columns"
        :options="tableOptions"
        size="small"
      />
    </div>
    <div v-else>No se ha seleccionado ningún evento.</div>
  </a-modal>
</template>

<script lang="ts" setup>
  import { ref, onBeforeMount, h } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  import { useDepartures, useTemplates } from '@/composables/adventure';
  import { Badge } from 'ant-design-vue';
  import BackendTableComponent from '@/components/global/BackendTableComponent.vue';

  const { fetchCalendarDepartures, calendarDepartures } = useDepartures();
  const { fetchTemplateServices, services } = useTemplates();

  const value = ref<Dayjs>(dayjs());
  const showModal = ref(false);
  const selectedEvent = ref(null);

  const columns = [
    {
      title: 'Fecha',
      dataIndex: 'date',
      key: 'date',
      align: 'center',
    },
    {
      title: 'Proveedor',
      dataIndex: 'provider',
      key: 'provider',
      align: 'center',
    },
    {
      title: 'Servicio',
      dataIndex: 'name',
      key: 'name',
      align: 'center',
    },
    {
      title: 'Observaciones',
      dataIndex: 'observations',
      key: 'observations',
      align: 'center',
    },
  ];

  const tableOptions = {
    showActions: false,
    pagination: false,
    rowKey: '_id',
    bordered: true,
  };

  const fetchMonthEvents = async (date: Dayjs) => {
    const year = date.startOf('month').format('YYYY');
    const month = date.endOf('month').format('M');
    await fetchCalendarDepartures(year, month);
  };

  const onPanelChange = (date: Dayjs) => {
    fetchMonthEvents(date);
  };

  onBeforeMount(async () => {
    const initialDate = dayjs();
    await fetchMonthEvents(initialDate);
  });

  const handleEventClick = async (event: any) => {
    await fetchTemplateServices('departure', event.id);
    selectedEvent.value = event;
    showModal.value = true;
  };

  const dateCellRender = (date: any) => {
    const dayjsDate = dayjs(date.current).format('YYYY-MM-DD');

    const listData = calendarDepartures?.value?.filter((event: any) => event.start === dayjsDate);

    const eventsToRender = listData || [];

    return h(
      'ul',
      { class: 'events-list' },
      eventsToRender.map((event: any) =>
        h(
          'li',
          {
            key: event.id,
            onClick: (e: Event) => {
              e.stopPropagation();
              handleEventClick(event);
            },
            class: 'calendar-event-item event-custom-style',
          },
          h(Badge, { status: 'success' }, [
            h('div', { class: 'event-title-text' }, event.title),
            h('small', { class: 'event-small-number' }, `${event.file}`),
          ])
        )
      )
    );
  };
</script>

<style scoped>
  @import '../../scss/views/adventure.scss';

  :deep(.ant-picker-calendar-date-content) {
    pointer-events: auto;
  }

  :deep(.events-list) {
    list-style: none;
    margin: 0;
    padding: 0;
    pointer-events: auto !important;
  }

  :deep(.calendar-event-item) {
    cursor: pointer;
    margin-bottom: 4px;
    pointer-events: auto;
  }

  :deep(.ant-picker-calendar-mode-switch) {
    display: none;
  }

  :deep(.ant-picker-today-btn) {
    display: none;
  }

  :deep(.calendar-event-item.event-custom-style) {
    background-color: #e6f7ff;
    border-radius: 4px;
    padding: 2px 5px;
    border-left: 3px solid #1890ff;
    margin-bottom: 3px !important;
    overflow: hidden;
    font-size: 10px;
  }

  :deep(.calendar-event-item .ant-badge-status-text) {
    display: none !important;
  }

  :deep(.calendar-event-item .ant-badge-status) {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    margin-right: 0 !important;
    width: 100%;
  }

  :deep(.event-title-text) {
    overflow: hidden;
    font-weight: 600;
    line-height: 1.2;
    color: #001529;
    margin: 3px 0;
    text-wrap: nowrap;
    text-overflow: ellipsis;
    white-space: nowrap;
    width: 100%;
  }

  :deep(.event-small-number) {
    font-size: 11px;
    font-weight: normal;
    color: #666;
    line-height: 1;
    margin-bottom: 3px;
  }

  :deep(.ant-picker-calendar-date-content) {
    pointer-events: auto;
  }

  :deep(.events-list) {
    pointer-events: auto !important;
  }
</style>
