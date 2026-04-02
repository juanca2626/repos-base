<template>
  <base-card class="has-shadow">
    <div class="card-stats-a-wrap">
      <div class="card-stats-a card-title" style="padding-bottom: 0">
        <slot name="card-icon">
          <div class="box-archive-icon">
            <font-awesome-icon icon="fa-solid fa-box-archive" style="width: 14px; height: 14px" />
          </div>
        </slot>
        <span class="card-title--text">
          <slot name="card-title">
            <div style="display: flex; justify-content: center; align-items: center; gap: 5px">
              {{ t('files.label.all_files') }}
              <a-popover>
                <template #content>
                  <span
                    style="font-weight: 500; font-size: 12px; line-height: 19px; color: #3d3d3d"
                  >
                    {{ t('files.label.files_contemplated_to') }} 45 {{ t('global.label.days') }}
                  </span>
                </template>
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  width="16"
                  height="16"
                  fill="none"
                  stroke="currentColor"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  class="feather feather-help-circle"
                  viewBox="0 0 24 24"
                >
                  <circle cx="12" cy="12" r="10" />
                  <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3M12 17h.01" />
                </svg>
              </a-popover>
            </div>
          </slot>
        </span>
      </div>
      <div class="card-stats-a card-stats-a-flex">
        <div class="card-stats-a-header" @click="handleClick">
          <div class="card-number">
            <slot name="card-number">00</slot>
          </div>
        </div>

        <div class="group-badged">
          <badge-stats type="default">
            <template #badge-title>
              <slot name="badge-title-1">Default</slot>
            </template>
            <template #badge-number>
              <slot name="badge-number-1">00</slot>
            </template>
          </badge-stats>

          <badge-stats type="light">
            <template #badge-title>
              <slot name="badge-title-2">Default</slot>
            </template>
            <template #badge-number>
              <slot name="badge-number-2">00</slot>
            </template>
          </badge-stats>
        </div>
      </div>
    </div>
  </base-card>
</template>

<script setup>
  import BaseCard from './BaseCard.vue';
  import BadgeStats from './BadgeStats.vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n({
    useScope: 'global',
  });

  const emit = defineEmits(['onClick']);

  const handleClick = () => emit('onClick');
</script>

<style scoped lang="scss">
  .has-shadow {
    border: 1px solid #bbbdbf;
  }
  .has-shadow:hover {
    border: 1px solid #fff;
  }
  .card-stats-a {
    min-width: 197px;
    display: flex;
    flex-direction: row;
    justify-content: flex-start;
    align-items: center;
    color: var(--files-default-dark);
    padding: 0 1rem 0 1rem;
    gap: 10px;
    &-wrap {
      padding: 14px 16px 16px 13px;
      background: #fff;
      border-radius: 6px;
    }
    &-flex {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 0.5rem;
    }
    &-header {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      cursor: pointer;
    }
    .card-title {
      display: flex;
      justify-content: flex-start;
      align-items: center;
      gap: 0.3rem;
      align-items: center;
      margin-top: 1rem;
    }
    .card-title--text {
      font-size: 0.8rem;
      font-weight: 600;
    }
    .card-number {
      font-size: 3.5rem;
      line-height: 4rem;
      font-weight: 700;
      letter-spacing: -0.01em;
      color: #575757;
    }

    .group-badged {
      display: flex;
      flex-direction: column;
      gap: 0.35rem;
      width: 80px;
    }
  }

  .card-stats-a-wrap:hover {
    background: #e9e9e9;
  }
  .box-archive-icon {
    width: 26px;
    height: 26px;
    padding: 2px;
    text-align: center;
    border-radius: 100px;
    display: flex;
    justify-content: center;
    align-items: center;
    animation-duration: all 3s ease;
  }
  .card-stats-a-wrap:hover .box-archive-icon {
    width: 26px;
    height: 26px;
    background-color: #4f4b4b;
    color: #fff;
    padding: 2px;
    text-align: center;
    border-radius: 100px;
    display: flex;
    justify-content: center;
    align-items: center;
  }
</style>
