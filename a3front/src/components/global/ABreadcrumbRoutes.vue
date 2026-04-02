<template>
  <a-breadcrumb v-if="lastElementHasBreadcrumb">
    <template #separator>
      <slot name="separator">
        <span class="separator" style="color: #bd0d12">
          <font-awesome-icon :icon="['fas', 'chevron-right']" />
        </span>
      </slot>
    </template>
    <a-breadcrumb-item
      v-for="(crumb, ci) in breadcrumbRoutes"
      :key="ci"
      class="breadcrumb-item"
      :class="isLastElement(ci) ? 'breadcrumb-item--active' : null"
    >
      <span v-if="isLastElement(ci)">
        {{ crumb.meta.breadcrumb }}
      </span>
      <a v-else @click="selected(crumb)">
        {{ crumb.meta.breadcrumb }}
      </a>
    </a-breadcrumb-item>
    <a-breadcrumb-item v-if="hasViewSection()">
      {{ actuaBreadcrumbView.label }}
    </a-breadcrumb-item>
  </a-breadcrumb>
</template>

<script setup>
  import { defineEmits, ref, onMounted, onUpdated, watch } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useBreadcrumb } from '@/utils/hooks.js';

  const { actuaBreadcrumbVars, actuaBreadcrumbView, changeView, setVariables } = useBreadcrumb();

  const route = useRoute();
  const router = useRouter();

  defineEmits(['selected']);

  const lastElementHasBreadcrumb = route.matched[route.matched.length - 1]?.meta?.breadcrumb !== '';
  const breadcrumbRoutes = ref([]);
  const hasViewSection = () => actuaBreadcrumbView.value.view;
  const isLastElement = (index) => index === breadcrumbRoutes.value.length - 1 && !hasViewSection();

  onMounted(() => {
    getBreadcrumbRoutes();
  });
  onUpdated(() => {
    getBreadcrumbRoutes();
  });

  watch(actuaBreadcrumbVars, (variables) => {
    if (Array.isArray(variables)) {
      replaceVarsOnBreadcrumName(variables);
    }
  });

  async function selected(crumb) {
    await router.push({ name: crumb.name });
    changeView(null);
    setVariables(null);
    getBreadcrumbRoutes();
  }

  function replaceVarsOnBreadcrumName(variables) {
    breadcrumbRoutes.value.forEach((bc, index) => {
      if (bc.meta.variables) {
        variables.forEach((_var) => {
          breadcrumbRoutes.value[index].meta.breadcrumb = bc.meta.breadcrumb.replace(
            _var.name,
            _var.val
          );
        });
      }
    });
  }

  function getBreadcrumbRoutes() {
    breadcrumbRoutes.value = [];
    route.matched.forEach((_route, index) => {
      if (_route.meta.parentBreadcrumb) {
        const parent = route.matched[index - 1];
        const parentRoute = parent.children.find(
          (childRoute) => childRoute.name === _route.meta.parentBreadcrumb
        );
        parentRoute.path = `${parent.path}/${parentRoute.path}`;
        breadcrumbRoutes.value.push(parentRoute);
      }
      breadcrumbRoutes.value.push(_route);
    });
  }
</script>
