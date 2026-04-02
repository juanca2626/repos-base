<template>
  <a-row>
    <a-col>
      <a-tag
        v-if="!record.confirmation_code_for_tickets || record.confirmation_code_for_tickets == ''"
        color="warning"
        :bordered="true"
      >
        <b>SIN COMPRA</b>
      </a-tag>
      <a-tag v-else color="success" :bordered="true"> <b>COMPRADO</b> </a-tag>
    </a-col>
  </a-row>
</template>

<script setup>
  defineProps({
    record: {
      type: String,
      default: () => {},
    },
  });
</script>

<style lang="scss">
  #reservations-modal {
    .ant-modal-body {
      padding-right: 0 !important;
      padding-left: 0 !important;
    }
  }
</style>
