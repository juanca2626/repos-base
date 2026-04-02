<template>
  <a-modal
    v-model:visible="isModalOpen"
    title="Servicio"
    :confirm-loading="isLoading"
    @ok="handleOk"
    @cancel="handleCancel"
    okText="Guardar"
    cancelText="Cancelar"
    width="700px"
  >
    <a-form :model="service" :rules="rules" layout="vertical" ref="formRef">
      <a-form-item label="Categoría" name="categoryId" class="mb-2">
        <a-select
          v-model:value="service.categoryId"
          placeholder="Seleccione una categoría"
          size="large"
          :show-search="false"
          :allow-clear="false"
          :disabled="service.locked"
          :options="categories"
          style="width: 100%"
        >
        </a-select>
      </a-form-item>
      <a-form-item label="Tipo" name="type" class="mb-2">
        <a-select
          v-model:value="service.type"
          placeholder="Seleccione un tipo"
          size="large"
          :show-search="false"
          :allow-clear="false"
          :disabled="service.locked"
          :options="serviceTypes"
          style="width: 100%"
        >
        </a-select>
      </a-form-item>
      <a-form-item label="Descripción" name="name" class="mb-2">
        <a-input
          v-model:value="service.name"
          size="large"
          placeholder="Descripción"
          autocomplete="off"
        />
      </a-form-item>
      <a-form-item label="Proveedor" name="provider" class="mb-2">
        <a-select
          v-model:value="providerTags"
          mode="tags"
          size="large"
          :disabled="service.locked"
          placeholder="Proveedor"
          :options="[]"
          :token-separators="[',']"
        >
          <template #notFoundContent>
            <small
              ><b
                >Escriba un código de proveedor y presione enter para asignar. Puede asignar más de
                uno.</b
              ></small
            >
          </template>
        </a-select>
        <p class="mb-0"><small>Ingrese uno o más códigos de proveedor</small></p>
      </a-form-item>
      <a-form-item label="Programable" name="isProgrammable" class="mb-2">
        <a-radio-group
          v-model:value="service.isProgrammable"
          :disabled="service.locked"
          button-style="solid"
        >
          <a-radio-button :value="true">Sí</a-radio-button>
          <a-radio-button :value="false">No</a-radio-button>
        </a-radio-group>
      </a-form-item>
      <a-form-item label="Entrada" name="isTicket" class="mb-2">
        <a-radio-group
          v-model:value="service.isTicket"
          :disabled="service.locked"
          button-style="solid"
        >
          <a-radio-button :value="true">Sí</a-radio-button>
          <a-radio-button :value="false">No</a-radio-button>
        </a-radio-group>
      </a-form-item>
      <a-form-item label="Tipo de cobro" name="paymentType" class="mb-2">
        <a-radio-group
          v-model:value="service.paymentType"
          :disabled="service.locked"
          button-style="solid"
        >
          <a-radio-button :value="'credit'">Crédito</a-radio-button>
          <a-radio-button :value="'cash'">Contado</a-radio-button>
        </a-radio-group>
      </a-form-item>
      <a-form-item label="Moneda" name="currency" class="mb-2">
        <a-radio-group
          v-model:value="service.currency"
          :disabled="service.locked"
          button-style="solid"
        >
          <a-radio-button :value="'USD'">Dólares (USD)</a-radio-button>
          <a-radio-button :value="'PEN'">Soles (PEN)</a-radio-button>
        </a-radio-group>
      </a-form-item>
      <a-form-item name="providers" class="mb-2">
        <template #label>
          Múltiples Proveedores
          <a-tooltip>
            <template #title>
              <small
                ><b>El servicio puede realizarse por múltiples proveedores (PORTEADORES)</b></small
              >
            </template>
            <InfoCircleOutlined class="ms-1"
          /></a-tooltip>
        </template>
        <a-radio-group
          v-model:value="service.multiProviders"
          :disabled="service.locked"
          button-style="solid"
        >
          <a-radio-button :value="true">Sí</a-radio-button>
          <a-radio-button :value="false">No</a-radio-button>
        </a-radio-group>
      </a-form-item>
      <a-form-item name="days" class="mb-2" v-if="props.template">
        <a-row type="flex" justify="space-between" align="middle" class="mb-2">
          <a-col>Seleccionar días</a-col>
          <a-col>
            <a-switch
              size="small"
              v-model:checked="service.all_days"
              label="Todos los días"
              :disabled="service.locked"
              @change="onCheckAllChange"
          /></a-col>
        </a-row>
        <a-checkbox-group v-model:value="service.days" class="w-100 mb-2" size="large">
          <a-row
            type="flex"
            justify="center"
            align="middle"
            class="bg-light p-3"
            style="gap: 5px; border: 1px solid #ddd; border-radius: 6px"
          >
            <a-col>
              <div
                class="w-100 bg-white py-3 ps-1"
                style="border: 1px dashed #ddd; border-radius: 6px"
              >
                <a-checkbox value="-2" :disabled="service.locked"
                  ><small><b>DIA -2</b></small></a-checkbox
                >
              </div>
            </a-col>
            <a-col>
              <div
                class="w-100 bg-white py-3 ps-1"
                style="border: 1px dashed #ddd; border-radius: 6px"
              >
                <a-checkbox value="-1" :disabled="service.locked"
                  ><small><b>DIA -1</b></small></a-checkbox
                >
              </div>
            </a-col>
            <a-col v-for="day in template.durationDays">
              <div
                class="w-100 bg-white py-3 ps-1"
                style="border: 1px dashed #ddd; border-radius: 6px"
              >
                <a-checkbox :value="day.toString()" :disabled="service.locked"
                  ><small
                    ><b>DIA {{ day }}</b></small
                  ></a-checkbox
                >
              </div>
            </a-col>
          </a-row>
        </a-checkbox-group>
      </a-form-item>

      <template v-if="service.type === 'costPerPerson'">
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
        <a-button
          type="primary"
          danger
          class="mb-2"
          style="width: 100%"
          @click="addRange"
          :disabled="service.locked"
          >Agregar rango</a-button
        >
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
            <a-col>
              <a-tooltip title="Eliminar Rango" placement="right">
                <a-button
                  type="primary"
                  size="large"
                  @click="removeRange(r)"
                  :disabled="service.locked"
                >
                  <DeleteOutlined />
                </a-button>
              </a-tooltip>
            </a-col>
          </a-row>
        </template>
      </template>
    </a-form>
  </a-modal>
