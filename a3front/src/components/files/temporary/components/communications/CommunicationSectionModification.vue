<template>
  <div>
    <a-row :gutter="24" align="middle" justify="space-between" class="mt-4">
      <div class="box-in-communications">
        <a-row :gutter="24" align="middle" justify="space-between" class="mt-4">
          <a-col :span="24">
            <div class="box-title">
              <IconPullRequest color="#212529" width="1.2em" height="1.2em" />
              {{ title }}
            </div>
          </a-col>
        </a-row>
        <div v-for="(item, index_modification) in items" :key="index_modification">
          <!-- Communication only one-->
          <a-row v-if="item.hasOneCommunications">
            <a-col :span="11">
              <div class="box-cancel-communications">
                <a-row :gutter="12" align="middle" justify="space-between">
                  <a-col :span="24">
                    <span class="box-cancel-title-service">
                      <font-awesome-icon :icon="['fas', 'users-gear']" />
                      {{ item.supplier_name }}
                    </span>
                  </a-col>
                </a-row>
                <a-row :gutter="24" align="middle" justify="space-between" class="mt-4">
                  <div
                    class="box-cancel-service-master"
                    v-for="(component, index) in item.cancellation.components"
                    :key="index"
                  >
                    <a-col :span="14">
                      <div class="box-name-service-master">{{ component.name }}</div>
                    </a-col>
                    <a-col :span="10">
                      <div class="box-cost-service-master">
                        <div class="box-icon-service-master">
                          <IconCircleCheck
                            color="#1ED790"
                            width="20px"
                            height="20px"
                            v-if="component.penality == 0"
                          />
                        </div>
                        <div class="box-icon-service-master-penality">
                          <i
                            class="bi bi-exclamation-triangle"
                            style="font-size: 1.2rem"
                            v-if="
                              component.penality !== undefined &&
                              component.penality !== null &&
                              component.penality > 0
                            "
                          ></i>
                        </div>
                        <div class="box-label-cost-service-master" v-if="component.penality == 0">
                          Costo
                        </div>
                        <div
                          class="box-label-penality-service-master"
                          v-if="
                            component.penality !== undefined &&
                            component.penality !== null &&
                            component.penality > 0
                          "
                        >
                          Penalidad
                        </div>
                        <div class="box-amount-service-master" v-if="component.penality == 0">
                          $. {{ component.amount_cost || 0 }}
                        </div>
                        <div
                          class="box-amount-penality-service-master"
                          v-if="
                            component.penality !== undefined &&
                            component.penality !== null &&
                            component.penality > 0
                          "
                        >
                          $. {{ component.penality || 0 }}
                        </div>
                      </div>
                    </a-col>
                  </div>
                </a-row>
              </div>
            </a-col>
            <a-col :span="2" class="text-center merge-icon">
              <div class="box-icon">
                <i class="bi bi-arrow-right-circle" style="font-size: 4rem"></i>
              </div>
            </a-col>
            <a-col :span="11">
              <div class="box-reservations-communications">
                <a-row :gutter="12" align="middle" justify="space-between">
                  <a-col :span="24">
                    <span class="box-cancel-title-service">
                      <font-awesome-icon :icon="['fas', 'users-gear']" />
                      {{ item.supplier_name }}
                    </span>
                  </a-col>
                </a-row>
                <a-row :gutter="24" align="middle" justify="space-between" class="mt-4">
                  <div
                    class="box-new-service-master"
                    v-for="(component, index) in item.reservations.components"
                    :key="index"
                  >
                    <a-col :span="16">
                      <div class="box-name-service-master">{{ component.name }}</div>
                    </a-col>
                    <a-col :span="8">
                      <div class="box-cost-service-master">
                        <div class="box-icon-service-master">
                          <IconCircleCheck color="#1ED790" width="20px" height="20px" />
                        </div>
                        <div class="box-label-cost-service-master">Costo</div>
                        <div class="box-amount-service-master">
                          $. {{ component.amount_cost || 0 }}
                        </div>
                      </div>
                    </a-col>
                  </div>
                </a-row>
              </div>
            </a-col>
            <div class="box-communications-notes">
              <a-col :span="6">
                <div
                  v-bind:class="[
                    'd-flex cursor-pointer',
                    !item.showNotes ? 'text-dark-gray' : 'text-danger',
                  ]"
                  @click="toggleNotes(item)"
                >
                  <template v-if="item.showNotes">
                    <i
                      class="bi bi-check-square-fill text-danger d-flex"
                      style="font-size: 1.5rem"
                    ></i>
                  </template>
                  <template v-else>
                    <i class="bi bi-square d-flex" style="font-size: 1.5rem"></i>
                  </template>
                  <span class="mx-2">Agregar nota al proveedor</span>
                </div>
              </a-col>
              <a-col :span="13" class="d-flex">
                <div class="box-info-supplier">
                  <div class="box-label-supplier">
                    Correo asociado a reservas:
                    <div
                      @click="
                        $emit(
                          'showModalEmails',
                          item.supplier_emails,
                          index_modification,
                          type,
                          null,
                          null
                        )
                      "
                    >
                      <IconCirclePlus color="#EB5757" width="20px" height="20px" />
                    </div>
                  </div>
                  <div class="boxes-email-supplier">
                    <div
                      class="box-email-supplier"
                      v-for="(email, index) in item.supplier_emails"
                      :key="index"
                    >
                      {{ email }}
                    </div>
                  </div>
                </div>
              </a-col>
              <a-col :span="5" class="col-end">
                <a-button
                  v-if="!item.showNotes"
                  danger
                  type="default"
                  :loading="fileServiceStore.isLoading"
                  @click="$emit('showCommunicationFrom', item, type)"
                  class="btn-communications d-flex ant-row-middle text-600"
                >
                  <i class="bi bi-search"></i>
                  <span class="mx-2">Ver comunicación</span>
                </a-button>
              </a-col>
            </div>
            <a-row
              v-if="item.showNotes"
              :gutter="24"
              style="width: 100%"
              align="middle"
              justify="space-between"
            >
              <a-col :span="24">
                <p class="text-danger my-2">
                  <IconPencilLinear color="#EB5757" width="1.4em" height="1.4em" />
                  Nota para el proveedor:
                </p>
                <a-row align="top" justify="space-between">
                  <a-col flex="auto">
                    <a-textarea
                      :class="{ 'error-border': noteError }"
                      v-model:value="item.notas"
                      :disabled="fileServiceStore.isLoading"
                      placeholder="Escribe una nota para el proveedor que podrás visualizar en la comunicación"
                      :auto-size="{ minRows: 3 }"
                    />
                    <p v-if="noteError && !item.notas?.trim()" class="text-danger mt-2">
                      La nota es requerida.
                    </p>
                  </a-col>
                </a-row>
                <a-row justify="end" class="mt-2 mb-4">
                  <a-col :offset="17" :span="7">
                    <a-button
                      danger
                      type="default"
                      size="large"
                      class="text-600 ms-2"
                      style="float: right"
                      :loading="fileServiceStore.isLoading"
                      @click="handleSaveNotes(item, index_modification, null, null)"
                    >
                      <i
                        v-bind:class="['bi bi-floppy', fileServiceStore.isLoading ? 'mx-2' : '']"
                      ></i>
                    </a-button>
                    <div style="float: right" v-show="!fileServiceStore.isLoading">
                      <file-upload
                        type="default"
                        v-bind:folder="'communications'"
                        @onResponseFiles="responseFilesFrom"
                      />
                    </div>
                    <a-button
                      danger
                      type="default"
                      :loading="fileServiceStore.isLoading"
                      style="height: 40px"
                      @click="$emit('showCommunicationFrom', item, type)"
                      class="btn-communications d-flex ant-row-middle text-600"
                    >
                      <i class="bi bi-search"></i>
                      <span class="mx-2">Ver comunicación</span>
                    </a-button>
                  </a-col>
                </a-row>
              </a-col>
            </a-row>
          </a-row>

          <!-- Communication multiples-->
          <a-row v-if="!item.hasOneCommunications">
            <a-col :span="11">
              <div
                class="box-cancel-communications"
                v-for="(cancellation, index_cancellation) in item.cancellation"
                :key="index_cancellation"
              >
                <a-row :gutter="12" align="middle" justify="space-between">
                  <a-col :span="24">
                    <span class="box-cancel-title-service">
                      <font-awesome-icon :icon="['fas', 'users-gear']" />
                      {{ cancellation.supplier_name }}
                    </span>
                  </a-col>
                  <a-col :span="24" class="mt-2" style="padding: 0">
                    <div class="box-info-supplier">
                      <div class="box-label-supplier">Correo asociado a reservas:</div>
                      <div class="boxes-email-supplier">
                        <div
                          class="box-email-supplier"
                          v-if="cancellation.supplier_emails.length > 0"
                        >
                          {{ cancellation.supplier_emails[0] }}
                        </div>
                      </div>
                      <div
                        class="box-action-supplier"
                        @click="
                          $emit(
                            'showModalEmails',
                            cancellation.supplier_emails,
                            index_modification,
                            type,
                            'cancellation',
                            index_cancellation
                          )
                        "
                      >
                        <IconCirclePlus color="#EB5757" width="20px" height="20px" />
                      </div>
                    </div>
                  </a-col>
                  <a-col :span="24" class="mt-1">
                    <div v-if="cancellation.supplier_emails.length > 1" class="box-info-supplier">
                      <div
                        class="boxes-email-supplier"
                        v-for="(email, index) in cancellation.supplier_emails.slice(1)"
                        :key="index"
                      >
                        <div class="box-email-supplier">{{ email }}</div>
                      </div>
                    </div>
                  </a-col>
                  <a-col :span="24">
                    <a-divider style="height: 0.8px; background-color: #c4c4c4" />
                  </a-col>
                </a-row>
                <a-row :gutter="24" align="middle" justify="space-between">
                  <div
                    class="box-cancel-service-master"
                    v-for="(component, index) in cancellation.components"
                    :key="index"
                  >
                    <a-col :span="14">
                      <div class="box-name-service-master">{{ component.name }}</div>
                    </a-col>
                    <a-col :span="10">
                      <div class="box-cost-service-master">
                        <div
                          class="box-icon-service-master"
                          v-if="parseFloat(component.penality) == 0"
                        >
                          <IconCircleCheck color="#1ED790" width="20px" height="20px" />
                        </div>
                        <div class="box-icon-service-master-penality">
                          <i
                            class="bi bi-exclamation-triangle"
                            style="font-size: 1.2rem"
                            v-if="component.penality > 0"
                          ></i>
                        </div>
                        <div
                          class="box-label-cost-service-master"
                          v-if="parseFloat(component.penality) == 0"
                        >
                          Costo
                        </div>
                        <div
                          class="box-label-cost-service-master"
                          v-if="parseFloat(component.penality) > 0"
                        >
                          Penalidad
                        </div>
                        <div
                          class="box-amount-service-master"
                          v-if="parseFloat(component.penality) == 0"
                        >
                          $. {{ component.amount_cost || 0 }}
                        </div>
                        <div
                          class="box-amount-penality-service-master"
                          v-if="component.penality > 0"
                        >
                          $. {{ component.penality || 0 }}
                        </div>
                      </div>
                    </a-col>
                  </div>
                </a-row>
                <a-row :gutter="24" align="middle" justify="space-between" class="mt-2">
                  <a-col :span="24">
                    <div
                      v-bind:class="[
                        'd-flex cursor-pointer',
                        !cancellation.showNotes ? 'text-dark-gray' : 'text-danger',
                      ]"
                      @click="toggleNotes(cancellation)"
                    >
                      <template v-if="cancellation.showNotes">
                        <i
                          class="bi bi-check-square-fill text-danger d-flex"
                          style="font-size: 1.5rem"
                        ></i>
                      </template>
                      <template v-else>
                        <i class="bi bi-square d-flex" style="font-size: 1.5rem"></i>
                      </template>
                      <span class="mx-2">Agregar nota al proveedor</span>
                    </div>
                  </a-col>
                  <a-col :span="24" class="col-end mt-3">
                    <a-button
                      v-if="!cancellation.showNotes"
                      danger
                      :loading="fileServiceStore.isLoading"
                      type="default"
                      @click="$emit('showCommunicationFrom', cancellation, type)"
                      class="btn-communications d-flex ant-row-middle text-600"
                    >
                      <i class="bi bi-search"></i>
                      <span class="mx-2">Ver comunicación</span>
                    </a-button>
                  </a-col>
                </a-row>
                <a-row
                  v-if="cancellation.showNotes"
                  :gutter="24"
                  style="width: 100%"
                  align="middle"
                  justify="space-between"
                >
                  <a-col :span="24">
                    <p class="text-danger my-2">
                      <IconPencilLinear color="#EB5757" width="1.4em" height="1.4em" />
                      Nota para el proveedor:
                    </p>
                    <a-row align="top" justify="space-between">
                      <a-col flex="auto">
                        <a-textarea
                          :class="{ 'error-border': noteError }"
                          v-model:value="cancellation.notas"
                          :disabled="fileServiceStore.isLoading"
                          placeholder="Escribe una nota para el proveedor que podrás visualizar en la comunicación"
                          :auto-size="{ minRows: 3 }"
                        />
                        <p v-if="noteError && !cancellation.notas?.trim()" class="text-danger mt-2">
                          La nota es requerida.
                        </p>
                      </a-col>
                    </a-row>
                    <a-row align="middle" justify="end" class="mt-2 mb-4">
                      <a-col :offset="6" :span="18">
                        <a-button
                          danger
                          type="default"
                          size="large"
                          class="text-600 ms-2"
                          style="float: right"
                          :loading="fileServiceStore.isLoading"
                          @click="
                            handleSaveNotes(
                              item,
                              index_modification,
                              'cancellation',
                              index_cancellation
                            )
                          "
                        >
                          <i
                            v-bind:class="[
                              'bi bi-floppy',
                              fileServiceStore.isLoading ? 'mx-2' : '',
                            ]"
                          ></i>
                        </a-button>
                        <div style="float: right" v-show="!fileServiceStore.isLoading">
                          <file-upload
                            type="default"
                            v-bind:folder="'communications'"
                            @onResponseFiles="responseFilesFrom"
                          />
                        </div>
                        <a-button
                          danger
                          :loading="fileServiceStore.isLoading"
                          style="height: 40px"
                          type="default"
                          @click="$emit('showCommunicationFrom', cancellation, type)"
                          class="btn-communications d-flex ant-row-middle text-600"
                        >
                          <i class="bi bi-search"></i>
                          <span class="mx-2">Ver comunicación</span>
                        </a-button>
                      </a-col>
                    </a-row>
                  </a-col>
                </a-row>
              </div>
            </a-col>
            <a-col :span="2" class="text-center merge-icon">
              <div class="box-icon" v-if="item.reservations.length > 0">
                <i class="bi bi-arrow-right-circle" style="font-size: 4rem"></i>
              </div>
            </a-col>
            <a-col :span="11">
              <div
                class="box-reservations-communications"
                v-for="(reservation, index_reservation) in item.reservations"
                :key="index_reservation"
              >
                <a-row :gutter="12" align="middle" justify="space-between">
                  <a-col :span="24">
                    <span class="box-cancel-title-service">
                      <font-awesome-icon :icon="['fas', 'users-gear']" />
                      {{ reservation.supplier_name }}
                    </span>
                  </a-col>
                  <a-col :span="24" class="mt-2" style="padding: 0">
                    <div class="box-info-supplier">
                      <div class="box-label-supplier">Correo asociado a reservas:</div>
                      <div class="boxes-email-supplier">
                        <div
                          class="box-email-supplier"
                          v-if="reservation.supplier_emails.length > 0"
                        >
                          {{ reservation.supplier_emails[0] }}
                        </div>
                      </div>

                      <div
                        class="box-action-supplier"
                        @click="
                          $emit(
                            'showModalEmails',
                            reservation.supplier_emails,
                            index_modification,
                            type,
                            'reservations',
                            index_reservation
                          )
                        "
                      >
                        <IconCirclePlus color="#EB5757" width="20px" height="20px" />
                      </div>
                    </div>
                  </a-col>
                  <a-col :span="24" class="mt-1">
                    <div v-if="reservation.supplier_emails.length > 1" class="box-info-supplier">
                      <div
                        class="boxes-email-supplier"
                        v-for="(email, index) in reservation.supplier_emails.slice(1)"
                        :key="index"
                      >
                        <div class="box-email-supplier">{{ email }}</div>
                      </div>
                    </div>
                  </a-col>
                  <a-col :span="24">
                    <a-divider style="height: 0.8px; background-color: #c4c4c4" />
                  </a-col>
                </a-row>
                <a-row :gutter="24" align="middle" justify="space-between">
                  <div
                    class="box-new-service-master"
                    v-for="(component, index) in reservation.components"
                    :key="index"
                  >
                    <a-col :span="16">
                      <div class="box-name-service-master">{{ component.name }}</div>
                    </a-col>
                    <a-col :span="8">
                      <div class="box-cost-service-master">
                        <div class="box-icon-service-master">
                          <IconCircleCheck color="#1ED790" width="20px" height="20px" />
                        </div>
                        <div class="box-label-cost-service-master">Costo</div>
                        <div class="box-amount-service-master">
                          $. {{ component.amount_cost || 0 }}
                        </div>
                      </div>
                    </a-col>
                  </div>
                </a-row>
                <a-row :gutter="24" align="middle" justify="space-between" class="mt-4">
                  <a-col :span="24">
                    <div
                      v-bind:class="[
                        'd-flex cursor-pointer',
                        !reservation.showNotes ? 'text-dark-gray' : 'text-danger',
                      ]"
                      @click="toggleNotes(reservation)"
                    >
                      <template v-if="reservation.showNotes">
                        <i
                          class="bi bi-check-square-fill text-danger d-flex"
                          style="font-size: 1.5rem"
                        ></i>
                      </template>
                      <template v-else>
                        <i class="bi bi-square d-flex" style="font-size: 1.5rem"></i>
                      </template>
                      <span class="mx-2">Agregar nota al proveedor</span>
                    </div>
                  </a-col>
                  <a-col :span="24" class="col-end mt-3">
                    <a-button
                      v-if="!reservation.showNotes"
                      danger
                      type="default"
                      :loading="fileServiceStore.isLoading"
                      @click="$emit('showCommunicationFrom', reservation, type)"
                      class="btn-communications d-flex ant-row-middle text-600"
                    >
                      <i class="bi bi-search"></i>
                      <span class="mx-2">Ver comunicación</span>
                    </a-button>
                  </a-col>
                </a-row>
                <a-row
                  v-if="reservation.showNotes"
                  :gutter="24"
                  style="width: 100%"
                  align="middle"
                  justify="space-between"
                >
                  <a-col :span="24">
                    <p class="text-danger my-2">
                      <IconPencilLinear color="#EB5757" width="1.4em" height="1.4em" />
                      Nota para el proveedor:
                    </p>
                    <a-row align="top" justify="space-between">
                      <a-col flex="auto">
                        <a-textarea
                          :class="{ 'error-border': noteError }"
                          v-model:value="reservation.notas"
                          :disabled="fileServiceStore.isLoading"
                          placeholder="Escribe una nota para el proveedor que podrás visualizar en la comunicación"
                          :auto-size="{ minRows: 3 }"
                        />
                        <p v-if="noteError && !reservation.notas?.trim()" class="text-danger mt-2">
                          La nota es requerida.
                        </p>
                      </a-col>
                    </a-row>
                    <a-row align="middle" justify="end" class="mt-2 mb-4">
                      <a-col :offset="6" :span="18">
                        <a-button
                          danger
                          type="default"
                          size="large"
                          class="text-600 ms-2"
                          style="float: right"
                          :loading="fileServiceStore.isLoading"
                          @click="
                            handleSaveNotes(
                              item,
                              index_modification,
                              'reservations',
                              index_reservation
                            )
                          "
                        >
                          <i
                            v-bind:class="[
                              'bi bi-floppy',
                              fileServiceStore.isLoading ? 'mx-2' : '',
                            ]"
                          ></i>
                        </a-button>
                        <div style="float: right" v-show="!fileServiceStore.isLoading">
                          <file-upload
                            type="default"
                            v-bind:folder="'communications'"
                            @onResponseFiles="responseFilesFrom"
                          />
                        </div>
                        <a-button
                          danger
                          :loading="fileServiceStore.isLoading"
                          style="height: 40px"
                          type="default"
                          @click="$emit('showCommunicationFrom', reservation, type)"
                          class="btn-communications d-flex ant-row-middle text-600"
                        >
                          <i class="bi bi-search"></i>
                          <span class="mx-2">Ver comunicación</span>
                        </a-button>
                      </a-col>
                    </a-row>
                  </a-col>
                </a-row>
              </div>
            </a-col>
          </a-row>
        </div>
      </div>
    </a-row>
  </div>
