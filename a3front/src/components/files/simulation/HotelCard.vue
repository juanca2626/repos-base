<template>
  <a-card class="hotel-card m-3" style="width: 500px">
    <a-row type="flex" justify="space-between" class="w-100 title-bordered">
      <a-col :span="18">
        <div class="hotel-info">
          <i class="bi bi-building-fill me-2"></i>
          <span class="ellipsis">
            <slot name="name">...</slot>
          </span>
          <a-tag color="magenta" style="margin-left: 10px">
            <slot name="category">...</slot>
          </a-tag>
        </div>
      </a-col>
    </a-row>

    <a-row class="hotel-details">
      <a-col :span="12">
        <div class="detail-item">
          <font-awesome-icon icon="fa-regular fa-calendar" class="me-2" />
          <b><slot name="date_in"></slot> | <slot name="date_out"></slot></b>
        </div>
      </a-col>
      <a-col :span="12">
        <div class="detail-item">
          <b
            ><span class="text-danger"><slot name="nights">0</slot></span>
            {{ t('global.label.nights') }}</b
          >
        </div>
      </a-col>
    </a-row>

    <a-row class="room-details">
      <a-col :span="18">
        <div class="detail-item">
          <b><slot name="quantity_rooms">0</slot> {{ t('global.label.room') }}:</b>
          <span
            >&nbsp;<slot name="type_room"><span class="text-uppercase">...</span></slot></span
          >
        </div>
      </a-col>
      <a-col :span="6">
        <div class="availability" v-if="!on_request">
          <font-awesome-icon :icon="['far', 'circle-check']" size="xl" class="text-success" />
        </div>
      </a-col>
    </a-row>
  </a-card>
</template>

<script setup>
  import { useI18n } from 'vue-i18n';

  defineProps({
    on_request: {
      type: Boolean,
      default: false,
    },
  });

  const { t } = useI18n({
    useScope: 'global',
  });
</script>

<style lang="scss">
  .hotel-card {
    border: 1px dashed #ccc !important;
    padding: 16px;
    border-radius: 8px;
    background-color: #f1f8ff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);

    .ant-card-body {
      border: 0 !important;
      padding: 0 !important;
    }

    .title-bordered {
      padding-bottom: 0.5rem;
      border-bottom: 1px solid #373737;
    }
  }

  .hotel-info {
    display: flex;
    align-items: center;
    font-size: 18px;
    font-weight: 500;
  }

  .hotel-details,
  .room-details {
    margin-top: 16px;
  }

  .detail-item {
    display: flex;
    align-items: center;
    font-size: 16px;
    color: #595959;
  }

  .availability {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    font-size: 16px;
    color: #52c41a;
  }
</style>
