<template>
  <popover-hover-and-click
    :data="data"
    @onPopoverClick="handlePopoverClickSetVip({ fileVips: data.vips })"
    @onCancel="handleSetVipCancel"
    :editable="editable"
    @onSave="handleSetVipSave({ fileId: data.id, vipsSaved: data.vips })"
    :hide-content-hover="data.vips.length === 0"
  >
    <template #default="{ isVip, vips }">
      <span class="group-vip">
        <template v-if="vipsStore.isLoading">
          <font-awesome-icon :icon="['fas', 'spinner']" spin-pulse />
        </template>
        <template v-else>
          <font-awesome-icon
            v-if="vips.length > 0"
            class="is-vip text-warning"
            icon="fa-solid fa-star"
          />
          <font-awesome-icon v-else icon="fa-regular fa-star" />
        </template>
      </span>
    </template>
    <template #content-hover="{ vips }" v-if="!vipsStore.isLoading">
      <span v-if="vips.length > 0">
        <strong>{{ t('global.label.motive_vip') }}:</strong>
        <!-- <div v-for="vip in vips" :key="vip.id">{{ vip.vipName }}</div> -->
        <div v-if="vips.length > 0">{{ vips.at(-1).vipName }}</div>
      </span>
      <template v-else>&nbsp;</template>
    </template>
    <template #content-click="{ fileNumber }" v-if="!vipsStore.isLoading">
      <div class="vip-content-click" v-if="editable">
        <div class="vip-content-click-title">
          FILE {{ fileNumber }} - {{ t('global.label.set') }} VIP
        </div>
        <a-form class="form-vip" :model="formVip">
          <base-select
            style="width: 100%"
            name="vipSelected"
            :label="t('global.label.choose_motive')"
            :placeholder="t('global.label.select_option')"
            size="large"
            :disabled="formVip.vipHasAnotherReason"
            :allowClear="false"
            :loading="vipsStore.isLoading"
            :comma="false"
            :options="vipsStore.getCustomVips"
            v-model:value="formVip.vipSelected"
          />
          <a-form-item :colon="false" style="margin-bottom: 0">
            <template #label>
              <a-checkbox v-model:checked="formVip.vipHasAnotherReason"
                >{{ t('global.label.other_motive') }}&nbsp;&nbsp;</a-checkbox
              >
            </template>
            <base-input
              name="vipAnotherReason"
              :placeholder="t('global.label.specify_motive')"
              size="large"
              width="210"
              :disabled="!formVip.vipHasAnotherReason"
              v-model="formVip.vipAnotherReason"
            />
          </a-form-item>
        </a-form>
      </div>
    </template>
  </popover-hover-and-click>
</template>

<script setup>
  import { reactive } from 'vue';
  import { useVipsStore } from '@store/files';
  import PopoverHoverAndClick from '@/components/files/reusables/PopoverHoverAndClick.vue';
  import BaseSelect from '@/components/files/reusables/BaseSelect.vue';
  import BaseInput from '@/components/files/reusables/BaseInput.vue';
  import { notification } from 'ant-design-vue';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const vipsStore = useVipsStore();

  const DEFAULT_FORM_VIP = {
    vipSelected: null,
    vipTemp: null,
    vipHasAnotherReason: false,
    vipAnotherReason: '',
  };

  const formVip = reactive(DEFAULT_FORM_VIP);

  // const emit = defineEmits(['onRefreshCache']);

  const props = defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
    editable: {
      type: Boolean,
      default: () => true,
    },
  });

  const handlePopoverClickSetVip = ({ fileVips }) => {
    if (props.editable) {
      const DEFAULT_VIP_INDEX = -1;
      formVip.value = DEFAULT_FORM_VIP;
      formVip.vipHasAnotherReason = false;
      const vip = fileVips?.at(DEFAULT_VIP_INDEX);
      formVip.vipSelected = vip?.vipId;
      formVip.vipTemp = vip?.vipId;
    }
  };

  const handleSetVipCancel = () => {
    // console.log('handleSetVipCancel...')
  };

  const handleSetVipSave = ({ fileId, vipsSaved }) => {
    const EMPTY_VIP = undefined;

    const isNew = formVip.vipTemp === EMPTY_VIP;
    const isUpdate = !isNew;
    const isDelete = !formVip.vipSelected && Boolean(fileId);

    const vipId = formVip.vipSelected;

    if (formVip.vipHasAnotherReason) {
      const vipName = formVip.vipAnotherReason;
      vipsStore
        .createVipRelated({ fileId, vipName })
        .then((response) => {
          if (!response.error) {
            vipsStore.inited();
          }
        })
        .catch(() => {
          notification['error']({
            message: `FILE ${fileId} - Establecer VIP`,
            description: 'No se guardó VIP',
            duration: 8,
          });
        });
      return;
    }

    if (isDelete) {
      const vipRelated = vipsSaved.find((vip) => vip.vipId === formVip.vipTemp);

      if (!vipRelated) return;

      vipsStore
        .removeVipAndFileRelated({ fileId, vipId: vipRelated.vipId })
        .then((response) => {
          if (!response.error) {
            vipsStore.inited();
          }
        })
        .catch((error) => {
          console.log(error);
          notification['error']({
            message: `FILE ${fileId} - Remover VIP`,
            description: 'No se removió VIP',
            duration: 8,
          });
        });
      return;
    }

    if (isNew) {
      vipsStore
        .addVipAndFileRelated({ fileId, vipId })
        .then((response) => {
          formVip.value = DEFAULT_FORM_VIP;
          if (!response.error) {
            vipsStore.inited();
          }
        })
        .catch(() => {
          notification['error']({
            message: `FILE ${fileId} - Establecer VIP`,
            description: 'No se guardó VIP',
            duration: 8,
          });
        });
      return;
    }

    if (isUpdate) {
      const vipRelated = vipsSaved.find((vip) => vip.vipId === formVip.vipTemp);

      if (!vipRelated) return;

      const vipsId = vipRelated.id;
      const vipId = formVip.vipSelected;

      vipsStore
        .changeVipAndFileRelated({ fileId, vipsId, vipId })
        .then((response) => {
          if (!response.error) {
            vipsStore.inited();
          }
        })
        .catch(() => {
          notification['error']({
            message: `FILE ${fileId} - Establecer VIP`,
            description: 'No se guardó VIP',
            duration: 8,
          });
        });
    }
  };
</script>

<style scoped lang="scss">
  .form-vip {
    display: flex;
    flex-direction: column;
    gap: 1.75rem;
    margin-bottom: 1.625rem;
  }
  .group-vip {
    cursor: pointer;
    transition: 0.3s ease all;
    .is-vip {
      color: var(--files-exclusives);
    }

    &:hover {
      color: var(--files-exclusives);
    }
  }
  .vip-content-click {
    font-family: var(--files-font-basic);
    width: 410px;
    height: 260px;
    // height: 337px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 3px;
    &-title {
      font-weight: 700;
      font-size: 1rem;
      line-height: 23px;
      padding: 20px 12px 23px;
      text-align: center;
      letter-spacing: -0.015em;
      color: #3d3d3d;
      border-bottom: 1px solid #e9e9e9;
      margin-bottom: 1.8125rem;
    }
  }
</style>
