<script lang="ts" setup>
  import DetailBoxComponent from '@/quotes/components/DetailBoxComponent.vue';
  import ContentExtensionComponent from '@/quotes/components/ContentExtensionComponent.vue';
  import { toRef, computed, reactive, ref } from 'vue';
  import type { QuoteService } from '@/quotes/interfaces';
  import { VueDraggableNext as Draggable } from 'vue-draggable-next';
  import { useQuote } from '@/quotes/composables/useQuote';
  // import { useI18n } from 'vue-i18n';
  import { useDateConflictCheck } from '@/quotes/composables/useDateConflictCheck';
  import QuoteShiftServiceModal from '@/quotes/components/modals/QuoteShiftServiceModal.vue';
  import dayjs from 'dayjs';
  // const { t } = useI18n();

  interface Props {
    items: QuoteService[] | GroupedServices[];
    category: number;
  }

  const props = defineProps<Props>();
  const items = toRef(props, 'items');
  // const category = toRef(props, 'category');

  interface GroupedServices {
    type: 'group_header' | 'hotel' | 'service';
    service: QuoteService;
    group: QuoteService[];
    selected: false;
  }

  // Order services
  const { orderServices, updateServicesOrder, quoteRowExtentions } = useQuote();
  const { checkDateConflict } = useDateConflictCheck();

  quoteRowExtentions.id = null;
  quoteRowExtentions.count = 0;
  const totalService = computed(() => items.value.length);

  /*
  const onClose = () => {
    alertCategory.value = null;
  };
  */

  const modalDateConflict = reactive({
    isOpen: false,
    date: null as string | null,
    previousDate: null as string | null,
    conflictingServices: [] as any[],
  });

  const pendingDragEvent = ref<{ oldIndex: number; newIndex: number } | null>(null);

  const handleDragEnd = (event: any) => {
    const oldIndex = event.oldIndex;
    const newIndex = event.newIndex;

    if (oldIndex === newIndex) return;

    // Get the moved service
    const movedItem = items.value[newIndex];
    const actualService = movedItem.service ? movedItem.service : movedItem;
    const originalDate = actualService.date_in;

    // Infer new date based on surrounding items
    // Usually it takes the date of the item right above it, or right below it if it's the first item
    let targetItem = null;
    if (newIndex > 0) {
      targetItem = items.value[newIndex - 1];
    } else if (items.value.length > 1) {
      targetItem = items.value[newIndex + 1];
    }

    if (!targetItem) {
      updateServicesOrder();
      return;
    }

    const targetService = targetItem.service ? targetItem.service : targetItem;
    const newDate = targetService.date_in;

    // If the date actually changes, we check for conflicts
    if (
      newDate &&
      originalDate &&
      dayjs(newDate).format('YYYY-MM-DD') !== dayjs(originalDate).format('YYYY-MM-DD')
    ) {
      const conflictResult = checkDateConflict(
        dayjs(newDate).format('DD/MM/YYYY'),
        actualService.id
      );

      if (conflictResult.hasConflict) {
        pendingDragEvent.value = { oldIndex, newIndex };
        modalDateConflict.date = newDate;
        modalDateConflict.previousDate = originalDate;
        modalDateConflict.conflictingServices = conflictResult.conflictingServices;
        modalDateConflict.isOpen = true;
        return;
      }
    }

    updateServicesOrder();
  };

  const confirmDateConflict = (propagate: boolean) => {
    console.log(propagate);
    modalDateConflict.isOpen = false;
    pendingDragEvent.value = null;
    updateServicesOrder();
  };

  const closeDateConflictModal = () => {
    modalDateConflict.isOpen = false;

    // Revert visual drag
    if (pendingDragEvent.value) {
      const { oldIndex, newIndex } = pendingDragEvent.value;
      // Revert in the local array using a temporary move
      const elementToMove = items.value.splice(newIndex, 1)[0];
      items.value.splice(oldIndex, 0, elementToMove);
    }

    pendingDragEvent.value = null;
  };
</script>

<template>
  <!-- a-alert
    :message="t('quote.label.hotel_changes_manually')"
    type="warning"
    closable
    @close="onClose"
    v-if="alertCategory == category"
  / -->

  <draggable
    class="dragArea"
    ghost-class="ghost"
    @change="orderServices"
    @end="handleDragEnd"
    :list="items"
    handle=".handle"
  >
    <template v-for="(service, indexService) in items">
      <ContentExtensionComponent
        :grouped-extension="service"
        :items="service.extensions"
        :services="items"
        v-if="service.type == 'group_extension'"
        :extension="service.service.service.new_extension"
      />

      <DetailBoxComponent
        :totalService="totalService"
        :item="indexService"
        :grouped-service="service"
        v-else
      />
    </template>
  </draggable>

  <QuoteShiftServiceModal
    v-if="modalDateConflict.isOpen"
    :is-open="modalDateConflict.isOpen"
    mode="date_conflict"
    :insertion-date="modalDateConflict.date || ''"
    :previous-date="modalDateConflict.previousDate || ''"
    :conflicting-services="modalDateConflict.conflictingServices"
    @confirm="confirmDateConflict"
    @close="closeDateConflictModal"
  />
</template>

<style lang="scss">
  .cabecera {
    padding: 20px;
    margin-bottom: 20px;
  }

  .ghost {
    opacity: 0.5;
    background: #c8ebfb;
  }

  .quotes-extensiones {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
    border-radius: 6px;
    border: 2px solid #e9e9e9;
    margin-bottom: 24px;
  }
</style>
