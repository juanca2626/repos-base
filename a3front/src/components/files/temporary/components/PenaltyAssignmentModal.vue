<template>
  <div>
    <a-modal
      class="file-modal"
      :open="isOpen"
      @cancel="handleCancel"
      :footer="null"
      :keyboard="!fileServiceStore.isLoading"
      :maskClosable="!fileServiceStore.isLoading"
      :focusTriggerAfterClose="false"
      :width="594"
    >
      <template #title>
        <div class="title-confirm">Penalidades del servicio</div>
        <hr />
      </template>
      <a-row :gutter="16">
        <a-col :span="24">
          <p class="text-center title-confirm__subtitle">
            Estás a punto de crear un servicio temporal
          </p>
        </a-col>
        <a-col :span="24">
          <a-alert class="text-warning" type="warning" show-icon closable>
            <template #icon>
              <IconAlertWarning color="#FFCC00" width="1.0em" height="1.0em" />
            </template>
            <template #message>
              <div class="text-warning-title">Servicio con penalidad</div>
            </template>
            <template #description>
              <div class="text-warning">
                Antes de pasar a la creación del servicio, debe indicar el responsable de los cargos
                por penalidad
              </div>
            </template>
          </a-alert>
        </a-col>
        <a-col :span="24">
          <div class="p-4 border bg-white my-4">
            <a-form
              layout="vertical"
              :model="formPenalty"
              class="mt-4"
              ref="formRef"
              :rules="rules"
            >
              <a-form-item name="asumed_by" label="¿Quién asume la penalidad?">
                <a-select
                  size="large"
                  placeholder="Selecciona"
                  :default-active-first-option="false"
                  :not-found-content="null"
                  :options="filesStore.getAsumedBy"
                  v-model:value="formPenalty.asumed_by"
                  showSearch
                  :filter-option="false"
                  :disabled="filesStore.isLoading || filesStore.isLoadingAsync"
                >
                </a-select>
              </a-form-item>
              <a-form-item
                v-if="formPenalty.asumed_by == 13"
                name="executive_id"
                label="Seleccione la especialista que asume la penalidad"
              >
                <a-select
                  size="large"
                  placeholder="Selecciona"
                  :default-active-first-option="false"
                  :not-found-content="null"
                  :options="executivesStore.getExecutives"
                  v-model:value="formPenalty.executive_id"
                  :field-names="{ label: 'name', value: 'id' }"
                  showSearch
                  :filter-option="false"
                  @search="searchExecutives"
                >
                </a-select>
              </a-form-item>
              <a-form-item
                v-if="formPenalty.asumed_by == 12"
                name="file_id"
                label="Seleccione el file que asume la penalidad"
              >
                <a-select
                  size="large"
                  placeholder="Selecciona"
                  :default-active-first-option="false"
                  :not-found-content="null"
                  :options="filesStore.getFiles"
                  v-model:value="formPenalty.file_id"
                  :field-names="{ label: 'description', value: 'id' }"
                  showSearch
                  :filter-option="false"
                  @search="searchFiles"
                >
                </a-select>
              </a-form-item>
              <a-form-item name="motive" label="Motivo">
                <a-textarea
                  :rows="4"
                  placeholder="Ingrese un motivo"
                  v-model:value="formPenalty.motive"
                />
              </a-form-item>
            </a-form>
          </div>
        </a-col>
        <a-col :span="24">
          <div class="sub-title py-3">¿Desea continuar?</div>
        </a-col>
        <a-col :span="24">
          <a-row :gutter="16">
            <a-col :span="24" class="text-center mt-2">
              <a-button
                @click="handleCancel"
                class="btn-close mx-4"
                :disabled="fileServiceStore.isLoading"
              >
                Cancelar
              </a-button>
              <a-button
                @click="handleContinue"
                class="btn-continue"
                :loading="fileServiceStore.isLoading"
              >
                Continuar
              </a-button>
            </a-col>
          </a-row>
        </a-col>
      </a-row>
    </a-modal>
  </div>
