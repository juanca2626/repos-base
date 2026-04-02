<template>
  <div class="files-edit">
    <a-spin size="large" :spinning="filesStore.isLoading || filesStore.isLoadingAsync">
      <div class="d-flex justify-content-between align-items-center pb-5">
        <div class="title">
          <font-awesome-icon
            :icon="['fas', 'cube']"
            class="text-danger"
            style="padding-right: 15px"
          />
          Proveedor de regalo
        </div>
        <div class="actions">
          <a-button
            class="btn-default btn-back"
            type="default"
            v-on:click="returnToProgram()"
            :loading="filesStore.isLoadingAsync"
            size="large"
          >
            Ir al programa
          </a-button>
          <a-button
            danger
            class="text-600 btn-temporary"
            type="default"
            v-on:click="returnToReplaceService()"
            :loading="filesStore.isLoadingAsync"
            size="large"
          >
            Volver a modificar servicio
          </a-button>
        </div>
      </div>
      <a-row>
        <a-col :span="24">
          <div class="files-edit__fileinfo-center">
            <span class="files-edit__filealert">
              <a-alert class="px-4" type="info" closable>
                <template #message>
                  <IconCircleExclamation width="1.2em" height="1.2em" color="#5C5AB4" />
                  <span class="text-info">
                    Antes de agregar los detalles de tu regalo, selecciona el proveedor del regalo
                    que vas a otorgar.
                  </span>
                </template>
              </a-alert>
            </span>
          </div>
        </a-col>
      </a-row>
      <a-row>
        <a-col :span="24" class="mt-5">
          <a-card class="cart-content">
            <a-row>
              <a-col :span="8">
                <a-row type="flex" justify="space-between" align="middle">
                  <a-col :span="4">
                    <font-awesome-icon :icon="['fas', 'inbox']" class="title-icon" />
                  </a-col>
                  <a-col :span="20">
                    <div class="title-service">Proveedor del servicio</div>
                    <div class="subtitle-query">Consulte los proveedores asociados a LITO</div>
                  </a-col>
                </a-row>
              </a-col>
              <a-col :span="16">
                <a-form
                  class="files-edit__form-service-temporary"
                  :model="formState"
                  ref="formRefSupplier"
                  layout="vertical"
                  :rules="rules"
                >
                  <a-form-item name="supplierCode" label="Ingrese RUC del proveedor">
                    <a-select
                      size="large"
                      placeholder="Selecciona"
                      :options="serviceMaskStore.getSuppliers"
                      v-model:value="formState.supplierCode"
                      :field-names="{ label: 'label', value: 'value' }"
                      :filter-option="false"
                      showSearch
                      label-in-value
                      :allowClear="true"
                      :loading="serviceMaskStore.isLoading"
                      @search="searchSuppliers"
                      @change="handleSupplierChange"
                      not-found-content="No hay proveedores disponibles"
                    >
                    </a-select>
                  </a-form-item>
                </a-form>
              </a-col>
            </a-row>
          </a-card>
          <a-row v-if="!serviceMaskStore.notFoundSupplier">
            <a-col :span="24" class="mt-5" style="text-align: right; margin-top: 50px">
              <a-button
                type="primary"
                class="btn-danger btn-continue mx-2 px-4 text-600"
                default
                html-type="submit"
                size="large"
                @click="handleFormSubmit"
              >
                Continuar
              </a-button>
            </a-col>
          </a-row>
        </a-col>
      </a-row>
      <a-row v-if="serviceMaskStore.notFoundSupplier">
        <a-col :span="24" class="my-5">
          <div class="alert-error-supplier">
            <a-alert class="text-danger" type="error" show-icon closable>
              <template #icon>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="24"
                  height="24"
                  fill="none"
                  stroke="#eb5757"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  class="feather feather-info"
                >
                  <circle cx="12" cy="12" r="10" />
                  <path d="M12 16v-4M12 8h.01" />
                </svg>
              </template>
              <template #message>
                <div class="text-danger">Proveedor no existe</div>
              </template>
              <template #description>
                <div class="text-danger">
                  Escoja otro proveedor o comuniquese con el área de finanzas para la creación del
                  nuevo proveedor.
                </div>
              </template>
            </a-alert>
          </div>
        </a-col>
      </a-row>
      <a-row v-if="serviceMaskStore.notFoundSupplier">
        <a-col :span="24" class="mb-1 mt-5">
          <div class="steps-supplier-text">Pasos para solicitar la creación del proveedor</div>
        </a-col>
      </a-row>
      <a-row v-if="serviceMaskStore.notFoundSupplier">
        <a-col :span="24" class="mt-5">
          <div class="alert-info-light-supplier">
            <a-alert class="text-default" type="info" show-icon>
              <template #icon>
                <div class="icon-circle">1</div>
              </template>
              <template #message>
                <div class="text-default">
                  Confirmar el documento mercantil que ofrece tu proveedor
                </div>
              </template>
              <template #description>
                <div class="text-default">
                  ¿Emite boleta o factura? Que el proveedor emita solo boleta aumenta el costo del
                  servicio en un 30%
                </div>
              </template>
            </a-alert>
          </div>
        </a-col>
        <a-col :span="24">
          <div class="alert-info-light-supplier">
            <a-alert class="text-default" type="info" show-icon>
              <template #icon>
                <div class="icon-circle">2</div>
              </template>
              <template #message>
                <div class="text-default">
                  Envía tu solicitud para la creación de proveedor a Contabilidad
                </div>
              </template>
              <template #description>
                <div class="text-default">
                  A través del correo electrónico comunicate con los siguientes datos:
                  <ul>
                    <li>RUC del proveedor</li>
                    <li>Tipo de documento mercantil que emite (datos recabados previamente)</li>
                    <li>Información relacionada al servicio que ofrece</li>
                  </ul>
                </div>
              </template>
            </a-alert>
          </div>
        </a-col>
      </a-row>
    </a-spin>
  </div>
