<template>
  <base-card :class="[statusClass, size, activeClass]">
    <div class="card-stats-b" @click="handleClick">
      <div class="card-title">
        <span class="card-title--icon">
          <slot name="icon">
            <font-awesome-icon icon="fa-solid fa-stopwatch" size="1x" />
          </slot>
        </span>
        <span class="card-title--text">
          <slot name="card-title">00 días</slot>
        </span>
      </div>
      <div class="card-label">
        <span>
          <slot name="card-button-label">00 files</slot>
        </span>
        <font-awesome-icon class="card-label--icon" icon="fa-arrow-right" />
      </div>
    </div>
  </base-card>
</template>

<script setup>
  import BaseCard from './BaseCard.vue';

  const emit = defineEmits(['onClick']);

  defineProps({
    statusClass: {
      type: String,
      default: 'isDefault',
    },
    size: {
      type: String,
      default: 'lg',
      validator(sizeValue) {
        return ['sm', 'lg'].includes(sizeValue);
      },
    },
    activeClass: {
      type: String,
      default: 'isDefault',
    },
  });

  const handleClick = () => emit('onClick');
</script>

<style scoped lang="scss">
  .card-stats-b {
    cursor: pointer;
    padding: 1rem 1rem;
    background-color: var(--files-background-1);
    border-radius: 6px;
    & .card-title {
      display: flex;
      justify-content: flex-start;
      align-items: center;
      gap: 0.5rem;
    }
    & .card-title--icon {
      display: flex;
      justify-content: center;
      align-items: center;
      border-radius: 100%;
      width: 28px;
      height: 26px;
    }
    & .card-title--text {
      font-size: 1.3rem;
      font-weight: 700;
    }
    & .card-label {
      display: flex;
      justify-content: space-around;
      align-items: center;
      font-size: 1rem;
      font-weight: 600;
      padding: 0.6rem;
      border-radius: 6px;
      min-width: 135px;
      letter-spacing: -0.015em;
      margin-top: 0.5rem;
    }
  }

  .sm .card-stats-b {
    padding: 0 1rem;
  }

  .isDefault {
    border: 1.5px solid var(--files-default-dark);
    &:hover {
      border-color: var(--files-default-light);
    }
    & .card-stats-b {
      &:hover {
        background-color: var(--files-default-light);
      }
      & .card-title,
      & .card-label--icon {
        color: var(--files-default-dark);
      }
      & .card-label,
      & .card-title--icon {
        background: var(--files-default-light);
      }
    }
    &.sm .card-label {
      display: none;
    }
  }

  .isDanger {
    border: 1.5px solid #bbbdbf;
    &--active {
      border: 1.5px solid #fff6f6;
      & .card-stats-b {
        background-color: var(--files-light-red-3);
        & .card-title,
        & .card-label--icon {
          color: var(--files-error-dark);
        }
        & .card-label,
        & .card-title--icon {
          background: var(--files-error-light) !important;
        }
        & .card-label span {
          color: #fff;
        }
        & .card-title--icon {
          background: var(--files-error-dark) !important;
          color: #fff;
        }
      }
    }
    &:hover {
      border: 1.5px solid #fff6f6;
      background: #fff6f6;
    }
    & .card-stats-b {
      &:hover {
        background-color: var(--files-light-red-3);
      }
      & .card-title,
      & .card-label--icon {
        color: var(--files-error-dark);
      }
      & .card-label,
      & .card-title--icon {
        background: var(--files-light-red-3);
      }
      &:hover .card-label {
        background: var(--files-error-light);
      }
      &:hover .card-label span {
        color: #fff;
      }
      &:hover .card-title--icon {
        background: var(--files-error-dark);
        color: #fff;
      }
    }
    &.sm .card-label {
      display: none;
    }
  }

  .isWarning {
    border: 1.5px solid #bbbdbf;
    &--active {
      border: 1.5px solid #fffbdb;
      & .card-stats-b {
        background-color: var(--files-warning-light);
        & .card-title,
        & .card-label--icon {
          color: var(--files-warning-dark);
        }
        & .card-label,
        & .card-title--icon {
          background: rgba(255, 218, 11, 0.3) !important;
        }
        & .card-label span {
          color: #fff;
        }
        & .card-title--icon {
          background: var(--files-warning-dark) !important;
          color: #fff;
        }
      }
    }
    &:hover {
      border: 1.5px solid #fffbdb;
      background: #fffbdb;
    }
    & .card-stats-b {
      &:hover {
        background-color: var(--files-warning-light);
      }
      & .card-title,
      & .card-label--icon {
        color: var(--files-warning-dark);
      }
      & .card-label,
      & .card-title--icon {
        background: var(--files-warning-light);
      }
      &:hover .card-label {
        background: rgba(255, 218, 11, 0.3);
      }
      &:hover .card-label span {
        color: #fff;
      }
      &:hover .card-title--icon {
        background: var(--files-warning-dark);
        color: #fff;
      }
    }
    &.sm .card-label {
      display: none;
    }
  }

  .isSuccess {
    border: 1.5px solid #bbbdbf;
    &--active {
      border: 1.5px solid #ededff;
      & .card-stats-b {
        background-color: #ededff;
        & .card-title,
        & .card-label--icon {
          color: #5c5ab4;
        }
        & .card-label,
        & .card-title--icon {
          background: #ededff;
        }
        & .card-label {
          background: rgba(149, 116, 175, 0.5) !important;
        }
        & .card-label span {
          color: #fff;
        }
        & .card-title--icon {
          background: #2e2b9e !important;
          color: #fff;
        }
      }
    }
    &:hover {
      border: 1.5px solid #ededff;
      background: #ededff;
    }
    & .card-stats-b {
      &:hover {
        background-color: #ededff;
      }
      & .card-title,
      & .card-label--icon {
        color: #5c5ab4;
      }
      & .card-label,
      & .card-title--icon {
        background: #ededff;
      }
      &:hover .card-label {
        background: rgba(149, 116, 175, 0.5);
      }
      &:hover .card-label span {
        color: #fff;
      }
      &:hover .card-title--icon {
        background: #2e2b9e;
        color: #fff;
      }
    }
    &.sm .card-label {
      display: none;
    }
  }
</style>
