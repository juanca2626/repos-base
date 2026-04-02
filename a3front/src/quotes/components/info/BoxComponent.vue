<template>
  <div :class="['info-box', { 'disabled-box': props.disabled }]">
    <span class="text">{{ props.title }}</span>
    <div class="down">
      <span class="text">
        <slot name="text" />
      </span>
      <span v-if="$props.showEdit && !props.disabled" class="edit" @click="emits('edit')">
        <a-date-picker
          v-if="$props.type === 'date'"
          @change="changeDate"
          v-model:value="date"
          :locale="lang"
        />
        <font-awesome-icon :style="{ fontSize: '14px' }" icon="pen-to-square" />
      </span>
      <slot name="actions" />
    </div>
    <div v-if="slots.form" class="form">
      <slot name="form" />
    </div>
  </div>
</template>
<script lang="ts" setup>
  import { computed, toRef, useSlots, watch, watchEffect } from 'vue';
  import dayjs, { Dayjs } from 'dayjs';
  import localeEn from 'ant-design-vue/es/date-picker/locale/en_US';
  import localeEs from 'ant-design-vue/es/date-picker/locale/es_ES';
  import localePt from 'ant-design-vue/es/date-picker/locale/pt_BR';

  import 'dayjs/locale/es-mx';
  import 'dayjs/locale/pt-br';
  import 'dayjs/locale/en-au';

  import { useLanguagesStore } from '@/stores/global';

  const languageStore = useLanguagesStore();

  interface Props {
    type?: string;
    title: string;
    defaultDate?: Dayjs | Date | string | null;
    showEdit?: boolean;
    showBox?: boolean;
    disabled?: boolean;
  }

  const props = withDefaults(defineProps<Props>(), {
    title: '',
    showEdit: true,
    showBox: false,
    disabled: false,
  });

  const slots = useSlots();

  const defaultDate = toRef(props, 'defaultDate');
  const date = computed(() => dayjs(defaultDate.value));

  watch(defaultDate, () => {}, { immediate: true });

  interface Emits {
    (e: 'edit'): void;

    (e: 'change', result: ChangeDate): void;
  }

  const emits = defineEmits<Emits>();

  interface ChangeDate {
    date: Date;
    dateString: string;
  }

  const changeDate = (date: Date, dateString: string) => {
    emits('change', { date: date, dateString: dateString });
  };

  let lang = null;

  watchEffect(() => {
    if (languageStore.currentLanguage == 'es') {
      lang = localeEs;
      dayjs.locale('es-mx');
    }

    if (languageStore.currentLanguage == 'en') {
      lang = localeEn;
      dayjs.locale('en-br');
    }

    if (languageStore.currentLanguage == 'pt') {
      lang = localePt;
      dayjs.locale('pt-br');
    }
  });
</script>

<style lang="scss">
  @import '@/scss/variables';

  .info-box {
    position: relative;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: flex-start;
    color: #575757;

    .text {
      text-align: right;
      font-size: 10px;
      font-style: normal;
      font-weight: 400;
      line-height: 17px;
      letter-spacing: 0.15px;
    }

    .down {
      display: flex;
      align-items: flex-start;
      gap: 4px;
      position: relative;

      .text {
        text-align: right;
        font-size: 14px;
        font-style: normal;
        font-weight: 700;
        line-height: 21px;
        letter-spacing: 0.21px;
        text-transform: capitalize;
      }

      .edit {
        display: flex;
        width: 17px;
        height: 17px;
        justify-content: center;
        align-items: center;
        cursor: pointer;
        position: relative;

        .ant-picker {
          position: absolute;
          background: transparent;
          font-size: 12px;
          border: none;

          svg {
            display: none;
          }
        }

        .passengers {
          display: inline-flex;
          padding: 12px 16px;
          flex-direction: column;
          align-items: flex-end;
          gap: 10px;
          border-radius: 0 0 6px 6px;
          background: #ffffff;
          box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);

          .top {
            display: flex;
            align-items: flex-start;
            gap: 10px;
          }

          .block {
            display: flex;
            align-items: center;
            gap: 10px;

            .input {
              display: flex;
              padding: 0 1px;
              flex-direction: column;
              align-items: flex-start;
              gap: 6px;

              label {
                color: #575757;
                font-size: 14px;
                font-style: normal;
                font-weight: 500;
                line-height: 21px;
                letter-spacing: 0.21px;
                align-self: stretch;
              }

              input {
                display: flex;
                height: 45px;
                padding: 5px 10px;
                align-items: center;
                gap: 16px;
                border-radius: 4px;
                border: 1px solid #c4c4c4;
                background: #ffffff;
              }
            }
          }
        }

        &:hover {
          svg {
            color: #eb5757;
          }
        }
      }
    }

    .form {
      display: flex;
      border-radius: 0 0 6px 6px;
      background: #fff;
      box-shadow: 0 4px 8px 0 rgba(16, 24, 40, 0.16);
      position: absolute;
      top: 15px;
      right: 0;
      z-index: 11;
    }

    &.disabled-box {
      opacity: 0.6;
      pointer-events: none;
      filter: grayscale(0.5);
    }
  }
</style>
