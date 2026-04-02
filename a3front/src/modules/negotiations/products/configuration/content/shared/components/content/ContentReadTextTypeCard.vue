<template>
  <div class="content-read-card content-read-card-text">
    <ContentCardHeader :title="title" :status="status" />
    <div v-if="!isExpanded && excerpt" class="content-read-excerpt">
      {{ excerpt }}
    </div>
    <div v-else-if="isExpanded && fullHtml" class="content-read-full" v-html="fullHtml" />
    <div v-if="excerpt || fullHtml" class="content-read-link-wrap">
      <a class="content-read-link" href="#" @click.prevent="$emit('toggle')">
        {{ isExpanded ? 'Ver menos' : expandLinkText }}

        <IconArrowRightLong v-if="!isExpanded" />
        <IconArrowUpLong v-else />
      </a>
    </div>
  </div>
</template>

<script setup lang="ts">
  import ContentCardHeader from './ContentCardHeader.vue';
  import IconArrowRightLong from '@/modules/negotiations/products/configuration/content/shared/icons/IconArrowRightLong.vue';
  import IconArrowUpLong from '@/modules/negotiations/products/configuration/content/shared/icons/IconArrowUpLong.vue';

  interface Props {
    title: string;
    status?: string;
    excerpt: string;
    fullHtml: string;
    isExpanded: boolean;
    expandLinkText?: string;
  }

  withDefaults(defineProps<Props>(), {
    status: undefined,
    expandLinkText: 'Ver itinerario completo',
  });

  defineEmits<{
    (e: 'toggle'): void;
  }>();
</script>

<style scoped lang="scss">
  @import '@/scss/_variables.scss';

  .content-read-card {
    background-color: $color-white;
    border-radius: 8px;
    border: 0.5px solid #e7e7e7;
    padding: 16px;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
  }

  .content-read-excerpt {
    font-size: 14px;
    color: $color-black-3;
    line-height: 1.5;
    padding-left: 12px;
    border-left: 3px solid #fff2dd;
    margin: 0 20px 12px;
  }

  .content-read-full {
    font-size: 14px;
    color: $color-black-2;
    line-height: 1.5;
    margin-bottom: 12px;

    :deep(p) {
      margin-bottom: 8px;
    }

    :deep(ul),
    :deep(ol) {
      margin-left: 1.2em;
    }
  }

  .content-read-link-wrap {
    display: flex;
    justify-content: flex-end;
  }

  .content-read-link {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 14px;
    font-weight: 500;
    color: #1890ff;
    text-decoration: none;
    cursor: pointer;

    &:hover {
      text-decoration: underline;
    }
  }
</style>