</template>

<script setup lang="ts">
  import { defineEmits, defineProps, ref, watch } from 'vue';
  import IconPullRequest from '@/components/icons/IconPullRequest.vue';
  import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome';
  import IconCircleCheck from '@/components/icons/IconCircleCheck.vue';
  import IconCirclePlus from '@/components/icons/IconCirclePlus.vue';
  import { useFileServiceStore } from '@/components/files/temporary/store/useFileServiceStore';
  import IconPencilLinear from '@/components/icons/IconPencilLinear.vue';
  import FileUpload from '@/components/global/FileUploadComponent.vue';
  import { useRouter } from 'vue-router';
  import { notification } from 'ant-design-vue';

  const fileServiceStore = useFileServiceStore();
  const router = useRouter();
  const noteError = ref(false);
  const filesFrom = ref([]);
  const props = defineProps({
    title: String,
    items: Array,
    type: String,
  });

  defineEmits(['showModalEmails', 'showCommunicationFrom']);

  const toggleNotes = (item) => {
    item.showNotes = !item.showNotes;
  };

  const handleSaveNotes = async (item, index, type = null, index_type = null) => {
    // Validar si el campo de nota está vacío
    if (type == null && index_type == null) {
      if (!item.notas.trim()) {
        noteError.value = true;
        return; // Detener la ejecución si está vacío
      }
    }

    if (type == 'cancellation' || type == 'reservations') {
      if (!item[type][index_type].notas.trim()) {
        noteError.value = true;
        return; // Detener la ejecución si está vacío
      }
    }

    noteError.value = false;

    item.attachments = filesFrom.value;

    const data = { ...item, html: '' };

    try {
      const partialPayload = {
        [props.type]: [data],
      };

      const response = await fileServiceStore.updateCommunicationsTemporary(
        router.currentRoute.value.params.id,
        router.currentRoute.value.params.service_id,
        partialPayload
      );
      if (fileServiceStore.isSuccess) {
        if (response && response.data && response.data[props.type]?.length) {
          if ((type == 'cancellation' || type == 'reservations') && index_type !== null) {
            console.log(item, index, type, index_type);
            console.log(props.items[0][type][index_type]);
            props.items[0][type][index_type] = {
              ...response.data[props.type][0][type][index_type],
            };
          } else {
            props.items[index] = { ...response.data[props.type][0] };
          }

          notification.success({
            message: 'Éxito',
            description: 'Se guardaron las notas correctamente.',
          });
        }
      }
    } catch (error) {
      console.error('Error al guardar las notas:', error);
    }
  };

  const handleNotSaveNotes = async (item) => {
    noteError.value = false;

    try {
      const partialPayload = {
        [props.type]: [item],
      };

      const response = await fileServiceStore.updateCommunicationsTemporary(
        router.currentRoute.value.params.id,
        router.currentRoute.value.params.service_id,
        partialPayload
      );

      if (fileServiceStore.isSuccess) {
        if (response && response.data && response.data[props.type]?.length) {
          const updatedItem = response.data[props.type][0];
          Object.assign(item, updatedItem);
          notification.success({
            message: 'Éxito',
            description: 'Se quitaron las notas correctamente.',
          });
        }
      }
    } catch (error) {
      console.error('Error al guardar las notas:', error);
    }
  };

  const responseFilesFrom = (files) => {
    filesFrom.value = files.map((item) => item.link);
  };

  // Watch para cargar la nota cuando los items cambian (carga inicial o actualización de datos)
  watch(
    () => props.items,
    (items) => {
      items.forEach((item) => {
        if (item.hasOneCommunications) {
          watch(
            () => item.showNotes,
            (newValue) => {
              if (!newValue) {
                if (item.notas !== '') {
                  item.notas = '';
                  item.html = '';
                  item.attachments = [];
                  handleNotSaveNotes(item);
                }
              }
            }
          );
        } else {
          if (item.reservations.length > 0) {
            item.reservations.forEach((reservation) => {
              watch(
                () => reservation.showNotes,
                (newValue) => {
                  if (!newValue) {
                    if (reservation.notas !== '') {
                      reservation.notas = '';
                      reservation.html = '';
                      reservation.attachments = [];
                      handleNotSaveNotes(item);
                    }
                  }
                }
              );
            });
          }

          if (item.cancellation.length > 0) {
            item.cancellation.forEach((cancellation) => {
              watch(
                () => cancellation.showNotes,
                (newValue) => {
                  if (!newValue) {
                    if (cancellation.notas !== '') {
                      cancellation.notas = '';
                      cancellation.html = '';
                      cancellation.attachments = [];
                      handleNotSaveNotes(item);
                    }
                  }
                }
              );
            });
          }
        }
      });
    },
    { deep: true } // Necesario para observar cambios en objetos anidados
  );
