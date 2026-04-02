<script lang="ts" setup>
  import { computed, toRef } from 'vue';
  import HotelsEditComponent from './search/HotelsEditComponent.vue';
  import ExtensionEditComponent from './search/ExtensionEditComponent.vue';
  import { useQuote } from '@/quotes/composables/useQuote';
  import type { QuoteService } from '@/quotes/interfaces';
  import ServiceEditComponent from '@/quotes/components/search/ServiceEditComponent.vue';
  import IconClose from '@/quotes/components/icons/IconClose.vue';
  import { useSiderBarStore } from '../store/sidebar';

  const storeSidebar = useSiderBarStore();
  const props = defineProps<{ page: string }>();

  const page = toRef(props, 'page');

  const { serviceSelected, deleteServiceSelected } = useQuote();

  const service = computed(() => serviceSelected.value.service as QuoteService);
  const name = computed(() => {
    if (page.value == 'service_edit') {
      return service.value.service?.service_translations[0].name;
    }
    if (page.value == 'hotel_edit') {
      return service.value.hotel?.name;
    }
    if (page.value == 'extension_edit') {
      return (
        service.value.service?.new_extension.translations[0].name +
        ' ' +
        (service.value.service?.new_extension.nights + 1) +
        'D / ' +
        service.value.service?.new_extension.nights +
        'N'
      );
    }
  });

  const closeSidebar = () => {
    storeSidebar.setStatus(false, '', '');
    deleteServiceSelected();
  };
</script>

<template>
  <div class="sidebar edit">
    <div class="header">
      <h2 :title="name" :class="page">{{ name }}</h2>
      <div class="icon-close-modal">
        <icon-close @click="closeSidebar()" />
      </div>
    </div>
    <div id="bodyPage" class="body">
      <HotelsEditComponent v-if="page == 'hotel_edit'" />
      <ServiceEditComponent v-if="page == 'service_edit'" />
      <ExtensionEditComponent v-if="page == 'extension_edit'" />
    </div>
  </div>
</template>

<style lang="scss" scoped></style>
