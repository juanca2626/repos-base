<template>
  <div>
    <a-row>
      <a-col span="24" class="mb-3">
        <a-typography-title :level="5" class="text-center">
          {{ title }}
        </a-typography-title>
      </a-col>
    </a-row>
    <a-row>
      <a-col span="24" align="center">
        <a-space class="space-between-buttons">
          <a-button
            v-for="button in buttons"
            :key="button.text"
            :type="button.type || 'default'"
            shape="round"
            size="middle"
            @click="button.onClick"
          >
            {{ button.text }}
          </a-button>
        </a-space>
      </a-col>
    </a-row>
  </div>
</template>

<script setup lang="ts">
  import { defineProps } from 'vue';

  interface Button {
    text: string;
    type?: 'primary' | 'default' | 'dashed' | 'text' | 'link';
    onClick: () => void;
  }

  const props = defineProps<{
    title: string;
    buttons: Button[];
  }>();

  console.log(props.buttons);
</script>

<style scoped lang="scss">
  .space-between-buttons {
    button {
      border-radius: 50px;
      height: auto;
    }
  }
</style>
