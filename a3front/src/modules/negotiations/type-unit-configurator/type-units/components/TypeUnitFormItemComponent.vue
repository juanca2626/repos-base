<template>
  <a-card :class="{ 'mt-3': index > 0 }">
    <template #title>
      <div class="container-card-title">
        <div>
          <div class="icon-bus-container">
            <IconTourBus width="18px" height="18px" />
            <span class="card-title custom-primary-font">Datos de unidad</span>
          </div>
        </div>
        <div>
          <a-button
            type="link"
            class="delete-button"
            v-if="index > 0"
            @click="deleteTypeUnit(index)"
          >
            <font-awesome-icon :icon="['far', 'trash-can']" />
          </a-button>
        </div>
      </div>
    </template>
    <a-form
      layout="vertical"
      :model="typeUnits[index]"
      ref="formRefTypeUnit"
      :rules="formRules"
      class="mt-3"
    >
      <a-flex gap="middle" horizontal>
        <a-form-item label="Código" name="code">
          <a-input
            placeholder="Ingresa el código de la unidad"
            v-model:value="typeUnits[index].code"
            :maxlength="4"
            @input="typeUnits[index].code = $event.target.value.toUpperCase()"
          />
        </a-form-item>
        <a-form-item label="Nombre" name="name">
          <a-input
            placeholder="Ingresa el nombre de unidad"
            v-model:value="typeUnits[index].name"
          />
        </a-form-item>
      </a-flex>
      <a-form-item>
        <a-checkbox v-model:checked="typeUnits[index].isTrunk">
          Usar la unidad para maletero.
        </a-checkbox>
      </a-form-item>
    </a-form>
  </a-card>
</template>
<script setup lang="ts">
  import { defineProps, defineExpose } from 'vue';
  import { useTypeUnitFormItem } from '@/modules/negotiations/type-unit-configurator/type-units/composables/useTypeUnitFormItem';
  import IconTourBus from '@/components/icons/IconTourBus.vue';

  defineProps({
    index: {
      type: Number,
      required: true,
    },
  });

  const { typeUnits, formRules, formRefTypeUnit, resetFields, validateFields, deleteTypeUnit } =
    useTypeUnitFormItem();

  // expose for parent
  defineExpose({ validateFields, resetFields });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .delete-button {
    color: $color-black-5;
    padding: 0;
    &:hover {
      color: $color-black-5 !important;
      text-decoration: underline;
    }
  }

  .container-card-title {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  .card-title {
    font-size: 14px;
    font-weight: 600;
    color: #2f353a;
  }

  .icon-bus-container {
    display: flex;
    align-items: center;
    gap: 8px;
  }
</style>
