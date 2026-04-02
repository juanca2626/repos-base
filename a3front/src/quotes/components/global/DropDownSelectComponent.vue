<template>
  <div class="dropdown-select">
    <div
      v-for="(item, index) in state.items"
      :key="index"
      :class="{ checked: state.selectedItems.includes(item.value) }"
      class="item"
      @click="selectItem(item)"
    >
      <div class="controls">
        <div v-if="props.multi" class="icon">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 20 20"
            fill="none"
          >
            <path
              d="M17.5 4.63086L7.1875 15.2423L2.5 10.4189"
              stroke="white"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
        </div>
        <span> {{ t(item.label) }}</span>
      </div>
    </div>
  </div>
</template>

<script lang="ts" setup>
  import { onMounted, reactive } from 'vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  interface Props {
    items: Item[];
    multi: boolean;
  }

  interface Item {
    label: string;
    value: string;
    selected: boolean;
  }

  interface State {
    items: Item[];
    selectedItems: string[];
  }

  const props = withDefaults(defineProps<Props>(), {
    multi: true,
  });

  const emit = defineEmits(['selected']);

  const state: State = reactive({
    items: [],
    selectedItems: [],
  });

  const selectItem = (item: Item) => {
    const selectedItems = [...state.selectedItems];
    let result;
    if (props.multi) {
      if (selectedItems.includes(item.value)) {
        const i = selectedItems.indexOf(item.value);
        selectedItems.splice(i, 1);
        result = selectedItems;
      } else {
        result = [...selectedItems, item.value];
      }
    } else {
      result = [item.value];
    }
    emit('selected', result);

    state.selectedItems = result;
  };

  onMounted(() => {
    state.items = props.items;

    state.selectedItems = props.items
      ?.filter((item: Item) => item.selected)
      .map((item: Item) => item.value);
  });
</script>

<style lang="scss" scoped>
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
