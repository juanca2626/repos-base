<template>
  <a-drawer
    v-model:open="childrenDrawer_1"
    title="Crear bloqueo"
    :width="525"
    :maskClosable="false"
    :keyboard="false"
    @close="handlerShowDrawer"
  >
    <a-flex justify="center">
      <a-typography-title :level="5" :style="{ color: '#1284ED' }">
        <a-badge
          count="1"
          :number-style="{
            backgroundColor: '#1284ED',
          }"
        />
        Selecciona motivo y guías
      </a-typography-title>
    </a-flex>

    <a-flex gap="middle" vertical class="my-5">
      <a-select
        v-model:value="selectedBlockingReason"
        label-in-value
        placeholder="Seleccione un motivo de bloqueo"
        :options="blockingReasonsOptions"
        @focus="focus"
      />

      <a-select
        class="custom-select"
        v-model:value="state.value"
        :show-arrow="true"
        show-search
        placeholder="Buscar guías por nombre o código"
        style="width: 100%"
        :filter-option="false"
        :not-found-content="state.fetching ? undefined : null"
        :options="state.data"
        menu-item-selected-icon="search-outlined"
        status="loading"
        @search="fetchProvider"
        @change="handleChange2"
      >
        <template v-if="state.fetching" #notFoundContent>
          <a-spin size="small" />
        </template>
        <template #suffixIcon>
          <SearchOutlined />
        </template>
      </a-select>
    </a-flex>

    <a-divider style="height: 1px; background-color: #f4f4f5ff" class="my-1 mb-5" />

    <div v-if="selectedProviders.length > 0">
      <a-typography-title :level="5" strong class="mb-3"> Listado de guías: </a-typography-title>

      <a-flex
        v-for="{ provider } in selectedProviders"
        :key="provider._id"
        justify="space-between"
        align="center"
        class="hover:bg-zinc-100"
      >
        <a-typography-paragraph :style="{ margin: 0 }">
          <a-typography-text strong>{{ provider.code }}</a-typography-text>
          {{ provider.fullname }}
        </a-typography-paragraph>
        <a-button
          type="none"
          shape="circle"
          :icon="h(DeleteOutlined)"
          @click="handleDeleteProvider(provider)"
        />
      </a-flex>
    </div>

    <template #footer>
      <a-row>
        <a-col :span="24">
          <a-button
            type="primary"
            block
            @click="showChildrenDrawer_to2"
            :disabled="selectedProviders.length === 0 && selectedBlockingReason !== null"
          >
            Siguiente <ArrowRightOutlined />
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>

  <!-- DRAWER 2 -->
  <a-drawer
    v-model:open="childrenDrawer_2"
    title="Crear bloqueo"
    :width="525"
    :maskClosable="false"
    :keyboard="false"
    @close="handlerShowDrawer"
  >
    <a-flex justify="center">
      <a-typography-title :level="5" :style="{ color: '#1284ED' }">
        <a-badge
          count="2"
          :number-style="{
            backgroundColor: '#1284ED',
          }"
        />
        Selecciona fechas y horarios
      </a-typography-title>
    </a-flex>

    <a-flex gap="middle" vertical class="mt-5">
      <a-typography-paragraph class="my-0">
        <a-typography-text strong>Motivo:</a-typography-text>
        {{ selectedBlockingReason.label }}
      </a-typography-paragraph>
      <a-typography-paragraph class="my-0" strong>
        Guías seleccionados:<br />
        <a-space direction="vertical">
          <a-tag color="purple" v-for="{ provider } in selectedProviders" :key="provider._id">
            {{ provider.code }} - {{ provider.fullname }}
          </a-tag>
        </a-space>
      </a-typography-paragraph>
    </a-flex>

    <a-flex gap="middle" vertical class="mt-4">
      <a-typography-paragraph class="my-0">
        <a-flex align="middle">
          <a-typography-text strong style="margin-right: 8px">
            Agregar fechas y horarios:
          </a-typography-text>
          <a-checkbox v-model:checked="completeDay" style="margin-left: auto">
            Días completos
          </a-checkbox>
        </a-flex>
      </a-typography-paragraph>

      <a-range-picker
        v-model:value="selectedTimeRangeModel"
        :show-time="completeDay ? false : { format: 'HH:mm' }"
        :format="completeDay ? 'DD/MM/YYYY' : 'DD/MM/YYYY HH:mm'"
        :placeholder="['Fecha Inicio', 'Fecha Fin']"
        @ok="onRangeOk"
      />

      <a-button danger block @click="addTimeRange"> <PlusOutlined /> Agregar </a-button>

      <a-alert
        v-if="isDateRangesOverlap"
        message="No es posible agregar un rango de tiempo que se superponga con otro."
        type="warning"
        show-icon
        closable
        :after-close="handleClose"
      />
      <!-- 
      <a-alert message="Warning" type="warning" show-icon />
      <a-alert
        message="Warning"
        description="This is a warning notice about copywriting. This is a warning notice about copywriting."
        type="warning"
        show-icon
      /> 
      -->
    </a-flex>

    <!-- <a-divider style="height: 1px; background-color: #f4f4f5ff" class="my-5" /> -->

    <a-typography-title :level="5" strong class="mt-4 mb-3">
      Listado de horarios creados:
    </a-typography-title>

    <a-flex
      justify="space-between"
      align="center"
      class="hover:bg-zinc-100"
      v-for="(range, index) in formattedTimeRanges"
      :key="index"
    >
      <a-typography-paragraph :style="{ margin: 0 }">
        <CalendarOutlined /> {{ range.date }}
      </a-typography-paragraph>
      <a-typography-paragraph :style="{ margin: 0 }">
        <FieldTimeOutlined /> {{ range.time }}
        <a-button
          type="none"
          shape="circle"
          :icon="h(DeleteOutlined)"
          @click="removeTimeRange(index)"
        />
      </a-typography-paragraph>
    </a-flex>

    <template #footer>
      <a-row :gutter="[8, 8]">
        <a-col :span="12">
          <a-button block danger @click="showChildrenDrawer_to1">
            <ArrowLeftOutlined /> Regresar
          </a-button>
        </a-col>
        <a-col :span="12">
          <!--  :disabled="selectedTimeRange.length === 0" -->
          <a-button
            block
            type="primary"
            @click="showChildrenDrawer_to3"
            :disabled="selectedTimeRange.length === 0"
          >
            Siguiente <ArrowRightOutlined />
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>

  <!-- DRAWER 3 -->
  <a-drawer
    v-model:open="childrenDrawer_3"
    title="Crear bloqueo"
    :width="525"
    :maskClosable="false"
    :keyboard="false"
    @close="handlerShowDrawer"
  >
    <a-flex justify="center">
      <a-typography-title :level="5" :style="{ color: '#1284ED' }">
        <a-badge
          count="3"
          :number-style="{
            backgroundColor: '#1284ED',
          }"
        />
        Crear bloqueo
      </a-typography-title>
    </a-flex>

    <a-flex gap="middle" vertical class="mt-5">
      <a-typography-paragraph class="my-0">
        <a-typography-text strong>Motivo:</a-typography-text>
        {{ selectedBlockingReason.label }}
      </a-typography-paragraph>
      <a-typography-paragraph class="my-0" strong>
        Guías seleccionados: <br />
        <a-tag strong color="purple" v-for="{ provider } in selectedProviders" :key="provider._id">
          {{ provider.code }} - {{ provider.fullname }}
        </a-tag>
      </a-typography-paragraph>

      <div>
        <table style="width: 100%">
          <thead style="background-color: #2f353a; color: #ffffff">
            <tr>
              <th style="padding: 10px; text-align: left; width: 50%">Fechas</th>
              <th style="padding: 10px; width: 50%">Horario</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(range, index) in formattedTimeRanges" :key="index">
              <td style="padding: 10px">{{ range.date }}</td>
              <td style="padding: 5px; text-align: center">{{ range.time }}</td>
            </tr>
          </tbody>
        </table>
      </div>

      <a-alert message="Las observaciones se replicarán para todos los guías." banner />

      <a-typography-paragraph strong :style="{ margin: '0px' }">
        Ingresar observaciones:
      </a-typography-paragraph>
      <a-textarea v-model:value="formNewLock.observations" show-count :maxlength="300" />
    </a-flex>

    <template #footer>
      <a-row :gutter="[8, 8]">
        <a-col :span="12">
          <a-button block danger @click="showChildrenDrawer_to2">
            <ArrowLeftOutlined /> Regresar
          </a-button>
        </a-col>
        <a-col :span="12">
          <a-button block type="primary" @click="createLock" :loading="loading"> Guardar </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
  <div
    v-if="childrenDrawer_1"
    class="fixed top-0 left-0 z-10 bg-black opacity-50 w-screen h-screen"
  ></div>
