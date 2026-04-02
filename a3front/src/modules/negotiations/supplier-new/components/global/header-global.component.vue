<template>
  <div v-if="hasClassifications" class="header-global-component">
    <div class="details-container">
      <div class="details-header">
        <div class="details-left">
          <div class="details-title">
            {{ commercialLocation.code ? `${commercialLocation.code} - ` : ''
            }}{{ generalInformation.business_name }}
          </div>
          <div>
            <div v-if="hasSingleClassification">
              <a-tag class="classification-tag">
                {{ classifications[0].name || '' }}
              </a-tag>
            </div>
            <div v-else>
              <a-select
                v-model:value="subClassificationSupplierId"
                size="small"
                style="width: 13rem"
                :options="classifications"
                :field-names="{ label: 'name', value: 'sub_classification_supplier_id' }"
              />
            </div>
          </div>
        </div>
      </div>
      <div class="details-info">
        <div>
          <div class="text-justify">RUC:</div>
          <div>
            <font-awesome-icon :icon="['fas', 'id-badge']" />
            <span>{{ generalInformation.document }}</span>
          </div>
        </div>
        <div>
          <div class="text-justify">Razón Social:</div>
          <font-awesome-icon :icon="['fas', 'circle-user']" />
          <span>{{ generalInformation.name }}</span>
        </div>
        <div>
          <div class="text-justify">Teléfono:</div>
          <font-awesome-icon :icon="['fas', 'phone']" />
          <span>{{ contactInformation.phone }}</span>
        </div>
        <div>
          <div class="text-justify">Email:</div>
          <font-awesome-icon :icon="['fas', 'envelope']" />
          <span>{{ contactInformation.email }}</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  import { useGeneralInformationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/general-information.composable';
  import { useContactInformationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/contact-information.composable';
  import { useCommercialLocationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/commercial-location.composable';
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';

  defineOptions({
    name: 'HeaderGlobalComponent',
  });

  const {
    classifications,
    subClassificationSupplierId,
    hasClassifications,
    hasSingleClassification,
  } = useSupplierGlobalComposable();
  const { formState: generalInformation } = useGeneralInformationComposable();
  const { formState: contactInformation } = useContactInformationComposable();
  const { formState: commercialLocation } = useCommercialLocationComposable();
</script>

<style scoped lang="scss">
  .header-global-component {
    padding-bottom: 1.5rem;

    .classification-tag {
      background-color: #ededff;
      color: #2e2b9e;
      font-weight: 600;
      font-size: 14px;
    }

    .details-container {
      border-bottom: 1px solid #e7e7e7;
      padding: 0 0 24px 0;
    }

    .details-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1rem;
      width: 100%;
    }

    .details-left {
      display: flex;
      gap: 10px;
      justify-content: center;
      align-items: center;
    }

    .details-title {
      font-weight: 700;
      font-size: 24px;
      line-height: 32px;
    }

    .details-info {
      display: flex;
      gap: clamp(1rem, 10vw, 7rem);
      color: #2f353a;
    }

    .text-justify {
      text-align: justify;
      font-weight: 400;
      font-size: 12px;
      line-height: 17px;
      vertical-align: middle;
      color: #2f353a;
    }

    span {
      font-weight: 500;
      font-size: 14px;
      line-height: 23px;
      margin-left: 4px;
      text-align: right;
      vertical-align: middle;
      color: #2f353a;
    }
  }
</style>