</template>

<script setup lang="ts">
  import { ref, watch, computed, onBeforeMount } from 'vue';
  import { useTemplates, useCategories, useConfiguration } from '@/composables/adventure';
  import { DeleteOutlined, InfoCircleOutlined } from '@ant-design/icons-vue';

  const emit = defineEmits(['handleOk', 'handleCancel', 'update:visible']);

  const { categories, fetchCategories } = useCategories();
  const { isLoading, template, service, error, serviceTypes } = useTemplates();
  const { configuration, fetchConfiguration } = useConfiguration();

  const providerTags = computed({
    get: () => {
      if (!service.value || !service.value.providers) return [];
      return typeof service.value.providers === 'string'
        ? service.value.providers.split(',').filter((p: string) => p.trim() !== '')
        : [];
    },
    set: (val) => {
      if (service.value) {
        service.value.providers = val.join(',');
      }
    },
  });

  const props = defineProps({
    visible: {
      type: Boolean,
      default: false,
    },
    locked: {
      type: Boolean,
      default: false,
    },
    template: {
      type: Boolean,
      default: true,
    },
  });

  const formRef = ref();
  const rules = {
    templateId: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    categoryId: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    name: [{ required: true, message: 'Requerido', trigger: 'blur' }],
    type: [{ required: true, message: 'Requerido', trigger: 'blur' }],
  };

  const exchange_rate = ref(1);

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

  const handleOk = async () => {
    try {
      await formRef.value.validate();
      emit('handleOk', service.value);
    } catch (err: any) {
      error.value = err;
    }
  };

  const handleCancel = () => {
    emit('handleCancel');
  };

  const onCheckAllChange = () => {
    if (!service.value.all_days) {
      service.value.days = [];
    } else {
      for (let i = -2; i <= template.value.durationDays; i++) {
        if (i != 0) {
          service.value.days.push(i.toString());
        }
      }
    }
  };

  watch(
    () => service.value.days,
    (val) => {
      if (props.template) {
        service.value.all_days = val.length === template.value.durationDays + 2;
      }
    }
  );

  const addRange = () => {
    service.value.pricing.push({
      pax: 0,
      value: 0,
    });
  };

  const removeRange = (index: number) => {
    service.value.pricing.splice(index, 1);
  };

  onBeforeMount(async () => {
    if (categories.value.length === 0) {
      await fetchCategories();
    }

    const exchangeRate = configuration.value?.data?.value;
    if (!exchangeRate) {
      await fetchConfiguration();
    }

    exchange_rate.value = parseFloat(configuration.value?.data?.value ?? 1);
  });
</script>
