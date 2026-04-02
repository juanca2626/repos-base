<template>
  <div class="details-section">
    <div class="details-container">
      <div class="details-header">
        <div class="details-left">
          <div class="details-title">
            {{ formState.business_name }}
          </div>
        </div>
        <div class="details-right">
          <!--          <div class="config-as">-->
          <!--            <font-awesome-icon :icon="['fas', 'rotate']" />-->
          <!--            Configurar como:-->
          <!--            <a-tooltip placement="topRight" overlay-class-name="tooltip-config-as">-->
          <!--              <template #title>-->
          <!--                <div class="font-bold">Configurar clasificación del proveedor:</div>-->
          <!--                <div class="font-semibold">-->
          <!--                  Si el proveedor tienes más de una clasificación, podrás seleccionar y cambiar de-->
          <!--                  clasificación desde aquí sin salir del registro principal.-->
          <!--                </div>-->
          <!--              </template>-->
          <!--              <font-awesome-icon :icon="['fas', 'circle-question']" class="cursor-pointer" />-->
          <!--            </a-tooltip>-->
          <!--          </div>-->
          <!--          <div>-->
          <!--            <div v-if="formStateNegotiation.classifications.length <= 1">-->
          <!--&lt;!&ndash;              <a-tag class="classifications">&ndash;&gt;-->
          <!--&lt;!&ndash;                {{ getConfigureAs() }}&ndash;&gt;-->
          <!--&lt;!&ndash;              </a-tag>&ndash;&gt;-->
          <!--            </div>-->
          <!--            <div v-else>-->
          <!--              <a-select-->
          <!--                class="classifications"-->
          <!--                v-model:value="configSubClassification"-->
          <!--                @change="setConfigSubClassification"-->
          <!--              >-->
          <!--                <a-select-option-->
          <!--                  v-for="item in formStateNegotiation.classifications"-->
          <!--                  :key="item.supplier_sub_classification_id"-->
          <!--                  :value="item.supplier_sub_classification_id"-->
          <!--                >-->
          <!--                  {{ item.name }}-->
          <!--                </a-select-option>-->
          <!--              </a-select>-->
          <!--            </div>-->
          <!--          </div>-->
        </div>
      </div>
      <div class="details-info">
        <div>
          <div class="text-justify">RUC:</div>
          <div><font-awesome-icon :icon="['fas', 'id-badge']" /> {{ formState.document }}</div>
        </div>
        <div>
          <div class="text-justify">Razón Social:</div>
          <font-awesome-icon :icon="['fas', 'circle-user']" /> {{ formState.name }}
        </div>
        <div>
          <div class="text-justify">Teléfono:</div>
          <font-awesome-icon :icon="['fas', 'phone']" /> -
        </div>
        <div>
          <div class="text-justify">Email:</div>
          <font-awesome-icon :icon="['fas', 'envelope']" /> -
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
  // import { useDetailsSection } from '@/modules/negotiations/supplier/register/configuration-module/composables/useDetailsSection';
  // import { useConfigurationModule } from '@/modules/negotiations/supplier/register/configuration-module/composables/useConfigurationModule';
  import { useGeneralInformationComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/general-information.composable';

  // const { isMuseumsSubClassification, setConfigSubClassification } = useConfigurationModule();

  // const { formStateNegotiation, configSubClassification } = useDetailsSection();

  const { formState } = useGeneralInformationComposable();
</script>

<style>
  .details-section {
    margin-bottom: 1.2rem;

    .details-container {
      border-bottom: 1px solid #e7e7e7;
      padding: 24px;
      border-radius: 6px;
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
      color: #2f353a;
      font-size: 24px;
      font-weight: 700;
    }

    .tags-container {
      display: flex;
    }

    .tag {
      background: #ededff;
      color: #2e2b9e;
    }

    .details-right {
      width: 10rem;
    }

    .details-right .config-as {
      margin-top: 2px;
    }

    .classifications {
      border: 2px solid #f2f2f2;
      color: #2f353a;
      width: 100%;
    }

    .details-info {
      display: flex;
      justify-content: space-between;
      color: #2f353a;
    }

    .text-justify {
      text-align: justify;
    }

    .tooltip-config-as {
      .font-bold {
        font-weight: 500;
        line-height: 18px;
      }

      .font-semibold {
        font-weight: 400;
        font-size: 12px;
        line-height: 18px;
      }

      .ant-tooltip-inner {
        min-width: 17rem !important;
      }
    }
  }
</style>
