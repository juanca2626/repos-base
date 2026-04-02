<!-- GeneralInformationComponent.vue -->
<template>
  <div>
    <a-row :gutter="24" justify="start">
      <!-- Código del proveedor -->
      <a-col :span="12">
        <a-input-group size="large">
          <a-row :gutter="16" justify="start">
            <a-col :span="6" class="align-bottom">
              <a-form-item name="cityCode" :rules="formRules.cityCode">
                <template #label>
                  <required-label label="Código ciudad" />
                </template>
                <a-input
                  v-model:value="formStateNegotiation.cityCode"
                  placeholder="Escribe el código de la ciudad"
                  :maxlength="3"
                  @input="formStateNegotiation.cityCode = $event.target.value.toUpperCase()"
                  :disabled="isFormEditMode || !isEditable"
                />
              </a-form-item>
            </a-col>
            <a-col :span="1" class="align-middle">
              <a-divider class="divider-code-supplier" orientation="middle" />
            </a-col>
            <a-col :span="6" class="align-bottom">
              <a-form-item name="supplierCode" :rules="formRules.supplierCode">
                <template #label>
                  <required-label label="Código proveedor" />
                </template>
                <a-input
                  v-model:value="formStateNegotiation.supplierCode"
                  placeholder="Escribe el código del proveedor"
                  :maxlength="3"
                  @input="formStateNegotiation.supplierCode = $event.target.value.toUpperCase()"
                  :disabled="isFormEditMode || !isEditable"
                />
              </a-form-item>
            </a-col>
            <a-col :span="11" class="align-bottom">
              <a-form-item class="form-item-checkbox">
                <a-checkbox
                  v-model:checked="formStateNegotiation.showSuggestedCodes"
                  @change="handleChangeSuggestedCode"
                  :disabled="isFormEditMode || !isEditable"
                >
                  Mostrar códigos sugeridos
                </a-checkbox>
              </a-form-item>
            </a-col>
          </a-row>
          <a-row v-if="suggestedCodeData.code && formStateNegotiation.showSuggestedCodes">
            <a-col :span="24" class="col-suggested-code">
              <div class="container-suggested-code">
                <div class="container-text-suggested-code">
                  <span class="text-suggested-code">
                    {{ suggestedCodeData.prefix }}{{ suggestedCodeData.code }}
                  </span>
                </div>
                <div class="container-actions-suggested-code">
                  <font-awesome-icon
                    class="icon-reload-suggested-code"
                    :icon="['fa', 'arrows-rotate']"
                    @click="handleReloadSuggestedCode"
                  />
                  <span class="btn-suggested-code" @click="applySuggestedCode">
                    <span class="btn-text-suggested-code"> Aplicar </span>
                  </span>
                </div>
              </div>
            </a-col>
          </a-row>
        </a-input-group>
      </a-col>
      <!-- Autorizado por gerencia -->
      <a-col :span="12" class="align-bottom">
        <a-form-item name="authorizedManagement" :rules="formRules.authorizedManagement">
          <a-switch
            v-model:checked="formStateNegotiation.authorizedManagement"
            :disabled="!isEditable"
          />
          <span class="text-approved-management"> Autorizado por gerencia </span>
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="24">
      <!-- Nombre comercial -->
      <a-col :span="12">
        <a-form-item name="businessName" :rules="formRules.businessName">
          <template #label>
            <required-label label="Nombre comercial:" />
          </template>
          <a-input
            v-model:value="formStateNegotiation.businessName"
            placeholder="Escribe el nombre comercial"
            :disabled="!isEditable"
          />
        </a-form-item>
      </a-col>
      <!-- Razón social -->
      <a-col :span="12">
        <a-form-item name="name" :rules="formRules.name">
          <template #label>
            <required-label label="Razón social:" />
          </template>
          <a-input
            v-model:value="formStateNegotiation.name"
            placeholder="Escribe la razón social"
            :disabled="!isEditable"
          />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="24">
      <!-- Clasificación del proveedor -->
      <a-col :span="12">
        <a-form-item name="supplierClassifications" :rules="formRules.supplierClassifications">
          <template #label>
            <required-label label="Clasificación del proveedor:" />
          </template>
          <a-select
            v-model:value="formStateNegotiation.supplierClassifications"
            mode="multiple"
            max-tag-count="responsive"
            placeholder="Seleccione clasificación"
            :options="supplierClassificationOptions"
            :loading="isLoadingClassifications"
            :filter-option="filterOption"
            :showArrow="true"
            show-search
            popupClassName="custom-dropdown-backend"
            allow-clear
            :disabled="!isEditable"
          />
        </a-form-item>
      </a-col>
      <!-- Pertenece a planta -->
      <a-col :span="12">
        <a-form-item name="belongsCompany" :rules="formRules.belongsCompany">
          <template #label>
            <required-label label="Pertenece a planta:" />
          </template>
          <a-radio-group
            v-model:value="formStateNegotiation.belongsCompany"
            :disabled="!isEditable"
          >
            <a-radio
              v-for="option in getConfirmOptions()"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </a-radio>
          </a-radio-group>
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="24">
      <!-- Número de RUC -->
      <a-col :span="12">
        <a-form-item name="rucNumber" :rules="formRules.rucNumber">
          <template #label>
            <required-label label="Número de RUC:" />
          </template>
          <a-input
            v-model:value="formStateNegotiation.rucNumber"
            placeholder="Escribe el número de RUC"
            :maxLength="11"
            :disabled="!isEditable"
          />
        </a-form-item>
      </a-col>
      <!-- Dirección fiscal -->
      <a-col :span="12">
        <a-form-item name="fiscalAddress" :rules="formRules.fiscalAddress">
          <template #label>
            <required-label label="Dirección fiscal:" />
          </template>
          <a-input
            v-model:value="formStateNegotiation.fiscalAddress"
            placeholder="Escribe la dirección fiscal"
            :disabled="!isEditable"
          />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="24">
      <!-- Aplica % de servicio -->
      <a-col :span="6">
        <a-form-item name="applyServicePercentage" :rules="formRules.applyServicePercentage">
          <template #label>
            <required-label label="Aplica % de servicio:" />
          </template>
          <a-radio-group
            v-model:value="formStateNegotiation.applyServicePercentage"
            :disabled="!isEditable"
          >
            <a-radio
              v-for="option in getConfirmOptions()"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </a-radio>
          </a-radio-group>
        </a-form-item>
      </a-col>
      <a-col :span="6">
        <a-form-item
          v-if="formStateNegotiation.applyServicePercentage"
          name="serviceCharges"
          :rules="formRules.serviceCharges"
        >
          <template #label> Porcentaje de servicio: </template>
          <a-input-number
            v-model:value="formStateNegotiation.serviceCharges"
            min="0"
            max="100"
            placeholder="Ingrese el porcentaje de servicio"
            class="custom-full-width"
            :disabled="!isEditable"
          />
        </a-form-item>
      </a-col>
      <!-- Aplica % de comisión -->
      <a-col :span="6">
        <a-form-item name="applyCommissionPercentage" :rules="formRules.applyCommissionPercentage">
          <template #label>
            <required-label label="Aplica % de comisión:" />
          </template>
          <a-radio-group
            v-model:value="formStateNegotiation.applyCommissionPercentage"
            :disabled="!isEditable"
          >
            <a-radio
              v-for="option in getConfirmOptions()"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </a-radio>
          </a-radio-group>
        </a-form-item>
      </a-col>
      <a-col :span="6">
        <a-form-item
          v-if="formStateNegotiation.applyCommissionPercentage"
          name="commissionCharges"
          :rules="formRules.commissionCharges"
        >
          <template #label> Porcentaje de comisión: </template>
          <a-input-number
            v-model:value="formStateNegotiation.commissionCharges"
            min="0"
            max="100"
            placeholder="Ingrese el porcentaje de comisión"
            class="custom-full-width"
            :disabled="!isEditable"
          />
        </a-form-item>
      </a-col>
    </a-row>

    <a-row :gutter="24">
      <!-- Aplica % de gastos financieros -->
      <a-col :span="12">
        <a-form-item name="applyFinancialExpenses" :rules="formRules.applyFinancialExpenses">
          <template #label>
            <required-label label="Aplica % de gastos financieros:" />
          </template>
          <a-radio-group
            v-model:value="formStateNegotiation.applyFinancialExpenses"
            :disabled="!isEditable"
          >
            <a-radio
              v-for="option in getConfirmOptions()"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </a-radio>
          </a-radio-group>
        </a-form-item>
      </a-col>
    </a-row>
  </div>
