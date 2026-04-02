<template>
  <div
    v-if="
      formState.tariffType && [TariffType.FLAT, TariffType.PERIODS].includes(formState.tariffType)
    "
    class="holiday-tariff-section"
  >
    <div class="col-12 flex-center">
      <span class="label-text mr-2">Incluir tarifas festivas:</span>
      <a-radio-group
        v-model:value="model.includeHolidayTariffs"
        @change="handleIncludeHolidayTariffsChange"
        :disabled="isDisabled || loadingHoliday"
      >
        <a-radio :value="true">Sí</a-radio>
        <a-radio :value="false">No</a-radio>
      </a-radio-group>
    </div>
  </div>

  <div class="holiday-wrapper" v-if="model.includeHolidayTariffs">
    <a-spin :spinning="loadingHoliday">
      <div class="holiday-body">
        <HolidaySidebar
          :selectedYear="selectedYear"
          :categories="holidays"
          :selectedCategoryId="formState.selectedCategoryId"
          :editMode="uiState.editMode"
          :editingItem="uiState.editingItemId"
          @selectCategory="selectCategory"
          @selectItem="selectItem"
          @toggleEdit="toggleEdit"
          @saveItem="saveEditingItem"
          @toggleCategoryCheck="toggleCategoryCheck"
          @moveItemToCategory="
            (payload) => moveItemToCategory(payload.itemId, payload.toCategoryId)
          "
          @addCategory="addCategory"
        />

        <HolidayCalendarPanel
          :months="allowedMonths"
          :categories="holidays"
          :selectedCategoryId="formState.selectedCategoryId"
          :editingItem="editingItem"
          :editMode="uiState.editMode"
          :loadingHoliday="loadingHoliday"
          :years="years"
          :travelFrom="travelFromStr"
          :travelTo="travelToStr"
          v-model:selectedYear="selectedYear"
          @rangeSelected="updateItemRange"
          @selectDay="selectCalendarDay"
        />
      </div>
    </a-spin>
  </div>
</template>

<script setup lang="ts">
  import { TariffType } from '@/modules/negotiations/products/configuration/enums/tariffType.enum';
  import HolidaySidebar from './components/HolidaySidebar.vue';
  import HolidayCalendarPanel from './components/HolidayCalendarPanel.vue';
  import { useHolidayTariffs } from './composables/useHolidayTariff';

  const props = defineProps<{
    model: any;
    errors: any;
    ratePlanId: string | null;
  }>();

  const formState = props.model;

  const holidayTariffs = useHolidayTariffs(formState, props.ratePlanId);

  const {
    holidays,
    uiState,
    loadingHoliday,
    selectedYear,
    years,
    editingItem,
    isDisabled,
    travelFromStr,
    travelToStr,
    allowedMonths,
    selectCategory,
    selectItem,
    toggleEdit,
    saveEditingItem,
    toggleCategoryCheck,
    moveItemToCategory,
    updateItemRange,
    selectCalendarDay,
    handleIncludeHolidayTariffsChange,
    addCategory,
  } = holidayTariffs;
</script>

<style scoped>
  .holiday-tariff-section {
    margin-top: 16px;
    background: #f9f9f9;
    padding: 16px;
    border-radius: 8px;
  }

  .holiday-wrapper {
    margin-top: 20px;
    display: flex;
    flex-direction: column;
    gap: 16px;
  }

  .holiday-body {
    display: flex;
    gap: 24px;
  }

  .label-text {
    font-size: 14px;
    font-weight: 500;
    color: #2f353a;
    margin-right: 8px;
  }
</style>
