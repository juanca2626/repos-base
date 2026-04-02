<template>
  <a-drawer
    v-model:open="open"
    :width="500"
    :maskClosable="false"
    :keyboard="false"
    class="policy-change-drawer"
    @close="handleClose"
  >
    <template #title>
      <div class="drawer-title-block">
        <div class="drawer-title">Cambiar política de tarifa</div>
        <div class="drawer-subtitle">Elimina las actuales y establece nuevas políticas</div>
      </div>
    </template>

    <a-alert type="warning" show-icon closable class="drawer-alert">
      <template #message>
        <span class="alert-title">Atención</span>
      </template>
      <template #description>
        <span>
          Cambiar la política desactiva la asignación automática.<br />
          Acepta ser responsable de posibles errores.
        </span>
      </template>
    </a-alert>

    <div class="drawer-field">
      <label class="drawer-label">Actual:</label>
      <a-select
        v-model:value="currentPolicy"
        placeholder="Selecciona"
        size="large"
        class="full-width"
        allow-clear
      >
        <a-select-option v-for="p in policyOptions" :key="p.id" :value="p.id">
          <span class="policy-option-name">{{ p.name }}</span>
          <span class="policy-option-detail"> • {{ p.passengers }}</span>
        </a-select-option>
      </a-select>

      <PolicyDetails :details="currentPolicyDetails" />
    </div>

    <div class="drawer-field">
      <label class="drawer-label">Nueva política</label>
      <a-select
        v-model:value="newPolicy"
        placeholder="Selecciona"
        size="large"
        class="full-width"
        allow-clear
      >
        <a-select-option v-for="p in policyOptions" :key="p.id" :value="p.id">
          <span class="policy-option-name">{{ p.name }}</span>
          <span class="policy-option-detail"> • {{ p.passengers }}</span>
        </a-select-option>
      </a-select>

      <PolicyDetails :details="newPolicyDetails" />
    </div>

    <div class="drawer-footer">
      <a-button class="button-cancel-white" @click="handleClose"> Cancelar </a-button>

      <a-button class="button-action-danger" :disabled="!newPolicy" @click="applyPolicy">
        Cambiar
      </a-button>
    </div>
  </a-drawer>
</template>

<script setup lang="ts">
  import { ref, computed } from 'vue';
  import PolicyDetails from './PolicyDetails.vue';

  const open = defineModel<boolean>('open');

  interface Policy {
    id: string;
    name: string;
    passengers: string;
  }

  const policyOptions: Policy[] = [
    { id: '1', name: 'Política general', passengers: '1 - 99 pasajeros' },
    { id: '2', name: 'Política grupos', passengers: '1 - 99 pasajeros' },
    { id: '3', name: 'Política Fits: Travel Ja Vu', passengers: '1 - 15 pasajeros' },
  ];

  interface PolicyDetail {
    color: string;
    label: string;
    value: string;
  }

  const policyDetailsMap: Record<string, PolicyDetail[]> = {
    canada: [
      {
        color: '#1284ED',
        label: 'Condición de pago:',
        value: 'Prepago a 15 días antes del servicio',
      },
      {
        color: '#E53935',
        label: 'Cancelación:',
        value: "15 días antes del servicio, <strong style='color:#E53935'>penalidad 100%</strong>",
      },
      {
        color: '#2F353A',
        label: 'Reconfirmación:',
        value: 'Confirmación a 15 días antes del servicio',
      },
      {
        color: '#43A047',
        label: 'Liberados:',
        value: "Por cada 20 pasajeros, <strong style='color:#43A047'>2 liberados</strong>",
      },
      {
        color: '#000',
        label: 'Edades:',
        value: 'Niños + 3 años - Infantes: 0 a 2 años',
      },
    ],
  };

  const currentPolicy = ref<string | null>(null);
  const newPolicy = ref<string | null>(null);

  const currentPolicyDetails = computed(() => {
    if (!currentPolicy.value) return null;
    return policyDetailsMap['canada'] || null;
  });

  const newPolicyDetails = computed(() => {
    if (!newPolicy.value) return null;
    return policyDetailsMap['canada'] || null;
  });

  function handleClose() {
    open.value = false;
    currentPolicy.value = null;
    newPolicy.value = null;
  }

  function applyPolicy() {
    console.log('change policy', currentPolicy.value, '->', newPolicy.value);
    handleClose();
  }
</script>

<style scoped lang="scss">
  .policy-change-drawer .drawer-title {
    font-weight: 600;
    font-size: 18px;
    color: #2f353a;
  }

  .drawer-subtitle {
    font-size: 14px;
    color: #9e9e9e;
    margin-top: 4px;
  }

  .drawer-alert {
    margin-bottom: 16px;
  }

  .drawer-field {
    margin-top: 20px;
  }

  .drawer-label {
    display: block;
    font-weight: 500;
    font-size: 14px;
    margin-bottom: 6px;
    color: #2f353a;
  }

  .policy-option-name {
    font-weight: 500;
    color: #2f353a;
  }

  .policy-option-detail {
    color: #9e9e9e;
  }

  .drawer-footer {
    display: flex;
    justify-content: flex-end;
    gap: 16px;
    margin-top: 28px;
    border-top: 1px solid #e8e8e8;
    padding-top: 16px;
  }

  .button-cancel-white {
    height: 44px;
    border-radius: 6px;
    border: 1px solid #2f353a;
    background: #fff;
    color: #2f353a;
  }

  .button-action-danger {
    height: 44px;
    border-radius: 6px;
    background: #bd0d12;
    border-color: #bd0d12;
    color: #fff;
  }

  .full-width {
    width: 100%;
  }

  .alert-title {
    font-weight: 600;
    color: #2f353a;
  }
</style>
