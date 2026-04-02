<template>
  <a-drawer
    :open="showDrawerForm"
    :width="424"
    :maskClosable="false"
    :keyboard="false"
    @close="handleClose"
    class="custom-filter-form-drawer hotel-drawer-list-dashboard"
  >
    <template #title>
      <div class="drawer-header">
        <div class="header-content">
          <div class="title-container">
            <IconMapPin class="icon-map-pin" />
            <span class="custom-title">{{ drawerTitle }}</span>
          </div>
          <div class="sub-title-container">
            <span class="sub-title">{{ drawerSubTitle }}</span>
          </div>
        </div>
      </div>
    </template>

    <div class="drawer-body">
      <!-- Hyperguest Section -->
      <div v-if="hyperguestData?.length > 0" class="section-container">
        <div class="section-header">
          <div class="section-title-wrapper">
            <div class="section-icon hyperguest-icon"></div>
            <div class="section-title">Hyperguest - {{ hyperguestData?.length }} hoteles</div>
          </div>
          <div class="percentage-badge">{{ percentageHyperguest }}%</div>
        </div>
        <div class="body-content">
          <div class="body-content-item" v-for="item in hyperguestData" :key="item.hotel_id">
            <div class="content-item-title">{{ item.hotel_name }}</div>
            <div class="badge-category">{{ item?.category || 'Categoría LITO' }}</div>
          </div>
        </div>
      </div>

      <!-- Aurora Section -->
      <div v-if="auroraData?.length > 0" class="section-container">
        <div class="section-header">
          <div class="section-title-wrapper">
            <div class="section-icon aurora-icon"></div>
            <div class="section-title">Aurora - {{ auroraData?.length }} hoteles</div>
          </div>
          <div class="percentage-badge">{{ percentageAurora }}%</div>
        </div>
        <div class="body-content">
          <div class="body-content-item" v-for="item in auroraData" :key="item.hotel_id">
            <div class="content-item-title">{{ item.hotel_name }}</div>
            <div class="badge-category">{{ item?.category || 'Categoría LITO' }}</div>
          </div>
        </div>
      </div>

      <!-- Ambos Section -->
      <div v-if="bothData?.length > 0" class="section-container">
        <div class="section-header">
          <div class="section-title-wrapper">
            <div class="section-icon ambos-icon"></div>
            <div class="section-title">Ambos - {{ bothData?.length }} hoteles</div>
          </div>
          <div class="percentage-badge">{{ percentageAmbos }}%</div>
        </div>
        <div class="body-content">
          <div class="body-content-item" v-for="item in bothData" :key="item.hotel_id">
            <div class="content-item-title">{{ item.hotel_name }}</div>
            <div class="badge-category">{{ item?.category || 'Categoría LITO' }}</div>
          </div>
        </div>
      </div>
    </div>
  </a-drawer>
</template>

<script setup lang="ts">
  import IconMapPin from '@/modules/negotiations/hotels/quotas/icons/icon-map-pin.vue';

  defineProps({
    showDrawerForm: {
      type: Boolean,
      required: true,
    },
    drawerTitle: {
      type: String,
      required: true,
    },
    drawerSubTitle: {
      type: String,
      required: true,
    },
    percentageAurora: {
      type: String,
      required: false,
    },
    percentageHyperguest: {
      type: String,
      required: false,
    },
    percentageAmbos: {
      type: String,
      required: false,
    },
    hyperguestData: {
      type: Object,
      required: false,
    },
    auroraData: {
      type: Object,
      required: false,
    },
    bothData: {
      type: Object,
      required: false,
    },
  });

  const emit = defineEmits(['closeDrawerForm']);

  const handleClose = () => {
    emit('closeDrawerForm', false);
  };
</script>

<style lang="scss">
  @import '@/scss/components/negotiations/_supplierList.scss';
