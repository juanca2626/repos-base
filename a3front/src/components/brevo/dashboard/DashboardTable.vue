<template>
  <a-table
    size="small"
    :showHeader="true"
    :columns="columns"
    :pagination="false"
    :data-source="filesStore.getFilePassengers"
  >
    <template #bodyCell="{ column, record }">
      <template v-if="column.key === 'check'">
        <span class="cursor-pointer">
          <i :class="['bi', 'bi-square']" style="font-size: 1.5rem"></i>
        </span>
      </template>
      <template v-if="column.key === 'type_room'">
        <strong>{{ record.type }}</strong>
      </template>
      <template v-if="column.key === 'accommodation'">
        <strong>{{ record.type }}</strong>
      </template>
      <template v-if="column.key === 'price_pax'">
        <strong>{{ record.type }}</strong>
      </template>
    </template>
  </a-table>
</template>
<script setup></script>
