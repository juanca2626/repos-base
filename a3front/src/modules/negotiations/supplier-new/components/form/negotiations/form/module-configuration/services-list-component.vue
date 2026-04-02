<template>
  <div class="services-summary">
    <div v-for="(srv, i) in visibleServices" :key="i" class="service-row">
      <span class="label">Código:</span>
      <span class="value">{{ srv.code }}</span>

      <span class="label sep">Nombre del servicio:</span>
      <span class="value">{{ srv.name }}</span>

      <span class="label sep">Tipo de servicio:</span>
      <span class="value">{{ srv.type }}</span>
    </div>

    <a
      v-if="totalCount > visibleCount"
      class="view-all"
      :href="linkHref"
      @click.prevent="$emit('view-all')"
    >
      Ver los {{ totalCount }} servicios
    </a>
  </div>
</template>

<script setup lang="ts">
  import { computed } from 'vue';

  defineOptions({
    name: 'ServicesListComponent',
  });

  type Service = { code: string; name: string; type: string };

  const props = withDefaults(
    defineProps<{
      services?: Service[];
      totalCount?: number;
      visibleCount?: number;
      linkHref?: string;
    }>(),
    {
      services: () => [
        { code: 'Lt001', name: 'Desayuno buffet', type: 'Desayuno' },
        { code: 'Lt002', name: 'Desayuno continental', type: 'Desayuno' },
        { code: 'Lt002', name: 'Desayuno continental', type: 'Desayuno' },
        { code: 'Lt002', name: 'Desayuno continental', type: 'Desayuno' },
      ],
      totalCount: 234,
      visibleCount: 4,
      linkHref: '#',
    }
  );

  defineEmits<{ (e: 'view-all'): void }>();

  const visibleServices = computed(() => props.services.slice(0, props.visibleCount));
  const totalCount = computed(() => props.totalCount);
  const visibleCount = computed(() => props.visibleCount);
  const linkHref = computed(() => props.linkHref);
</script>

<style scoped>
  .services-summary {
    font-size: 12px;
    color: #2f353a;
  }
  .service-row {
    margin-bottom: 6px;
    line-height: 1.6;
  }
  .label {
    font-weight: 600;
    margin-right: 6px;
    color: #7e8285;
  }
  .label.sep {
    margin-left: 14px;
  }
  .value {
    color: #2f353a;
  }
  .view-all {
    display: inline-block;
    margin-top: 6px;
    font-size: 12px;
    color: #1284ed;
    text-decoration: underline;
  }
  .view-all:hover {
    opacity: 0.9;
  }
</style>
