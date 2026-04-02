<template>
  <div class="passenger-card">
    <div class="passenger-card__header">
      <div class="passenger-card__info">
        <UserGroupIcon class="passenger-icon" v-if="icon === 'user-group'" />
        <UserIcon class="passenger-icon" v-if="icon === 'user'" />
        <ChildIcon class="passenger-icon" v-if="icon === 'child'" />

        <div>
          <span class="passenger-type">{{ title }}</span>
          <span class="passenger-age">{{ subtitle }}</span>
        </div>
      </div>

      <div v-if="allowDiscount" class="discount-block">
        <div>
          <span class="discount-label mr-2">% descuento</span>
          <a-switch v-model:checked="discountEnabled" size="small" />
        </div>
        <a-input-number
          v-if="discountEnabled"
          v-model:value="discountPercent"
          size="large"
          class="discount-input"
          :min="0"
          :max="100"
          :formatter="(v: number) => `% ${v}`"
          :parser="(v: string) => v.replace('%', '')"
        />
      </div>
    </div>

    <div class="passenger-card__body">
      <div v-if="discountEnabled" class="net-row">
        <span class="label">Tarifa neta ($)</span>
        <span class="net-value">{{ format(effectiveRate) }}</span>
      </div>

      <template v-else>
        <label class="label">Tarifa neta ($)</label>
        <a-input-number
          v-model:value="rate"
          size="large"
          class="full-width"
          :min="0"
          :precision="2"
        />
      </template>

      <div class="breakdown">
        <div class="break-row">
          <span>Servicios ({{ servicePercent }}%)</span>
          <span>{{ format(serviceValue) }}</span>
        </div>

        <div class="break-row">
          <span>IGV ({{ igvPercent }}%)</span>
          <span>{{ format(igvValue) }}</span>
        </div>

        <div class="break-row total">
          <span>Total</span>
          <span>{{ format(total) }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import {
    UserGroupIcon,
    UserIcon,
    ChildIcon,
  } from '@/modules/negotiations/products/configuration/content/pricingPlans/icons';

  const props = defineProps({
    title: String,
    subtitle: String,
    icon: String,
    allowDiscount: Boolean,
    servicePercent: Number,
    igvPercent: Number,
  });

  const rate = defineModel<number | null>('rate');
  const discountEnabled = defineModel<boolean>('discountEnabled');
  const discountPercent = defineModel<number>('discountPercent');

  const effectiveRate = computed(() => {
    if (!discountEnabled.value) return rate.value || 0;
    return (rate.value || 0) - ((rate.value || 0) * (discountPercent.value || 0)) / 100;
  });

  const serviceValue = computed(() => (effectiveRate.value * (props.servicePercent || 0)) / 100);
  const igvValue = computed(() => (effectiveRate.value * (props.igvPercent || 0)) / 100);

  const total = computed(() => effectiveRate.value + serviceValue.value + igvValue.value);

  function format(v: number) {
    return v.toFixed(2);
  }
</script>

<style scoped lang="scss">
  .passenger-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
  }

  .passenger-card__header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
  }

  .passenger-card__info {
    display: flex;
    gap: 10px;
    align-items: center;
  }

  .passenger-icon {
    font-size: 20px;
    color: #2f353a;
  }

  .passenger-type {
    font-weight: 600;
    font-size: 15px;
    display: block;
  }

  .passenger-age {
    font-size: 12px;
    color: #7e8285;
  }

  .discount-block {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
  }

  .discount-label {
    font-size: 12px;
    color: #7e8285;
  }

  .discount-input {
    width: 70px;
  }

  .label {
    font-weight: 500;
    font-size: 13px;
    color: #2f353a;
    display: block;
    margin-bottom: 4px;
  }

  .breakdown {
    margin-top: 10px;
  }

  .break-row {
    display: flex;
    justify-content: space-between;
    font-size: 13px;
    color: #7e8285;
    padding: 4px 0;
  }

  .break-row.total {
    font-weight: 600;
    color: #2f353a;
    border-top: 1px solid #e0e0e0;
    margin-top: 6px;
    padding-top: 6px;
  }

  .full-width {
    width: 100%;
  }

  .net-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .net-value {
    font-weight: 600;
  }
  .mr-2 {
    margin-right: 8px;
  }
</style>
