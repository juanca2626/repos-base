<script setup lang="ts">
  import BoxComponent from '@/quotes/components/info/BoxComponent.vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { computed, ref } from 'vue';
  import { useQuoteHotelCategories } from '@/quotes/composables/useQuoteHotelCategories';
  import { useQuote } from '@/quotes/composables/useQuote';
  import AddCategoryModal from '@/quotes/components/modals/AddCategoryModal.vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const { quoteHotelCategories } = useQuoteHotelCategories();
  const {
    quoteCategoriesSelected,
    updateQuoteAccommodation,
    accommodation,
    updateQuoteCategory,
    // getQuote,
    getQuoteAccommodation,
    assignQuoteOccupation,
    operation,
    quote,
    alertCategory,
    selectedCategory,
    processing,
  } = useQuote();

  const single = computed(() => accommodation.value.single);
  const double = computed(() => accommodation.value.double);
  const triple = computed(() => accommodation.value.triple);

  const selectedCategoriesLabel = computed(() => {
    let cat: Array<string> = quoteHotelCategories.value
      .filter((c) => quoteCategoriesSelected.value.includes(c.value))
      .map((sc) => sc.label);

    let result: string = '';
    if (cat.length > 0) {
      result = cat.length > 1 ? cat[0] + ', ' + t('quote.label.others') : cat[0];
    }

    return result;
  });

  const isAddCategoryModalVisible = ref(false);

  const openAddModal = () => {
    if (processing.value) return;
    isAddCategoryModalVisible.value = true;
  };

  const handleConfirmAddCategory = async ({
    categoryIds,
    mode,
    sourceId,
  }: {
    categoryIds: number[];
    mode: string;
    sourceId?: number | string;
  }) => {
    if (!categoryIds || categoryIds.length === 0) return;
    const flagCategories = quote.value.categories.length === 0;

    let sourceCategoryId: number | string | undefined;
    if (mode === 'copy' && sourceId) {
      const sourceCategory = quote.value.categories.find((c) => c.type_class_id == sourceId);
      sourceCategoryId = sourceCategory?.id;
    } else if (mode === 'programacion') {
      sourceCategoryId = 'programming';
    }

    let key = 1;
    for (const categoryId of categoryIds) {
      await updateQuoteCategory(categoryId, 'new', sourceCategoryId, key, categoryIds.length);
      key++;
    }

    /*
    if (mode === 'copy' && sourceCategoryId) {
      let key = 1;
      for (const categoryId of categoryIds) {
        const newCategory = quote.value.categories.find((c) => c.type_class_id == categoryId);

        if (newCategory) {
          await quoteCategoryCopy(
            parseInt(sourceCategoryId.toString()),
            newCategory.id,
            key,
            categoryIds.length
          );
        }
        key++;
      }
    }
    */

    if (flagCategories) {
      const generatedDistribution = await getQuoteAccommodation(
        single.value,
        double.value,
        triple.value,
        quote.value.people[0].adults,
        quote.value.people[0].child
      );

      if (operation.value == 'passengers') {
        await updateQuoteAccommodation(
          generatedDistribution,
          single.value,
          double.value,
          triple.value,
          1,
          true
        );
      } else {
        await assignQuoteOccupation(single.value, double.value, triple.value, true);
      }
    }

    const lastCategoryId = categoryIds[0];
    selectedCategory.value = parseInt(lastCategoryId.toString());
    alertCategory.value = parseInt(lastCategoryId.toString());
  };
</script>

<template>
  <BoxComponent
    class="extra"
    :show-edit="false"
    :title="t('quote.label.category_hotel')"
    :disabled="processing"
  >
    <template #text>{{ selectedCategoriesLabel }}</template>
    <template #actions>
      <div class="button-container" @click="openAddModal()">
        <font-awesome-icon :style="{ color: '#eb5757' }" icon="plus-circle" />
        <span class="text"> {{ t('quote.label.add_categories') }}</span>
      </div>
    </template>
  </BoxComponent>

  <AddCategoryModal
    v-model:visible="isAddCategoryModalVisible"
    :existing-categories="quote.categories"
    :available-category-labels="quoteHotelCategories"
    @confirm="handleConfirmAddCategory"
  />
</template>

<style scoped lang="scss">
  .hotel-category {
    display: flex;
    width: 260px;
    flex-direction: column;
    align-items: flex-start;
    gap: 16px;
    border-radius: 0 0 6px 6px;
    background: #fff;
    box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
    position: absolute;
    top: 35px;
    left: -10px;
    height: 235px;
    overflow-y: auto;
    overflow-x: hidden;

    /* width */
    &::-webkit-scrollbar {
      width: 5px;
      margin-right: 4px;
      padding-right: 2px;
    }

    /* Track */
    &::-webkit-scrollbar-track {
      border-radius: 10px;
    }

    /* Handle */
    &::-webkit-scrollbar-thumb {
      border-radius: 4px;
      background: #c4c4c4;
      margin-right: 4px;
    }

    /* Handle on hover */
    &::-webkit-scrollbar-thumb:hover {
      background: #eb5757;
    }
  }
</style>
