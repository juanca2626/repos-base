<template>
  <div class="box-info-supplier">
    <div class="box-label-supplier">Correo asociado a reservas:</div>
    <div class="box-email-supplier" v-for="(email, emailIndex) in emails" :key="emailIndex">
      {{ email }}
      <font-awesome-icon icon="fas xmark" @click="$emit('deleteEmail', type, index, emailIndex)" />
    </div>
    <div class="box-action-supplier" @click="$emit('showModalEmails', emails, index, type)">
      <IconCirclePlus color="#EB5757" width="20px" height="20px" />
    </div>
  </div>
</template>

<script setup lang="ts">
  import { defineEmits, defineProps } from 'vue';
  import IconCirclePlus from '@/components/icons/IconCirclePlus.vue';

  defineProps({
    emails: {
      type: Array,
      required: true,
    },
    index: {
      type: Number,
      required: true,
    },
    type: {
      type: String,
      required: true,
    },
  });

  defineEmits(['deleteEmail', 'showModalEmails']);
</script>

<style scoped lang="scss">
  .box-info-supplier {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 10px;

    .box-label-supplier {
      font-weight: 500;
      color: #4f4b4b;
    }

    .box-email-supplier {
      border: 1px dashed #bbbdbf;
      padding: 1px 8px;
      border-radius: 4px;
      display: inline-flex;
      align-items: center;
      gap: 5px;
      font-size: 14px;
      color: #4f4b4b;

      svg {
        cursor: pointer;
        color: #c4c4c4;
      }
    }

    .box-action-supplier {
      cursor: pointer;
      display: flex;
      align-items: center;
    }
  }
</style>
