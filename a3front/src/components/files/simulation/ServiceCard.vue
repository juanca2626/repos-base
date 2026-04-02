<template>
  <a-card class="service-card m-3" style="width: 400px">
    <a-row class="service-details">
      <a-col :span="12">
        <div class="detail-item">
          <font-awesome-icon icon="fa-regular fa-calendar" class="me-2" />
          <b>
            <slot name="date_in">...</slot>
          </b>
        </div>
      </a-col>
      <a-col :span="12">
        <div class="info-link">
          <a href="javascript:;" v-on:click="showInformation" class="info-text text-capitalize"
            >{{ t('global.label.information') }} {{ t('global.label.of') }}
            {{ t('files.button.service') }}</a
          >
        </div>
      </a-col>
    </a-row>

    <a-row class="description-row">
      <a-col :span="24">
        <div class="detail-item">
          <a-icon type="car" style="margin-right: 8px" />
          <span class="ellipsis">
            <slot name="name">...</slot>
          </span>
        </div>
      </a-col>
    </a-row>

    <a-row class="price-row">
      <a-col :span="24">
        <div class="price-details">
          <template v-if="props.flag_old_price">
            <span class="old-price"><slot name="old_price">-</slot></span>
            <font-awesome-icon :icon="['fas', 'arrow-right']" class="mx-2" />
          </template>
          <span class="new-price"><slot name="new_price">-</slot></span>
        </div>
      </a-col>
    </a-row>
  </a-card>

  <a-modal v-model:visible="modalInformation" :width="800">
    <template #title>
      <div class="text-left px-4 pt-4">
        <h6 class="mb-0">{{ serviceDetail.name }}</h6>
        <a-tag
          color="#EB5757"
          style="
            position: absolute;
            top: 0px;
            right: 120px;
            border-top-left-radius: 0;
            border-top-right-radius: 0;
            padding: 7px 15px;
            font-size: 18px;
            font-weight: 500;
          "
        >
          {{ serviceDetail.service_type.name }}
        </a-tag>
      </div>
    </template>
    <div class="px-2">
      <a-row :gutter="24" type="flex" justify="space-between" align="top">
        <a-col :span="15">
          <p class="text-700">Operatividad</p>
          <p class="mb-0">Sistema horario de 24 horas</p>
          <p>
            {{ serviceDetail.operations.turns[0].departure_time }}
            {{ serviceDetail.operations.turns[0].shifts_available }}
          </p>
        </a-col>
        <a-col :span="9">
          <template v-if="serviceDetail.inclusions.length > 0">
            <p>
              <b>Incluye</b>
            </p>
            <p>
              <template v-for="inclusion in serviceDetail.inclusions">
                <a-tag v-for="item in inclusion.include" class="mb-2">{{ item.name }}</a-tag>
              </template>
            </p>
          </template>
          <p>
            <b>Disponibilidad</b>
          </p>
          <a-row type="flex" justify="space-between" align="top" style="gap: 5px">
            <a-col>
              <svg
                width="24"
                height="25"
                viewBox="0 0 24 25"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  d="M22 11.2049V12.1249C21.9988 14.2813 21.3005 16.3795 20.0093 18.1067C18.7182 19.8338 16.9033 21.0973 14.8354 21.7088C12.7674 22.3202 10.5573 22.2468 8.53447 21.4994C6.51168 20.7521 4.78465 19.371 3.61096 17.5619C2.43727 15.7529 1.87979 13.6129 2.02168 11.4612C2.16356 9.30943 2.99721 7.26119 4.39828 5.62194C5.79935 3.98268 7.69279 2.84025 9.79619 2.36501C11.8996 1.88977 14.1003 2.1072 16.07 2.98486"
                  stroke="#1ED790"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
                <path
                  d="M22 4.125L12 14.135L9 11.135"
                  stroke="#1ED790"
                  stroke-width="2"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />
              </svg>
            </a-col>
            <a-col>
              <p class="mb-1">Dias:</p>
              <template v-if="Object.values(serviceDetail.operations.days).length == 7">
                Todos los días
              </template>
              <template v-else>
                <p class="m-0" v-for="(day, d) in serviceDetail.operations.days">
                  {{ d }}
                </p>
              </template>
            </a-col>
            <a-col>
              <p class="mb-1">Horario</p>
              <template v-if="Object.values(serviceDetail.operations.days).length == 7">
                <p class="text-danger text-400 mb-0">
                  {{ serviceDetail.operations.schedule[0]['friday'] }}.
                </p>
              </template>
              <template v-else>
                <p class="m-0" v-for="(day, d) in serviceDetail.operations.schedule">{{ d }}.</p>
              </template>
            </a-col>
          </a-row>
        </a-col>
      </a-row>
    </div>
    <template #footer></template>
  </a-modal>
</template>

<script setup>
  import { ref } from 'vue';
  import { useFilesStore } from '@store/files';
  import { useI18n } from 'vue-i18n';

  const filesStore = useFilesStore();

  const props = defineProps({
    flag_old_price: {
      type: Boolean,
      default: false,
    },
    service: {
      type: Object,
      default: {},
    },
  });

  const { t } = useI18n({
    useScope: 'global',
  });

  const modalInformation = ref(false);
  const serviceDetail = ref({});

  const showInformation = async () => {
    console.log('SERVICE: ', props.service);
    const _service = props.service;
    await filesStore.findServiceInformation(
      _service.id,
      _service.date_reserve,
      parseInt(_service.quantity_adult || 0) + parseInt(_service.quantity_child || 0)
    );

    serviceDetail.value = filesStore.getServiceInformation;

    setTimeout(() => {
      modalInformation.value = true;
    }, 100);
  };
</script>

<style lang="scss">
  .service-card {
    border: 1px dashed #ccc !important;
    background-color: #fff;
    padding: 16px;
    border-radius: 8px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);

    .ant-card-body {
      border: 0 !important;
      padding: 0 !important;
    }
  }

  .service-details {
    display: flex;
    align-items: center;
    font-size: 16px;
  }

  .info-link {
    text-align: right;
  }

  .info-text {
    color: #ff4d4f;
    font-weight: 500;
  }

  .description-row {
    margin-top: 16px;
  }

  .detail-item {
    display: flex;
    align-items: center;
    font-size: 16px;
    color: #595959;
  }

  .price-row {
    margin-top: 16px;
  }

  .price-details {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .old-price {
    color: #ff4d4f;
    text-decoration: line-through;
    font-size: 18px;
  }

  .new-price {
    color: #ff4d4f;
    font-size: 18px;
    font-weight: 500;
  }
</style>
