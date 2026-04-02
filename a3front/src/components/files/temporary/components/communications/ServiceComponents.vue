<template>
  <div class="box-components">
    <a-row
      :gutter="24"
      align="middle"
      justify="space-between"
      class="mt-4"
      v-for="(component, componentIndex) in components"
      :key="componentIndex"
    >
      <a-col :span="19">
        <div class="box-name-service-master">{{ component.name }}</div>
      </a-col>
      <a-col :span="5">
        <div class="box-cost-service-master">
          <IconCircleCheck color="#1ED790" width="20px" height="20px" />
          <span class="box-label-cost-service-master">Costo</span>
          <span class="box-amount-service-master">$ {{ component.amount_cost || 0 }}</span>
        </div>
      </a-col>
    </a-row>
  </div>
</template>

<script setup lang="ts">
  import { defineProps } from 'vue';
  import IconCircleCheck from '@/components/icons/IconCircleCheck.vue';

  defineProps({
    components: {
      type: Array,
      required: true,
    },
  });
</script>

<style scoped lang="scss">
  .box-components {
    .box-name-service-master {
      font-weight: bold;
      color: #3d3d3d;
    }

    .box-cost-service-master {
      display: flex;
      align-items: center;
      gap: 5px;

      .box-label-cost-service-master {
        font-size: 12px;
        font-weight: 500;
        color: #4f4b4b;
      }

      .box-amount-service-master {
        font-size: 16px;
        font-weight: 700;
        color: #4f4b4b;
      }
    }
  }
</style>
