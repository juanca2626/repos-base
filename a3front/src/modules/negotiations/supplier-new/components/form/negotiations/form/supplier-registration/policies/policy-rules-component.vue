<template>
  <div class="mt-5">
    <div>
      <span class="main-title">Reglas</span>
    </div>
    <div class="mt-4">
      <!-- condicion de pago -->
      <a-collapse
        :defaultActiveKey="['payment-term']"
        expand-icon-position="end"
        ghost
        class="custom-collapse"
      >
        <template #expandIcon="{ isActive }">
          <font-awesome-icon
            :icon="isActive ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']"
            class="collapse-icon"
          />
        </template>

        <a-collapse-panel key="payment-term" class="custom-collapse-panel">
          <template #header>
            <div class="header-title">
              <span>Condición de pago</span>
            </div>
            <div class="header-divider"></div>
          </template>

          <template v-if="!formState.paymentTerm.isEditable">
            <policy-rule-read-row-component :isMultiple="false" @onEdit="enablePaymentTermEdit">
              <template #text>
                <p class="text-form">
                  El pago será
                  <span class="text-bold">
                    {{ formState.paymentTerm.paymentTypeName }}
                  </span>
                  <template v-if="formState.paymentTerm.paymentTypeId !== 3">
                    con
                    <span class="text-bold">
                      {{ formState.paymentTerm.conditionValue }}
                    </span>
                    {{ toRegularSingularText(formState.paymentTerm.conditionValue, 'días') }}
                    <span class="text-bold">
                      {{ toLowerText(formState.paymentTerm.conditionTypeName) }}
                    </span>
                  </template>
                </p>
                <div v-if="formState.paymentTerm.partialPaymentsEnabled">
                  <p
                    class="text-form"
                    v-for="(partial, index) in formState.paymentTerm.partialPayments"
                    :key="index"
                  >
                    El pago en partes ({{ index + 1 }}) será
                    <span class="text-bold">
                      {{ partial.partialConditionValue }}
                    </span>
                    {{ toRegularSingularText(partial.partialConditionValue, 'días') }}
                    <span class="text-bold">
                      {{
                        toLowerText(
                          filteredConditionTypes.find(
                            (c) => c.value === partial.partialConditionTypeId
                          )?.label || ''
                        )
                      }}
                    </span>
                    -
                    <span
                      class="text-bold"
                      v-if="partial.partialAmount && partial.partialAmountType"
                    >
                      {{ formatValueWithSymbol(partial.partialAmount, partial.partialAmountType) }}
                    </span>
                  </p>
                </div>
              </template>
            </policy-rule-read-row-component>
          </template>
          <template v-else>
            <div>
              <a-form>
                <div class="form-row-container pr-20 pl-20">
                  <span class="text-form"> Tipo de pago </span>

                  <a-select
                    class="custom-select-form"
                    placeholder="Seleccionar"
                    v-model:value="formState.paymentTerm.paymentTypeId"
                    allow-clear
                    :options="paymentTypes"
                    :class="getSelectErrorClass('paymentTerm', 'paymentTypeId')"
                  />

                  <template v-if="formState.paymentTerm.paymentTypeId !== 3">
                    <span class="text-form"> a </span>

                    <a-input-number
                      v-model:value="formState.paymentTerm.conditionValue"
                      :min="1"
                      :precision="0"
                      placeholder="0"
                      class="input-condition"
                      :class="getInputNumberErrorClass('paymentTerm', 'conditionValue')"
                    />

                    <span class="text-form"> días </span>

                    <a-select
                      class="custom-select-form"
                      placeholder="Seleccionar"
                      v-model:value="formState.paymentTerm.conditionTypeId"
                      allow-clear
                      :options="filteredConditionTypes"
                      :class="getSelectErrorClass('paymentTerm', 'conditionTypeId')"
                    />
                  </template>
                </div>

                <section>
                  <a-checkbox
                    v-model:checked="formState.paymentTerm.partialPaymentsEnabled"
                    @change="handlePartialPaymentsEnabled"
                    class="mt-20 select-none"
                    :disabled="isPartialPaymentsBlocked"
                    :class="{ 'pb-1': !formState.paymentTerm.partialPaymentsEnabled }"
                  >
                    Pago en partes
                  </a-checkbox>

                  <div
                    v-if="formState.paymentTerm.partialPaymentsEnabled"
                    class="send-list-options"
                  >
                    <div
                      v-for="(partial, index) in formState.paymentTerm.partialPayments"
                      :key="partial._uid || `pp-${index}`"
                      class="partial-payment-row-container"
                      :class="{ 'mb-3': index < formState.paymentTerm.partialPayments.length - 1 }"
                    >
                      <div class="row-item flex items-center gap-2 flex-wrap">
                        <div class="text-form ml-2">A</div>

                        <div class="field-group flex items-center gap-2">
                          <a-input-number
                            v-model:value="partial.partialConditionValue"
                            :min="1"
                            :precision="0"
                            placeholder="0"
                            class="input-condition"
                            :disabled="isPartialConditionValueDisabled(index)"
                            :class="{
                              'ant-input-number-status-error':
                                (isPartialConditionValueDisabled(index)
                                  ? false
                                  : !partial.partialConditionValue) &&
                                partial.partialConditionTypeId === 1,
                            }"
                          />
                          <span class="text-form">días</span>
                        </div>

                        <div class="field-group">
                          <a-select
                            class="custom-select-form"
                            placeholder="Seleccionar"
                            v-model:value="partial.partialConditionTypeId"
                            allow-clear
                            :options="getPartialConditionOptions(index)"
                          />
                        </div>

                        <div class="text-form">un pago de</div>

                        <div class="field-group">
                          <a-input-number
                            v-model:value="partial.partialAmount"
                            :min="1"
                            placeholder="0"
                            class="input-condition"
                          />
                        </div>

                        <div class="field-group">
                          <a-select
                            class="custom-select-form"
                            placeholder="Seleccionar"
                            v-model:value="partial.partialAmountType"
                            allow-clear
                            :options="valueTypes"
                          />
                        </div>

                        <div class="actions flex items-center gap-2 ml-auto">
                          <div
                            class="action-icon cursor-pointer"
                            @click="addPartialPayment(index)"
                            v-if="canAddPartialPayment"
                          >
                            <svg
                              width="24"
                              height="24"
                              viewBox="0 0 24 24"
                              fill="none"
                              xmlns="http://www.w3.org/2000/svg"
                            >
                              <path
                                d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                              <path
                                d="M12 8V16"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                              <path
                                d="M8 12H16"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                            </svg>
                          </div>
                          <div
                            class="action-icon cursor-pointer"
                            @click="deletePartialPayment(index)"
                            v-if="formState.paymentTerm.partialPayments.length > 1"
                          >
                            <font-awesome-icon
                              icon="fa-regular fa-trash-can"
                              style="font-size: 20px; color: #1284ed"
                            />
                          </div>
                        </div>
                      </div>
                      <div
                        v-if="
                          errorsPartialPaymentsRows[index] &&
                          errorsPartialPaymentsRows[index].length > 0
                        "
                        class="mt-2 ml-4"
                      >
                        <form-error-alert :errors="errorsPartialPaymentsRows[index]" />
                      </div>
                    </div>
                  </div>
                </section>
                <div
                  v-if="errorsPaymentTerm.length > 0"
                  :class="showPartialPaymentInputs ? 'mt-5' : 'mt-3'"
                >
                  <div v-if="errorsPaymentTerm.length > 0" class="mt-2">
                    <form-error-alert :errors="errorsPaymentTerm" />
                  </div>
                </div>
              </a-form>
            </div>
          </template>
        </a-collapse-panel>
      </a-collapse>
      <!-- condicion de pago -->

      <!-- cancelacion -->
      <a-collapse
        :defaultActiveKey="['cancellation']"
        expand-icon-position="end"
        ghost
        class="custom-collapse mt-4"
      >
        <template #expandIcon="{ isActive }">
          <font-awesome-icon
            :icon="isActive ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']"
            class="collapse-icon"
          />
        </template>

        <a-collapse-panel key="cancellation" class="custom-collapse-panel">
          <template #header>
            <div class="header-title">
              <span>Cancelación</span>
            </div>
            <div class="header-divider"></div>
          </template>

          <div class="collapse-content-with-action">
            <template v-if="formState.cancellations.length === 0">
              <no-data-message-component />
            </template>
            <template v-else>
              <template v-for="(row, index) in formState.cancellations">
                <template v-if="!row.isEditable">
                  <policy-rule-read-row-component
                    :isMultiple="true"
                    :class="{ 'mt-4': index > 0 }"
                    @onEdit="enableCancellationEdit(index)"
                  >
                    <template #text>
                      <div>
                        Hasta
                        <span class="text-bold">
                          {{ row.timeLimitValue }}
                          {{
                            toLowerText(
                              toRegularSingularText(
                                row.timeLimitValue,
                                getTimeUnitName(row.timeLimitUnit)
                              )
                            )
                          }}
                        </span>
                        antes del servicio, genera
                        <span class="text-bold" v-if="row.penaltyValue && row.penaltyType">
                          {{ formatValueWithSymbol(row.penaltyValue, row.penaltyType) }}
                        </span>
                        en penalidad
                      </div>
                      <div v-if="row.automaticReconfirmation" class="mt-1">
                        <span class="text-bold">Activado para</span> reconfirmaciones automáticas
                      </div>
                    </template>
                  </policy-rule-read-row-component>
                </template>
                <template v-else>
                  <div :class="{ 'mt-4': index > 0 }" class="pl-20 pr-20">
                    <a-form>
                      <div class="cancellation-inline-row">
                        <span class="text-form"> Hasta </span>

                        <a-input-number
                          v-model:value="row.timeLimitValue"
                          :min="1"
                          :precision="0"
                          placeholder="0"
                          class="input-condition"
                          :class="
                            getInputNumberErrorClass('cancellations', 'timeLimitValue', index)
                          "
                        />

                        <a-select
                          class="custom-select-form"
                          placeholder="Seleccionar"
                          v-model:value="row.timeLimitUnit"
                          allow-clear
                          :options="timeUnits"
                          :class="getSelectErrorClass('cancellations', 'timeLimitUnit', index)"
                        />

                        <span class="text-form cancellation-inline-text">
                          antes del servicio, genera
                        </span>

                        <a-input-number
                          v-model:value="row.penaltyValue"
                          :min="0.01"
                          placeholder="0"
                          class="input-condition"
                          :class="getInputNumberErrorClass('cancellations', 'penaltyValue', index)"
                        />

                        <a-select
                          class="custom-select-form"
                          placeholder="Seleccionar"
                          v-model:value="row.penaltyType"
                          allow-clear
                          :options="valueTypes"
                          :class="getSelectErrorClass('cancellations', 'penaltyType', index)"
                        />

                        <span class="text-form cancellation-inline-text"> en penalidad </span>

                        <div class="actions flex items-center gap-2 ml-auto">
                          <div class="action-icon cursor-pointer" @click="addCancellation(index)">
                            <svg
                              width="24"
                              height="24"
                              viewBox="0 0 24 24"
                              fill="none"
                              xmlns="http://www.w3.org/2000/svg"
                            >
                              <path
                                d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                              <path
                                d="M12 8V16"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                              <path
                                d="M8 12H16"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                            </svg>
                          </div>
                          <div
                            class="action-icon cursor-pointer"
                            @click="deleteCancellation(index)"
                            v-if="formState.cancellations.length > 1"
                          >
                            <font-awesome-icon
                              icon="fa-regular fa-trash-can"
                              style="font-size: 20px; color: #1284ed"
                            />
                          </div>
                        </div>
                      </div>

                      <div class="send-list-options" v-if="showCancellationScope">
                        <div class="mb-3">
                          <span class="text-form">Alcance de la Cancelación</span>
                        </div>
                        <div class="flex items-center gap-2">
                          <a-select
                            class="custom-select-form"
                            placeholder="Seleccionar"
                            v-model:value="row.cancellationScope"
                            allow-clear
                            :options="cancellationScopes"
                          />
                          <template v-if="row.cancellationScope === 'partial'">
                            <a-input-number
                              v-model:value="row.cancellationPartialValue"
                              :min="1"
                              :precision="0"
                              placeholder="0"
                              class="input-condition"
                            />
                            <a-select
                              class="custom-select-form"
                              placeholder="Seleccionar"
                              v-model:value="row.cancellationPartialUnit"
                              allow-clear
                              :options="cancellationPenaltyScopes"
                            />
                          </template>
                          <span class="text-form">de la reserva</span>
                        </div>
                      </div>

                      <div
                        v-if="errorsCancellations[index] && errorsCancellations[index].length > 0"
                        class="mt-2 ml-4"
                      >
                        <form-error-alert :errors="errorsCancellations[index]" />
                      </div>
                    </a-form>
                  </div>
                </template>
              </template>
              <div v-if="formState.cancellations.length > 0">
                <a-checkbox v-model:checked="globalAutomaticReconfirmation" class="mt-20">
                  Reconfirmaciones automáticas
                </a-checkbox>
              </div>
            </template>
          </div>
        </a-collapse-panel>
      </a-collapse>
      <!-- cancelacion -->

      <!-- reconfirmación -->
      <a-collapse
        v-model:activeKey="reconfirmationActiveKey"
        expand-icon-position="end"
        ghost
        class="custom-collapse mt-4"
        :class="{
          'disabled-section': globalAutomaticReconfirmation,
          'content-disabled':
            formState.reconfirmationsNotApplicable && !globalAutomaticReconfirmation,
        }"
        @change="handleReconfirmationCollapseChange"
      >
        <template #expandIcon="{ isActive }">
          <font-awesome-icon
            :icon="isActive ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']"
            class="collapse-icon"
          />
        </template>

        <a-collapse-panel key="reconfirmation" class="custom-collapse-panel">
          <template #header>
            <div class="header-title">
              <div class="reconfirmation-title">
                <span>Reconfirmación</span>
                <a-checkbox
                  v-model:checked="formState.reconfirmationsNotApplicable"
                  class="not-applicable-checkbox"
                  :disabled="globalAutomaticReconfirmation"
                  @click.stop
                  @change="handleReconfirmationsNotApplicableChange"
                >
                  No aplica
                </a-checkbox>
              </div>
              <div class="header-divider"></div>
            </div>
          </template>

          <div class="collapse-content-with-action">
            <template v-if="formState.reconfirmations.length === 0">
              <no-data-message-component />
            </template>
            <template v-else>
              <template v-for="(row, index) in formState.reconfirmations">
                <template v-if="!row.isEditable">
                  <policy-rule-read-row-component
                    :isMultiple="true"
                    :class="{ 'mt-4': index > 0 }"
                    @onEdit="enableReconfirmationEdit(index)"
                  >
                    <template #text>
                      <p class="text-form">
                        Toda
                        <span class="text-bold">
                          {{ toLowerText(getConfirmationTypeName(row.confirmationType)) }}
                        </span>
                        debe realizarse
                        <span class="text-bold">
                          {{ row.timeValue }}
                          {{
                            toLowerText(
                              toRegularSingularText(row.timeValue, getTimeUnitName(row.timeUnit))
                            )
                          }}
                        </span>
                        antes del servicio
                      </p>
                      <p class="text-form" v-if="row.sendListEnabled">
                        Activar envío de lista -
                        <span class="text-bold">
                          {{ row.listType === 'preliminary' ? 'Preliminar' : 'Final' }}
                        </span>
                        - Enviar lista con
                        <span class="text-bold" v-if="row.listSendTimeValue">
                          {{ row.listSendTimeValue }}
                          {{
                            toLowerText(
                              toRegularSingularText(
                                row.listSendTimeValue,
                                getTimeUnitName(row.listSendTimeUnit ?? null)
                              )
                            )
                          }}
                        </span>
                        antes del servicio, y un cupo de
                        <span class="text-bold">{{ row.unassignedQuota }}</span>
                        no asignados
                      </p>
                    </template>
                  </policy-rule-read-row-component>
                </template>
                <template v-else>
                  <div :class="{ 'mt-4': index > 0 }">
                    <a-form>
                      <div class="flex items-center gap-2 pl-20 pr-20">
                        <span class="text-form"> Toda </span>

                        <a-select
                          class="custom-select-form"
                          placeholder="Seleccionar"
                          v-model:value="row.confirmationType"
                          allow-clear
                          :options="confirmationTypes"
                          :class="getSelectErrorClass('reconfirmations', 'confirmationType', index)"
                        />

                        <span class="text-form"> debe realizarse </span>

                        <a-input-number
                          v-model:value="row.timeValue"
                          :min="1"
                          :precision="0"
                          placeholder="0"
                          class="input-condition"
                          :class="getInputNumberErrorClass('reconfirmations', 'timeValue', index)"
                        />

                        <a-select
                          class="custom-select-form"
                          placeholder="Seleccionar"
                          v-model:value="row.timeUnit"
                          allow-clear
                          :options="reconfirmationTimeUnits"
                          :class="getSelectErrorClass('reconfirmations', 'timeUnit', index)"
                        />

                        <span class="text-form"> antes del servicio </span>

                        <div class="actions flex items-center gap-2 ml-auto">
                          <div class="action-icon cursor-pointer" @click="addReconfirmation(index)">
                            <svg
                              width="24"
                              height="24"
                              viewBox="0 0 24 24"
                              fill="none"
                              xmlns="http://www.w3.org/2000/svg"
                            >
                              <path
                                d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                              <path
                                d="M12 8V16"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                              <path
                                d="M8 12H16"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                            </svg>
                          </div>
                          <div
                            class="action-icon cursor-pointer"
                            @click="deleteReconfirmation(index)"
                            v-if="formState.reconfirmations.length > 1"
                          >
                            <font-awesome-icon
                              icon="fa-regular fa-trash-can"
                              style="font-size: 20px; color: #1284ed"
                            />
                          </div>
                        </div>
                      </div>

                      <div class="mt-4 pr-20">
                        <a-checkbox
                          v-model:checked="row.sendListEnabled"
                          @change="handleSendListEnabled(index)"
                        >
                          Activar envío de lista
                        </a-checkbox>
                      </div>

                      <div v-if="row.sendListEnabled" class="send-list-options mt-4 pl-20 pr-20">
                        <a-radio-group
                          v-model:value="row.listType"
                          class="mb-3"
                          @change="handleListTypeChange(index)"
                        >
                          <a-radio value="preliminary">Preliminar</a-radio>
                          <a-radio value="final">Final</a-radio>
                        </a-radio-group>

                        <div class="flex items-center gap-2">
                          <span class="text-form"> Enviar lista con </span>

                          <a-input-number
                            v-model:value="row.listSendTimeValue"
                            :min="1"
                            :precision="0"
                            placeholder="0"
                            class="input-condition"
                            :class="
                              getInputNumberErrorClass(
                                'reconfirmations',
                                'listSendTimeValue',
                                index
                              )
                            "
                          />

                          <a-select
                            class="custom-select-form"
                            placeholder="Seleccionar"
                            v-model:value="row.listSendTimeUnit"
                            allow-clear
                            :options="timeUnits"
                            :class="
                              getSelectErrorClass('reconfirmations', 'listSendTimeUnit', index)
                            "
                          />

                          <span class="text-form"> antes del servicio</span>

                          <template v-if="row.listType === 'preliminary'">
                            <span class="text-form">, y un cupo de </span>

                            <a-input-number
                              v-model:value="row.unassignedQuota"
                              :min="1"
                              :precision="0"
                              placeholder="0"
                              class="input-condition"
                              :class="
                                getInputNumberErrorClass(
                                  'reconfirmations',
                                  'unassignedQuota',
                                  index
                                )
                              "
                            />

                            <span class="text-form"> no asignados </span>
                          </template>
                        </div>
                      </div>

                      <div
                        v-if="
                          errorsReconfirmations[index] && errorsReconfirmations[index].length > 0
                        "
                        class="mt-2 ml-4"
                      >
                        <form-error-alert :errors="errorsReconfirmations[index]" />
                      </div>
                    </a-form>
                  </div>
                </template>
              </template>
            </template>
          </div>
        </a-collapse-panel>
      </a-collapse>
      <!-- reconfirmación -->

      <!--liberados -->
      <a-collapse
        v-model:activeKey="releasedActiveKey"
        expand-icon-position="end"
        ghost
        class="custom-collapse mt-4"
        :class="{ 'content-disabled': formState.releasedNotApplicable }"
        @change="handleReleasedCollapseChange"
      >
        <template #expandIcon="{ isActive }">
          <font-awesome-icon
            :icon="isActive ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']"
            class="collapse-icon"
          />
        </template>

        <a-collapse-panel key="released" class="custom-collapse-panel">
          <template #header>
            <div class="header-title">
              <div class="released-title">
                <span>Liberados</span>
                <a-checkbox
                  v-model:checked="formState.releasedNotApplicable"
                  class="not-applicable-checkbox"
                  @click.stop
                  @change="handleReleasedNotApplicableChange"
                >
                  No aplica
                </a-checkbox>
              </div>
            </div>
            <div class="header-divider"></div>
          </template>
          <div>
            <template v-if="formState.released.length === 0">
              <no-data-message-component />
            </template>
            <template v-else>
              <template v-for="(row, index) in formState.released">
                <template v-if="!row.isEditable">
                  <policy-rule-read-row-component
                    :isMultiple="true"
                    :class="{ 'mt-4': index > 0 }"
                    @onEdit="enableReleasedEdit(index)"
                  >
                    <template #text>
                      <div>
                        Por cada
                        <span class="text-bold">
                          {{ row.timeLimitValue }}
                          {{
                            toLowerText(
                              applyPluralToText(
                                row.timeLimitValue,
                                getReleaseTypeName(row.releaseType)
                              )
                            )
                          }}
                        </span>
                        ,
                        <span class="text-bold">
                          {{ row.releaseQuantity }}
                        </span>
                        {{ toRegularSingularText(row.releaseQuantity, 'liberados') }}
                        <template v-if="row.benefitType">
                          ,
                          <span class="text-bold">
                            {{ getBenefitTypeName(row.benefitType) }}
                          </span>
                        </template>
                      </div>
                      <div v-if="row.hasMaximumCap && row.maximumCapValue" class="mt-1">
                        Tope máximo para liberados
                        <span class="text-bold">{{ row.maximumCapValue }}</span>
                      </div>
                    </template>
                  </policy-rule-read-row-component>
                </template>
                <template v-else>
                  <div :class="{ 'mt-4': index > 0 }">
                    <a-form>
                      <div class="released-inline-row pl-20 pr-20">
                        <div class="released-inline-fields">
                          <span class="text-form"> Por cada </span>

                          <a-input-number
                            v-model:value="row.timeLimitValue"
                            :min="1"
                            :precision="0"
                            placeholder="0"
                            class="input-condition"
                            :class="getInputNumberErrorClass('released', 'timeLimitValue', index)"
                          />

                          <a-select
                            class="custom-select-form"
                            placeholder="Seleccionar"
                            v-model:value="row.releaseType"
                            allow-clear
                            :options="[
                              { label: 'Habitaciones', value: 'rooms' },
                              { label: 'Pasajeros', value: 'pax' },
                            ]"
                            :class="getSelectErrorClass('released', 'releaseType', index)"
                          />

                          <span class="text-form"> , </span>

                          <a-input-number
                            v-model:value="row.releaseQuantity"
                            :min="1"
                            :precision="0"
                            placeholder="0"
                            class="input-condition"
                            :class="getInputNumberErrorClass('released', 'releaseQuantity', index)"
                          />
                          <span class="text-form"> liberados</span>

                          <template v-if="row.releaseType === 'rooms'">
                            <span class="text-form ml-2">, en </span>
                            <a-select
                              class="custom-select-form"
                              placeholder="Seleccionar"
                              v-model:value="row.benefitType"
                              allow-clear
                              :options="benefitTypes"
                              :class="getSelectErrorClass('released', 'benefitType', index)"
                            />
                            <span class="text-form ml-2"> con desayuno </span>
                            <a-select
                              class="custom-select-form"
                              placeholder="Seleccionar"
                              v-model:value="row.breakfastIncluded"
                              allow-clear
                              :options="[
                                { label: 'Incluido', value: true },
                                { label: 'No incluido', value: false },
                              ]"
                              :class="getSelectErrorClass('released', 'breakfastIncluded', index)"
                            />
                          </template>
                        </div>

                        <div class="actions flex items-center gap-2 ml-auto">
                          <div class="action-icon cursor-pointer" @click="addReleased(index)">
                            <svg
                              width="24"
                              height="24"
                              viewBox="0 0 24 24"
                              fill="none"
                              xmlns="http://www.w3.org/2000/svg"
                            >
                              <path
                                d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                              <path
                                d="M12 8V16"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                              <path
                                d="M8 12H16"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                              />
                            </svg>
                          </div>
                          <div
                            class="action-icon cursor-pointer"
                            @click="deleteReleased(index)"
                            v-if="formState.released.length > 1"
                          >
                            <font-awesome-icon
                              icon="fa-regular fa-trash-can"
                              style="font-size: 20px; color: #1284ed"
                            />
                          </div>
                        </div>
                      </div>

                      <div class="flex items-center gap-2 mt-4">
                        <a-checkbox
                          v-model:checked="row.hasMaximumCap"
                          @change="
                            (e: any) => {
                              if (!e.target.checked) row.maximumCapValue = null;
                            }
                          "
                        >
                          Tope máximo para liberados
                        </a-checkbox>

                        <a-input-number
                          v-if="row.hasMaximumCap"
                          v-model:value="row.maximumCapValue"
                          :min="1"
                          :precision="0"
                          placeholder="0"
                          class="input-condition ml-3"
                          :class="getInputNumberErrorClass('released', 'maximumCapValue', index)"
                        />
                      </div>

                      <div
                        v-if="errorsReleased[index] && errorsReleased[index].length > 0"
                        class="mt-2 ml-4"
                      >
                        <form-error-alert :errors="errorsReleased[index]" />
                      </div>
                    </a-form>
                  </div>
                </template>
              </template>
            </template>
          </div>
        </a-collapse-panel>
      </a-collapse>
      <!-- liberados -->

      <!-- edades -->
      <a-collapse
        :defaultActiveKey="['children']"
        expand-icon-position="end"
        ghost
        class="custom-collapse mt-4"
      >
        <template #expandIcon="{ isActive }">
          <font-awesome-icon
            :icon="isActive ? ['fas', 'chevron-up'] : ['fas', 'chevron-down']"
            class="collapse-icon"
          />
        </template>

        <a-collapse-panel key="children" class="custom-collapse-panel">
          <template #header>
            <div class="header-title">
              <span>Edades</span>
            </div>
            <div class="header-divider"></div>
          </template>

          <template v-if="!formState.children.isEditable">
            <policy-rule-read-row-component :isMultiple="false" @onEdit="enableChildrenEdit">
              <template #text>
                <p
                  class="text-form"
                  v-if="formState.children.infantAgeMin && formState.children.infantAgeMax"
                >
                  Infante de
                  <span class="text-bold">
                    {{ formState.children.infantAgeMin }}
                  </span>
                  a
                  <span class="text-bold">
                    {{ formState.children.infantAgeMax }}
                  </span>
                  años
                  <template
                    v-if="
                      formState.children.inclusionsEnabled &&
                      formState.children.infantInclusions.length > 0
                    "
                  >
                    -
                    <span class="text-bold">{{ formState.children.infantInclusions.length }}</span>
                    {{
                      formState.children.infantInclusions.length === 1 ? 'inclusión' : 'inclusiones'
                    }}
                  </template>
                </p>
                <p
                  class="text-form"
                  v-if="formState.children.childAgeMin && formState.children.childAgeMax"
                >
                  Niños de
                  <span class="text-bold">
                    {{ formState.children.childAgeMin }}
                  </span>
                  a
                  <span class="text-bold">
                    {{ formState.children.childAgeMax }}
                  </span>
                  años
                  <template
                    v-if="
                      formState.children.inclusionsEnabled &&
                      formState.children.inclusions.length > 0
                    "
                  >
                    -
                    <span class="text-bold">{{ formState.children.inclusions.length }}</span>
                    {{ formState.children.inclusions.length === 1 ? 'inclusión' : 'inclusiones' }}
                  </template>
                </p>
                <p
                  class="text-form mt-3 text-justify"
                  v-if="formState.children.additionalInformation"
                >
                  <span class="text-bold"> Información adicional: </span>
                  <span>
                    {{ formState.children.additionalInformation }}
                  </span>
                </p>
              </template>
            </policy-rule-read-row-component>
          </template>
          <template v-else>
            <div>
              <a-form>
                <div class="p-20">
                  <div class="form-row-container">
                    <template v-if="formState.children.inclusionsEnabled">
                      <a-radio-group
                        v-model:value="formState.children.selectedAgeType"
                        class="flex items-center gap-2"
                        @change="handleAgeTypeChange"
                      >
                        <a-radio value="infant">
                          <span class="text-form">Infante de </span>
                        </a-radio>
                        <a-input-number
                          v-model:value="formState.children.infantAgeMin"
                          :min="1"
                          :max="18"
                          :precision="0"
                          placeholder="0"
                          class="input-condition"
                          :class="getInputNumberErrorClass('children', 'infantAgeMin')"
                          :disabled="formState.children.selectedAgeType !== 'infant'"
                        />
                        <span class="text-form"> a </span>
                        <a-input-number
                          v-model:value="formState.children.infantAgeMax"
                          :min="formState.children.infantAgeMin ?? 1"
                          :max="18"
                          :precision="0"
                          placeholder="0"
                          class="input-condition"
                          :class="getInputNumberErrorClass('children', 'infantAgeMax')"
                          :disabled="formState.children.selectedAgeType !== 'infant'"
                        />
                        <span class="text-form"> años </span>

                        <a-radio value="child" class="ml-4">
                          <span class="text-form">Niños de </span>
                        </a-radio>
                        <a-input-number
                          v-model:value="formState.children.childAgeMin"
                          :min="(formState.children.infantAgeMax ?? 0) + 1"
                          :precision="0"
                          placeholder="0"
                          class="input-condition"
                          :class="getInputNumberErrorClass('children', 'childAgeMin')"
                          :disabled="formState.children.selectedAgeType !== 'child'"
                        />
                        <span class="text-form"> a </span>
                        <a-input-number
                          v-model:value="formState.children.childAgeMax"
                          :min="
                            Math.max(
                              (formState.children.infantAgeMax ?? 0) + 1,
                              formState.children.childAgeMin ?? 1
                            )
                          "
                          :precision="0"
                          placeholder="0"
                          class="input-condition"
                          :class="getInputNumberErrorClass('children', 'childAgeMax')"
                          :disabled="formState.children.selectedAgeType !== 'child'"
                        />
                        <span class="text-form"> años </span>
                      </a-radio-group>
                    </template>
                    <template v-else>
                      <span class="text-form"> Infante de </span>
                      <a-input-number
                        v-model:value="formState.children.infantAgeMin"
                        :min="1"
                        :max="18"
                        :precision="0"
                        placeholder="0"
                        class="input-condition"
                        :class="getInputNumberErrorClass('children', 'infantAgeMin')"
                      />
                      <span class="text-form"> a </span>
                      <a-input-number
                        v-model:value="formState.children.infantAgeMax"
                        :min="formState.children.infantAgeMin ?? 1"
                        :max="18"
                        :precision="0"
                        placeholder="0"
                        class="input-condition"
                        :class="getInputNumberErrorClass('children', 'infantAgeMax')"
                      />
                      <span class="text-form"> años </span>

                      <span class="text-form ml-4"> Niños de </span>
                      <a-input-number
                        v-model:value="formState.children.childAgeMin"
                        :min="(formState.children.infantAgeMax ?? 0) + 1"
                        :precision="0"
                        placeholder="0"
                        class="input-condition"
                        :class="getInputNumberErrorClass('children', 'childAgeMin')"
                      />
                      <span class="text-form"> a </span>
                      <a-input-number
                        v-model:value="formState.children.childAgeMax"
                        :min="
                          Math.max(
                            (formState.children.infantAgeMax ?? 0) + 1,
                            formState.children.childAgeMin ?? 1
                          )
                        "
                        :precision="0"
                        placeholder="0"
                        class="input-condition"
                        :class="getInputNumberErrorClass('children', 'childAgeMax')"
                      />
                      <span class="text-form"> años </span>
                    </template>
                  </div>

                  <div v-if="errorsChildren.length > 0" class="mt-2">
                    <form-error-alert :errors="errorsChildren" />
                  </div>
                </div>

                <div class="mt-4">
                  <a-checkbox
                    v-model:checked="formState.children.inclusionsEnabled"
                    @change="handleInclusionsEnabled"
                  >
                    Activar inclusiones
                  </a-checkbox>

                  <div v-if="formState.children.inclusionsEnabled" class="mt-4">
                    <div class="inclusions-title-row mb-3">
                      <span class="inclusions-title">
                        {{
                          formState.children.selectedAgeType === 'infant'
                            ? 'Inclusiones infantes'
                            : 'Inclusiones niños'
                        }}
                      </span>
                      <span class="inclusions-subtitle">
                        {{
                          formState.children.selectedAgeType === 'infant'
                            ? 'que incluye o no para infantes'
                            : 'que incluye o no para niños'
                        }}
                      </span>
                    </div>

                    <div class="inclusions-table">
                      <div class="inclusions-header">
                        <div class="inclusions-col-description">Descripción</div>
                        <div class="inclusions-col-small">Incluye | No incluye</div>
                        <div class="inclusions-col-small">Visible</div>
                        <div class="inclusions-col-actions">Acciones</div>
                      </div>

                      <div
                        v-for="(inclusion, index) in currentInclusions"
                        :key="`${formState.children.selectedAgeType}-${index}`"
                        class="inclusions-row"
                      >
                        <div class="inclusions-col-description">
                          <a-select
                            v-model:value="inclusion.description"
                            placeholder="Selecciona"
                            allow-clear
                            :options="childrenInclusions"
                            class="w-full"
                          />
                        </div>
                        <div class="inclusions-col-small">
                          <a-switch v-model:checked="inclusion.isIncluded" />
                        </div>
                        <div class="inclusions-col-small">
                          <a-switch v-model:checked="inclusion.isVisible" />
                        </div>
                        <div class="inclusions-col-actions">
                          <div
                            class="inclusion-add-icon"
                            @click="addChildrenInclusion"
                            v-if="index === currentInclusions.length - 1"
                          >
                            <svg
                              width="24"
                              height="24"
                              viewBox="0 0 24 24"
                              fill="none"
                              xmlns="http://www.w3.org/2000/svg"
                            >
                              <circle cx="12" cy="12" r="10" stroke="#1284ED" stroke-width="2" />
                              <path
                                d="M12 8V16M8 12H16"
                                stroke="#1284ED"
                                stroke-width="2"
                                stroke-linecap="round"
                              />
                            </svg>
                          </div>
                          <div
                            v-if="index > 0"
                            class="inclusion-delete-icon"
                            @click="deleteChildrenInclusion(index)"
                          >
                            <font-awesome-icon icon="fa-regular fa-trash-can" />
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- <div class="additional-information">
                  <div>
                    <span class="additional-information-title"> Información adicional </span>
                  </div>
                  <div class="mt-2">
                    <a-textarea
                      v-model:value="formState.children.additionalInformation"
                      :rows="3"
                      :maxlength="200"
                      placeholder="Escribe información adicional sobre las reglas para las edades de los menores"
                      class="w-full"
                    />
                  </div>
                  <div class="info-length-text-area">
                    <span>{{ formState.children.additionalInformation?.length ?? 0 }} / 200</span>
                  </div>
                </div> -->
              </a-form>
            </div>
          </template>
        </a-collapse-panel>
      </a-collapse>
      <!-- edades -->

      <!-- reglas adicionales dinámicas -->
      <a-collapse
        v-for="(rule, index) in formState.additionalRules"
        :key="`additional-${rule.policyId}-${index}`"
        :defaultActiveKey="[`additional-${rule.policyId}`]"
        expand-icon-position="end"
        ghost
        class="custom-collapse mt-4"
      >
        <template #expandIcon>
          <div class="action-icon cursor-pointer" @click.stop="deleteAdditionalRule(index)">
            <font-awesome-icon
              icon="fa-regular fa-trash-can"
              style="font-size: 20px; color: #1284ed"
            />
          </div>
        </template>

        <a-collapse-panel :key="`additional-${rule.policyId}`" class="custom-collapse-panel">
          <template #header>
            <div class="header-title">
              <span>{{ rule.policyName }}</span>
            </div>
          </template>

          <template v-if="!rule.isEditable">
            <policy-rule-read-row-component
              :isMultiple="false"
              @onEdit="enableAdditionalRuleEdit(index)"
            >
              <template #text>
                <div
                  class="text-form text-justify html-content"
                  v-if="rule.contentHtml || rule.additionalInformation"
                  v-html="rule.contentHtml || rule.additionalInformation"
                ></div>
                <p class="text-form text-justify" v-else>Sin información adicional</p>
              </template>
            </policy-rule-read-row-component>
          </template>
          <template v-else>
            <div class="additional-rule-edit-container">
              <a-form>
                <div class="additional-rule-editor">
                  <EditorQuillComponent
                    v-model="rule.additionalInformation"
                    placeholder="Escribe información adicional sobre esta regla"
                    :max-length="1000"
                  />
                </div>
              </a-form>
            </div>
          </template>
        </a-collapse-panel>
      </a-collapse>
      <!-- reglas adicionales dinámicas -->
    </div>

    <div class="btn-add-rule" @click="openAdditionalRulesDrawer">
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
      >
        <path
          d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
          stroke="#1284ED"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M12 8V16"
          stroke="#1284ED"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M8 12H16"
          stroke="#1284ED"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
      <span> Reglas adicionales </span>
    </div>

    <div class="mt-5 btn-actions">
      <a-button
        size="large"
        type="default"
        class="button-cancel-white btn-cancel"
        @click="handleBack"
      >
        Atrás
      </a-button>
      <a-button
        size="large"
        type="primary"
        class="button-action-black btn-save"
        :disabled="isLoading"
        @click="handleSave"
      >
        Continuar
      </a-button>
    </div>

    <!-- Drawer de Reglas Adicionales -->
    <a-drawer
      v-model:open="isAdditionalRulesDrawerOpen"
      placement="right"
      :width="500"
      :closable="false"
      @close="closeAdditionalRulesDrawer"
    >
      <template #title>
        <div class="drawer-header">
          <svg
            width="24"
            height="24"
            viewBox="0 0 24 24"
            fill="none"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
              stroke="#2F353A"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M12 8V16"
              stroke="#2F353A"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
            <path
              d="M8 12H16"
              stroke="#2F353A"
              stroke-width="2"
              stroke-linecap="round"
              stroke-linejoin="round"
            />
          </svg>
          <span class="drawer-header-text">Reglas adicionales para políticas</span>
          <font-awesome-icon
            icon="fa-solid fa-xmark"
            class="drawer-close-icon"
            @click="closeAdditionalRulesDrawer"
          />
        </div>
      </template>
      <div class="drawer-content">
        <div class="drawer-field">
          <label class="drawer-label">Reglas adicionales</label>
          <a-select
            v-model:value="selectedAdditionalPolicies"
            mode="multiple"
            placeholder="Selecciona"
            :options="availableAdditionalPolicies"
            class="w-full multiselect-with-checkbox"
            allow-clear
            :max-tag-count="2"
          >
            <template #option="{ label, value }">
              <div class="multiselect-option">
                <a-checkbox :checked="selectedAdditionalPolicies.includes(value)" />
                <span>{{ label }}</span>
              </div>
            </template>
          </a-select>
        </div>

        <div class="drawer-bottom-divider"></div>

        <div class="drawer-button-container">
          <a-button type="primary" size="large" @click="handleAdditionalRulesSave">
            Agregar
          </a-button>
        </div>
      </div>
    </a-drawer>
  </div>
