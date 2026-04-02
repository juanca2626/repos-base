<script setup lang="ts">
  import BoxComponent from '@/quotes/components/info/BoxComponent.vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import { computed } from 'vue';
  import { useQuoteHotelCategories } from '@/quotes/composables/useQuoteHotelCategories';
  import { useQuote } from '@/quotes/composables/useQuote';
  import { usePopup } from '@/quotes/composables/usePopup';
  import DropDownSelectComponent from '@/quotes/components/global/DropDownSelectComponent.vue';
  import { useI18n } from 'vue-i18n';

  const { t } = useI18n();

  const { quoteHotelCategories } = useQuoteHotelCategories();
  const { quoteCategoriesSelected, quoteNew } = useQuote();

  const { showForm, toggleForm } = usePopup();

  const selectedCategoriesLabel = computed(() => {
    let cat: Array<string> = quoteHotelCategories.value
      .filter((c) => quoteNew.value.quoteCategoriesSelected.includes(c.value))
      .map((sc) => sc.label);

    let result: string = '';
    if (cat.length > 0) {
      result = cat.length > 1 ? cat[0] + ', ' + t('quote.label.others') : cat[0];
    }

    return result;
  });
  const items = computed(() =>
    quoteHotelCategories.value.map((c) => ({
      ...c,
      selected: quoteNew.value.quoteCategoriesSelected.includes(c.value),
    }))
  );

  const selected = async (selectedCategoriesId: number[]) => {
    quoteNew.value.quoteCategoriesSelected = selectedCategoriesId;
  };
</script>

<template>
  <BoxComponent class="extra" :title="t('quote.label.category_hotel')" @edit="toggleForm()">
    <template #text>{{ selectedCategoriesLabel }}</template>
    <template #actions>
      <div class="button-container" @click="toggleForm()">
        <font-awesome-icon :style="{ color: '#eb5757' }" icon="plus-circle" />
        <span class="text"> {{ t('quote.label.add_categories') }}</span>
      </div>
    </template>
    <template #form>
      <div v-if="showForm" class="hotel-category">
        <DropDownSelectComponent
          :default="quoteCategoriesSelected"
          :items="items"
          @selected="selected"
        />
      </div>
    </template>
  </BoxComponent>
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
