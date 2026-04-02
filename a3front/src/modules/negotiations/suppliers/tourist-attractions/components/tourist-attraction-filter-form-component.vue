<template>
  <supplier-filter-form-component
    :showDrawerForm="showDrawerForm"
    @closeDrawerForm="handleClose"
    @cleanFilters="handleCleanFilters"
    @applyFilters="handleApplyFilters"
  >
    <template #body>
      <a-spin :spinning="isLoading">
        <div class="main-container mt-2">
          <a-collapse
            v-model:activeKey="collapseSectionKeys"
            expand-icon-position="end"
            ghost
            class="custom-filter-collapse"
          >
            <template #expandIcon="{ isActive }">
              <font-awesome-icon
                :icon="isActive ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']"
              />
            </template>

            <!-- panel chains -->
            <a-collapse-panel key="chains" class="custom-collapse-panel mt-3">
              <template #header>
                <span class="title-section"> Cadena </span>
              </template>

              <filterable-checkbox-list-component
                :options="chains"
                v-model:selectedOptions="filterState.chains"
                v-model:search="filterState.searchChain"
                @loading="isLoading = $event"
              />
            </a-collapse-panel>

            <!-- panel country states -->
            <a-collapse-panel key="country-states" class="custom-collapse-panel">
              <template #header>
                <span class="title-section"> Ciudad </span>
              </template>

              <filterable-checkbox-list-component
                :options="countryStateOptions"
                v-model:selectedOptions="filterState.countryStateKeys"
                v-model:search="filterState.searchCountryState"
                @loading="isLoading = $event"
              />
            </a-collapse-panel>

            <!-- panel status -->
            <a-collapse-panel key="status" class="custom-collapse-panel">
              <template #header>
                <span class="title-section"> Estado </span>
              </template>

              <div>
                <a-checkbox-group v-model:value="filterState.status" class="custom-filter-checkbox">
                  <div v-for="item in statusOptions" :key="item.value" class="mb-2 w-100">
                    <a-checkbox :value="item.value">
                      <span class="checkbox-label">{{ item.label }}</span>
                    </a-checkbox>
                  </div>
                </a-checkbox-group>
              </div>
            </a-collapse-panel>
          </a-collapse>
        </div>
      </a-spin>
    </template>
  </supplier-filter-form-component>
</template>
<script setup lang="ts">
  import SupplierFilterFormComponent from '@/modules/negotiations/suppliers/components/supplier-filter-form-component.vue';
  import { useTouristAttractionFilterForm } from '@/modules/negotiations/suppliers/tourist-attractions/composables/tourist-attraction-filter-form.composable';
  import FilterableCheckboxListComponent from '@/modules/negotiations/suppliers/tourist-attractions/components/filterable-checkbox-list-component.vue';

  defineProps({
    showDrawerForm: {
      type: Boolean,
      default: false,
    },
  });

  const emit = defineEmits(['update:showDrawerForm']);

  const {
    isLoading,
    collapseSectionKeys,
    filterState,
    chains,
    countryStateOptions,
    statusOptions,
    handleClose,
    handleCleanFilters,
    handleApplyFilters,
  } = useTouristAttractionFilterForm(emit);
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .main-container {
    padding-right: 5px;
  }

  .custom-collapse-panel {
    :deep(.ant-collapse-content-box) {
      display: flex;
      flex-direction: column;
      max-height: 290px !important;
    }
  }

  .custom-filter-checkbox {
    .checkbox-label {
      font-size: 16px;
      font-weight: 400;
      color: $color-black;
    }
  }

  .custom-filter-collapse {
    :deep(.ant-collapse-header) {
      display: flex !important;
      align-items: center !important;
    }
  }

  .title-section {
    font-size: 20px;
    font-weight: 600;
  }
</style>
