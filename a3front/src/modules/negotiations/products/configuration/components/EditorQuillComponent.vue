<template>
  <div class="editor-quill-container">
    <QuillEditor
      v-model:content="content"
      :placeholder="placeholder"
      theme="snow"
      :toolbar="toolbarOptions"
      content-type="html"
      @update:content="handleContentUpdate"
    />
    <div class="character-count">{{ characterCount }}/{{ maxLength }}</div>
  </div>
</template>

<script setup lang="ts">
  import { ref, computed, watch } from 'vue';
  import { QuillEditor } from '@vueup/vue-quill';
  import '@vueup/vue-quill/dist/vue-quill.snow.css';

  defineOptions({
    name: 'EditorQuillComponent',
  });

  interface Props {
    modelValue?: string;
    placeholder?: string;
    maxLength?: number;
  }

  const props = withDefaults(defineProps<Props>(), {
    modelValue: '',
    placeholder: 'Detalla lo que hará el pasajero durante el servicio',
    maxLength: 1000,
  });

  const emit = defineEmits<{
    (e: 'update:modelValue', value: string): void;
  }>();

  const content = ref(props.modelValue);

  // Configuración personalizada del toolbar
  const toolbarOptions = [
    [{ header: [false, 1, 2, 3, 4, 5, 6] }],
    ['bold', 'italic', 'underline'],
    [{ list: 'ordered' }, { list: 'bullet' }],
    [{ align: '' }, { align: 'center' }, { align: 'right' }, { align: 'justify' }],
  ];

  const characterCount = computed(() => {
    const text = content.value.replace(/<[^>]*>/g, '');
    return text.length;
  });

  const handleContentUpdate = (value: string) => {
    const textLength = value.replace(/<[^>]*>/g, '').length;
    if (textLength <= props.maxLength) {
      emit('update:modelValue', value);
    }
  };

  watch(
    () => props.modelValue,
    (newValue) => {
      if (newValue !== content.value) {
        content.value = newValue;
      }
    }
  );
</script>

<style scoped lang="scss">
  .editor-quill-container {
    position: relative;
    width: 100%;
    border-radius: 4px;
    padding: 0 16px;
    margin-bottom: 35px;
  }

  :deep(.ql-toolbar.ql-snow) {
    border: none;
    border-bottom: 1px solid #d9d9d9;
    padding: 8px 12px;
    font-family: inherit;

    .ql-formats {
      margin-right: 15px;

      &:first-child {
        margin-right: 15px;
      }

      button {
        width: 28px;
        height: 28px;
        padding: 3px 5px;
        color: #595959;

        &:hover {
          color: #262626;

          .ql-stroke {
            stroke: #262626;
          }

          .ql-fill {
            fill: #262626;
          }
        }

        &.ql-active {
          color: #373636;
          background-color: #e8e8e8;

          .ql-stroke {
            stroke: #262626;
          }

          .ql-fill {
            fill: #262626;
          }
        }
      }

      .ql-picker {
        color: #595959;
        font-size: 13px;
        position: relative;
        z-index: 100;

        .ql-picker-label {
          padding-left: 8px;
          padding-right: 8px;
          border: none;

          &::before {
            line-height: 28px;
          }

          &:hover {
            color: #262626;

            .ql-stroke {
              stroke: #262626;
            }
          }
        }

        &.ql-expanded {
          .ql-picker-label {
            border: none;
            color: #262626;
            z-index: 200;
          }

          .ql-picker-options {
            z-index: 9999 !important;
          }
        }

        .ql-picker-options {
          border: 1px solid #d9d9d9;
          border-radius: 4px;
          box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
          padding: 4px 0;
          background-color: #ffffff;
          z-index: 1000;

          .ql-picker-item {
            padding: 5px 12px;
            color: #595959;

            &:hover {
              background-color: #f5f5f5;
              color: #262626;
            }

            &.ql-selected {
              color: #262626;
            }
          }
        }
      }
    }
  }

  :deep(.ql-container.ql-snow) {
    border: 1px solid #d9d9d9;
    border-top: none;
    border-bottom-left-radius: 4px;
    border-bottom-right-radius: 4px;
    min-height: 80px;
    font-family: inherit;
    font-size: 14px;
    background-color: #ffffff;
    position: relative;
    z-index: 1;

    .ql-editor {
      min-height: 80px;
      padding: 12px 16px;
      color: #262626;
      line-height: 1.5715;

      &.ql-blank::before {
        color: #bfbfbf;
        font-style: normal;
        left: 16px;
        right: 16px;
        font-size: 14px;
      }

      p {
        margin-bottom: 0;
      }

      strong {
        font-weight: 600;
      }

      h1 {
        font-size: 32px;
        font-weight: 600;
        margin: 0;
        padding: 0;
      }

      h2 {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
        padding: 0;
      }

      h3 {
        font-size: 18.72px;
        font-weight: 600;
        margin: 0;
        padding: 0;
      }

      h4 {
        font-size: 16px;
        font-weight: 600;
        margin: 0;
        padding: 0;
      }

      h5 {
        font-size: 13.28px;
        font-weight: 600;
        margin: 0;
        padding: 0;
      }

      h6 {
        font-size: 10.72px;
        font-weight: 600;
        margin: 0;
        padding: 0;
      }
    }
  }

  .character-count {
    position: absolute;
    bottom: 8px;
    right: 12px;
    font-size: 12px;
    color: #8c8c8c;
    background-color: rgba(255, 255, 255, 0.95);
    padding: 2px 6px;
    border-radius: 2px;
    pointer-events: none;
  }
</style>
