<template>
  <a-typography-text :style="{ display: 'block' }"> {{ title }}: </a-typography-text>
  <a-space :direction="direction.direction" :size="direction.size">
    <template v-if="source === 'fontAwesome'">
      <template v-if="type === 'link'"></template>
      <template v-else>
        <a-typography-text v-for="(v, i) in safeContent" :key="i">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 512 512"
            width="16"
            height="16"
            style="margin-right: 5px"
          >
            <path :d="infoCurrentIcon?.path" />
          </svg>
          <span v-if="icon === 'user'">
            {{ v.fullname + ' ' + '(' + v.code + ')' }}
          </span>
        </a-typography-text>
      </template>
    </template>
    <template v-else>
      <template v-if="type === 'link'">
        <a-typography-link
          v-for="(v, i) in safeContent"
          :key="i"
          :href="v.link"
          target="_blank"
          :title="v.alt"
        >
          <a-flex justify="flex-start" align="center">
            <MailOutlined v-if="icon === 'email'" :style="{ marginRight: '5px' }" />
            <LinkOutlined v-if="icon === 'link'" :style="{ marginRight: '5px' }" />
            <PhoneOutlined
              v-if="icon === 'phone'"
              :style="{ transform: 'scaleX(-1)', marginRight: '5px' }"
            />
            <span>{{ v.value }}</span>
          </a-flex>
        </a-typography-link>
      </template>
      <template v-else>
        <a-typography-text v-for="(v, i) in safeContent" :key="i">
          <PhoneOutlined
            v-if="icon === 'phone'"
            :style="{ transform: 'scaleX(-1)', marginRight: '5px' }"
          />
          <span>{{ v.value }}</span>
        </a-typography-text>
      </template>
    </template>
  </a-space>
</template>

<script setup lang="ts">
  import { computed } from 'vue';
  import { LinkOutlined, PhoneOutlined, MailOutlined } from '@ant-design/icons-vue';

  interface ContentItem {
    value: string;
    link?: string;
  }

  interface Props {
    title: string;
    icon: string;
    source: string;
    direction: { direction: 'horizontal' | 'vertical'; size: number };
    content: ContentItem[] | undefined;
    type: string;
  }

  const props = defineProps<Props>();
  // Íconos disponibles
  const icons = [
    {
      source: 'fontAwesome',
      icon: 'user',
      path: 'M406.5 399.6C387.4 352.9 341.5 320 288 320l-64 0c-53.5 0-99.4 32.9-118.5 79.6C69.9 362.2 48 311.7 48 256C48 141.1 141.1 48 256 48s208 93.1 208 208c0 55.7-21.9 106.2-57.5 143.6zm-40.1 32.7C334.4 452.4 296.6 464 256 464s-78.4-11.6-110.5-31.7c7.3-36.7 39.7-64.3 78.5-64.3l64 0c38.8 0 71.2 27.6 78.5 64.3zM256 512A256 256 0 1 0 256 0a256 256 0 1 0 0 512zm0-272a40 40 0 1 1 0-80 40 40 0 1 1 0 80zm-88-40a88 88 0 1 0 176 0 88 88 0 1 0 -176 0z',
    },
  ];

  // Computed para encontrar el ícono actual basado en `props.icon`
  const infoCurrentIcon = computed(() => {
    return icons.find((v) => v.icon === props.icon);
  });

  // Asegura que `content` siempre sea un array
  const safeContent: any = computed(() => (Array.isArray(props.content) ? props.content : []));
</script>

<style scoped></style>
