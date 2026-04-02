<template>
  <popover-click placement-click="left">
    <span
      style="font-weight: 600; font-size: 16px; line-height: 23px; color: #4f4b4b; cursor: pointer"
      >${{ data.item.amount_cost }}</span
    >
    <template v-if="data.type == 'service'">
      <files-edit-field-static
        :inline="true"
        :hide-content="false"
        v-if="data.item.service_amount?.file_amount_type_flag_id > 0"
      >
        <template #label>
          <svg
            v-if="data.item.service_amount.file_amount_type_flag_id === 1"
            style="margin: -5px 0; color: #ffcc00; cursor: pointer"
            class="feather feather-lock"
            xmlns="http://www.w3.org/2000/svg"
            width="12"
            height="12"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="3"
            viewBox="0 0 24 24"
          >
            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
          <svg
            v-if="data.item.service_amount.file_amount_type_flag_id === 2"
            style="margin: -5px 0; color: #3d3d3d; cursor: pointer"
            class="feather feather-lock"
            xmlns="http://www.w3.org/2000/svg"
            width="12"
            height="12"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="3"
            viewBox="0 0 24 24"
          >
            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
          <svg
            v-if="data.item.service_amount.file_amount_type_flag_id === 3"
            style="margin: -5px 0; color: #c4c4c4; cursor: pointer"
            class="feather feather-lock"
            xmlns="http://www.w3.org/2000/svg"
            width="12"
            height="12"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="3"
            viewBox="0 0 24 24"
          >
            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
        </template>
        <template #popover-content>{{
          data.item.service_amount.file_amount_type_flag.description
        }}</template>
      </files-edit-field-static>
    </template>
    <template v-if="data.type == 'room'">
      <files-edit-field-static :inline="true" :hide-content="false">
        <template #label>
          <svg
            v-if="data.item.room_amount.file_amount_type_flag_id === 1"
            style="margin-top: 7px; color: #ffcc00; cursor: pointer"
            class="feather feather-lock"
            xmlns="http://www.w3.org/2000/svg"
            width="12"
            height="12"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="3"
            viewBox="0 0 24 24"
          >
            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
          <svg
            v-if="data.item.room_amount.file_amount_type_flag_id === 2"
            style="margin: -5px 0; color: #3d3d3d; cursor: pointer"
            class="feather feather-lock"
            xmlns="http://www.w3.org/2000/svg"
            width="12"
            height="12"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="3"
            viewBox="0 0 24 24"
          >
            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
          <svg
            v-if="data.item.room_amount.file_amount_type_flag_id === 3"
            style="margin: -5px 0; color: #c4c4c4; cursor: pointer"
            class="feather feather-lock"
            xmlns="http://www.w3.org/2000/svg"
            width="12"
            height="12"
            fill="none"
            stroke="currentColor"
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="3"
            viewBox="0 0 24 24"
          >
            <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
            <path d="M7 11V7a5 5 0 0 1 10 0v4" />
          </svg>
        </template>
        <template #popover-content>{{
          data.item.room_amount.file_amount_type_flag.description
        }}</template>
      </files-edit-field-static>
    </template>
    <template #content-click>
      <div
        style="
          display: flex;
          flex-direction: column;
          gap: 10px;
          align-items: center;
          margin-bottom: -15px;
        "
        class="p-2"
      >
        <div>
          <span>B.T.: </span>
          <span style="color: #c4c4c4" class="text-uppercase"
            >{{ data.item.rate_plan_code }} - {{ data.item.rate_plan_name }}</span
          >
        </div>
        <template v-if="view == ''">
          <div style="display: flex; gap: 5px; align-items: center">
            <div style="display: flex; flex-direction: column; align-items: flex-end">
              <div style="color: #eb5757; font-weight: 600; font-size: 0.9rem">
                {{ t('global.label.cost') }}
              </div>
              <div style="color: #c4c4c4; font-weight: 700; font-size: 0.4rem; line-height: 0.4rem">
                {{ t('global.label.of') }} {{ t('global.label.service') }}
              </div>
            </div>
            <div style="color: #3d3d3d; font-weight: 700; font-size: 2.1rem">
              <font-awesome-icon style="width: 17px; height: 32px" icon="fa-solid fa-dollar-sign" />
              {{ data.item.amount_cost }}
            </div>
            <div @click="changeView('form')">
              <svg
                style="cursor: pointer"
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                class="feather feather-edit"
                viewBox="0 0 24 24"
              >
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7" />
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z" />
              </svg>
            </div>
          </div>
          <div
            style="
              display: flex;
              align-items: center;
              color: #c4c4c4;
              font-weight: 600;
              font-size: 0.6rem;
              gap: 2px;
            "
            v-if="data.item.service_amount?.file_amount_type_flag_id > 0"
          >
            <template v-if="data.type == 'service'">
              <svg
                v-if="data.item.service_amount.file_amount_type_flag_id === 1"
                style="color: #ffcc00"
                xmlns="http://www.w3.org/2000/svg"
                width="12"
                height="12"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                class="feather feather-lock"
                viewBox="0 0 24 24"
              >
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              <svg
                v-if="data.item.service_amount.file_amount_type_flag_id === 2"
                style="color: #3d3d3d"
                xmlns="http://www.w3.org/2000/svg"
                width="12"
                height="12"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                class="feather feather-lock"
                viewBox="0 0 24 24"
              >
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              <svg
                v-if="data.item.service_amount.file_amount_type_flag_id === 3"
                style="color: #c4c4c4"
                xmlns="http://www.w3.org/2000/svg"
                width="12"
                height="12"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                class="feather feather-lock"
                viewBox="0 0 24 24"
              >
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              {{ data.item.service_amount.file_amount_type_flag.description }}
            </template>
            <template v-if="data.type == 'room'">
              <svg
                v-if="data.item.room_amount.file_amount_type_flag_id === 1"
                style="color: #ffcc00"
                xmlns="http://www.w3.org/2000/svg"
                width="12"
                height="12"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                class="feather feather-lock"
                viewBox="0 0 24 24"
              >
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              <svg
                v-if="data.item.room_amount.file_amount_type_flag_id === 2"
                style="color: #3d3d3d"
                xmlns="http://www.w3.org/2000/svg"
                width="12"
                height="12"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                class="feather feather-lock"
                viewBox="0 0 24 24"
              >
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              <svg
                v-if="data.item.room_amount.file_amount_type_flag_id === 3"
                style="color: #c4c4c4"
                xmlns="http://www.w3.org/2000/svg"
                width="12"
                height="12"
                fill="none"
                stroke="currentColor"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                class="feather feather-lock"
                viewBox="0 0 24 24"
              >
                <rect width="18" height="11" x="3" y="11" rx="2" ry="2" />
                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
              </svg>
              {{ data.item.room_amount.file_amount_type_flag.description }}
            </template>
          </div>
        </template>
        <template v-if="view == 'form'">
          <base-select
            style="width: 100%"
            name="reasonValue"
            :filter-option="false"
            size="large"
            width="210"
            label-in-value
            :showSearch="false"
            :allowClear="true"
            v-model:value="newReason"
            placeholder="Selecciona un motivo"
            :options="filesStore.getCustomReasons"
            :comma="false"
            @change="handleChange"
          >
          </base-select>
          <a-form-item style="width: 210px">
            <div style="display: flex; gap: 10px; align-items: center">
              <div style="display: flex; flex-direction: column; align-items: flex-end">
                <div style="color: #eb5757; font-weight: 600; font-size: 0.9rem">
                  {{ t('global.label.cost') }}
                </div>
                <div
                  style="color: #c4c4c4; font-weight: 700; font-size: 0.4rem; line-height: 0.4rem"
                >
                  {{ t('global.label.manual_adjust') }}
                </div>
              </div>
              <div>
                <a-input
                  type="number"
                  min="0"
                  @change="validAmount"
                  v-model:value="newAmount"
                  :placeholder="'$' + data.item.amount_cost"
                  size="large"
                  style="height: 45px; width: 100%"
                >
                </a-input>
              </div>
            </div>
          </a-form-item>
          <div class="d-flex">
            <a-button
              @click="view = ''"
              v-bind:disabled="loading"
              type="default"
              class="mx-2 text-600 text-capitalize"
              default
              size="large"
            >
              {{ t('global.button.cancel') }}
            </a-button>
            <a-button
              @click="updateAmount"
              v-bind:disabled="loading"
              type="primary"
              class="mx-2 text-600 text-capitalize"
              default
              size="large"
            >
              {{ t('global.button.accept') }}
            </a-button>
          </div>
        </template>
      </div>
    </template>
    <template #content-buttons>&nbsp;</template>
  </popover-click>
