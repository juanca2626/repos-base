<template>
  <a-drawer
    :open="showDrawerForm"
    :width="500"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
  >
    <template #title>
      <span class="custom-primary-font">
        {{ formState.id ? 'Actualizar conductor' : 'Agregar conductor' }}
      </span>
    </template>
    <p class="custom-primary-font form-title">
      Ingresar los siguientes datos para poder {{ formState.id ? 'actualizar' : 'agregar' }} el
      conductor:
    </p>

    <a-spin :spinning="isLoading">
      <a-flex gap="middle" vertical>
        <a-form
          layout="vertical"
          :model="formState"
          class="mt-4"
          ref="formRefTransportDriver"
          :rules="formRules"
        >
          <a-row :gutter="16">
            <a-col class="gutter-row" :span="24">
              <a-form-item name="name">
                <template #label>
                  <span class="custom-primary-font"> Nombres: </span>
                </template>
                <a-input
                  v-model:value="formState.name"
                  placeholder="Ingrese los nombres"
                  :maxlength="45"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="24">
              <a-form-item name="surnames">
                <template #label>
                  <span class="custom-primary-font"> Apellidos: </span>
                </template>
                <a-input
                  v-model:value="formState.surnames"
                  placeholder="Ingrese los apellidos"
                  :maxlength="150"
                />
              </a-form-item>
            </a-col>
            <a-col class="gutter-row" :span="24">
              <a-form-item name="phone">
                <template #label>
                  <span class="custom-primary-font"> Número de celular: </span>
                </template>
                <a-input
                  v-model:value="formState.phone"
                  placeholder="Ingrese el número de celular"
                  :maxLength="9"
                  type="tel"
                />
              </a-form-item>
            </a-col>
          </a-row>
        </a-form>
      </a-flex>
    </a-spin>
    <template #footer>
      <a-row :gutter="10">
        <a-col :span="12">
          <a-button type="primary" class="btn-secondary ant-btn-md w-100" @click="handleClose"
            >Cancelar</a-button
          >
        </a-col>
        <a-col :span="12">
          <a-button
            type="primary"
            class="ant-btn-md w-100"
            @click="handleSubmit()"
            :disabled="isLoading"
          >
            Guardar
          </a-button>
        </a-col>
      </a-row>
    </template>
  </a-drawer>
</template>
<script setup lang="ts">
  import { useTransportDriverForm } from '@/modules/negotiations/supplier/register/configuration-module/composables/technical-sheets/transport-drivers/useTransportDriverForm';

  defineProps({
    showDrawerForm: {
      type: Boolean,
      default: false,
    },
  });

  const emit = defineEmits(['update:showDrawerForm']);

  const { formRefTransportDriver, formState, isLoading, formRules, handleClose, handleSubmit } =
    useTransportDriverForm(emit);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .form-title {
    font-size: 15px;
    font-weight: 400;
    text-align: justify;
  }
</style>
