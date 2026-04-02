<template>
  <a-col v-if="flagShowIcon">
    <files-edit-field-static :inline="true" :hide-content="false" :highlighted="false" :link="true">
      <template #label style="position: relative; background-color: none">
        <span class="d-flex cursor-pointer" @click="toggleFlagModal">
          <font-awesome-icon :icon="['fas', 'receipt']" />
          <!-- font-awesome-icon :icon="['far', 'money-bill-1']" style="font-size: 16px" / -->
        </span>
      </template>
      <template #popover-content>
        {{ t('global.label.voucher') }}
      </template>
    </files-edit-field-static>
  </a-col>

  <a-modal v-model:visible="flagModal" :width="620" :closable="false" :maskClosable="false">
    <template #title> Voucher ({{ providers.length }}) </template>
    <div id="files-layout">
      <div style="border-top: 1px solid #ccc; font-size: 14px" class="pt-3">
        <a-alert type="info" class="mb-3">
          <template #description>
            <a-row type="flex" justify="start" align="middle" style="gap: 10px">
              <a-col>
                <i class="bi bi-exclamation-circle" style="font-size: 18px"></i>
              </a-col>
              <a-col>
                <p class="text-700 mb-1 p-0">Envío automático</p>
                El voucher se generará y enviará al proveedor el día:<br />
                <b>(48hrs antes del inicio del servicio)</b>
              </a-col>
            </a-row>
          </template>
        </a-alert>

        <a-alert type="error" class="mb-3 text-danger text-400" v-if="false">
          <template #description>
            <a-row type="flex" justify="start" align="top" style="gap: 10px">
              <a-col>
                <i class="bi bi-exclamation-circle" style="font-size: 18px"></i>
              </a-col>
              <a-col>
                <p class="text-700 mb-1 p-0">Envío automático</p>
                El voucher se generará y enviará al proveedor el día:<br />
                <b>(48hrs antes del inicio del servicio)</b>
              </a-col>
            </a-row>
          </template>
        </a-alert>
      </div>

      <div class="voucher-box mb-0 mt-3" v-for="(_provider, p) in providers">
        <div class="voucher-box-name">
          <a-row class="my-1" type="flex" justify="start" align="middle">
            <a-col class="bg-white ps-2 py-2">
              <font-awesome-icon :icon="['fas', 'user-gear']" />
            </a-col>
            <a-col class="bg-white px-2 py-2">{{ _provider.code_request_voucher }}</a-col>
          </a-row>
        </div>

        <div class="voucher-box-description text-400" style="font-size: 14px">
          {{ _provider.service_name }}
        </div>

        <a-row
          v-bind:class="['d-flex text-dark-gray text-500 my-2']"
          type="flex"
          justify="start"
          align="middle"
          style="gap: 10px"
        >
          <a-col>
            <i
              @click="_provider.flagNotes = !_provider.flagNotes"
              v-bind:class="[
                'bi',
                !_provider.flagNotes ? 'bi-square' : 'bi-check-square-fill text-danger',
                'cursor-pointer',
              ]"
              style="font-size: 1rem"
            ></i>
          </a-col>
          <a-col>
            <span
              v-bind:class="[
                'cursor-pointer',
                _provider.flagNotes ? 'text-danger' : '',
                'text-500',
              ]"
              @click="_provider.flagNotes = !_provider.flagNotes"
              style="font-size: 14px"
              >Agregar nota para el proveedor</span
            >
          </a-col>
        </a-row>

        <template v-if="_provider.flagNotes">
          <div
            class="voucher-box-note"
            v-if="_provider.locked_note && _provider.notes !== '' && _provider.notes !== null"
          >
            <div class="voucher-box-note-title">
              <a-row type="flex" justify="space-between" align="middle">
                <a-col> Nota de voucher </a-col>
                <a-col>
                  <span class="cursor-pointer" @click="_provider.locked_note = false">
                    <a-tooltip>
                      <template #title>Editar nota al proveedor</template>
                      <IconPencilLinear />
                    </a-tooltip>
                  </span>
                </a-col>
              </a-row>
            </div>

            <div class="voucher-box-note-description">
              {{ _provider.notes }}
            </div>
          </div>

          <div class="voucher-box-new" v-if="_provider.flagNotes && !_provider.locked_note">
            <a-row
              type="flex"
              justify="start"
              align="middle"
              style="gap: 5px"
              class="text-danger text-600 my-2 voucher-box-new-title"
            >
              <a-col>
                <label for="note">
                  <IconPencilLinear />
                </label>
              </a-col>
              <a-col> <label for="note">Nota para el proveedor:</label></a-col>
            </a-row>
            <a-textarea rows="4" v-model:value="_provider.notes" id="note"></a-textarea>
            <a-row type="flex" justify="end" align="middle" style="gap: 5px" class="mt-3">
              <a-col>
                <a-button
                  danger
                  type="primary"
                  size="large"
                  v-bind:disabled="_provider.notes == '' || _provider.notes == null"
                  :loading="filesStore.isLoading"
                  @click="handleSaveNote(p)"
                >
                  <i v-bind:class="['bi bi-floppy', filesStore.isLoading ? 'ms-2' : '']"></i>
                </a-button>
              </a-col>
            </a-row>
          </div>
        </template>
      </div>
    </div>

    <template #footer>
      <div class="text-center" id="files-layout">
        <a-button class="text-600" default @click="closeModal" size="large">
          {{ t('global.button.close') }}
        </a-button>
        <a-button type="primary" class="text-600" default @click="saveVoucher" size="large">
          {{ t('global.button.save') }}
        </a-button>
      </div>
    </template>
  </a-modal>
</template>

<script setup>
  import { ref, onBeforeMount } from 'vue';
  import FilesEditFieldStatic from '@/components/files/edit/FilesEditFieldStatic.vue';
  import IconPencilLinear from '@/components/icons/IconPencilLinear.vue';
  import { useFilesStore } from '@store/files';

  import { useI18n } from 'vue-i18n';
  const { t } = useI18n({
    useScope: 'global',
  });

  const filesStore = useFilesStore();

  const props = defineProps({
    itinerary: {
      type: Object,
      default: () => ({}),
    },
  });

  const flagModal = ref(false);
  const flagShowIcon = ref(false);
  const providers = ref([]);

  const closeModal = () => {
    flagModal.value = false;
  };

  const toggleFlagModal = () => {
    flagModal.value = !flagModal.value;
  };

  const saveVoucher = () => {
    console.log('Guardando Voucher..');
  };

  const handleSaveNote = (_provider) => {
    providers.value[_provider].locked_note = true;
  };

  onBeforeMount(async () => {
    props.itinerary.services.forEach((service) => {
      service.compositions.forEach((composition) => {
        if (composition.use_voucher) {
          flagShowIcon.value = true;
          composition.supplier.service_name = composition.name;
          providers.value.push(composition.supplier);
        }
      });
    });
  });
</script>

<style lang="scss">
  .voucher {
    &-box {
      margin-bottom: 1.2rem;
      padding: 16px 30px;
      border: 0.5px solid #e9e9e9;
      border-radius: 4px;
      background-color: #fafafa;

      &-note {
        border: 1px solid #e9e9e9;
        border-radius: 4px;
        padding: 15px 10px;
        font-size: 12px;

        &-title {
          color: #979797;
          padding: 5px 10px;
          margin-bottom: 5px;
          border-bottom: 1px solid #e9e9e9;
        }

        &-description {
          padding: 5px 13px;
          font-weight: 700;
        }
      }

      &-new {
        font-size: 12px;
        margin: 15px 0;
      }
    }
  }
</style>
