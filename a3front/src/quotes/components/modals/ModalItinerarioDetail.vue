<script lang="ts" setup>
  import { toRef } from 'vue';

  import ContentHotel from '@/quotes/components/modals/ContentHotel.vue';
  import ContentExtension from '@/quotes/components/modals/ContentExtension.vue';
  import ContentQuoteDetail from '@/quotes/components/modals/ContentQuoteDetail.vue';
  import type { Hotel } from '@/quotes/interfaces';
  import type { ServiceExtensionsResponse } from '@/quotes/interfaces/services';
  import ModalComponent from '@/quotes/components/global/ModalComponent.vue';
  import IconClose from '@/quotes/components/icons/IconClose.vue';
  import type { Service } from '@/quotes/interfaces/services';

  interface Props {
    show: boolean;
    type: string;
    category: string;
    categoryName: string;
    title: string;
    hotel: Hotel;
    extension: ServiceExtensionsResponse;
    service?: Service;
    serviceDateOut?: Date | string;
    section?: string;
  }

  const props = withDefaults(defineProps<Props>(), {
    show: false,
    type: 'transfers',
    category: undefined,
    title: '',
    categoryName: '',
    hotel: undefined,
    extension: undefined,
    service: undefined,
    section: 'default',
  });

  const hotel = toRef(props, 'hotel');
  const extension = toRef(props, 'extension');
  const service = toRef(props, 'service');
  const show = toRef(props, 'show');

  // Emits
  interface Emits {
    (e: 'close'): void;
  }

  const emits = defineEmits<Emits>();

  const onClose = () => {
    emits('close');
  };
</script>

<template>
  <ModalComponent v-if="show" :modalActive="show" class="modal-itinerario-detail" @close="onClose">
    <template #body>
      <div class="icon-close-modal" @click="onClose">
        <icon-close />
        <!-- {{ props.type }} - {{ props.category }} - {{ props.categoryName }} -->
      </div>
      <ContentHotel
        v-if="props.type == 'group_header' && hotel"
        :hotel="hotel"
        :flag-remarks="true"
      />
      <ContentExtension v-else-if="props.type == 'extension' && extension" :extension="extension" />

      <template v-if="props.type == 'service'">
        <ContentQuoteDetail
          :title="props.title"
          :service="service!"
          :service-date="serviceDateOut!"
          :categoryName="categoryName"
          :section="props.section"
          :flagRemarks="true"
        />
      </template>
    </template>
  </ModalComponent>
</template>

<style lang="scss" scoped></style>
