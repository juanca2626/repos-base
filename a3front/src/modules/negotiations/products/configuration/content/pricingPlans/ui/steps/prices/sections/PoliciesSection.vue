<template>
  <div class="policies-card">
    <div class="policies-header">
      <span class="policies-title">Políticas asociadas:</span>
      <RepeatIcon @click="reloadPolicies" class="policies-refresh" />
    </div>

    <div class="policies-tags">
      <div v-for="policy in policies" :key="policy.id" class="policy-tag">
        <font-awesome-icon :icon="['fas', 'file-lines']" class="policy-icon" />
        <span class="policy-name">{{ policy.name }}</span>
        <span class="policy-separator">•</span>
        <font-awesome-icon :icon="['fas', 'users']" class="policy-users" />
        <span class="policy-passengers">{{ policy.passengers }}</span>
      </div>
    </div>
  </div>

  <PolicyChangeDrawer v-model:open="drawerOpen" />
</template>

<script setup lang="ts">
  import { ref } from 'vue';
  import { RepeatIcon } from '@/modules/negotiations/products/configuration/content/pricingPlans/icons';
  import PolicyChangeDrawer from './PolicyChangeDrawer.vue';

  interface Props {
    model: any;
  }

  withDefaults(defineProps<Props>(), {
    model: () => ({
      policies: [],
    }),
  });
  // const props = defineProps<Props>()

  interface Policy {
    id: string;
    name: string;
    passengers: string;
  }

  const policies = ref<Policy[]>([
    { id: '1', name: 'Política general', passengers: '1 - 99 pasajeros' },
    { id: '2', name: 'Política grupos', passengers: '1 - 99 pasajeros' },
    { id: '3', name: 'Política Fits: Travel Ja Vu', passengers: '1 - 15 pasajeros' },
  ]);

  const drawerOpen = ref(false);

  function reloadPolicies() {
    drawerOpen.value = true;
  }
</script>

<style scoped lang="scss">
  .policies-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    background: #f9f9f9;
  }

  .policies-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
  }

  .policies-title {
    font-weight: 600;
    font-size: 14px;
    color: #2f353a;
  }

  .policies-refresh {
    cursor: pointer;
  }

  .policies-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
  }

  .policy-tag {
    display: flex;
    align-items: center;
    gap: 6px;
    background: #fff;
    border: 1px solid #e8e8e8;
    border-radius: 6px;
    padding: 6px 12px;
    font-size: 13px;
  }

  .policy-icon {
    color: #575b5f;
    font-size: 14px;
  }

  .policy-name {
    font-weight: 500;
    color: #2f353a;
  }

  .policy-separator {
    color: #9e9e9e;
  }

  .policy-users {
    color: #9e9e9e;
    font-size: 13px;
  }

  .policy-passengers {
    color: #9e9e9e;
  }
</style>
