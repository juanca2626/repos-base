<template>
  <a-card :class="['service-card', cardStyle]" :bordered="true">
    <div class="service-content">
      <a-avatar size="large" class="service-icon" :class="iconStyle" style="line-height: 45px">
        <template v-if="action === 'unassigned'">
          <font-awesome-icon
            :icon="['fas', 'list-check']"
            color="#1284ED"
            style="font-size: 20px"
          />
        </template>

        <template v-else-if="action === 'confirmed'">
          <font-awesome-icon
            :icon="['far', 'circle-check']"
            color="#07DF81"
            style="font-size: 20px"
          />
        </template>

        <template v-else-if="action === 'unconfirmed'">
          <font-awesome-icon :icon="['far', 'clock']" color="#F99500" style="font-size: 20px" />
        </template>

        <template v-else-if="action === 'canceled'">
          <font-awesome-icon :icon="['fas', 'ban']" color="#FF3B3B" style="font-size: 20px" />
        </template>

        <template v-else-if="action === 'without_service_order'">
          <font-awesome-icon :icon="['far', 'envelope']" color="#5C5AB4" style="font-size: 20px" />
        </template>

        <template v-else-if="action === 'no_report'">
          <WarningOutlined style="font-size: 20px; color: #ffcc00" />
        </template>

        <template v-else>
          <svg
            width="19"
            height="20"
            viewBox="0 0 19 20"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <g clip-path="url(#clip0_12129_20813)">
              <path
                d="M8.14624 3.56247L1.44082 14.7566C1.30257 14.996 1.22942 15.2675 1.22864 15.544C1.22787 15.8204 1.2995 16.0923 1.43641 16.3325C1.57331 16.5727 1.77073 16.7728 2.009 16.913C2.24728 17.0532 2.51812 17.1286 2.79457 17.1316H16.2054C16.4819 17.1286 16.7527 17.0532 16.991 16.913C17.2292 16.7728 17.4267 16.5727 17.5636 16.3325C17.7005 16.0923 17.7721 15.8204 17.7713 15.544C17.7706 15.2675 17.6974 14.996 17.5592 14.7566L10.8537 3.56247C10.7126 3.3298 10.5139 3.13743 10.2768 3.00393C10.0396 2.87043 9.77211 2.80029 9.49999 2.80029C9.22786 2.80029 8.96033 2.87043 8.72321 3.00393C8.48608 3.13743 8.28737 3.3298 8.14624 3.56247V3.56247Z"
                stroke="#7E8285"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M9.5 13.9648H9.50792"
                stroke="#7E8285"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <path
                d="M9.5 7.63159V10.7983"
                stroke="#7E8285"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </g>
            <defs>
              <clipPath id="clip0_12129_20813">
                <rect width="19" height="19" fill="white" transform="translate(0 0.506592)" />
              </clipPath>
            </defs>
          </svg>
        </template>
      </a-avatar>
      <div>
        <div :class="['service-title', titleStyle]">
          <!-- <span @click="handleClick(action)" style="cursor: pointer"> -->
          <span @click="" style="cursor: pointer">
            {{ total }}
            <template v-if="total === 1"> servicio </template>
            <template v-else> servicios </template>
          </span>
        </div>
        <div class="service-subtitle">{{ subtitle }}</div>
      </div>
    </div>
    <div class="service-buttons">
      <template v-if="module === 'service-management'">
        <a-button
          v-for="(btn, index) in buttons"
          :key="index"
          type="default"
          @click="formServiceManagementStore.handleClick(action, btn.type)"
          class="service-button"
          :class="btn.style"
        >
          <ArrowRightOutlined /> {{ btn.text }}
        </a-button>
      </template>
      <template v-else-if="module === 'providers'">
        <a-button
          v-for="(btn, index) in buttons"
          :key="index"
          type="default"
          size="large"
          @click="formProvidersStore.handleClick(action, btn.type)"
          class="service-button"
          style="padding: 0 10px"
          :class="btn.style"
        >
          <a-flex justify="space-between" align="center">
            <span style="font-weight: 600">{{ btn.text }}</span>
            <ArrowRightOutlined style="color: #bd0d12; font-weight: bold" />
          </a-flex>
        </a-button>
      </template>
      <template v-else></template>
    </div>
  </a-card>
</template>