</script>

<style scoped lang="scss">
  .box-communications-notes {
    display: flex;
    width: 100%;
    margin-bottom: 25px;
    align-items: center;
  }

  .box-communications {
    padding: 40px;
    border: 1px solid #e9e9e9;
    border-radius: 6px;
    margin-bottom: 20px;

    .box-title-service {
      font-family: Montserrat, serif;
      font-size: 18px;
      font-weight: 700;
      line-height: 25px;
      letter-spacing: -0.01em;
      text-align: left;
      color: #575757;
    }

    .flex-row-service {
      display: flex;
      justify-content: left;
      align-items: center;
      gap: 50px;

      .date-service {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 5px;
        font-size: 14px;
        color: #979797;

        .label-date {
          color: #979797;
          font-family: 'Montserrat', sans-serif !important;
          font-weight: 700;
        }
      }

      .pax-service {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: #979797;

        .label-pax,
        .label-adult,
        .label-child {
          color: #979797;
          font-size: 14px;
          font-family: 'Montserrat', sans-serif !important;
          font-weight: 700;
        }
      }
    }

    .box-in-communications {
      padding: 0 20px 20px 20px;
      border: 0;
      border-radius: 6px;
      background-color: #fafafa91;
      width: 100%;

      .box-title {
        font-family: Montserrat, serif;
        font-size: 14px;
        font-weight: 600;
        line-height: 21px;
        letter-spacing: 0.015em;
        text-align: left;
        color: #212529;
        margin-bottom: 20px;
        text-decoration: underline;
        text-underline-position: under;
        text-underline-offset: 1px;

        svg {
          margin-right: 10px;
        }
      }

      .box-info-supplier {
        display: flex;
        flex-direction: row;
        gap: 2px;
        justify-content: flex-end;
        align-items: flex-start;
        flex-wrap: wrap;

        .box-title-supplier {
          font-family: Montserrat, serif;
          font-size: 14px;
          font-weight: 400;
          line-height: 21px;
          letter-spacing: 0.015em;
          text-align: left;
        }

        .box-label-supplier {
          font-family: Montserrat, serif;
          font-size: 14px;
          font-weight: 500;
          line-height: 21px;
          letter-spacing: 0.015em;
          text-align: left;
          color: #4f4b4b;
          display: flex;
          gap: 5px;
        }

        .boxes-email-supplier {
          display: flex;
          flex-direction: row;
          gap: 2px;
          justify-content: flex-end;
          flex-wrap: wrap;

          .box-email-supplier {
            font-family: Montserrat, serif;
            font-size: 14px;
            font-weight: 400;
            line-height: 21px;
            letter-spacing: 0.015em;
            text-align: left;
            color: #4f4b4b;
            border: 1px dashed #bbbdbf;
            padding: 1px 8px 1px 8px;
            border-radius: 4px;
          }
        }

        .box-action-supplier {
          cursor: pointer;
        }
      }

      .box-cancel-service-master {
        background-color: #fff2f2;
        padding: 10px 20px;
        border-radius: 6px;
        display: flex;
        flex-direction: row;
        width: 100%;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 10px;
        margin-bottom: 10px;
      }

      .box-new-service-master {
        background-color: #ededff;
        padding: 10px 20px;
        border-radius: 6px;
        display: flex;
        flex-direction: row;
        width: 100%;
        margin-left: 10px;
        margin-right: 10px;
        margin-top: 10px;
      }

      .box-name-service-master {
        font-family: Montserrat, serif;
        font-size: 14px;
        font-weight: 600;
        line-height: 21px;
        letter-spacing: 0.015em;
        text-align: left;
        color: #3d3d3d;
        width: 100%;
      }

      .box-cost-service-master {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: flex-end;

        .box-label-cost-service-master {
          font-family: Montserrat, serif;
          font-size: 12px;
          font-weight: 500;
          line-height: 21px;
          letter-spacing: 0.015em;
          color: #4f4b4b;
        }

        .box-icon-service-master-penality {
          color: #ffcc00;
        }

        .box-label-penality-service-master {
          font-size: 12px;
          font-weight: 500;
          line-height: 21px;
          letter-spacing: 0.015em;
          color: #4f4b4b;
        }

        .box-amount-penality-service-master {
          font-size: 15px;
          font-weight: 700;
          line-height: 23px;
          letter-spacing: 0.015em;
          color: #e4b804;
          margin-left: 10px;
        }

        .box-amount-service-master {
          font-size: 16px;
          font-weight: 700;
          line-height: 23px;
          letter-spacing: 0.015em;
          color: #1ed790;
          margin-left: 10px;
        }
      }

      .btn-communications {
        height: 45px;
        color: #eb5757;
        border: 1px solid #eb5757;

        &:hover {
          color: #c63838;
          background-color: #fff6f6;
          border: 1px solid #c63838;
        }
      }

      .col-end {
        display: flex;
        justify-content: end;
      }

      .d-flex {
        align-items: center;
      }

      .check-notes {
        color: #c4c4c4;
      }

      .notes {
        color: #979797;
      }

      .box-communications {
        padding: 5px 20px 20px 20px;
        border: 1px solid #e9e9e9;
      }
    }
  }

  .title-add-emails {
    font-family: Montserrat, serif;
    font-weight: 700;
    font-size: 18px;
    line-height: 25px;
    letter-spacing: -0.015em;
    text-align: center;
    color: #3d3d3d;
    margin-top: 20px;
    margin-bottom: 0px;
  }

  .hr-add-emails {
    border: 0;
    height: 1px;
    background-color: #e9e9e9;
  }

  .box-reservations-communications {
    padding: 20px;
    border: 1px solid #2e2b9e;
    border-radius: 6px;
    margin-bottom: 20px;
    background-color: #ededff;
    height: auto;

    .box-cancel-title-service {
      font-family: Montserrat, serif;
      font-size: 14px;
      font-weight: 400;
      line-height: 25px;
      letter-spacing: -0.01em;
      text-align: left;
      color: #575757;
      background-color: #ffffff;
      padding: 5px;
      border-radius: 6px;

      svg {
        margin-right: 5px;
      }
    }

    .box-info-supplier {
      display: flex;
      flex-direction: row;
      gap: 2px;
      justify-content: flex-start;
      align-items: flex-start;
      flex-wrap: wrap;

      .box-label-supplier {
        font-family: Montserrat, serif;
        font-size: 14px;
        font-weight: 500;
        line-height: 21px;
        letter-spacing: 0.015em;
        text-align: right;
        color: #4f4b4b;
      }

      .boxes-email-supplier {
        display: flex;
        flex-direction: column;
        gap: 2px;

        .box-email-supplier {
          font-family: Montserrat, serif;
          font-size: 14px;
          font-weight: 400;
          line-height: 21px;
          letter-spacing: 0.015em;
          text-align: left;
          color: #4f4b4b;
          border: 1px dashed #bbbdbf;
          padding: 1px 8px 1px 8px;
          border-radius: 4px;
        }
      }
    }

    .box-new-service-master {
      margin-right: 0 !important;
      margin-left: 0 !important;
      margin-top: 0 !important;
      padding: 0 !important;
      margin-bottom: 10px;
    }
  }

  .ant-btn-dangerous {
    background: rgb(0 0 0 / 0%);
  }

  .box-icon {
    display: flex;
    flex-direction: row;
    justify-content: center;
    height: 100%;
    align-items: center;
  }

  .box-cancel-communications {
    padding: 20px;
    border: 1px solid #c63838;
    border-radius: 6px;
    margin-bottom: 20px;
    background-color: #fff2f2;
    height: auto;

    .box-cancel-title-service {
      font-family: Montserrat, serif;
      font-size: 14px;
      font-weight: 400;
      line-height: 25px;
      letter-spacing: -0.01em;
      text-align: left;
      color: #575757;
      background-color: #ffffff;
      padding: 5px;
      border-radius: 6px;

      svg {
        margin-right: 5px;
      }
    }

    .box-info-supplier {
      display: flex;
      flex-direction: row;
      gap: 2px;
      justify-content: flex-start;
      align-items: flex-start;
      flex-wrap: wrap;

      .box-label-supplier {
        font-family: Montserrat, serif;
        font-size: 14px;
        font-weight: 500;
        line-height: 21px;
        letter-spacing: 0.015em;
        text-align: left;
        color: #4f4b4b;
      }

      .boxes-email-supplier {
        display: flex;
        flex-direction: column;
        gap: 2px;

        .box-email-supplier {
          font-family: Montserrat, serif;
          font-size: 14px;
          font-weight: 400;
          line-height: 21px;
          letter-spacing: 0.015em;
          text-align: left;
          color: #4f4b4b;
          border: 1px dashed #bbbdbf;
          padding: 1px 8px 1px 8px;
          border-radius: 4px;
        }
      }
    }

    .box-cancel-service-master {
      margin-right: 0 !important;
      margin-left: 0 !important;
      margin-top: 0 !important;
      padding: 0 !important;
    }
  }
</style>
