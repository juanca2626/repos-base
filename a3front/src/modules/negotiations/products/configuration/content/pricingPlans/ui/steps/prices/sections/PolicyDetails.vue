<template>
  <div v-if="details" class="policy-details">
    <div v-for="(item, index) in details" :key="index" class="policy-row">
      <span class="policy-dot" :style="{ backgroundColor: item.color }"></span>

      <span class="policy-label">
        {{ item.label }}
      </span>

      <span class="policy-value" v-html="item.value"></span>
    </div>
  </div>
</template>

<script setup lang="ts">
  interface PolicyDetail {
    color: string;
    label: string;
    value: string;
  }

  defineProps<{
    details: PolicyDetail[] | null;
  }>();
</script>

<style scoped lang="scss">
  .policy-details {
    margin-top: 16px;
    padding: 16px;
    border: 1px solid #e8e8e8;
    border-radius: 8px;
    background: #fafafa;
    display: flex;
    flex-direction: column;
    gap: 10px;
  }

  .policy-row {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    font-size: 13px;
    line-height: 20px;
  }

  .policy-dot {
    width: 8px;
    height: 8px;
    min-width: 8px;
    border-radius: 50%;
    margin-top: 6px;
  }

  .policy-label {
    font-weight: 600;
    color: #2f353a;
  }

  .policy-value {
    color: #575b5f;
  }
</style>
