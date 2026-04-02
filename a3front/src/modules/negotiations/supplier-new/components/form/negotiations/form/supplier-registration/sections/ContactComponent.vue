<template>
  <div class="contact-component b-bottom">
    <!-- Loading overlay bloqueante (solo al guardar) -->
    <div v-if="isLoading" class="loading-overlay">
      <a-spin size="small" />
    </div>

    <h2 v-if="isEditMode" class="title-section">Contactos</h2>

    <!-- Modo lectura -->
    <ReadModeComponent v-if="!isEditMode" title="Contactos" @edit="handleEditMode">
      <div class="read-item">
        <span class="read-item-label">Web</span>
        <span class="read-item-value">{{ readData.web }}</span>
      </div>
      <div class="read-item">
        <span class="read-item-label">Teléfono del establecimiento</span>
        <span class="read-item-value">{{ readData.phone }}</span>
      </div>
      <div class="read-item">
        <span class="read-item-label">Correo genérico</span>
        <span class="read-item-value">{{ readData.email }}</span>
      </div>

      <!-- Lista de contactos en modo lectura -->
      <div v-if="hasRealContacts" class="read-contacts-section">
        <div class="read-item-label contacts-title">Listado de contactos</div>
        <div class="custom-list-contact">
          <ol>
            <li
              v-for="(contact, index) in showAllContacts
                ? formState.contacts.filter(
                    (c) =>
                      c.id ||
                      c.fullName?.trim() ||
                      c.phone?.trim() ||
                      c.email?.trim() ||
                      c.typeContactId ||
                      c.stateId
                  )
                : formState.contacts
                    .filter(
                      (c) =>
                        c.id ||
                        c.fullName?.trim() ||
                        c.phone?.trim() ||
                        c.email?.trim() ||
                        c.typeContactId ||
                        c.stateId
                    )
                    .slice(0, 5)"
              :key="contact.id || `new-${index}`"
            >
              <div class="contact-row">
                <div>
                  <strong>Tipo de contacto:</strong>
                  {{ contact.typeContactName || getTypeContactName(contact.typeContactId) }}
                </div>
                <div><strong>Nombre:</strong> {{ contact.fullName }}</div>
                <div><strong>Teléfono:</strong> {{ contact.phone }}</div>
                <div><strong>Correo:</strong> {{ contact.email }}</div>
                <div>
                  <strong>Ciudad:</strong> {{ contact.stateName || getStateName(contact.stateId) }}
                </div>
              </div>
            </li>
          </ol>

          <span
            v-if="!showAllContacts && totalContacts > 5"
            class="show-more-contacts"
            @click="showAllContacts = true"
          >
            {{
              totalContacts - 5 === 1
                ? 'Ver el contacto adicional'
                : `Ver los ${totalContacts - 5} contactos adicionales`
            }}
          </span>

          <span
            v-if="showAllContacts && totalContacts > 3"
            class="show-less-contacts"
            @click="showAllContacts = false"
          >
            Mostrar menos
          </span>
        </div>
      </div>
    </ReadModeComponent>

    <!-- Modo edición -->
    <div v-else class="contact-form">
      <a-form ref="formRef" :model="formState" :rules="rules" layout="vertical">
        <!-- Web -->
        <a-row>
          <a-col class="gutter-row" :span="12">
            <a-form-item name="web">
              <template #label>
                <span class="form-label">Web:</span>
              </template>
              <a-input v-model:value="formState.web" placeholder="www.ejemplo.com" />
            </a-form-item>
          </a-col>
        </a-row>

        <!-- Teléfono -->
        <a-row>
          <a-col class="gutter-row" :span="12">
            <!-- Tipo de teléfono (radio) -->
            <a-form-item>
              <template #label>
                <span class="form-label">Tipo de teléfono:</span>
              </template>
              <a-radio-group v-model:value="typePhone" :options="optionsTypePhone" />
            </a-form-item>

            <!-- Wrapper personalizado para teléfono -->
            <div class="phone-wrapper">
              <label class="ant-form-item-label">
                <span class="form-label">Teléfono del establecimiento:</span>
              </label>

              <div class="phone-input-container">
                <a-input-group compact class="phone-input-group">
                  <!-- País -->
                  <a-select
                    placeholder="País"
                    class="country-select"
                    v-model:value="formState.countryPhoneCode"
                    :list-height="200"
                    :virtual-scroll="true"
                    :filter-option="filterOptionPhone"
                    show-search
                    @blur="touchedFields.countryPhoneCode = true"
                  >
                    <a-select-option
                      v-for="(value, index) in phoneCodeOptions"
                      :key="index"
                      :value="String(value.phone_code)"
                    >
                      <div class="country-option">
                        <img :src="value.icon" class="country-flag" alt="" loading="lazy" />
                        <span>+{{ value.phone_code }}</span>
                      </div>
                    </a-select-option>
                  </a-select>

                  <!-- Prefijo (solo si es teléfono fijo) -->
                  <a-auto-complete
                    v-if="typePhone == 1"
                    placeholder="Prefijo"
                    style="width: 4rem"
                    v-model:value="formState.statePhoneCode"
                    :options="statePhoneCodeFormattedOptions"
                    :filter-option="false"
                    allow-clear
                    @blur="handlePrefixBlur"
                    @search="handlePrefixSearch"
                    @change="handleStatePhoneCodeChange"
                    @keydown="handlePrefixKeyDown"
                  />

                  <!-- Teléfono -->
                  <a-input
                    v-model:value="formState.phone"
                    placeholder="Ingrese el número de teléfono"
                    type="number"
                    class="hide-arrows"
                    :class="{
                      'phone-input-full': typePhone == 2,
                      'phone-input-partial': typePhone == 1,
                    }"
                    @blur="touchedFields.phone = true"
                  />
                </a-input-group>

                <!-- Mensajes de validación condicionales -->
                <div class="phone-validation-messages">
                  <div
                    v-if="
                      (shouldShowPhoneValidation || touchedFields.countryPhoneCode) &&
                      !formState.countryPhoneCode
                    "
                    class="validation-error"
                  >
                    El país es requerido
                  </div>
                  <div
                    v-if="
                      (shouldShowPhoneValidation || touchedFields.statePhoneCode) &&
                      typePhone == 1 &&
                      !formState.statePhoneCode &&
                      formState.countryPhoneCode
                    "
                    class="validation-error"
                  >
                    Prefijo requerido
                  </div>
                  <div
                    v-if="(shouldShowPhoneValidation || touchedFields.phone) && !formState.phone"
                    class="validation-error"
                  >
                    El teléfono es requerido
                  </div>
                  <div
                    v-if="
                      (shouldShowPhoneValidation || touchedFields.phone) &&
                      formState.phone &&
                      !/^[0-9]+$/.test(formState.phone)
                    "
                    class="validation-error"
                  >
                    Ingrese un teléfono válido
                  </div>
                </div>
              </div>
            </div>
          </a-col>
        </a-row>

        <!-- Email -->
        <a-row>
          <a-col class="gutter-row" :span="12">
            <a-form-item name="email">
              <template #label>
                <required-label class="form-label" label="Correo genérico:" />
              </template>
              <a-input
                v-model:value="formState.email"
                placeholder="Ingrese el correo electrónico genérico"
              />
            </a-form-item>
          </a-col>
        </a-row>

        <!-- Listado de contactos -->
        <div
          v-if="
            !isLoading && (hasRealContacts || (formState.contacts && formState.contacts.length > 0))
          "
          class="container-table mt-5"
        >
          <div class="title-table">Listado de contactos</div>
          <div class="custom-table-list-contact">
            <a-table
              :bordered="true"
              :columns="columns"
              :row-key="(record: any, index: number) => record.id || `new-${index}`"
              :data-source="formState.contacts"
              :pagination="false"
              :showSorterTooltip="false"
              tableLayout="auto"
            >
              <template #bodyCell="{ column, index, record }">
                <template v-if="record.isEditMode">
                  <template v-if="column.dataIndex === 'typeContactName'">
                    <a-form-item
                      :name="['contacts', index, 'typeContactId']"
                      :rules="contactsFormRules.typeContactId"
                    >
                      <a-select
                        v-model:value="formState.contacts[index].typeContactId"
                        :options="typeContacts"
                        :filter-option="filterOption"
                        show-search
                        placeholder="Seleccionar"
                        allow-clear
                      />
                    </a-form-item>
                  </template>
                  <template v-else-if="column.dataIndex === 'fullName'">
                    <a-form-item
                      :name="['contacts', index, 'fullName']"
                      :rules="contactsFormRules.fullName"
                    >
                      <a-input
                        v-model:value="formState.contacts[index].fullName"
                        placeholder="Nombre"
                      />
                    </a-form-item>
                  </template>
                  <template v-else-if="column.dataIndex === 'phone'">
                    <a-form-item
                      :name="['contacts', index, 'phone']"
                      :rules="contactsFormRules.phone"
                    >
                      <a-input
                        v-model:value="formState.contacts[index].phone"
                        placeholder="Teléfono"
                        class="hide-arrows"
                        type="number"
                      />
                    </a-form-item>
                  </template>
                  <template v-else-if="column.dataIndex === 'email'">
                    <a-form-item
                      :name="['contacts', index, 'email']"
                      :rules="contactsFormRules.email"
                    >
                      <a-input
                        v-model:value="formState.contacts[index].email"
                        placeholder="correo@correo.com"
                      />
                    </a-form-item>
                  </template>
                  <template v-else-if="column.dataIndex === 'stateName'">
                    <a-form-item
                      :name="['contacts', index, 'stateId']"
                      :rules="contactsFormRules.stateId"
                    >
                      <a-select
                        class="w-100"
                        v-model:value="formState.contacts[index].stateId"
                        :options="states"
                        :filter-option="filterOption"
                        show-search
                        placeholder="Seleccionar"
                        allow-clear
                      />
                    </a-form-item>
                  </template>
                  <template v-if="column.dataIndex === 'actions'">
                    <div class="list-actions">
                      <span class="list-add-contact" @click="addContact">
                        <svg
                          width="25"
                          height="24"
                          viewBox="0 0 25 24"
                          fill="none"
                          xmlns="http://www.w3.org/2000/svg"
                        >
                          <path
                            d="M12.5 22C18.0228 22 22.5 17.5228 22.5 12C22.5 6.47715 18.0228 2 12.5 2C6.97715 2 2.5 6.47715 2.5 12C2.5 17.5228 6.97715 22 12.5 22Z"
                            stroke="#1284ED"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M12.5 8V16"
                            stroke="#1284ED"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                          <path
                            d="M8.5 12H16.5"
                            stroke="#1284ED"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                          />
                        </svg>
                      </span>
                      <span
                        v-if="formState.contacts.length > 1 && index > 0"
                        class="list-contact-button"
                        @click="removeContact(index)"
                      >
                        <font-awesome-icon
                          :icon="['far', 'trash-can']"
                          :style="{ height: '20px' }"
                        />
                      </span>
                    </div>
                  </template>
                </template>
                <template v-else>
                  <template v-if="column.dataIndex === 'actions'">
                    <div class="list-actions">
                      <span class="list-contact-button" @click="editContact(index)">
                        <font-awesome-icon
                          :icon="['far', 'pen-to-square']"
                          :style="{ height: '20px' }"
                        />
                      </span>
                      <span class="list-contact-button" @click="removeContact(index)">
                        <font-awesome-icon
                          :icon="['far', 'trash-can']"
                          :style="{ height: '20px' }"
                        />
                      </span>
                    </div>
                  </template>
                </template>
              </template>
            </a-table>
          </div>
        </div>

        <div class="form-actions">
          <a-button @click="handleCancel">Cancelar</a-button>
          <a-button type="primary" :disabled="!isFormValid" @click="handleSave"
            >Guardar datos</a-button
          >
        </div>
      </a-form>
    </div>
  </div>