</template>
<script setup lang="ts">
  import { useFilesStore } from '@/stores/files';
  import { onMounted, reactive, ref, watch } from 'vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { useRouter } from 'vue-router';
  import IconCircleExclamation from '@/components/icons/IconCircleExclamation.vue';
  import type { Rule } from 'ant-design-vue/es/form';
  import { debounce } from 'lodash-es';
  import { useServiceMaskStore } from '@/components/files/services-masks/store/serviceMaskStore';
  import { type FormInstance, notification } from 'ant-design-vue';

  const filesStore = useFilesStore();
  const serviceMaskStore = useServiceMaskStore();
  const emit = defineEmits(['complete', 'nextStep']);
  const router = useRouter();
  const formState = reactive({
    supplierCode: null,
  });
  const supplierSelected = ref({});
  const formRefSupplier = ref<FormInstance | null>(null);
  const rules: Record<string, Rule[]> = {
    supplierCode: [
      {
        required: true,
        message: 'Debe seleccionar un proveedor',
        trigger: 'change',
        validator: (_, value) => {
          if (value === null || value === '') {
            return Promise.reject('Debe seleccionar un proveedor');
          }
          return Promise.resolve();
        },
      },
    ],
  };

  const returnToReplaceService = () => {
    const currentId = router.currentRoute.value.params.id; // Obtenemos el :id desde la URL
    const currentServiceId = router.currentRoute.value.params.service_id; // Obtenemos el :service_id desde la URL
    localStorage.removeItem('stepsData');
    localStorage.removeItem('currentStep');
    filesStore.setServiceEdit(null);
    router
      .push({
        name: 'files-replace-service',
        params: {
          id: currentId, // Usamos el id actual
          service_id: currentServiceId, // Usamos el service_id actual
        },
      })
      .then(() => {
        window.location.reload();
      });
  };

  const returnToProgram = () => {
    localStorage.removeItem('stepsData');
    localStorage.removeItem('currentStep');
    filesStore.setServiceEdit(null);
    router
      .push({ name: 'files-edit', params: { id: router.currentRoute.value.params.id } })
      .then(() => {
        window.location.reload();
      });
  };

  const searchSuppliers = debounce(async (value) => {
    if (value.trim()) {
      await serviceMaskStore.fetchSuppliers({ filter: value, limit: 10 });

      if (serviceMaskStore.notFoundSupplier) {
        formState.supplierCode = null; // Limpiar el valor seleccionado si no hay resultados
      }
    } else {
      formState.supplierCode = null; // Limpia el valor si no hay texto
      serviceMaskStore.suppliers = []; // Limpia las opciones
    }
  }, 300);

  const goToNextStep = () => {
    emit('complete');
    emit('nextStep');
  };

  const handleSupplierChange = (supplier: any) => {
    if (supplier) {
      let supplier_name = supplier.label.split(' - ')[1].trim();
      supplierSelected.value = {
        value: supplier.value,
        label: supplier.label,
        supplier_name: supplier_name,
      };
    }
  };

  const handleFormSubmit = async () => {
    try {
      await formRefSupplier.value?.validate();
      serviceMaskStore.setServiceMaskSupplier(supplierSelected.value);
      goToNextStep();
    } catch (errorInfo) {
      console.log(errorInfo);
      notification.error({
        message: 'Error de validación',
        description: 'Por favor, complete todos los campos requeridos',
      });
    }
  };

  watch(
    () => formState.supplierCode,
    (newValue) => {
      if (newValue != null) {
        serviceMaskStore.setNotFoundSupplier(false);
      }
    },
    { immediate: true }
  );

  onMounted(async () => {
    filesStore.finished();
    const supplier = serviceMaskStore.getServiceMaskSupplier;
    if (supplier.value) {
      formState.supplierCode = supplier;
    }
  });
