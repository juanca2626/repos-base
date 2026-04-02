<template>
  <a-typography-text strong style="font-size: 16px">
    Complete los siguientes campos para filtrar:
  </a-typography-text>

  <a-form :model="formState" layout="vertical" v-bind="{}" class="my-3">
    <a-row :gutter="16">
      <a-col :span="6">
        <a-form-item label="Tipo:">
          <a-select
            v-model:value="formState.contractProvider.iso"
            :options="providerContractTypesOptions"
            @change="onSubmit"
          />
        </a-form-item>
      </a-col>

      <a-col :span="6">
        <a-form-item label="Perfil:">
          <a-select
            v-model:value="formState.profileProvider.iso"
            :options="providerProfileTypesOptions"
            @change="onSubmit"
          />
        </a-form-item>
      </a-col>

      <a-col :span="6">
        <a-form-item label="Sede:">
          <a-select
            v-model:value="formState.headquarter.code"
            :options="headquartersOptions"
            @change="onSubmit"
          />
        </a-form-item>
      </a-col>

      <a-col :span="6">
        <a-form-item label="Ingresar término:">
          <a-input
            v-model:value="formState.searchTerm"
            placeholder="Ingresa una palabra..."
            @keyup.enter="onSubmit"
            style="padding: 0px 10px"
          >
            <template #prefix>
              <SearchOutlined style="color: #bdbdbd" />
            </template>
          </a-input>
        </a-form-item>
      </a-col>
    </a-row>

    <a-row type="flex" align="middle" :gutter="16">
      <a-col :span="6">
        <!-- format="MMMM YYYY" -->
        <a-config-provider :locale="locale">
          <a-date-picker
            class="custom-date-picker"
            v-model:value="formState.monthYear"
            :format="customFormat"
            picker="month"
            size="large"
            style="width: 100%"
            @change="onSubmit"
          >
            <template #suffixIcon>
              <DownOutlined />
            </template>
          </a-date-picker>
        </a-config-provider>
      </a-col>

      <a-col :span="6">
        <a-flex justify="flex-center" align="middle">
          <a-space>
            <a-badge color="#5C5AB4" text="Servicios programados" style="color: #5c5ab4" />

            <a-badge color="#FF3B3B" text="Feriados" style="color: #ff3b3b" />
          </a-space>
        </a-flex>
      </a-col>

      <a-col :span="12">
        <a-flex justify="flex-end" align="middle">
          <a-space>
            <a-button
              type="link"
              :style="{ color: '#bd0d12' }"
              size="large"
              @click="cleanFilters()"
            >
              <svg
                class="colorPrimary_SVG"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 576 512"
                width="24"
                height="24"
              >
                <path
                  d="M234.7 42.7L197 56.8c-3 1.1-5 4-5 7.2s2 6.1 5 7.2l37.7 14.1L248.8 123c1.1 3 4 5 7.2 5s6.1-2 7.2-5l14.1-37.7L315 71.2c3-1.1 5-4 5-7.2s-2-6.1-5-7.2L277.3 42.7 263.2 5c-1.1-3-4-5-7.2-5s-6.1 2-7.2 5L234.7 42.7zM46.1 395.4c-18.7 18.7-18.7 49.1 0 67.9l34.6 34.6c18.7 18.7 49.1 18.7 67.9 0L529.9 116.5c18.7-18.7 18.7-49.1 0-67.9L495.3 14.1c-18.7-18.7-49.1-18.7-67.9 0L46.1 395.4zM484.6 82.6l-105 105-23.3-23.3 105-105 23.3 23.3zM7.5 117.2C3 118.9 0 123.2 0 128s3 9.1 7.5 10.8L64 160l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L128 160l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L128 96 106.8 39.5C105.1 35 100.8 32 96 32s-9.1 3-10.8 7.5L64 96 7.5 117.2zm352 256c-4.5 1.7-7.5 6-7.5 10.8s3 9.1 7.5 10.8L416 416l21.2 56.5c1.7 4.5 6 7.5 10.8 7.5s9.1-3 10.8-7.5L480 416l56.5-21.2c4.5-1.7 7.5-6 7.5-10.8s-3-9.1-7.5-10.8L480 352l-21.2-56.5c-1.7-4.5-6-7.5-10.8-7.5s-9.1 3-10.8 7.5L416 352l-56.5 21.2z"
                />
              </svg>
              Limpiar filtros
            </a-button>
          </a-space>
          <!-- <a-button type="text" class="p-1"> 07 seleccionados </a-button> -->

          <a-space v-if="hasSelectedDays">
            <a-dropdown>
              <template #overlay>
                <a-menu>
                  <a-menu-item
                    key="2"
                    :icon="h(DeleteOutlined)"
                    @click="
                      modalStore.showModal('deleteMultiple', {
                        filteredlocksByProvidersAndSelectedDates,
                      })
                    "
                  >
                    Eliminar bloqueo(s)
                  </a-menu-item>
                </a-menu>
              </template>
              <a-button size="large" class="custom-button">
                {{ totalSelectedDays }} Seleccionado(s)
                <DownOutlined />
              </a-button>
            </a-dropdown>

            <a-button :style="{ color: '#bd0d12' }" danger size="large" @click="deselectAllDays">
              Deseleccionar
            </a-button>
          </a-space>
        </a-flex>
      </a-col>
    </a-row>
  </a-form>