</template>

<script setup>
  import { ref, onMounted } from 'vue';
  import PopoverClick from '@/components/files/reusables/PopoverClick.vue';
  import FilesEditFieldStatic from '@/components/files/edit/FilesEditFieldStatic.vue';
  import BaseSelect from '@/components/files/reusables/BaseSelect.vue';
  import { notification } from 'ant-design-vue';
  import { useFilesStore } from '@/stores/files';
  import { getUserId } from '@/utils/auth.js';
  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const emit = defineEmits(['onRefreshItineraryCache']);

  const loading = ref(false);
  const newAmount = ref('');
  const newReason = ref('');
  const view = ref('');
  const filesStore = useFilesStore();

  onMounted(() => {
    //
  });

  const props = defineProps({
    data: {
      type: Object,
      default: () => ({}),
    },
  });

  const changeView = (_view) => {
    view.value = _view;
  };

  const handleChange = (value) => {
    console.log(value);
  };

  const validAmount = () => {
    if (!isNaN(newAmount.value) && newAmount.value < 0) {
      newAmount.value = 0;
    }
  };

  const updateAmount = () => {
    if (newReason.value == '' || newReason.value == undefined || newReason.value == null) {
      notification['error']({
        message: `Actualización de Montos`,
        description: 'El motivo es obligatorio. Por favor, intente nuevamente.',
        duration: 5,
      });

      return false;
    }

    if (newAmount.value < 0 || newAmount.value == '') {
      notification['error']({
        message: `Actualización de Montos`,
        description: 'El monto no puede ser negativo o vacío. Por favor, intente nuevamente.',
        duration: 5,
      });

      return false;
    }

    let params = {
      type: props.data.type,
      itinerary_id: props.data.itinerary_id,
      file_number: filesStore.getFile.fileNumber,
      object_id: props.data.item.id,
      file_amount_reason_id: newReason.value.value,
      new_amount: newAmount.value,
      user_id: getUserId,
    };

    loading.value = true;

    filesStore
      .putUpdateAmounts(params)
      .then(() => {
        loading.value = false;
        newReason.value = '';
        newAmount.value = '';
        /*
        notification['success']({
          message: `Actualización de Montos`,
          description: 'Se actualizó correctamente',
          duration: 5,
        });
        */
        emit('onRefreshItineraryCache');
      })
      .catch((err) => console.log(err));
  };
</script>