</script>
<style scoped lang="scss">
  .cart-content {
    background-color: #fafafa;
    padding: 20px;

    ::v-deep(.ant-card-body) {
      border: 0 !important;
    }

    .title-icon {
      font-size: 52px;
    }

    .title-service {
      font-family: Montserrat, sans-serif;
      font-weight: 700;
      font-size: 16px;
    }

    .subtitle-query {
      font-family: Montserrat, sans-serif;
      font-weight: 400;
      font-size: 12px;
    }
  }

  .alert-error-supplier {
    ::v-deep(.ant-alert-message) {
      font-family: Montserrat, sans-serif;
      font-weight: 600 !important;
      font-size: 16px !important;
    }

    ::v-deep(.ant-alert-description) {
      font-family: Montserrat, sans-serif;
      font-weight: 400 !important;
      font-size: 14px !important;
    }
  }

  .alert-info-light-supplier {
    ::v-deep(.ant-alert-info) {
      justify-content: center;
      flex-direction: row;
      flex-wrap: nowrap;
      padding: 20px 50px;
      border: 2px solid #80baff;
      background-color: #ffffff;
      align-items: flex-start;
    }

    ::v-deep(.ant-alert-icon) {
      font-family: Montserrat, sans-serif;
      font-weight: 600 !important;
      font-size: 14px !important;
      background-color: #55a3ff !important;
      width: 23px !important;
      height: 23px !important;
      border-radius: 50% !important;
      display: flex;
      flex-direction: row;
      flex-wrap: nowrap;
      align-content: center;
      justify-content: center;
      align-items: center;
      color: #ffffff !important;
    }

    ::v-deep(.ant-alert-message) {
      font-family: Montserrat, sans-serif;
      font-weight: 600 !important;
      font-size: 14px !important;
      color: #3d3d3d !important;
    }

    ::v-deep(.ant-alert-description) {
      font-family: Montserrat, sans-serif;
      font-weight: 400 !important;
      font-size: 12px !important;
      color: #3d3d3d !important;
      padding-left: 30px;
    }
  }

  .steps-supplier-text {
    font-family: Montserrat, sans-serif;
    font-weight: 600;
    font-size: 16px;
    padding-left: 50px;
  }

  .text-danger {
    font-family: Montserrat, sans-serif;
    color: #eb5757 !important;
  }

  .actions {
    display: flex;
    justify-content: flex-end;
    gap: 25px;
  }

  .text-info {
    font-family: Montserrat, sans-serif;
    font-weight: 500;
    font-size: 16px;
    color: #2e2b9e !important;
  }

  ::v-deep(.ant-alert-message) {
    display: flex;
    align-items: center;
    flex-direction: row;
    gap: 10px;
    align-content: center;
    flex-wrap: nowrap;
  }

  .btn-continue {
    background-color: #eb5757;
    border-color: #fafafa;
    color: #ffffff;
    padding: 12px 24px;
    height: 54px;
  }

  .btn-temporary {
    width: auto;
    height: 45px;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 14px;
    font-weight: 600 !important;
    color: #eb5757 !important;
    border: 1px solid #eb5757 !important;

    svg {
      margin-right: 10px;
    }

    &:hover {
      color: #eb5757 !important;
      background-color: #fff6f6 !important;
      border: 1px solid #eb5757 !important;
    }
  }

  .btn-back {
    width: auto;
    height: 45px;
    padding: 12px 24px 12px 24px;
    display: flex;
    align-items: center;
    flex-wrap: nowrap;
    justify-content: center;
    font-size: 14px;
    font-weight: 600 !important;
    color: #575757 !important;
    border: 1px solid #fafafa !important;

    &:hover {
      color: #575757 !important;
      background-color: #e9e9e9 !important;
      border: 1px solid #e9e9e9 !important;
    }
  }
</style>
