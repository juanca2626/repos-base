<script lang="ts" setup>
  import type { UnwrapRef } from 'vue';
  import { computed, onMounted, reactive, watchEffect } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  import { notification } from 'ant-design-vue';

  import { useQuote } from '@/quotes/composables/useQuote';
  import IconMagnifyingGlass from '@/quotes/components/icons/IconMagnifyingGlass.vue';
  import type { GroupedServices } from '@/quotes/interfaces';
  import { useSiderBarStore } from '@/quotes/store/sidebar';

  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const state = reactive({
    modalHotelDetail: {
      isOpen: false,
    },
    modalActive: '',
  });

  console.log(state);

  const { serviceSelected: groupedService, updateExtension, unsetServiceEdit } = useQuote();

  const service = computed(() => (groupedService.value as GroupedServices).service);
  const cityName = computed(() => service.value.service?.new_extension.destinations);

  const price = computed(() => {
    let price = 0;
    groupedService.value.extensions.forEach((row) => {
      if (row.type === 'group_header') {
        row.group.forEach((itemGroup) => {
          price =
            Number(itemGroup.import_amount ? itemGroup.import_amount.total : 0) + Number(price);
        });
      } else if (row.type === 'flight') {
        //price = Number(row.service.import.total_amount) + Number(price)
      } else {
        //console.log(row.service.import.total_amount);
        price = Number(row.service.import.total_amount) + Number(price);
      }
    });
    price = price > 0 ? price : '';

    //price = '$120';

    return price;
  });

  interface SearchHotelsForm {
    checkIn: Dayjs | undefined;
  }

  const hotelsFormState: UnwrapRef<SearchHotelsForm> = reactive({
    checkIn: dayjs(),
  });

  onMounted(() => {
    // console.log(service.value.date);
    hotelsFormState.checkIn = dayjs(service.value.date, 'YYYY-MM-DD');
  });

  watchEffect(() => {
    if (service.value) {
      hotelsFormState.checkIn = dayjs(service.value.date, 'YYYY-MM-DD');
    }
  });

  const storeSidebar = useSiderBarStore();

  const updateSelectedService = async () => {
    await updateExtension(
      dayjs(hotelsFormState.checkIn).format('YYYY-MM-DD'),
      service.value.service?.new_extension.id
    )
      .then(() => {
        unsetServiceEdit();
        storeSidebar.setStatus(false, 'search', '');
      })
      .catch((e) => {
        openNotificationWithIcon(e.message);
      });
  };

  const openNotificationWithIcon = (message: string) => {
    notification['error']({
      message: 'Error',
      description: message,
    });
  };
</script>

<template>
  <div>
    <div class="editComponent">
      <div class="place">
        <div>
          <icon-magnifying-glass />
          {{ cityName }}
        </div>
        <!--<div>{{ nights + 1 }}D / {{ nights }}N</div>-->
      </div>

      <div class="description">
        <div class="input-box">
          <a-date-picker
            v-model:value="hotelsFormState.checkIn"
            id="start-date"
            :format="'DD/MM/YYYY'"
          />
        </div>
      </div>
    </div>

    <div class="row-flex">
      <div class="quotes-actions-btn save" @click="updateSelectedService">
        <div class="content">
          <div class="text">{{ t('quote.label.save') }}</div>
        </div>
      </div>

      <div class="price">${{ price }} <span>ADL</span></div>
    </div>
  </div>
</template>

<style lang="scss" scoped>
  .quotes-actions-btn.save .content .text {
    color: #fff !important;
  }

  .row-flex {
    padding: 21px 18px;
    border-top: 1px solid #c4c4c4;
  }

  .description {
    height: 350px;
    padding: 0 16px 0 16px;
  }

  .block {
    display: flex;
    align-items: center;
    gap: 10px;
    width: 65px;
    margin-top: 24px;

    .input {
      display: flex;
      padding: 0 1px;
      flex-direction: column;
      align-items: flex-start;
      gap: 6px;
      width: 100%;

      label {
        color: #575757;
        font-size: 14px;
        font-style: normal;
        font-weight: 500;
        line-height: 21px;
        letter-spacing: 0.21px;
        align-self: stretch;
      }

      .box {
        display: flex;
        align-items: center;
        border: 1px solid #c4c4c4;
        border-radius: 4px;
        gap: 8px;
        width: 100%;
        justify-content: center;
        align-items: center;

        span {
          color: #575757;
          font-size: 14px;
          font-style: normal;
          font-weight: 400;
          line-height: 21px;
          letter-spacing: 0.21px;
        }

        input {
          border: none;
          width: 100%;
        }
      }
    }
  }

  :deep(.ant-picker) {
    width: 100%;
    font-size: 12px;

    .ant-picker-suffix {
      color: #eb5757;
    }
  }
</style>
