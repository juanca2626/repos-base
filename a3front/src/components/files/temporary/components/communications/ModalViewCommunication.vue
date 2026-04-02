<template>
  <a-modal
    class="modal-communications"
    v-model:open="modal.open"
    :width="720"
    :closable="true"
    :maskClosable="false"
  >
    <template #footer>
      <a-row align="middle" justify="center">
        <a-col>
          <a-button key="button" type="primary" size="large" @click="$emit('close')"
            >Cerrar
          </a-button>
        </a-col>
      </a-row>
    </template>
    <div class="email-communications" v-html="modal.html"></div>
  </a-modal>
</template>

<script setup lang="ts">
  import { defineProps } from 'vue';

  defineProps({
    modal: {
      type: Object,
      required: true,
    },
  });
</script>

<style scoped lang="scss">
  .modal-communications {
    .ant-modal-content {
      padding: 0 !important;

      .ant-modal-body {
        .email-communications {
          font-family: Montserrat, serif;
          font-size: 14px;
          font-weight: 400;
          line-height: 19px;
          letter-spacing: 0.015em;
          color: #3d3d3d;

          h3 {
            font-family: Montserrat, serif;
            font-size: 18px;
            font-weight: 700;
            line-height: 25px;
            letter-spacing: -0.01em;
            color: #3d3d3d;
            margin-bottom: 20px;
          }
        }
      }
    }
  }
</style>