</template>

<script setup lang="ts">
  import EditorQuillComponent from '@/modules/negotiations/products/configuration/components/EditorQuillComponent.vue';
  import PolicyRuleReadRowComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/policies/policy-rule-read-row-component.vue';
  import FormErrorAlert from '@/modules/negotiations/supplier-new/components/form/partials/form-error-alert.vue';
  import NoDataMessageComponent from '@/modules/negotiations/supplier-new/components/form/partials/no-data-message-component.vue';
  import { usePolicyRuleComposable } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/policies/rules/policy-rule.composable';
  import { toLowerText } from '@/modules/negotiations/suppliers/helpers/supplier-form-helper';
  import { computed, ref, watch } from 'vue';

  const {
    isLoading,
    formState,
    filteredConditionTypes,
    isPartialConditionValueDisabled,
    paymentTypes,
    valueTypes,
    timeUnits,
    confirmationTypes,
    // releaseTypes,
    // benefitTypes,
    childrenInclusions,
    cancellationPenaltyScopes,
    cancellationScopes,
    showCancellationScope,
    // policiesClone,
    availableAdditionalPolicies,
    isAdditionalRulesDrawerOpen,
    selectedAdditionalPolicies,
    showPartialPaymentInputs,
    // showCancellationPenaltyScopeSelector,
    errorsPaymentTerm,
    errorsPartialPaymentsRows,
    errorsCancellations,
    errorsReconfirmations,
    errorsReleased,
    errorsChildren,

    addReleased, // Added here
    addCancellation,
    deleteCancellation,
    deleteReconfirmation,
    deleteReleased,
    addReconfirmation,
    handleSave,
    getInputNumberErrorClass,
    getSelectErrorClass,
    formatValueWithSymbol,
    getTimeUnitName,
    getConfirmationTypeName,
    getReleaseTypeName,
    getBenefitTypeName,
    toRegularSingularText,
    applyPluralToText,
    enablePaymentTermEdit,
    enableCancellationEdit,
    enableReconfirmationEdit,
    enableReleasedEdit,
    enableChildrenEdit,
    handlePartialPaymentsEnabled,
    handleSendListEnabled,
    handleListTypeChange,
    handleInclusionsEnabled,
    addChildrenInclusion,
    deleteChildrenInclusion,
    currentInclusions,
    handleAgeTypeChange,
    openAdditionalRulesDrawer,
    closeAdditionalRulesDrawer,
    handleAdditionalRulesSave,
    enableAdditionalRuleEdit,
    deleteAdditionalRule,
    // backToPolicies,
    handleBack,
    addPartialPayment,
    deletePartialPayment,
    benefitTypes,
    getPartialConditionOptions,
    canAddPartialPayment,
    isPartialPaymentsBlocked,
  } = usePolicyRuleComposable();

  const globalAutomaticReconfirmation = computed({
    get: () => {
      // Return true if all cancellations have it checked
      if (formState.cancellations.length === 0) return false;
      return formState.cancellations.every((c) => c.automaticReconfirmation);
    },
    set: (val: boolean) => {
      // Aplicar el valor a todas las cancelaciones
      formState.cancellations.forEach((c) => {
        c.automaticReconfirmation = val;
      });

      if (val) {
        // Si se activa, limpiar todas las reconfirmaciones
        formState.reconfirmations = [];
      } else {
        // Si se desactiva y no hay reconfirmaciones, agregar una por defecto
        if (formState.reconfirmations.length === 0) {
          addReconfirmation();
        }
      }
    },
  });

  const reconfirmationActiveKey = ref<string[]>(['reconfirmation']);
  const releasedActiveKey = ref<string[]>(['released']);

  const reconfirmationTimeUnits = computed(() => {
    return timeUnits.value.filter((t) => t.value !== 'months');
  });

  watch(globalAutomaticReconfirmation, (val) => {
    if (val) {
      // Cerrar collapse si es automática
      reconfirmationActiveKey.value = [];
      // Asegurar que 'No aplica' esté desmarcado
      formState.reconfirmationsNotApplicable = false;
    } else {
      // Abrir collapse si se desactiva automática
      reconfirmationActiveKey.value = ['reconfirmation'];
    }
  });

  watch(
    () => formState.reconfirmationsNotApplicable,
    (val) => {
      // Si no aplica, cerrar. Si aplica, abrir.
      // Nota: Si globalAutomaticReconfirmation es true, esto podría tener conflicto si se permitiera cambiar,
      // pero el checkbox está deshabilitado.
      if (!globalAutomaticReconfirmation.value) {
        reconfirmationActiveKey.value = val ? [] : ['reconfirmation'];
      }
    }
  );

  watch(
    () => formState.releasedNotApplicable,
    (val) => {
      releasedActiveKey.value = val ? [] : ['released'];
    }
  );

  // Handler para cuando cambia el checkbox "No aplica" de Reconfirmación
  const handleReconfirmationsNotApplicableChange = (e: any) => {
    const checked = e.target.checked;
    if (checked) {
      // Cerrar el collapse y resetear datos
      reconfirmationActiveKey.value = [];
      formState.reconfirmations = [];
      // Agregar una fila vacía para que esté lista cuando se desmarque
      addReconfirmation();
    } else {
      // Abrir el collapse
      reconfirmationActiveKey.value = ['reconfirmation'];
    }
  };

  // Handler para cuando cambia el checkbox "No aplica" de Liberados
  const handleReleasedNotApplicableChange = (e: any) => {
    const checked = e.target.checked;
    if (checked) {
      // Cerrar el collapse y resetear datos
      releasedActiveKey.value = [];
      formState.released = [];
      // Agregar una fila vacía para que esté lista cuando se desmarque
      addReleased();
    } else {
      // Abrir el collapse
      releasedActiveKey.value = ['released'];
    }
  };

  // Handler para prevenir que el collapse de Reconfirmación se abra cuando está bloqueado
  const handleReconfirmationCollapseChange = (keys: string | string[]) => {
    // Si "No aplica" o "Reconfirmaciones automáticas" está marcado, prevenir apertura
    if (globalAutomaticReconfirmation.value || formState.reconfirmationsNotApplicable) {
      reconfirmationActiveKey.value = [];
      return;
    }
    // Si no está bloqueado, permitir el cambio normal
    reconfirmationActiveKey.value = Array.isArray(keys) ? keys : [keys];
  };

  // Handler para prevenir que el collapse de Liberados se abra cuando está bloqueado
  const handleReleasedCollapseChange = (keys: string | string[]) => {
    // Si "No aplica" está marcado, prevenir apertura
    if (formState.releasedNotApplicable) {
      releasedActiveKey.value = [];
      return;
    }
    // Si no está bloqueado, permitir el cambio normal
    releasedActiveKey.value = Array.isArray(keys) ? keys : [keys];
  };

  // Watcher para abrir el collapse de Reconfirmación si hay errores de validación
  watch(
    () => errorsReconfirmations.value,
    (errors) => {
      // Si hay errores y el collapse está cerrado, abrirlo (excepto si "No aplica" está marcado)
      if (
        errors &&
        errors.length > 0 &&
        !formState.reconfirmationsNotApplicable &&
        !globalAutomaticReconfirmation.value
      ) {
        const hasAnyError = errors.some((errorRow: any) => {
          if (!errorRow) return false;
          return Object.values(errorRow).some((fieldErrors) => {
            if (Array.isArray(fieldErrors)) return fieldErrors.length > 0;
            return !!fieldErrors;
          });
        });
        if (hasAnyError && !reconfirmationActiveKey.value.includes('reconfirmation')) {
          reconfirmationActiveKey.value = ['reconfirmation'];
        }
      }
    },
    { deep: true }
  );

  // Watcher para abrir el collapse de Liberados si hay errores de validación
  watch(
    () => errorsReleased.value,
    (errors) => {
      // Si hay errores y el collapse está cerrado, abrirlo (excepto si "No aplica" está marcado)
      if (errors && errors.length > 0 && !formState.releasedNotApplicable) {
        const hasAnyError = errors.some((errorRow: any) => {
          if (!errorRow) return false;
          return Object.values(errorRow).some((fieldErrors) => {
            if (Array.isArray(fieldErrors)) return fieldErrors.length > 0;
            return !!fieldErrors;
          });
        });
        if (hasAnyError && !releasedActiveKey.value.includes('released')) {
          releasedActiveKey.value = ['released'];
        }
      }
    },
    { deep: true }
  );

  // Computed para verificar si hay alguna cancelación en modo edición
  // const hasCancellationInEditMode = computed(() => {
  //   return formState.cancellations.some((c) => c.isEditable);
  // });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';
  @import '@/scss/components/negotiations/_supplierForm.scss';

  .additional-information {
    margin-top: 18px;

    &-title {
      font-size: 14px;
      font-weight: 500;
      color: $color-black;
    }

    .info-length-text-area {
      text-align: right;
      margin-top: 6px;
      font-size: 10px;
      font-weight: 400;
      color: $color-black;
    }
  }

  .reconfirmation-title {
    display: flex;
    align-items: center;
    gap: 8px;

    &-info {
      font-size: 12px;
      font-weight: 400;
      color: $color-black-3;
    }
  }

  .released-title {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .not-applicable-checkbox {
    margin-left: 16px;
    font-size: 14px;
    font-weight: 400;
    color: $color-black-3;

    .ant-checkbox + span {
      color: $color-black-3;
    }
  }

  .disabled-section {
    opacity: 0.6;
    pointer-events: none;
    background-color: $color-white-2;

    :deep(.ant-collapse-header) {
      color: $color-black-3 !important;
    }

    // Forzar color gris al título
    .reconfirmation-title span {
      color: $color-black-3 !important;
    }

    :deep(.ant-checkbox-wrapper) {
      color: $color-black-3 !important;
    }

    // Forzar estilo de checkbox deshabilitado
    :deep(.ant-checkbox-inner) {
      background-color: #f5f5f5 !important;
      border-color: #d9d9d9 !important;
    }
  }

  // Clase para cuando "No aplica" está marcado - solo afecta el contenido, no el header
  .content-disabled {
    :deep(.ant-collapse-content) {
      opacity: 0.5;
      pointer-events: none;
    }

    // Mantener el header interactivo (especialmente el checkbox)
    :deep(.ant-collapse-header) {
      pointer-events: auto !important;
      opacity: 1 !important;
    }
  }

  .p-20 {
    padding: 0px 20px 0px 20px;
  }

  .py-20 {
    padding-top: 20px;
    padding-bottom: 20px;
  }

  .pt-20 {
    padding-top: 20px;
  }

  .pl-20 {
    padding-left: 20px;
  }

  .pr-20 {
    padding-right: 20px;
  }

  .pb-20 {
    padding-bottom: 20px;
  }

  .container-relative {
    position: relative;
  }

  .select-none {
    user-select: none;
  }

  .row-delete-icon {
    position: absolute;
    right: 14px;
    font-size: 20px;
    cursor: pointer;
    transition: color 0.2s;
    color: #1284ed;
  }

  .main-title {
    font-size: 16px;
    font-weight: 600;
    color: $color-black;
  }

  .btn-actions {
    display: flex;
    align-items: center;
    gap: 12px;

    .btn-cancel {
      min-width: 90px;
      font-weight: 600;
    }

    .btn-save {
      min-width: 125px;
      font-weight: 600;
    }
  }

  .custom-collapse {
    .custom-collapse-panel {
      border: 1px solid $color-black-4;
      border-radius: 8px;
      padding: 7px 3px;

      :deep(.ant-collapse-content-box) {
        padding: 16px;
      }
    }

    .collapse-icon {
      font-size: 16px !important;
    }

    .header-title {
      font-size: 16px;
      font-weight: 600;
      color: $color-black;
    }

    .collapse-content-with-action {
      position: relative;
    }

    .add-rule-icon-inline {
      position: absolute;
      top: 18px;
      right: 40px;
      cursor: pointer;
      display: flex;
      align-items: center;

      svg {
        display: block;
        transition: opacity 0.2s;
      }

      &:hover svg {
        opacity: 0.8;
      }
    }
  }

  .container-bordered {
    border-top: 1px solid $color-black-4;
    // border-bottom: 1px solid $color-black-4;

    &-padding {
      padding: 20px;
    }
  }

  .custom-select-form {
    width: 202px;
  }

  .form-row-container {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-right: 60px;
  }

  .cancellation-inline-row {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: nowrap;
    overflow-x: auto;
  }

  .released-inline-row {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    flex-wrap: nowrap;
    overflow: visible;
    width: 100%;

    .released-inline-fields {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-wrap: wrap;
      flex: 1 1 auto;
      min-width: 0;
    }

    .actions {
      flex: 0 0 auto;
      margin-left: auto;
      align-self: center;
    }

    .text-form {
      white-space: nowrap;
      flex: 0 0 auto;
      font-size: 16px;
    }

    .input-condition {
      width: 56px;
      flex: 0 0 auto;
    }

    .custom-select-form {
      width: 132px;
      flex: 0 0 auto;
    }
  }

  .cancellation-inline-text {
    white-space: nowrap;
    flex: 0 0 auto;
  }

  .text-form {
    font-size: 16px;
    font-weight: 400;
    color: $color-black;
  }

  .text-bold {
    font-weight: bold;
  }

  .input-condition {
    width: 72px;
  }

  .divider-condition {
    margin: 0;
    border-color: $color-black-4;
    border-width: 1px;
  }

  .btn-add-rule {
    cursor: pointer;
    color: $color-blue;
    font-size: 16px;
    font-weight: 600;
    margin-top: 20px;
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;

    svg {
      display: block;
    }
  }

  .send-list-options {
    margin-top: 16px;
    padding: 20px;
    background-color: #f2f7fa;
    border-radius: 6px;

    .mb-3 {
      margin-bottom: 12px;
    }
  }

  .ml-3 {
    margin-left: 12px;
  }

  .ml-4 {
    margin-left: 16px;
  }

  .mb-3 {
    margin-bottom: 12px;
  }

  .w-full {
    width: 100%;
  }

  .inclusions-title-row {
    display: flex;
    align-items: center;
    gap: 8px;
  }

  .inclusions-title {
    font-size: 14px;
    font-weight: 600;
    color: $color-black;
  }

  .inclusions-subtitle {
    font-size: 12px;
    font-weight: 400;
    color: $color-black-3;
  }

  .inclusions-table {
    border: 1px solid $color-black-4;
    border-radius: 8px;
    overflow: hidden;
  }

  .inclusions-header {
    display: grid;
    grid-template-columns: 2fr 1.5fr 1fr 100px;
    gap: 12px;
    padding: 12px 16px;
    background-color: #d6e4f0;
    font-size: 14px;
    font-weight: 600;
    color: $color-black;
  }

  .inclusions-row {
    display: grid;
    grid-template-columns: 2fr 1.5fr 1fr 100px;
    gap: 12px;
    padding: 16px;
    border-top: 1px solid $color-black-4;
    align-items: center;
  }

  .inclusions-col-description {
    display: flex;
    align-items: center;
  }

  .inclusions-col-small {
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .inclusions-col-actions {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .inclusion-add-icon {
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;

    svg {
      display: block;
      transition: opacity 0.2s;
    }

    &:hover svg {
      opacity: 0.8;
    }
  }

  .inclusion-delete-icon {
    cursor: pointer;
    color: #1284ed;
    font-size: 18px;
    transition: opacity 0.2s;

    &:hover {
      opacity: 0.8;
    }
  }

  .drawer-header {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;

    .drawer-header-text {
      flex: 1;
      font-size: 16px;
      font-weight: 600;
      color: $color-black;
    }

    .drawer-close-icon {
      cursor: pointer;
      font-size: 20px;
      color: $color-black;
      transition: opacity 0.2s;

      &:hover {
        opacity: 0.7;
      }
    }
  }

  .drawer-content {
    padding: 0;
    margin: 0 -24px;
  }

  .drawer-top-divider,
  .drawer-bottom-divider {
    height: 1px;
    background-color: #e8e8e8;
    width: 100%;
  }

  .drawer-field {
    padding-left: 24px;
    padding-right: 24px;
    padding-top: 10px;
    padding-bottom: 34px;

    .drawer-label {
      display: block;
      margin-bottom: 12px;
      font-size: 14px;
      font-weight: 600;
      color: $color-black;
    }
  }

  .drawer-button-container {
    padding: 32px 24px;
    display: flex;
    justify-content: flex-end;

    .ant-btn {
      width: 225px;
      height: 48px;
      font-size: 16px;
      font-weight: 600;
    }
  }

  .multiselect-option {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;

    span {
      flex: 1;
    }
  }

  :deep(.multiselect-with-checkbox) {
    .ant-select-item-option-content {
      width: 100%;
    }
  }

  .additional-rule-edit-container {
    border-top: 1px solid $color-black-4;
    padding-top: 16px;
  }

  .additional-rule-title-section {
    padding: 0 16px;
    margin-bottom: 16px;
  }

  .additional-rule-title {
    font-size: 14px;
    font-weight: 500;
    color: $color-black;
  }

  .additional-rule-editor {
    margin-bottom: 16px;
  }

  .action-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
  }

  .header-divider {
    border-bottom: 1px solid #babcbd;
    margin-top: 20px;
    display: none;
  }

  // Mostrar el divider solo cuando el collapse está abierto
  :deep(.ant-collapse-item-active) .header-divider {
    display: block;
  }

  .mt-20 {
    margin-top: 20px;
  }

  // Utility classes
  .flex {
    display: flex;
  }
  .items-center {
    align-items: center;
  }
  .gap-2 {
    gap: 8px;
  }
  .ml-auto {
    margin-left: auto;
  }
  .ml-2 {
    margin-left: 8px;
  }
  .cursor-pointer {
    cursor: pointer;
  }
  .text-danger {
    color: #ff4d4f;
    &:hover {
      color: #ff7875;
    }
  }
</style>