</style>
<style lang="scss" scoped>
  @import '@/scss/_variables.scss';

  .custom-filter-form-drawer {
    :deep(.ant-drawer-body) {
      padding: 0;
      display: flex;
      flex-direction: column;
      height: 100%;
      overflow: hidden;
      border: none;
      padding-right: 24px;
      padding-left: 24px;
      padding-bottom: 24px;
      padding-top: 0px;
    }

    :deep(.ant-drawer-header) {
      padding: 20px 24px !important;
      padding-bottom: 20px !important;
      border-bottom: 1px solid #e4e5e6;
      position: relative !important;
      display: flex !important;
      flex-direction: row !important;
      justify-content: flex-start !important;
      align-items: flex-start !important;
    }

    :deep(.ant-drawer .ant-drawer-header-title) {
      flex: 1 !important;
      padding-right: 40px !important;
      padding-top: 8px !important;
      margin-right: 0 !important;
      flex-direction: row !important;
      width: 100% !important;
      order: 0 !important;
      display: flex !important;
      align-items: flex-start !important;
    }

    :deep(.ant-drawer-close) {
      position: absolute !important;
      top: 20px !important;
      right: 24px !important;
      left: auto !important;
      bottom: auto !important;
      padding: 0 !important;
      margin: 0 !important;
      font-size: 18px !important;
      color: #2f353a !important;
      width: 32px !important;
      height: 32px !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      z-index: 1000 !important;
      line-height: 1 !important;
      opacity: 1 !important;
      visibility: visible !important;
      order: 999 !important;
      transform: none !important;
      float: none !important;
    }

    :deep(.ant-drawer-close:hover) {
      color: #2f353a !important;
    }

    :deep(.ant-drawer-close-x) {
      width: 32px !important;
      height: 32px !important;
      line-height: 32px !important;
    }
  }

  .drawer-header {
    padding: 0;
    padding-top: 8px;
    flex-shrink: 0;
    width: 100%;
  }

  .header-content {
    display: flex;
    flex-direction: column;
    gap: 3px;
    margin-top: 30px;
  }

  .title-container {
    display: flex;
    align-items: center;
  }

  .icon-map-pin {
    color: #212121;
    font-size: 18px;
    flex-shrink: 0;
  }

  .custom-title {
    font-size: 24px;
    font-weight: 600;
    color: #212121;
    line-height: 1.5;
  }

  .sub-title-container {
    padding-left: 43px;
  }

  .sub-title {
    font-size: 14px;
    font-weight: 400;
    color: #7e8285;
    line-height: 1.5;
  }

  .drawer-body {
    flex: 1;
    overflow-y: auto;
    padding: 20px 0px;
  }

  .section-container {
    margin-bottom: 24px;

    &:last-child {
      margin-bottom: 0;
    }
  }

  .section-header {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    gap: 10px;
  }

  .section-title-wrapper {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .section-icon {
    width: 16px;
    height: 16px;
    border-radius: 50%;
    flex-shrink: 0;
  }

  .hyperguest-icon {
    background-color: #74a7a3;
  }

  .aurora-icon {
    background-color: #6798e8;
  }

  .ambos-icon {
    background-color: #bbade4;
  }

  .section-title {
    font-size: 16px;
    font-weight: 600;
    color: #212121;
  }

  .percentage-badge {
    background-color: #f2f2f2;
    color: #2f353a;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    min-width: 40px;
    text-align: center;
  }

  .body-content-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
  }

  .content-item-title {
    font-size: 12px;
    font-weight: 500;
    color: #2f353a;
    line-height: 1.5;
  }

  .badge-category {
    background-color: #f9f9f9;
    color: #7e8285;
    padding: 4px 10px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
    white-space: nowrap;
    flex-shrink: 0;
    width: 30%;
    text-align: center;
  }

  .anticon {
    font-size: 24px !important;
    color: #2f353a !important;
  }
</style>

<style lang="scss">
  /* Estilo específico solo para HotelDrawerListDashboard */
  [class*='css-dev-only-do-not-override'].ant-drawer.hotel-drawer-list-dashboard
    .ant-drawer-header-title,
  [class*='css-dev-only-do-not-override'].ant-drawer
    .hotel-drawer-list-dashboard
    .ant-drawer-header-title,
  .hotel-drawer-list-dashboard.ant-drawer .ant-drawer-header-title,
  .hotel-drawer-list-dashboard .ant-drawer .ant-drawer-header-title {
    align-items: flex-start !important;
  }
</style>
