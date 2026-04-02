<template>
  <div class="form" v-if="schedules.length > 0">
    <!-- <template v-for="(s,index) in schedules" :key="index"> -->
    <div class="time-picker" @click="toggleForm()">
      <!--  v-if="index == 0" -->

      <div class="input-dp" v-if="hours">
        {{ hours ? hours.substring(0, 5) : '' }}
      </div>
      <div class="arrow" v-if="hoursOut">
        <icon-arrow-point-down />
      </div>
      <div class="input-dp" v-if="hoursOut">
        {{ hoursOut }}
      </div>

      <!-- <div class="input-dp">{{ s.services_schedule_detail[0].monday.substring(0, 5) }}</div> -->
      <!-- <template v-if="s.services_schedule_detail[1].monday">
            <div class="arrow">
              <icon-arrow-point-down/>
            </div>
            <div class="input-dp">{{ s.services_schedule_detail[1].monday.substring(0, 5) }}</div>      
          </template> -->

      <div class="clock">
        <icon-clock />
      </div>
    </div>
    <!-- <div v-if="showForm" class="hotel-service">
          <div class="dropdown-select">
            
            <div class="time-picker-2" v-if="index > 0" >
              <div class="input-dp">{{ s.services_schedule_detail[0].monday.substring(0, 5) }}</div>
              <template v-if="s.services_schedule_detail[1].monday">
                <div class="arrow">
                  <icon-arrow-point-down/>
                </div>
                <div class="input-dp">{{ s.services_schedule_detail[1].monday.substring(0, 5) }}</div>
              </template> 
            </div>
          

          </div>
      </div> -->
    <!-- </template> -->
  </div>
</template>
<script lang="ts" setup>
  import { computed, toRef } from 'vue';
  import IconArrowPointDown from '@/quotes/components/icons/iconArrowPointDown.vue';
  import IconClock from '@/quotes/components/icons/IconClock.vue';
  import { usePopup } from '@/quotes/composables/usePopup';
  import moment from 'moment';

  const props = defineProps<{
    schedules: [];
    hours: '';
    duration_id: number;
    duration: '';
  }>();

  const schedules = toRef(props, 'schedules');
  const hours = toRef(props, 'hours');
  const durationId = toRef(props, 'duration_id');
  const duration = toRef(props, 'duration');

  const { toggleForm } = usePopup();
  // const { quote } = useQuote();

  const hoursOut = computed(() => {
    // return durationId.value.toString();
    if (durationId.value == '1') {
      return moment(hours.value, 'HH:mm:ss')
        .add(duration.value * 60, 'minutes')
        .format('HH:mm');
    } else if (durationId.value == '5') {
      return moment(hours.value, 'HH:mm:ss').add(duration.value, 'minutes').format('HH:mm');
    } else {
      return '';
    }
  });
</script>

<style lang="scss" scoped>
  .time-picker-2 {
    display: inline-flex;
    padding: 4px 12px;
    align-items: center;
    border-radius: 6px;
    border: 0px solid #c4c4c4;
    background: #ffffff;

    .input-dp {
      display: flex;
      width: 47.5px;
      padding: 1px 0;
      align-items: center;
      gap: 4px;
    }

    .arrow {
      display: flex;
      width: 32px;
      padding: 1px 8px;
      justify-content: center;
      align-items: center;
      gap: 10px;
      align-self: stretch;
      opacity: 0.8700000047683716;
    }

    .clock {
      display: flex;
      width: 31px;
      padding: 0 8px;
      justify-content: center;
      align-items: center;
      gap: 10px;
      align-self: stretch;
      opacity: 0.8700000047683716;
    }
  }

  .hotel-service {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    border-radius: 0 0 6px 6px;
    background: #fff;
    box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
    position: absolute;
    top: 35px;
  }

  .dropdown-select {
    display: flex;
    width: 260px;
    align-items: flex-start;
    border-radius: 6px;
    background: #ffffff;
    box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
    flex-direction: column;

    .item {
      display: flex;
      padding: 12px 16px;
      flex-direction: column;
      align-items: flex-start;
      gap: 10px;
      align-self: stretch;
      background: #ffffff;
      color: #212529;

      &.checked {
        color: #eb5757;

        .controls {
          .icon {
            border: 1px solid #eb5757;
            background: #eb5757;
            color: #fff;
            border-radius: 3px;
          }
        }
      }

      &:hover {
        cursor: pointer;
        color: #eb5757;

        .controls {
          .icon {
            border: 1px solid #eb5757;
          }
        }
      }

      .controls {
        display: flex;
        align-items: center;
        gap: 10px;
        align-self: stretch;

        .icon {
          border: 1px solid #c4c4c4;
          width: 24px;
          height: 24px;
          color: transparent;
          line-height: 22px;
          padding: 0;
          text-align: center;

          svg {
            display: inline-block;
            vertical-align: middle;
          }
        }

        span {
          font-size: 16px;
          font-style: normal;
          font-weight: 500;
          line-height: 23px;
          letter-spacing: -0.24px;
        }
      }
    }
  }
</style>
