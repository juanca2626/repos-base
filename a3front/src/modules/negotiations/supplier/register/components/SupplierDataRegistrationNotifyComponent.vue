<template>
  <a-modal
    class="supplier-data-registration-notify-modal"
    :open="showModal"
    :closable="false"
    :footer="null"
    @cancel="handleCancel"
  >
    <template #title>
      <div class="container-header">
        <span class="header-title"> Envió de notificación a los colaboradores </span>
        <span @click="handleCancel">
          <font-awesome-icon class="custom-close-icon" :icon="['fa', 'xmark']" />
        </span>
      </div>
    </template>
    <div class="container-body">
      <span class="body-title">
        {{ bodyTitle }}
      </span>
      <div class="container-body-icon">
        <div class="circle-container">
          <div class="circle">
            <font-awesome-icon class="check-icon" :icon="['fa', 'check']" />
          </div>
        </div>
      </div>
      <span class="body-message">
        {{ bodyMessage }}
      </span>
    </div>
  </a-modal>
</template>

<script setup lang="ts">
  defineProps({
    showModal: {
      type: Boolean,
      required: true,
    },
    bodyTitle: {
      type: String,
      required: true,
    },
    bodyMessage: {
      type: String,
      required: true,
    },
  });

  const emit = defineEmits(['update:showModal']);

  const handleCancel = () => {
    emit('update:showModal', false);
  };
</script>
<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .body-title {
    color: $color-black;
    font-size: 18px;
    font-weight: 700;
  }

  .body-message {
    padding: 10px;
    color: $color-black;
    font-size: 18px;
    font-weight: 500;
  }

  .container-body {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
  }

  .container-body-icon {
    padding-top: 30px;
    padding-bottom: 30px;
  }

  .custom-close-icon {
    font-size: 18px;
    color: $color-white;
    cursor: pointer;
  }

  .header-title {
    color: $color-white;
    font-size: 18px;
    font-weight: 700;
  }

  .circle-container {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 120px;
    height: 120px;
  }

  .circle {
    width: 120px;
    height: 120px;
    border-radius: 50%;
    background-color: $color-info-light;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .check-icon {
    font-size: 55px;
    color: #00a15b;
  }

  .container-header {
    padding-left: 20px;
    padding-right: 20px;
    height: 54px;
    background-color: $color-primary-strong;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: space-between;
  }
</style>
<style>
  .supplier-data-registration-notify-modal {
    .ant-modal-content {
      padding: 0 !important;
    }
  }
</style>
