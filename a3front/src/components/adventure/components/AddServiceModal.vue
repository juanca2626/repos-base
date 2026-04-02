<template>
  <a-modal
    v-model:visible="isModalOpen"
    title="SERVICIO ADICIONAL"
    :confirm-loading="isLoading || exchange_rate === 1"
    @ok="handleOk"
    @cancel="handleCancel"
    okText="Agregar Servicio ADICIONAL"
    cancelText="Cancelar"
    width="700px"
  >
    <a-form :model="service" :rules="rules" layout="vertical" ref="formRef">
      <a-form-item name="service" class="mb-3">
        <a-select
          v-model:value="extraService.service"
          placeholder="Seleccione un servicio"
          size="large"
          :show-search="true"
          :allow-clear="false"
          :options="extraServices"
          option-filter-prop="label"
        >
        </a-select>
      </a-form-item>
      <a-form-item name="paxs" class="mb-3" :help="`CNT. MAX. PAX (${departure.paxs} máximo)`">
        <a-input
          v-model:value="extraService.paxs"
          size="large"
          type="number"
          min="1"
          :max="departure.paxs"
          placeholder="0"
          autocomplete="off"
          class="text-center"
        />
      </a-form-item>
      <a-form-item name="isProgrammable" class="mb-3">
        <a-row type="flex" align="middle" justify="space-between">
          <a-col>
            <label>Programable</label>
          </a-col>
          <a-col>
            <a-radio-group
              v-model:value="service.isProgrammable"
              :disabled="service.locked"
              button-style="solid"
            >
              <a-radio-button :value="true">Sí</a-radio-button>
              <a-radio-button :value="false">No</a-radio-button>
            </a-radio-group>
          </a-col>
        </a-row>
      </a-form-item>
      <a-form-item name="isTicket" class="mb-3">
        <a-row type="flex" align="middle" justify="space-between">
          <a-col>
            <label>Entrada</label>
          </a-col>
          <a-col>
            <a-radio-group
              v-model:value="service.isTicket"
              :disabled="service.locked"
              button-style="solid"
            >
              <a-radio-button :value="true">Sí</a-radio-button>
              <a-radio-button :value="false">No</a-radio-button>
            </a-radio-group>
          </a-col>
        </a-row>
      </a-form-item>
      <a-form-item name="paymentType" class="mb-3">
        <a-row type="flex" align="middle" justify="space-between">
          <a-col>
            <label>Tipo de cobro</label>
          </a-col>
          <a-col>
            <a-radio-group
              v-model:value="service.paymentType"
              :disabled="service.locked"
              button-style="solid"
            >
              <a-radio-button :value="'credit'">Crédito</a-radio-button>
              <a-radio-button :value="'cash'">Contado</a-radio-button>
            </a-radio-group>
          </a-col>
        </a-row>
      </a-form-item>
      <a-form-item name="currency" class="mb-3">
        <a-row type="flex" align="middle" justify="space-between">
          <a-col>
            <label
              >Moneda<br />
              <small class="text-uppercase"
                >Tipo de Cambio: <i class="text-500">{{ exchange_rate }}</i></small
              ></label
            >
          </a-col>
          <a-col>
            <a-radio-group
              v-model:value="service.currency"
              :disabled="service.locked"
              button-style="solid"
            >
              <a-radio-button :value="'USD'">Dólares (USD)</a-radio-button>
              <a-radio-button :value="'PEN'">Soles (PEN)</a-radio-button>
            </a-radio-group>
          </a-col>
        </a-row>
      </a-form-item>
      <a-form-item name="providers">
        <a-row type="flex" align="middle" justify="space-between">
          <a-col>
            <label>
              Múltiples Proveedores
              <a-tooltip>
                <template #title>
                  <small
                    ><b
                      >El servicio puede realizarse por múltiples proveedores (PORTEADORES)</b
                    ></small
                  >
                </template>
                <InfoCircleOutlined class="ms-1"
              /></a-tooltip>
            </label>
          </a-col>
          <a-col>
            <a-radio-group
              v-model:value="service.multiProviders"
              :disabled="service.locked"
              button-style="solid"
            >
              <a-radio-button :value="true">Sí</a-radio-button>
              <a-radio-button :value="false">No</a-radio-button>
            </a-radio-group>
          </a-col>
        </a-row>
      </a-form-item>
      <a-form-item
        name="days"
        class="mb-2 bg-light"
        style="gap: 5px; border: 1px solid #ddd; border-radius: 6px"
      >
        <a-row type="flex" justify="space-between" align="middle" class="pt-3 px-3">
          <a-col>
            <small class="text-uppercase text-600">Seleccionar días:</small>
          </a-col>
          <a-col>
            <a-row type="flex" align="middle" style="gap: 7px">
              <a-col>
                <label for="allDays">
                  <small class="text-uppercase text-600">Todos los días</small>
                </label>
              </a-col>
              <a-col>
                <a-switch
                  id="allDays"
                  size="small"
                  v-model:checked="extraService.all_days"
                  @change="onCheckAllChange"
                />
              </a-col>
            </a-row>
          </a-col>
        </a-row>
        <a-checkbox-group v-model:value="extraService.days" class="w-100 p-3" size="large">
          <a-row type="flex" justify="center" align="middle" style="gap: 5px">
            <a-col flex="auto">
              <div
                class="bg-white text-center py-3 ps-1"
                style="border: 1px dashed #ddd; border-radius: 6px"
              >
                <a-checkbox value="-2"
                  ><small><b>DIA -2</b></small></a-checkbox
                >
              </div>
            </a-col>
            <a-col flex="auto">
              <div
                class="bg-white text-center py-3 ps-1 text-center"
                style="border: 1px dashed #ddd; border-radius: 6px"
              >
                <a-checkbox value="-1"
                  ><small><b>DIA -1</b></small></a-checkbox
                >
              </div>
            </a-col>
            <a-col flex="auto" v-for="day in template.durationDays">
              <div
                class="bg-white text-center py-3 ps-1"
                style="border: 1px dashed #ddd; border-radius: 6px"
              >
                <a-checkbox :value="day.toString()"
                  ><small
                    ><b>DIA {{ day }}</b></small
                  ></a-checkbox
                >
              </div>
            </a-col>
          </a-row>
        </a-checkbox-group>
      </a-form-item>

      <template v-if="service.type === 'costPerPerson' || service.type === 'costPerPerson'">
        <a-form-item
          :label="`Costo por persona (${service.currency})`"
          name="costPerPerson"
          class="mb-0"
        >
          <a-input
            v-model:value="service.costPerPerson"
            size="large"
            type="number"
            autocomplete="off"
            :disabled="service.locked"
          />
        </a-form-item>
        <template v-if="service.currency === 'USD'">
          <small
            ><i>{{ parseFloat(service.costPerPerson * exchange_rate).toFixed(2) }} PEN</i></small
          >
        </template>
        <template v-if="service.currency === 'PEN'">
          <small
            ><i>{{ parseFloat(service.costPerPerson / exchange_rate).toFixed(2) }} USD</i></small
          >
        </template>
      </template>

      <template v-if="service.type === 'ratePerDay'">
        <a-row type="flex" justify="space-between" align="bottom" style="gap: 1rem">
          <a-col flex="auto">
            <a-form-item
              :label="`Costo por día (${service.currency})`"
              name="ratePerDay"
              class="mb-0"
            >
              <a-input
                v-model:value="service.ratePerDay"
                type="number"
                size="large"
                autocomplete="off"
                :disabled="service.locked"
              />
            </a-form-item>
            <template v-if="service.currency === 'USD'">
              <small
                ><i>{{ parseFloat(service.ratePerDay * exchange_rate).toFixed(2) }} PEN</i></small
              >
            </template>
            <template v-if="service.currency === 'PEN'">
              <small
                ><i>{{ parseFloat(service.ratePerDay / exchange_rate).toFixed(2) }} USD</i></small
              >
            </template>
          </a-col>
          <a-col flex="auto">
            <a-form-item name="total" class="mb-0">
              <a-row type="flex" justify="space-between" align="middle" class="mb-2">
                <a-col> Total ({{ service.currency }}) </a-col>
                <a-col> {{ service.days.length }} días </a-col>
              </a-row>
              <a-input
                :value="service.ratePerDay * service.days.length"
                :disabled="true"
                type="number"
                size="large"
                autocomplete="off"
              />
            </a-form-item>
            <template v-if="service.currency === 'USD'">
              <small
                ><i
                  >{{
                    parseFloat(service.ratePerDay * service.days.length * exchange_rate).toFixed(2)
                  }}
                  PEN</i
                ></small
              >
            </template>
            <template v-if="service.currency === 'PEN'">
              <small
                ><i
                  >{{
                    parseFloat((service.ratePerDay * service.days.length) / exchange_rate).toFixed(
                      2
                    )
                  }}
                  USD</i
                ></small
              >
            </template>
          </a-col>
        </a-row>
      </template>

      <template v-if="service.type === 'range'">
        <template v-for="(range, r) in service.pricing" :key="r">
          <a-row
            type="flex"
            justify="space-between"
            align="middle"
            class="px-3 py-2 my-1 bg-light"
            style="border: 1px dashed #ddd; border-radius: 6px"
          >
            <a-col :span="6">
              <a-form-item label="PAX" name="pax" class="mb-0">
                <a-input
                  v-model:value="range.pax"
                  :disabled="service.locked"
                  size="large"
                  type="number"
                  autocomplete="off"
                />
              </a-form-item>
            </a-col>
            <a-col :span="6">
              <a-form-item name="value" class="mb-0">
                <template #label>
                  <span>Monto ({{ service.currency }})</span>
                </template>
                <a-input
                  v-model:value="range.value"
                  :disabled="service.locked"
                  size="large"
                  type="number"
                  autocomplete="off"
                />
              </a-form-item>
            </a-col>
            <a-col>
              <p class="mb-0">
                Hasta <b>{{ range.pax }}</b> pax:
                <b>{{ service.currency }} {{ parseFloat(range.value).toFixed(2) }}</b>
              </p>
              <p v-if="service.currency === 'USD'" class="mb-0 text-right">
                <small
                  ><i>{{ parseFloat(range.value * exchange_rate).toFixed(2) }} PEN</i></small
                >
              </p>
              <p v-if="service.currency === 'PEN'" class="mb-0 text-right">
                <small
                  ><i>{{ parseFloat(range.value / exchange_rate).toFixed(2) }} USD</i></small
                >
              </p>
            </a-col>
          </a-row>
        </template>
      </template>
    </a-form>
  </a-modal>