</template>
<script setup lang="ts">
  import { useFileServiceStore } from '@/components/files/temporary/store/useFileServiceStore';
  import IconAlertWarning from '@/components/icons/IconAlertWarning.vue';
  import { useExecutivesStore, useFilesStore } from '@store/files';
  import { onBeforeMount, reactive, ref, watch } from 'vue';
  import { debounce } from 'lodash-es';
  import type { Rule } from 'ant-design-vue/es/form';
  import type { FormInstance } from 'ant-design-vue';
  import { useRouter } from 'vue-router';
  import { useServiceItineraryPenaltyStore } from '@/components/files/temporary/store/serviceItineraryPenaltyStore';

  const filesStore = useFilesStore();
  const executivesStore = useExecutivesStore();
  const formRef = ref<FormInstance | null>(null);
  const router = useRouter();
  const serviceItineraryPenaltyStore = useServiceItineraryPenaltyStore();

  const formPenalty = reactive({
    asumed_by: null,
    executive_id: null,
    file_id: null,
    motive: null,
  });

  defineProps({
    isOpen: {
      type: Boolean,
      required: true,
    },
  });

  const rules: Record<string, Rule[]> = {
    asumed_by: [
      {
        required: true,
        message: 'Debe seleccionar quién asume la penalidad',
        trigger: 'change',
        validator: (_, value) => {
          if (value === null || value === '') {
            return Promise.reject('Debe seleccionar quién asume la penalidad');
          }
          return Promise.resolve();
        },
      },
    ],
    motive: [
      {
        required: true,
        message: 'Debe ingresar el motivo',
        trigger: 'blur',
        validator: (_, value) => {
          if (!value || value.trim() === '') {
            return Promise.reject('Debe ingresar el motivo');
          }
          return Promise.resolve();
        },
      },
    ],
    executive_id: [
      {
        required: true,
        message: 'Debe seleccionar un especialista',
        trigger: 'change',
        validator: (_, value) => {
          if (formPenalty.asumed_by == 13 && !value) {
            return Promise.reject('Debe seleccionar un especialista');
          }
          return Promise.resolve();
        },
      },
    ],
    file_id: [
      {
        required: true,
        message: 'Debe seleccionar un file',
        trigger: 'change',
        validator: (_, value) => {
          if (formPenalty.asumed_by == 12 && !value) {
            return Promise.reject('Debe seleccionar un file');
          }
          return Promise.resolve();
        },
      },
    ],
  };

  const emit = defineEmits(['update:isOpen', 'submit']);
  const fileServiceStore = useFileServiceStore();

  const searchExecutives = debounce(async (value) => {
    if (value != '' || (value == '' && executivesStore.getExecutives.length == 0)) {
      await executivesStore.fetchAll(value);
    }
  }, 300);

  const handleContinue = async () => {
    if (!formRef.value) {
      console.error('Referencia del formulario no encontrada');
    }

    try {
      // Validar el formulario antes de continuar
      await formRef.value?.validate();

      const currentId = router.currentRoute.value.params.id; // Obtenemos el :id desde la URL
      const currentServiceId = router.currentRoute.value.params.service_id; // Obtenemos el :service_id desde la URL
      localStorage.setItem('currentStep', '0');

      serviceItineraryPenaltyStore.addAssumesPenalty({
        hasPenalty: true,
        executive_id: formPenalty.executive_id,
        status_reason_id: formPenalty.asumed_by,
        file_id: formPenalty.file_id,
        motive: formPenalty.motive,
      });

      router.push({
        name: 'files-add-service-temporary',
        params: {
          id: currentId, // Usamos el id actual
          service_id: currentServiceId, // Usamos el service_id actual
        },
      });
    } catch (err) {
      console.error('Validación fallida:', err);
    }
  };

  const searchFiles = debounce(async (value) => {
    if (value != '') {
      await filesStore.fetchAll({ filter: value });
    }
  }, 300);

  const handleCancel = () => {
    if (!fileServiceStore.isLoading) {
      emit('update:isOpen', false);
    }
  };

  watch(
    () => formPenalty.asumed_by,
    (newVal) => {
      if (newVal == 13) {
        rules.executive_id = [
          { required: true, message: 'Seleccione un especialista', trigger: 'change' },
        ];
      } else if (newVal == 12) {
        rules.file_id = [{ required: true, message: 'Seleccione un file', trigger: 'change' }];
      } else {
        rules.executive_id = [];
        rules.file_id = [];
      }
    }
  );

  onBeforeMount(async () => {
    await executivesStore.fetchAll('');
    await filesStore.fetchAsumedBy();
  });
</script>
<style scoped lang="scss">
  .file-modal {
    font-family: Montserrat, serif !important;

    .title-confirm {
      padding-top: 35px;
    }

    .sub-title {
      font-family: Montserrat, serif;
      font-weight: 700;
      font-size: 18px;
      color: #4f4b4b;
      text-align: center;
    }

    .title-confirm__subtitle {
      font-family: Montserrat, serif;
      font-weight: 500;
      font-size: 18px;
      color: #4f4b4b;
    }

    .text-warning-title {
      font-family: Montserrat, serif !important;
      font-weight: 700;
      font-size: 16px;
      color: #e4b804;
    }

    .border {
      border: 1px solid #e9e9e9;
      border-radius: 8px;
    }

    .btn-close {
      font-family: Montserrat, serif;
      font-weight: 500;
      font-size: 17px;
      background-color: #fafafa;
      color: #575757;
      border: 1px solid #fafafa;
      border-radius: 8px;
      width: auto;
      height: 54px !important;

      &:hover {
        background-color: #e9e9e9;
        color: #575757;
        border-color: #e9e9e9;
      }
    }

    .btn-continue {
      font-family: Montserrat, serif;
      font-weight: 500;
      font-size: 17px;
      background-color: #c63838;
      color: #ffffff;
      border: 1px solid #c63838;
      border-radius: 8px;
      width: auto;
      height: 54px;

      &:hover {
        background-color: #c63838;
        color: #ffffff;
        border-color: #c63838;
      }
    }
  }
</style>
