<template>
  <div class="button-download-container">
    <ButtonComponent
      after-icon="chevron-down"
      class="button-download"
      :text="t('quote.label.download')"
      type="outline"
      :disabled="props.disabled"
      @click="toggleForm"
      >{{ t('quote.label.download') }}
    </ButtonComponent>
    <div v-if="showForm" :class="{ openDownload: showForm }" class="box">
      <DropDownSelectComponent
        v-if="!hideDropdown"
        :items="props.items"
        :multi="false"
        @click="toggleForm"
        @selected="selectedItem"
      />
    </div>
  </div>
</template>
<script lang="ts" setup>
  import DropDownSelectComponent from '@/quotes/components/global/DropDownSelectComponent.vue';
  import { reactive, ref, watch } from 'vue';
  import ButtonComponent from '@/quotes/components/global/ButtonComponent.vue';
  import { usePopup } from '@/quotes/composables/usePopup';
  import { useI18n } from 'vue-i18n';
  import { storeToRefs } from 'pinia';
  import { useQuoteStore } from '@/quotes/store/quote.store';

  const { t } = useI18n();

  const quoteStore = useQuoteStore();
  const { quote } = storeToRefs(quoteStore);

  const { showForm, toggleForm } = usePopup();

  const hideDropdown = ref(false);

  // const { quote } = useQuote();

  watch(
    () => quote.value?.age_child,
    (ageChildren) => {
      hideDropdown.value = ageChildren?.some((child) => child.age === 0) ?? false;
    },
    { immediate: true, deep: true }
  );

  interface downloadItem {
    label: string;
    value: string;
  }

  interface downloadItems {
    [key: string]: downloadItem[];
  }

  interface Props {
    items: downloadItems;
    disabled?: boolean;
  }

  const props = withDefaults(defineProps<Props>(), {
    disabled: false,
  });

  const emit = defineEmits(['selected']);

  const state = reactive({
    isOpen: false,
  });

  const selectedItem = (item: downloadItem) => {
    state.isOpen = false;
    emit('selected', item);
  };
</script>

<style lang="sass">
  .button-download-container
    position: relative

    .box
      position: absolute
      display: none

      &.openDownload
        display: block
        z-index: 2
</style>
