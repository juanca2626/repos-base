<template>
  <div>
    <template v-if="isAdmin">
      <a-tabs v-model:activeKey="activeKey">
        <a-tab-pane key="1" tab="Vista resumen">
          <DashboardResultsClient :composable="dashboardsComposable" />
        </a-tab-pane>
        <a-tab-pane key="2" tab="Vista completa" force-render>
          <DashboardResults :composable="dashboardsComposable" />
        </a-tab-pane>
      </a-tabs>
    </template>

    <DashboardResultsClient v-else-if="isClient" :composable="dashboardsComposable" />
  </div>
</template>
<script setup>
  import { computed, ref } from 'vue';
  import { useDashboards } from '../composables/useDashboards';
  import DashboardResults from '../components/DashboardResults.vue';
  import DashboardResultsClient from '../components/DashboardResultsClient.vue';

  const activeKey = ref('1');

  const dashboardsComposable = useDashboards();
  const userType = localStorage.getItem('type');
  const isAdmin = computed(() => userType === '3');
  const isClient = computed(() => userType === '4');
</script>
