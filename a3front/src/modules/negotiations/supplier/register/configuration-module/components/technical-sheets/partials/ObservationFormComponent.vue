<template>
  <a-form layout="vertical" :model="formState" ref="formRefObservations" :rules="formRules">
    <a-form-item name="observations">
      <template #label>
        <span class="custom-primary-font"> Observaciones: </span>
      </template>
      <a-textarea
        v-model:value="formState.observations"
        :rows="3"
        :maxlength="150"
        placeholder="Ingrese las observaciones (máximo 150 caracteres)"
        class="textarea-custom"
      />
      <div class="observations-info position-observation">
        <span>{{ formState.observations?.length ?? 0 }} / 150</span>
      </div>
    </a-form-item>
  </a-form>
</template>
<script setup lang="ts">
  import type { FormInstance } from 'ant-design-vue';
  import { ref } from 'vue';

  defineProps({
    formState: {
      type: Object,
      required: true,
    },
    formRules: {
      type: Object,
      required: true,
    },
  });

  const formRefObservations = ref<FormInstance | null>(null);

  defineExpose({
    validate: () => formRefObservations.value?.validate(),
    resetFields: () => formRefObservations.value?.resetFields(),
  });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .position-observation {
    text-align: right;
    margin-top: 5px;
    margin-bottom: -20px; // ajusta mensaje de validacion
  }
</style>