</template>

<script lang="ts" setup>
  import { h, watch } from 'vue';
  import { storeToRefs } from 'pinia';
  import dayjs from 'dayjs';
  import { onMounted, computed } from 'vue';
  import { SearchOutlined, DownOutlined, DeleteOutlined } from '@ant-design/icons-vue';
  import { setDefaultSelectValue } from '@operations/shared/utils/formUtils';

  import {
    type Headquarter,
    type ProviderContractType,
    type ProviderProfileType,
  } from '@operations/modules/blackout-calendar/interfaces';

  import { useBlockCalendarStore } from '@operations/modules/blackout-calendar/store/blockCalendar.store';
  import { useSelectedDaysStore } from '@operations/modules/blackout-calendar/store/selectedDays.store';
  import { useFiltersFormStore } from '@operations/modules/blackout-calendar/store/filtersForm.store';
  import { useModalStore } from '@operations/modules/blackout-calendar/store/modal.store';
  import { useDataStore } from '@operations/modules/blackout-calendar/store/data.store';

  import es_ES from 'ant-design-vue/es/locale/es_ES';
  import 'dayjs/locale/es';
  dayjs.locale('es_ES');

  const locale = es_ES;
  const selectedDaysStore = useSelectedDaysStore();
  const modalStore = useModalStore();

  const blockCalendarStore = useBlockCalendarStore();
  const { deselectAllDays } = blockCalendarStore;

  const filtersFormStore = useFiltersFormStore();
  const { formState, cleanFilters, onSubmit } = filtersFormStore;
  // const {
  //   providerContractTypesOptions: contractProviderOptions,
  //   providerProfileTypesOptions: profileProviderOptions,
  //   headquartersOptions: headquartersOptions,
  // } = storeToRefs(filtersFormStore);

  /* data.store.ts */
  const dataStore = useDataStore();
  const { getProviderContractTypes, getProviderProfileTypes, getHeadquarters, getBlockingReasons } =
    dataStore;
  const { providerContractTypesOptions, providerProfileTypesOptions, headquartersOptions } =
    storeToRefs(dataStore);

  /* blockCalendar.store.ts */
  const { locksByProvidersAndSelectedDates } = storeToRefs(blockCalendarStore);

  /* selectedDays.store.ts */
  const { selectedDays } = storeToRefs(selectedDaysStore);

  const customFormat = (date: string) => {
    return date
      ? dayjs(date)
          .locale('es')
          .format('MMMM YYYY')
          .replace(/^\w/, (c: string) => c.toUpperCase())
      : '';
  };

  const filteredlocksByProvidersAndSelectedDates = computed(() => {
    return locksByProvidersAndSelectedDates.value.filter(
      (item: any) => Object.keys(item.locks).length > 0
    );
  });

  const totalSelectedDays = computed(() => {
    return selectedDays.value.reduce((total, item) => {
      return total + item.days.length;
    }, 0);
  });
  const hasSelectedDays = computed(() => totalSelectedDays.value > 0);

  // Calculando mes / año actual

  // const contractProviderOptions = providerContractTypesOptions;

  // onMounted(async () => {
  //   await loadFiltersData();
  //   await onSubmit();
  // });

  // Usamos un solo watchEffect para todos los selects
  watch(providerContractTypesOptions, (newOptions: any) => {
    if (newOptions.length > 0) {
      setDefaultSelectValue<ProviderContractType>(newOptions, formState.contractProvider, 'iso');
    }
  });

  watch(providerProfileTypesOptions, (newOptions: any) => {
    if (newOptions.length > 0) {
      setDefaultSelectValue<ProviderProfileType>(newOptions, formState.profileProvider, 'iso');
    }
  });

  watch(headquartersOptions, (newOptions: any) => {
    if (newOptions.length > 2) {
      setDefaultSelectValue<Headquarter>(newOptions, formState.headquarter, 'code', 2);
    }
  });

  onMounted(async () => {
    try {
      await Promise.all([
        getProviderContractTypes(),
        getProviderProfileTypes(),
        getHeadquarters(),
        getBlockingReasons(),
      ]);
      await onSubmit();
    } catch (error) {
      console.error('Error cargando datos iniciales:', error);
    }
  });
</script>

<style scoped>
  /*@import '@/modules/operations/shared/styles/tailwind.css';*/

  .colorPrimary_SVG {
    fill: #bd0d12;
    margin-right: 5px;
  }
  .custom-button {
    background-color: #e6f7ff;
    border-color: #91d5ff;
    color: #1284ed;
    font-weight: 500;
  }
  .custom-button:hover {
    background-color: #e6f7ff;
    border-color: #1284ed;
    color: #1284ed;
    font-weight: 500;
  }
  ::v-deep(.custom-date-picker input) {
    font-weight: bold;
  }
  ::v-deep(.ant-input) {
    outline: none !important;
    box-shadow: none !important;
  }
</style>
