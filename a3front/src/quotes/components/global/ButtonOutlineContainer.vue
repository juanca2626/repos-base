<template>
  <button :disabled="disabled" class="button-outline">
    <div class="button-outline-container">
      <div class="content">
        <div v-if="props.icon" class="icon">
          <!--<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none">
            <path d="M19 12H5" stroke="#EB5757" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            <path d="M12 19L5 12L12 5" stroke="#EB5757" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
          </svg>-->
          <font-awesome-icon :icon="props.icon" />
        </div>
        <div class="text">{{ text }}</div>
        <div v-if="props.afterIcon" class="icon">
          <font-awesome-icon :icon="props.afterIcon" />
        </div>
      </div>
    </div>
  </button>
</template>
<script lang="ts" setup>
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';

  interface Props {
    icon?: string;
    afterIcon?: string;
    text: string;
    disabled?: boolean;
  }

  const props = defineProps<Props>();
</script>

<style lang="scss" scoped>
  .button-outline {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    gap: 10px;
    border: none;
    background: transparent;

    &:disabled {
      .button-outline-container {
        border-color: #d9d9d9;
        cursor: not-allowed;
        background: #f5f5f5;

        .icon svg,
        .text {
          color: #bfbfbf;
        }
      }
    }

    .button-outline-container {
      display: flex;
      height: 45px;
      padding: 12px 24px;
      align-items: center;
      border-radius: 6px;
      border: 1px solid #eb5757;
      cursor: pointer;

      &:hover {
        background: rgba(255, 225, 225, 0.4);
      }

      .content {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 10px;

        .icon {
          width: 24px;
          height: 24px;
          font-size: 24px;
          line-height: 24px;

          svg {
            color: #eb5757;
          }
        }

        .text {
          color: #eb5757;
          text-align: center;
          font-size: 14px;
          font-style: normal;
          font-weight: 600;
          line-height: 21px;
          letter-spacing: 0.21px;
        }
      }
    }
  }
</style>
