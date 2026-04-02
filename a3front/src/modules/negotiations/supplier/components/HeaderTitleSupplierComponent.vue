<template>
  <div class="header-container">
    <div v-if="prefixIcon">
      <component :is="prefixIcon" />
    </div>
    <div class="title-form">{{ name }} {{ separator }} {{ title }}</div>
    <div v-if="suffixIcon">
      <component :is="suffixIcon" />
    </div>
  </div>
</template>

<script lang="ts">
  import { defineComponent, computed } from 'vue';
  import { useRoute } from 'vue-router';

  export default defineComponent({
    name: 'HeaderTitleSupplierComponent',
    props: {
      name: {
        type: String,
        required: true,
      },
      separator: {
        type: String,
        default: '-',
        required: false,
      },
      prefixIcon: {
        type: [String, Object],
        default: null,
      },
      suffixIcon: {
        type: [String, Object],
        default: null,
      },
      title: {
        type: String,
        default: '',
        required: false,
      },
    },
    setup(props) {
      const route = useRoute();
      const computedTitle = computed(() => route.meta.title || '');
      const title = computed(() => props.title || computedTitle.value);

      return {
        title,
      };
    },
  });
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  * {
    font-family: $font_general !important;
  }

  .header-container {
    display: flex;
    align-items: end;
    gap: 9px;
  }

  .title-form {
    font-size: 18px !important;
    color: #2f353a;
    font-weight: 600;
    width: 36rem;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
  }
</style>