<script setup lang="ts">
  import { defineProps } from 'vue';
  import { ArrowRightOutlined, WarningOutlined } from '@ant-design/icons-vue';

  import { useFormStore as useFormServiceManagementStore } from '@operations/modules/service-management/store/form.store';
  import { useFormStore as useFormProvidersStore } from '@operations/modules/providers/store/form.store';
  const formServiceManagementStore = useFormServiceManagementStore();
  const formProvidersStore = useFormProvidersStore();

  defineProps({
    module: { type: String, required: true, default: 'service-management' },
    action: { type: String, required: true },
    subtitle: { type: String, default: '' },
    buttons: { type: Array, required: true },
    cardStyle: { type: String, default: '' },
    iconStyle: { type: String, default: '' },
    titleStyle: { type: String, default: '' },
    total: { type: Number, default: 0 },
  });

  // const handleClick = (action: string, type = null) => {
  //   console.log('🚀 ~ handleClick ~ type:', type);
  //   console.log('🚀 ~ handleClick ~ action:', action);
  //   if (action === 'upcoming') {
  //     const startDate = dayjs().add(2, 'days');
  //     const endDate = dayjs().add(5, 'days'); // 5 días después
  //     formStore.updateDateRange(startDate, endDate);
  //   } else {
  //     formStore.updateExtraParams(action, type);
  //   }
  //   formStore.fetchServicesWithParams();
  //   const url = formStore.generateUrl('/search');
  //   console.log('🚀 ~ handleClick ~ url:', url);
  // };
</script>

<style lang="scss" scoped>
  .service-card {
    // width: 230px;
    // height: 110px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: space-between;
    border-radius: 4px;
    background-color: transparent !important;
    // padding: 0px 8px;
    // gap: 16px;

    // Estilos de los diferentes estados del card
    &.default {
      border-color: #2f353a;
    }
    &.blue {
      border-color: #004e96;
    }
    &.orange {
      border-color: #f97800;
    }
    &.red {
      border-color: #d80404;
    }
    &.purple {
      border-color: #2e2b9e;
    }
    &.green {
      border-color: #07df81;
    }
    &.yellow {
      border-color: #ffcc00;
    }

    .service-content {
      display: flex;
      align-items: center;
      justify-content: left;
      margin-bottom: 8px;

      .service-icon {
        margin-right: 8px;

        // Estilos de los diferentes iconos
        &.default {
          background-color: #f0f0f0;
        }
        &.blue {
          background-color: #e6f7ff;
        }
        &.orange {
          background-color: #fff7e6;
        }
        &.red {
          background-color: #fff1f0;
        }
        &.purple {
          background-color: #f9f0ff;
        }
        &.green {
          background-color: #e6fff7;
        }
        &.yellow {
          background-color: #fffbdb;
        }
      }

      .service-title {
        font-size: 20px;
        font-weight: bold;

        // Estilos de los diferentes títulos
        &.default {
          color: #2f353a;
        }
        &.blue {
          color: #004e96;
        }
        &.orange {
          color: #f97800;
        }
        &.red {
          color: #d80404;
        }
        &.purple {
          color: #2e2b9e;
        }
        &.green {
          color: #00a15b;
        }
        &.yellow {
          color: #e4b804;
        }
      }

      .service-subtitle {
        font-size: 12px;
        color: #8c8c8c;
      }
    }

    .service-buttons {
      display: flex;
      justify-content: center;
      align-items: center;

      .service-button {
        font-size: 14px;
        margin: 0 4px;
        font-weight: 600;
        padding: 2px 5px;
      }

      // Estilos de los diferentes botones
      .default {
        width: 190px;
        color: #2f353a;
        background-color: #f2f2f2;
        border-color: #f2f2f2;
        &:hover {
          border-color: #f2f2f2;
        }
      }

      .blue {
        color: #004e96;
        background-color: #ebf5ff;
        border-color: #ebf5ff;
        &:hover {
          border-color: #ebf5ff;
        }
      }
      .orange {
        color: #f97800;
        background-color: #fff2dd;
        border-color: #fff2dd;
        &:hover {
          border-color: #fff2dd;
        }
      }
      .red {
        color: #d80404;
        background-color: #fff2f2;
        border-color: #fff2f2;
        &:hover {
          border-color: #fff2f2;
        }
      }
      .purple {
        color: #2e2b9e;
        background-color: #ededff;
        border-color: #ededff;
        &:hover {
          border-color: #ededff;
        }
      }
      .green {
        color: #07df81;
        background-color: #e6fff7;
        border-color: #e6fff7;
        &:hover {
          border-color: #e6fff7;
        }
      }
      .yellow {
        color: #ffcc00;
        background-color: #fffbdb;
        border-color: #fffbdb;
        &:hover {
          border-color: #fffbdb;
        }
      }
    }

    ::v-deep(.ant-card-body) {
      padding: 0 !important; // Ajusta el padding según tus necesidades
    }
  }
</style>