</template>

<script setup lang="ts">
  import { ref, watch, computed, onMounted } from 'vue';
  import {
    useTemplates,
    useExtraServices,
    useDepartures,
    useConfiguration,
  } from '@/composables/adventure';
  import { InfoCircleOutlined } from '@ant-design/icons-vue';

  const emit = defineEmits(['handleOk', 'handleCancel', 'update:visible']);

  const props = defineProps({
    visible: {
      type: Boolean,
      default: false,
    },
    locked: {
      type: Boolean,
      default: false,
    },
  });

  const { departure } = useDepartures();
  const { isLoading, template, service, error } = useTemplates();
  const { fetchExtraServices, extraServices, extraService } = useExtraServices();
  const { configuration, fetchConfiguration } = useConfiguration();

  const formRef = ref();
  const rules = {};

  const isModalOpen = computed({
    get() {
      return props.visible;
    },
    set(value) {
      emit('update:visible', value);
    },
  });

  watch(
    () => props.visible,
    (newVisible) => {
      if (newVisible) {
        service.value = { ...service.value, locked: props.locked };
      }
    },
    { immediate: true }
  );

  watch(
    () => props.locked,
    (newLocked) => {
      if (newLocked) {
        service.value = { ...service.value, locked: newLocked };
      }
    },
    { immediate: true }
  );

  watch(
    () => extraService.value.service,
    (newService) => {
      service.value = {};
      if (newService) {
        const _service = extraServices.value.find((service: any) => service._id === newService);

        const data = {
          ..._service,
          costPerPerson: parseFloat(_service.pricing[0].value),
          ratePerDay: parseFloat(_service.pricing[0].value),
          days: [],
          locked: true,
        };

        service.value = JSON.parse(JSON.stringify(data));
      }
    },
    { immediate: true }
  );

  const handleOk = async () => {
    try {
      await formRef.value.validate();
    } catch (err: any) {
      error.value = err;
    } finally {
      isModalOpen.value = false;
      emit('handleOk', extraService.value);
    }
  };

  const handleCancel = () => {
    isModalOpen.value = false;
    emit('handleCancel');
  };

  const onCheckAllChange = () => {
    if (!extraService.value.all_days) {
      extraService.value.days = [];
    } else {
      for (let i = -2; i <= template.value.durationDays; i++) {
        if (i != 0) {
          extraService.value.days.push(i.toString());
        }
      }
    }
  };

  watch(
    () => extraService.value.days,
    (val) => {
      extraService.value.all_days = val.length === template.value.durationDays + 2;
    }
  );

  const exchange_rate = ref(1);

  onMounted(async () => {
    extraService.value = {
      service: '',
      paxs: 0,
      days: [],
    };
    await fetchConfiguration();
    await fetchExtraServices();

    template.value = JSON.parse(JSON.stringify(departure.value.template));
    exchange_rate.value = parseFloat(configuration.value?.data?.value ?? 1);
  });
</script>