</template>

<script lang="ts" setup>
  import { Dayjs } from 'dayjs';
  import { ref, h, watch, onMounted, computed, reactive } from 'vue';
  import { debounce } from 'lodash-es';
  import {
    ArrowRightOutlined,
    ArrowLeftOutlined,
    DeleteOutlined,
    CalendarOutlined,
    FieldTimeOutlined,
    PlusOutlined,
    SearchOutlined,
  } from '@ant-design/icons-vue';
  import { useBlockCalendarStore } from '@operations/modules/blackout-calendar/store/blockCalendar.store';
  import { useFiltersFormStore } from '@operations/modules/blackout-calendar/store/filtersForm.store';

  import { storeToRefs } from 'pinia';
  import type { BlockingReason } from '../interfaces/blocking-reason.interface';
  import { type SelectProps } from 'ant-design-vue';
  import type { Provider } from '../interfaces';

  import { notification } from 'ant-design-vue';

  const blockCalendarStore = useBlockCalendarStore();
  const { blockingReasons, providers } = storeToRefs(blockCalendarStore);
  const { getBlockingReasons, getProviders } = blockCalendarStore;

  const filtersFormStore = useFiltersFormStore();
  // const { onSubmit } = filtersFormStore;

  const selectedBlockingReason = ref<BlockingReason | null>(blockingReasons.value[0]);

  const blockingReasonsOptions = computed<SelectProps['options']>(() =>
    blockingReasons.value.map((reason) => ({
      value: reason._id,
      label: `${reason.iso} - ${reason.description}`,
      disabled: reason.guide_type === 'A', // Deshabilitar opciones de tipo 'A'
    }))
  );

  interface Props {
    showDrawer: boolean;
  }
  const props = defineProps<Props>();

  const emit = defineEmits(['handlerShowDrawer', 'otherEvents']);

  const childrenDrawer_1 = ref<boolean>(props.showDrawer);
  const loading = ref(false);

  // Opcion 01
  watch(
    () => props.showDrawer,
    (newVal) => {
      childrenDrawer_1.value = newVal;
    }
  );

  // Opcion 02
  // const childrenDrawer_1 = computed(() => props.showDrawer);

  const childrenDrawer_2 = ref<boolean>(false);
  const childrenDrawer_3 = ref<boolean>(false);

  const showChildrenDrawer_to1 = () => {
    restartNewBlock();
    childrenDrawer_1.value = true;
    childrenDrawer_2.value = false;
    childrenDrawer_3.value = false;
  };
  const showChildrenDrawer_to2 = () => {
    childrenDrawer_1.value = false;
    childrenDrawer_2.value = true;
    childrenDrawer_3.value = false;
  };
  const showChildrenDrawer_to3 = () => {
    childrenDrawer_1.value = false;
    childrenDrawer_2.value = false;
    childrenDrawer_3.value = true;
  };

  const createLock = async () => {
    console.log('Guardando bloqueo...');
    loading.value = true;
    try {
      const blocking_reason_id = selectedBlockingReason.value.value;

      console.log(selectedProviders.value);
      const providers = selectedProviders.value.map((provider) => {
        return provider.value;
      });

      const locks = selectedTimeRange.value.map((range) => {
        const startDate = range.datetime_start.format('YYYY-MM-DD');
        const startTime = range.datetime_start.format('HH:mm');
        const endDate = range.datetime_end.format('YYYY-MM-DD');
        const endTime = range.datetime_end.format('HH:mm');

        return {
          datetime_start: `${startDate}T${startTime}`,
          datetime_end: `${endDate}T${endTime}`,
          complete_day: range.complete_day,
        };
      });

      const payload = {
        blocking_reason_id,
        providers,
        locks,
        observations: formNewLock.value.observations,
        created_by: localStorage.getItem('user_code'),
      };

      const createdLock = await blockCalendarStore.createLock(payload);
      if (createdLock) {
        emit('handlerShowDrawer', false);
        childrenDrawer_3.value = false;
        restartNewBlock();
        filtersFormStore.onSubmit();
      }
    } catch (error) {
      console.error('Error al crear el bloqueo:', error);
      // Puedes agregar aquí cualquier manejo de error adicional, como mostrar un mensaje al usuario
      notification.error({
        message: 'Error',
        description: error.response.data.error,
      });
    } finally {
      loading.value = false;
    }
  };

  // Reiniciar
  const restartNewBlock = () => {
    loading.value = false;
    formNewLock.value.observations = '';
    selectedBlockingReason.value = blockingReasons.value[0];
    selectedProviders.value = [];
    selectedTimeRange.value = [];
    completeDay.value = false;
    selectedTimeRangeModel.value = undefined;
  };

  // Observaciones
  const formNewLock = ref({
    observations: '',
  });

  const handlerShowDrawer = () => {
    // console.log('ola');
    // childrenDrawer_1.value = false;
    emit('handlerShowDrawer', false);
  };

  onMounted(async () => {
    await getBlockingReasons();
  });

  const focus = () => {
    console.log('focus');
  };

  const state = reactive({
    data: [],
    value: [],
    fetching: false,
  });

  const fetchProvider = debounce(async (value: string) => {
    state.data = [];
    state.fetching = true;

    await getProviders(value.toUpperCase());
    const data = providers.value.map((provider) => ({
      label: `${provider.code} - ${provider.fullname}`,
      value: provider._id,
      provider,
    }));
    state.data = data;
    state.fetching = false;
  }, 300);

  const selectedProviders = ref([]);
  const handleChange2 = (selectedOption, item) => {
    const isDuplicate = selectedProviders.value.some(
      (provider: unknown) => provider.value === selectedOption
    );

    if (!isDuplicate) {
      // Agregar el proveedor a la lista
      selectedProviders.value.push(item);
      // Limpiar el valor seleccionado en el a-select
      state.value = '' || [];
    } else {
      console.warn('El proveedor ya está en la lista');
    }
  };

  const handleDeleteProvider = (provider: Provider) => {
    const index = selectedProviders.value.findIndex(
      (selectedProvider) => selectedProvider.value === provider._id
    );
    if (index !== -1) {
      selectedProviders.value.splice(index, 1);
    }
  };

  const completeDay = ref(false);

  const onRangeOk = (value: [Dayjs, Dayjs]) => {
    console.log('onOk: ', value);
  };

  type RangeValue = [Dayjs, Dayjs];
  type TimeRange = {
    datetime_start: Dayjs;
    datetime_end: Dayjs;
    complete_day: boolean;
  };

  const isDateRangesOverlap = ref(false);

  const selectedTimeRangeModel = ref<RangeValue | undefined>();
  const selectedTimeRange = ref<TimeRange[]>([]);
  const addTimeRange = () => {
    if (selectedTimeRangeModel.value) {
      const newRange: TimeRange = {
        datetime_start: selectedTimeRangeModel.value[0],
        datetime_end: selectedTimeRangeModel.value[1],
        complete_day: completeDay.value,
      };

      isDateRangesOverlap.value = isOverlapping(newRange, selectedTimeRange.value);

      if (!isDateRangesOverlap.value) {
        selectedTimeRange.value.push(newRange);
      }
    }
  };

  const handleClose = () => {
    isDateRangesOverlap.value = false;
  };

  const isOverlapping = (newRange: TimeRange, existingRanges: TimeRange[]) => {
    return existingRanges.some((range) => {
      return (
        newRange.datetime_start.isBefore(range.datetime_end) &&
        newRange.datetime_end.isAfter(range.datetime_start)
      );
    });
  };

  const removeTimeRange = (index) => {
    selectedTimeRange.value.splice(index, 1);
  };

  let formattedTimeRanges = computed(() => {
    return selectedTimeRange.value.map((range) => {
      const startDate = range.datetime_start.format('DD/MM/YYYY');
      const endDate = range.datetime_end.format('DD/MM/YYYY');
      const startTime = range.datetime_start.format('HH:mm');
      const endTime = range.datetime_end.format('HH:mm');

      let date;
      let time;

      if (range.complete_day) {
        if (startDate === endDate) {
          date = startDate;
        } else {
          date = `${startDate} - ${endDate}`;
        }
        time = 'Todo el día';
      } else if (startDate === endDate) {
        date = startDate;
        time = `${startTime} - ${endTime}`;
      } else {
        date = `${startDate} - ${endDate}`;
        time = `${startTime} - ${endTime}`;
      }

      return { date, time };
    });
  });
</script>

<style scoped>
  /* @import '@/modules/operations/shared/styles/tailwind.css'; */
  th.column-money,
  td.column-money {
    text-align: right !important;
  }
  ::v-deep(.custom-select svg) {
    fill: #7e8285;
    position: absolute;
    left: -455px;
    width: 15px;
  }
  ::v-deep(.custom-select .ant-select-selection-placeholder),
  ::v-deep(.custom-select .ant-select-selector input[type='search']) {
    padding-left: 20px;
  }
</style>
