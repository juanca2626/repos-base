<template>
  <button
    :type="type"
    :disabled="disabled"
    :class="['icon-action-button', { 'is-disabled': disabled }]"
    @click="handleClick"
    :style="{ color: getColorValue }"
  >
    <template v-if="actionType === 'add'">
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        class="custom-svg-icon"
      >
        <path
          d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M12 8V16"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M8 12H16"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </template>

    <template v-else-if="actionType === 'edit'">
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        class="custom-svg-icon"
      >
        <path
          d="M10 3.12132H3C2.46957 3.12132 1.96086 3.33203 1.58579 3.70711C1.21071 4.08218 1 4.59089 1 5.12132V19.1213C1 19.6518 1.21071 20.1605 1.58579 20.5355C1.96086 20.9106 2.46957 21.1213 3 21.1213H17C17.5304 21.1213 18.0391 20.9106 18.4142 20.5355C18.7893 20.1605 19 19.6518 19 19.1213V12.1213M17.5 1.62132C17.8978 1.2235 18.4374 1 19 1C19.5626 1 20.1022 1.2235 20.5 1.62132C20.8978 2.01915 21.1213 2.55871 21.1213 3.12132C21.1213 3.68393 20.8978 4.2235 20.5 4.62132L11 14.1213L7 15.1213L8 11.1213L17.5 1.62132Z"
          stroke="#1284ED"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </template>

    <template v-else-if="actionType === 'delete_v1'">
      <svg
        width="24"
        height="24"
        viewBox="0 0 24 24"
        fill="none"
        xmlns="http://www.w3.org/2000/svg"
        class="custom-svg-icon"
      >
        <path
          d="M12 22C17.5228 22 22 17.5228 22 12C22 6.47715 17.5228 2 12 2C6.47715 2 2 6.47715 2 12C2 17.5228 6.47715 22 12 22Z"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
        <path
          d="M4.92969 4.92969L19.0697 19.0697"
          stroke="currentColor"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
    </template>

    <template v-else-if="actionType === 'delete_v2'">
      <font-awesome-icon :icon="['far', 'trash-can']" :style="{ height: '20px' }" class="fa-icon" />
    </template>

    <slot />
  </button>
</template>

<script setup lang="ts">
  import { type PropType, computed } from 'vue';

  // --- Tipos ---
  type ActionType = 'add' | 'delete_v1' | 'delete_v2' | 'edit';
  type IconColor = 'default' | 'primary' | 'danger' | string;
  type ButtonType = 'button' | 'submit' | 'reset';

  defineOptions({
    name: 'IconActionButtonComponent',
  });

  // --- Props ---
  const props = defineProps({
    actionType: {
      type: String as PropType<ActionType>,
      required: true,
      validator: (value: string) => ['add', 'delete_v1', 'delete_v2', 'edit'].includes(value),
    },
    iconColor: {
      type: String as PropType<IconColor>,
      default: 'default',
    },
    type: {
      type: String as PropType<ButtonType>,
      default: 'button',
    },
    disabled: {
      type: Boolean,
      default: false,
    },
  });

  // --- Computadas ---
  const getColorValue = computed(() => {
    switch (props.iconColor) {
      case 'primary':
        return '#bd0d12'; // Rojo de la marca
      case 'danger':
        return '#FF4D4F'; // Rojo de error estándar
      case 'default':
        return '#575B5F'; // Gris por defecto
      default:
        // Si es un color CSS válido (ej: #333) lo usa, si no, usa el gris por defecto
        return props.iconColor.startsWith('#') ||
          props.iconColor.includes('rgb') ||
          props.iconColor.includes('var')
          ? props.iconColor
          : '#575B5F';
    }
  });

  // --- Emits y Lógica ---
  const emit = defineEmits<{
    (e: 'click', event: MouseEvent): void;
  }>();

  const handleClick = (event: MouseEvent) => {
    if (!props.disabled) {
      emit('click', event);
    }
  };
</script>

<style scoped lang="scss">
  .icon-action-button {
    // --- ESTILOS BASE DEL BOTÓN PURO DE ÍCONO ---
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px;

    // ELIMINACIÓN DE BORDES, FONDO Y SOMBRAS
    border: none;
    background-color: transparent;
    box-shadow: none;
    outline: none;

    cursor: pointer;
    transition: opacity 0.2s ease-in-out;
    line-height: 1;
    gap: 4px;

    // El color se hereda de la prop a través de style="{ color: ... }"

    // --- ESTILOS DE HOVER Y FOCUS ---
    &:hover:not(.is-disabled) {
      opacity: 0.7; // Reduce opacidad al hacer hover para feedback visual
    }

    &:focus {
      outline: none;
      box-shadow: none;
    }

    &:active:not(.is-disabled) {
      opacity: 0.5;
    }

    // --- ESTILOS DESHABILITADO ---
    &.is-disabled {
      cursor: not-allowed;
      opacity: 0.4;
      background-color: transparent;
    }

    // --- ESTILOS DEL ÍCONO ---
    .custom-svg-icon {
      width: 24px;
      height: 24px;
      vertical-align: middle;
      // stroke: currentColor lo controla el color
    }

    .fa-icon {
      height: 20px;
      vertical-align: middle;
      // Font Awesome es coloreado por la propiedad 'color' heredada
    }
  }
</style>
