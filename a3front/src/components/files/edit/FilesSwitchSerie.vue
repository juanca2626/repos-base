<template>
  <span class="files-switch-serie" v-if="!isEditing">
    <div v-if="selectedSerie === ''">
      <span
        :class="[!isEditing ? 'text-gray' : 'text-danger', 'cursor-pointer']"
        @click="isEditing = !isEditing"
      >
        <font-awesome-icon :icon="['fas', 'toggle-off']" size="2xl" v-if="!isEditing" />
        <font-awesome-icon :icon="['fas', 'toggle-on']" size="2xl" v-else />
      </span>
      <span
        class="files-switch-serie-label cursor-pointer"
        @click="isEditing = !isEditing"
        :class="{ 'opacity-50': !isEditable }"
      >
        {{ !isEditing ? labelUnchecked : labelChecked }}
      </span>
    </div>
    <div v-else class="files-switch-serie-selected">
      <div class="files-switch-serie-selected-label">
        {{ t('files.label.serie') }} - {{ selectedSerie }}
      </div>
      <div class="escoger-series-icon" @click="clean">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="16" fill="none">
          <path
            fill="#C4C4C4"
            d="M7.438 4.484V.5h-5.86a.701.701 0 0 0-.703.703v13.594c0 .39.313.703.703.703h9.844c.39 0 .703-.313.703-.703v-9.61H8.141a.705.705 0 0 1-.704-.703Zm1.76 3.12-1.76 2.74 1.76 2.739a.352.352 0 0 1-.296.542H7.88a.353.353 0 0 1-.31-.185c-.575-1.06-1.07-2.012-1.07-2.012-.188.433-.293.586-1.072 2.015a.348.348 0 0 1-.308.185H4.098a.352.352 0 0 1-.296-.542l1.766-2.74-1.766-2.739a.351.351 0 0 1 .296-.542h1.02c.128 0 .248.07.31.185.764 1.43.586.984 1.072 2.007 0 0 .179-.343 1.072-2.007a.353.353 0 0 1 .31-.185h1.02a.35.35 0 0 1 .296.54Zm2.927-3.533v.179h-3.75V.5h.179c.187 0 .366.073.498.205l2.868 2.871a.7.7 0 0 1 .205.495Z"
          />
        </svg>
      </div>
    </div>
  </span>
  <template v-else>
    <div class="escoger-series" style="min-width: 150px">
      <div class="escoger-series-select d-block w-100">
        <div class="escoger-series-header">
          {{ t('files.label.select_serie') }}
          <div class="escoger-series-icon" @click="save">
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="16" fill="none">
              <path
                fill="#C4C4C4"
                d="M7.438 4.484V.5h-5.86a.701.701 0 0 0-.703.703v13.594c0 .39.313.703.703.703h9.844c.39 0 .703-.313.703-.703v-9.61H8.141a.705.705 0 0 1-.704-.703Zm1.76 3.12-1.76 2.74 1.76 2.739a.352.352 0 0 1-.296.542H7.88a.353.353 0 0 1-.31-.185c-.575-1.06-1.07-2.012-1.07-2.012-.188.433-.293.586-1.072 2.015a.348.348 0 0 1-.308.185H4.098a.352.352 0 0 1-.296-.542l1.766-2.74-1.766-2.739a.351.351 0 0 1 .296-.542h1.02c.128 0 .248.07.31.185.764 1.43.586.984 1.072 2.007 0 0 .179-.343 1.072-2.007a.353.353 0 0 1 .31-.185h1.02a.35.35 0 0 1 .296.54Zm2.927-3.533v.179h-3.75V.5h.179c.187 0 .366.073.498.205l2.868 2.871a.7.7 0 0 1 .205.495Z"
              />
            </svg>
          </div>
        </div>
        <div class="escoger-series-list">
          <div @click="selectSerie('B1H13')">Serie 01</div>
          <div @click="selectSerie('B1H14')">Serie 02</div>
          <div @click="selectSerie('B1H15')">Serie 03</div>
        </div>
      </div>
    </div>
  </template>
</template>

<script setup>
  // TODO: Este componente debe ser parte de los componentes reusables como reusables/BaseSwitch.vue
  import { ref } from 'vue';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const isEditing = ref(false);
  const selectedSerie = ref('');

  defineProps({
    labelUnchecked: {
      type: String,
      default: '',
    },
    labelChecked: {
      type: String,
      default: '',
    },
    isEditable: true,
  });

  const save = () => {
    isEditing.value = !isEditing.value;
  };

  const selectSerie = (serieName) => {
    selectedSerie.value = serieName;
    save();
  };

  const clean = () => {
    selectedSerie.value = '';
  };
</script>

<style scoped lang="scss">
  .files-switch-serie {
    display: inline-flex;
    justify-content: center;
    align-items: center;
    gap: 5px;
    color: #5c5ab4;
    font-style: normal;
    font-weight: 600;
    font-size: 12px;
    line-height: 19px;
    letter-spacing: 0.015em;

    &-label {
      color: #575757;
      padding-left: 5px;
    }

    .ant-switch-handle {
      top: 2px !important;
    }
  }
  .escoger-series {
    position: relative;
    color: #eb5757;
    filter: drop-shadow(0px 4px 8px rgba(16, 24, 40, 0.25));
    z-index: 9999;
    min-width: 150px;

    &-select {
      position: absolute;
      top: 0;
      left: 0;
      z-index: 10;
      width: 100%;
      background-color: #fff;
    }

    &-header {
      display: flex;
      justify-content: space-between;
      width: 100%;
      height: 38px;
      background-color: #fff;
      padding: 8px 8px 8px 10px;
      align-items: center;
      font-weight: 700;
      font-size: 10px;
      line-height: 17px;
    }

    &-icon {
      width: 28px;
      height: 28px;
      background: #fff2f2;
      border-radius: 3px;
      display: flex;
      justify-content: center;
      align-items: center;
      cursor: pointer;
    }

    &-list div {
      padding: 12px 16px 12px 16px;
      font-weight: 600;
      font-size: 12px;
      line-height: 19px;
      letter-spacing: 0.015em;
      color: #212529;
    }

    &-list div:hover {
      background-color: #eb5757;
      color: #fff;
      cursor: pointer;
    }

    &-list {
      overflow: hidden;
      overflow-y: auto;
      max-height: 250px;
    }

    &-list::-webkit-scrollbar {
      width: 0.8em;
    }

    &-list::-webkit-scrollbar-track {
    }

    &-list::-webkit-scrollbar-thumb {
      background-color: #c4c4c4;
      border-radius: 8px;
    }
  }
  .files-switch-serie-selected {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    width: 136px;
    font-weight: 700;
    font-size: 10px;
    line-height: 17px;
    color: #5c5ab4;

    &-label {
      margin-right: 10px;
    }
  }
  .opacity-50 {
    opacity: 0.5;
  }
</style>