</template>

<script setup lang="ts">
  import ReadModeComponent from '@/modules/negotiations/products/configuration/components/ReadModeComponent.vue';
  import RequiredLabel from '@/modules/negotiations/supplier-new/components/required-label.vue';
  import { useContactComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/v2/useContactComposable';

  defineOptions({
    name: 'ContactComponent',
  });

  const {
    formState,
    formRef,
    isEditMode,
    isLoading,
    typePhone,
    showAllContacts,
    shouldShowPhoneValidation,
    touchedFields,
    phoneCodeOptions,
    typeContacts,
    states,
    statePhoneCodeFormattedOptions,
    optionsTypePhone,
    hasRealContacts,
    totalContacts,
    readData,
    columns,
    isFormValid,
    rules,
    contactsFormRules,
    handleEditMode,
    handleCancel,
    handleSave,
    addContact,
    editContact,
    removeContact,
    getTypeContactName,
    getStateName,
    filterOption,
    filterOptionPhone,
    handleStatePhoneCodeChange,
    handlePrefixSearch,
    handlePrefixBlur,
    handlePrefixKeyDown,
  } = useContactComposable();
</script>

<style lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .country-flag {
    width: 1rem !important;
    height: 1rem !important;
    margin-right: 6px;
  }

  .contact-component {
    position: relative;
    margin-bottom: 0;

    .title-section {
      font-size: 16px !important;
      line-height: 23px !important;
      font-weight: 600;
      color: #2f353a;
      margin-bottom: 16px;
    }
  }

  // Eliminar el espacio después del separador
  .contact-component::v-deep .custom-separator {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
  }

  .contact-component::v-deep .read-mode-container {
    padding-bottom: 0 !important;
  }

  .loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: rgba(255, 255, 255, 0.9);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
  }

  .loading-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background-color: #f0f5ff;
    border: 1px solid #d6e4ff;
    border-radius: 4px;
    margin-bottom: 12px;
    font-size: 14px;
    color: #1890ff;
  }

  .contact-form {
    background-color: $color-white;

    .ant-form-item-required::before {
      display: none !important;
    }

    .ant-form-item-required::after {
      content: '' !important;
    }

    .ant-form-item {
      margin-bottom: 10px !important;
    }

    .ant-form-item-label > label {
      font-weight: 600;
      font-size: 16px;
      line-height: 24px;
      color: #2f353a;
    }

    .form-label {
      font-weight: 700;
      font-size: 14px;
      line-height: 20px;
      color: #2f353a;
    }

    .ant-row {
      display: block;
    }

    .ant-form-item-control-input {
      width: 422px;
    }

    .ant-input,
    .ant-select {
      width: 422px !important;
    }

    // Excepción para los inputs/selects dentro de la tabla de contactos
    .custom-table-list-contact {
      .ant-form-item-control-input {
        width: 100%;
      }

      .ant-input,
      .ant-select {
        width: 100% !important;
      }
    }

    // Excepción para los inputs/selects dentro del phone-wrapper
    .phone-wrapper {
      max-width: 422px;

      .ant-input,
      .ant-select,
      .ant-select-auto-complete {
        width: auto !important;
      }

      .country-select {
        width: 6rem !important;
      }

      .ant-select-auto-complete {
        width: 6rem !important;
      }
    }

    .phone-input-full {
      width: calc(100% - 6rem);
      flex: 1;
    }

    .hide-arrows {
      &::-webkit-outer-spin-button,
      &::-webkit-inner-spin-button {
        -webkit-appearance: none;
        appearance: none;
        margin: 0;
      }
    }

    /* Firefox */
    .hide-arrows input[type='number'] {
      -moz-appearance: textfield;
      appearance: textfield;
    }

    .phone-input-partial {
      width: calc(100% - 12rem);
      flex: 1;
    }

    .country-select {
      width: 6rem;
    }

    .country-option {
      display: flex;
      align-items: center;
    }

    // Estilos para el wrapper personalizado de teléfono
    .phone-wrapper {
      margin-bottom: 10px !important; // Mismo margen que otros form-items

      .ant-form-item-label {
        display: block;
        margin-bottom: 8px;

        .form-label {
          font-weight: 700;
          font-size: 14px;
          line-height: 20px;
          color: #2f353a;

          &::after {
            content: ' *';
            color: #ff4d4f;
          }
        }
      }

      .phone-input-container {
        position: relative;

        .phone-input-group {
          display: flex;

          .ant-select,
          .ant-input {
            border-radius: 0;
          }

          // Primer elemento (país)
          .ant-select:first-child .ant-select-selector {
            border-top-left-radius: 6px;
            border-bottom-left-radius: 6px;
          }

          // Último elemento (teléfono)
          .ant-input:last-child {
            border-top-right-radius: 6px;
            border-bottom-right-radius: 6px;
            flex: 1;
          }
        }

        .phone-validation-messages {
          margin-top: 4px;

          .validation-error {
            color: #ff4d4f;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 2px;

            &:last-child {
              margin-bottom: 10px;
            }
          }
        }
      }
    }

    .container-table {
      margin-top: 20px;

      .title-table {
        font-weight: 500;
        font-size: 16px;
        line-height: 24px;
        color: #2f353a;
        margin-bottom: 1rem;
      }

      .custom-table-list-contact {
        thead {
          th {
            background: #e6ebf2 !important;
            height: 56px !important;
          }
        }
      }

      .list-actions {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 16px;
      }

      .list-contact-button {
        color: #1284ed;
        cursor: pointer;
      }

      .list-add-contact {
        font-size: 16px;
        font-weight: 500;
        color: #1284ed;
        cursor: pointer;

        &.disabled {
          color: #acaeb0;
          cursor: not-allowed;
        }
      }
    }

    .form-actions {
      display: flex;
      gap: 12px;
      justify-content: flex-start;
      margin-top: 24px;

      .ant-btn {
        height: 48px;
        font-weight: 600;
        font-size: 16px;
        line-height: 24px;
        padding: 0 24px;
      }

      .ant-btn-default {
        width: 118px;
        color: #2f353a;
        background: #ffffff;
        border-color: #2f353a !important;

        &:hover,
        &:active {
          color: #2f353a !important;
          background: #ffffff !important;
          border-color: #2f353a !important;
        }
      }

      .ant-btn-primary {
        width: 159px;
        background: #2f353a;
        border-color: #2f353a;
        color: #ffffff;

        &:hover,
        &:active {
          background: #2f353a;
          border-color: #2f353a;
        }

        &:disabled {
          color: #ffffff !important;
          background: #acaeb0 !important;
          border-color: #acaeb0 !important;

          &:hover,
          &:active {
            color: #ffffff !important;
            background: #acaeb0 !important;
            border-color: #acaeb0 !important;
          }
        }
      }
    }
  }

  .read-contacts-section {
    margin-top: 16px;

    .contacts-title {
      font-weight: 600;
      margin-bottom: 12px;
    }

    .custom-list-contact ol {
      list-style: decimal;
      padding-left: 40px;

      li::marker {
        color: #7e8285;
        font-weight: 500;
      }

      li {
        padding-left: 8px;
        margin-bottom: 6px;
      }
    }

    .custom-list-contact li strong {
      color: #7e8285;
    }

    .custom-list-contact li div {
      color: #7e8285;
    }

    .show-more-contacts,
    .show-less-contacts {
      font-weight: 500;
      font-size: 16px;
      line-height: 24px;
      color: #1284ed;
      cursor: pointer;
      text-decoration: underline;
      padding-left: 20px;
    }

    .contact-row {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 16px;
      width: 100%;

      div {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        color: #7e8285;

        strong {
          color: #7e8285;
          font-weight: 600;
        }
      }
    }
  }

  .mt-5 {
    margin-top: 20px;
  }

  @media (max-width: 768px) {
    .contact-form {
      .container-table {
        .custom-table-list-contact {
          :deep(.ant-table-content) {
            overflow-x: hidden !important;
          }

          :deep(.ant-table-container table) {
            width: 100% !important;
            table-layout: fixed !important;
          }

          :deep(.ant-table-thead) {
            display: none;
          }

          :deep(.ant-table-tbody > tr) {
            display: block;
            border: 1px solid #d9d9d9;
            border-radius: 8px;
            margin-bottom: 12px;
            overflow: hidden;
          }

          :deep(.ant-table-tbody > tr > td) {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 10px;
            padding: 8px 10px !important;
            white-space: normal !important;
            word-break: break-word;
            border-bottom: 1px solid #f0f0f0;
          }

          :deep(.ant-table-tbody > tr > td:last-child) {
            border-bottom: 0;
          }

          :deep(.ant-table-tbody > tr > td::before) {
            font-weight: 600;
            color: #2f353a;
            min-width: 110px;
            flex: 0 0 110px;
          }

          :deep(.ant-table-tbody > tr > td:nth-child(1)::before) {
            content: 'Tipo de contacto';
          }

          :deep(.ant-table-tbody > tr > td:nth-child(2)::before) {
            content: 'Nombre';
          }

          :deep(.ant-table-tbody > tr > td:nth-child(3)::before) {
            content: 'Teléfono';
          }

          :deep(.ant-table-tbody > tr > td:nth-child(4)::before) {
            content: 'Correo';
          }

          :deep(.ant-table-tbody > tr > td:nth-child(5)::before) {
            content: 'Ciudad';
          }

          :deep(.ant-table-tbody > tr > td:nth-child(6)::before) {
            content: 'Acciones';
          }

          :deep(.ant-table-cell) {
            font-size: 13px;
          }

          :deep(.ant-table-cell .ant-form-item) {
            margin-bottom: 0 !important;
            width: 100%;
          }

          :deep(.ant-table-cell .ant-form-item-control-input),
          :deep(.ant-table-cell .ant-input),
          :deep(.ant-table-cell .ant-select) {
            width: 100% !important;
          }

          .list-actions {
            justify-content: flex-start;
          }
        }
      }
    }
  }
</style>
