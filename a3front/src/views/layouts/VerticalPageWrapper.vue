<template>
  <VerticalLayout :isLoading="isLoading">
    <!-- Slot personalizado para breadcrumb -->
    <template #breadcrumb>
      <slot name="breadcrumb">
        <a-breadcrumb class="breadcrumb-component">
          <template #separator>
            <span class="separator"> / </span>
          </template>
          <a-breadcrumb-item>{{ breadcrumbTitle }}</a-breadcrumb-item>
        </a-breadcrumb>
      </slot>
    </template>

    <!-- Contenido principal -->
    <slot />
  </VerticalLayout>
</template>

<script setup>
  import VerticalLayout from './VerticalLayout.vue';

  defineProps({
    breadcrumbTitle: {
      type: String,
      default: 'Sin título',
    },
    isLoading: {
      type: Boolean,
      default: false,
    },
  });
</script>
