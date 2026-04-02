<template>
  <div class="period-group-card">
    <!-- Botón cerrar grupo -->
    <div v-if="totalGroups > 1" class="remove-group-btn" @click="$emit('removeGroup', groupIndex)">
      <font-awesome-icon :icon="['fas', 'times']" />
    </div>

    <!-- RANGOS -->
    <PeriodRangeRow
      v-for="(range, rangeIndex) in group.ranges"
      :key="rangeIndex"
      :range="range"
      :rangeIndex="Number(rangeIndex)"
      :group="group"
      :groupIndex="Number(groupIndex)"
      :totalRanges="group.ranges.length"
      :periodTypeOptions="periodTypeOptions"
      @addRange="addRange(group)"
      @removeRange="removeRange(group, $event)"
      :getDisabledDate="getDisabledDate"
    />
  </div>
</template>

<script setup lang="ts">
  import PeriodRangeRow from './PeriodRangeRow.vue';

  defineProps<{
    group: any;
    groupIndex: number;
    totalGroups: number;
    periodTypeOptions: any;
    getDisabledDate: any;
    addRange: (group: any) => void;
    removeRange: (group: any, index: number) => void;
  }>();

  defineEmits(['removeGroup']);
</script>

<style scoped>
  .period-group-card {
    position: relative;
    background: #f5f6f8;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
  }

  .remove-group-btn {
    position: absolute;
    top: 12px;
    right: 16px;
    cursor: pointer;
    font-size: 16px;
    color: #7e8285;
  }
</style>
