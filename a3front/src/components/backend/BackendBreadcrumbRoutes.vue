<template>
  <a-breadcrumb class="breadcrumb-component" v-if="lastElementHasBreadcrumb">
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
      <a v-else-if="crumb.name" @click="selected(crumb)">
        {{ crumb.meta.breadcrumb }}
      </a>
      <span v-else>
        {{ crumb.meta.breadcrumb }}
      </span>
    </a-breadcrumb-item>
    <a-breadcrumb-item v-if="hasViewSection()">
      {{ actuaBreadcrumbView.label }}
    </a-breadcrumb-item>
  </a-breadcrumb>
</template>

<script setup>
  import { ref, watch, onMounted } from 'vue';
  import { useRoute, useRouter } from 'vue-router';
  import { useBreadcrumb } from '@/utils/hooks.js';
  import { useRouteUpdate } from '@/composables/useRouteUpdate';
  import { useRouteStore } from '@/stores/global/routeStore';
  import { storeToRefs } from 'pinia';
  import { useSelectedSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/selected-supplier-classification.store';
  import { useSupplierClassificationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/supplier-classification.store';

  const { actuaBreadcrumbVars, actuaBreadcrumbView, changeView, setVariables } = useBreadcrumb();
  const route = useRoute();
  const router = useRouter();
  const { currentPath, currentRouteName } = useRouteUpdate();
  const routeStore = useRouteStore();
  const { currentPath: storePath, currentRouteName: storeRouteName } = storeToRefs(routeStore);
  const selectedSupplierClassificationStore = useSelectedSupplierClassificationStore();
  const supplierClassificationStore = useSupplierClassificationStore();
  const { selectedClassificationId } = storeToRefs(selectedSupplierClassificationStore);
  const {
    supplierClassificationId,
    supplierSubClassificationId,
    supplierClassificationName,
    supplierSubClassificationName,
  } = storeToRefs(supplierClassificationStore);

  defineEmits(['selected']);

  const lastElementHasBreadcrumb = ref(true);
  const breadcrumbRoutes = ref([]);
  const hasViewSection = () => actuaBreadcrumbView.value.view;
  const isLastElement = (index) => index === breadcrumbRoutes.value.length - 1 && !hasViewSection();

  onMounted(() => {
    getBreadcrumbRoutes();
  });

  watch(
    [
      currentPath,
      currentRouteName,
      storePath,
      storeRouteName,
      selectedClassificationId,
      supplierClassificationId,
      supplierSubClassificationId,
      supplierClassificationName,
      supplierSubClassificationName,
    ],
    () => {
      getBreadcrumbRoutes();
    }
  );

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

  function isSupplierFormBreadcrumbRoute(routeName) {
    return routeName === 'supplier-register-form' || routeName === 'supplier-edit-form';
  }

  function buildSupplierBreadcrumbRoute(supplierRoute) {
    return {
      name: supplierRoute.name,
      meta: {
        breadcrumb: supplierRoute.label,
      },
    };
  }

  function getSupplierClassificationBreadcrumb() {
    const supplierBreadcrumbMap = {
      TRP: { label: 'Transporte terrestre', name: 'supplierLandTransportList' },
      ATT: { label: 'Atractivos turísticos', name: 'supplierTouristAttractionList' },
      RES: { label: 'Restaurante', name: 'supplierRestaurantList' },
      LOD: { label: 'Lodges', name: 'supplierLodgeList' },
      ACU: { label: 'Lanchas', name: 'supplierWaterTransportList' },
      STA: { label: 'Staff', name: 'supplierStaffList' },
      OTR: { label: 'Misceláneos', name: 'supplierMiscellaneousList' },
      CRC: { label: 'Cruceros', name: 'supplierCruiseList' },
      OPE: { label: 'Operadores locales', name: 'supplierLocalOperatorList' },
      TRN: { label: 'Trenes', name: 'supplierTrainList' },
      AER: { label: 'Aerolíneas', name: 'supplierAirlinesList' },
    };

    const classificationCodes = [
      supplierSubClassificationId.value,
      supplierClassificationId.value,
      selectedClassificationId.value,
    ];

    const routeByCode = classificationCodes
      .map((code) => (code ? supplierBreadcrumbMap[code] : null))
      .find(Boolean);

    if (routeByCode) {
      return buildSupplierBreadcrumbRoute(routeByCode);
    }

    const classificationLabels = [
      supplierSubClassificationName.value,
      supplierClassificationName.value,
    ];

    const routeByLabel = classificationLabels
      .map((label) =>
        Object.values(supplierBreadcrumbMap).find(
          (supplierRoute) => supplierRoute.label.toLowerCase() === label?.toLowerCase()
        )
      )
      .find(Boolean);

    if (routeByLabel) {
      return buildSupplierBreadcrumbRoute(routeByLabel);
    }

    if (supplierSubClassificationName.value) {
      return {
        name: null,
        meta: {
          breadcrumb: supplierSubClassificationName.value,
        },
      };
    }

    if (supplierClassificationName.value) {
      return {
        name: null,
        meta: {
          breadcrumb: supplierClassificationName.value,
        },
      };
    }

    return null;
  }

  function getBreadcrumbRoutes() {
    breadcrumbRoutes.value = [];
    const matched = route.matched;
    matched.forEach((_route, index) => {
      if (!_route.meta?.breadcrumb) return;
      if (_route.meta.parentBreadcrumb) {
        const parent = matched[index - 1];
        const parentRoute = parent.children.find(
          (childRoute) => childRoute.name === _route.meta.parentBreadcrumb
        );
        if (parentRoute) {
          parentRoute.path = `${parent.path}/${parentRoute.path}`;
          breadcrumbRoutes.value.push(parentRoute);
        }
      }
      breadcrumbRoutes.value.push(_route);

      if (isSupplierFormBreadcrumbRoute(_route.name)) {
        const supplierClassificationBreadcrumb = getSupplierClassificationBreadcrumb();
        if (supplierClassificationBreadcrumb) {
          breadcrumbRoutes.value.splice(
            breadcrumbRoutes.value.length - 1,
            0,
            supplierClassificationBreadcrumb
          );
        }
      }
    });
    lastElementHasBreadcrumb.value = matched[matched.length - 1]?.meta?.breadcrumb !== '';
  }
</script>

<style scoped>
  .breadcrumb-component {
    border-radius: 8px 8px 0 0 !important;
    padding: 20px 20px !important;
    margin-bottom: 0 !important;
    border-bottom: 1px solid #dcdcdc;

    a span {
      font-weight: 500;
      font-size: 12px;
      line-height: 150%;
    }

    a {
      color: #bdbdbd;
    }

    span {
      color: #2f353a;
    }

    .separator {
      color: #bd0d12 !important;
    }
  }
</style>