</template>

<script setup lang="ts">
  import { useGeneralInformation } from '@/modules/negotiations/supplier/register/composables/useGeneralInformation';
  import RequiredLabel from '@/modules/negotiations/supplier/components/RequiredLabel.vue';

  defineProps({
    isEditable: {
      type: Boolean,
      default: false,
    },
  });

  const {
    formStateNegotiation,
    supplierClassificationOptions,
    isLoadingClassifications,
    suggestedCodeData,
    formRules,
    isFormEditMode,
    getConfirmOptions,
    handleChangeSuggestedCode,
    filterOption,
    handleReloadSuggestedCode,
    applySuggestedCode,
  } = useGeneralInformation();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .text-suggested-code {
    font-size: 16px;
    font-weight: 500;
  }

  .container-text-suggested-code {
    width: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
  }

  .container-actions-suggested-code {
    width: 50%;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }

  .icon-reload-suggested-code {
    width: 22px;
    height: 18px;
    color: $color-black-5;
    cursor: pointer;

    &:hover {
      color: $color-black;
    }
  }

  .col-suggested-code {
    margin-bottom: 25px;
  }

  .container-suggested-code {
    display: flex;
    justify-content: space-between;
    align-items: center;
    height: 50px;
    border: 1px dashed $color-black-5;
    border-radius: 4px;
    padding: 6px 12px;
  }

  .btn-suggested-code {
    background-color: $color-info-light;
    width: 150px !important;
    height: 35px;
    border-radius: 4px;
    padding-top: 4px;
    padding-bottom: 4px;
    padding-right: 8px;
    padding-left: 8px;
    text-align: center;
    cursor: pointer;
  }

  .btn-text-suggested-code {
    color: $color-info-dark;
    font-size: 16px;
    font-weight: 600;
  }

  .text-approved-management {
    margin-left: 5px;
  }

  .form-item-checkbox {
    margin-bottom: 21px;
  }

  .align-bottom {
    display: flex;
    align-items: flex-end; /* Alineación inferior */
  }

  .align-middle {
    display: flex;
    align-items: center; /* Alineación central */
  }

  .divider-code-supplier {
    height: 2px;
    background-color: $color-black-5;
    width: 12px;
  }
</style>
