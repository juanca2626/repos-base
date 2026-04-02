<template>
  <a-collapse
    class="supplier-container-by-item"
    expandIconPosition="end"
    v-model:activeKey="activeCollapseKey"
  >
    <template v-slot:expandIcon="{ isActive }">
      <font-awesome-icon
        :icon="['fa', 'angle-up']"
        :class="{ 'rotate-0': isActive, 'rotate-180': !isActive }"
      />
    </template>
    <a-collapse-panel key="negotiations">
      <template #header>
        <div class="title-header">Negociaciones / Producto</div>
        <div class="progress-header">
          {{
            `${getProgressCountFormComponent('complete', 'negotiations')} de ${getProgressCountFormComponent('total', 'negotiations')} completados`
          }}
        </div>
      </template>
      <div :key="supplierId || 'new-supplier'">
        <div ref="classificationRef" class="scroll-target">
          <ClassificationComponent
            v-if="isClassificationVisible"
            :key="`classification-${supplierId || 'new'}`"
          />
        </div>
        <div ref="generalInformationRef" class="scroll-target">
          <GeneralInformationComponent
            v-if="isGeneralInformationVisible"
            :key="`general-${supplierId || 'new'}`"
          />
        </div>
        <div ref="locationRef" class="scroll-target">
          <LocationComponent v-if="isLocationVisible" :key="`location-${supplierId || 'new'}`" />
        </div>
        <div ref="contactsRef" class="scroll-target">
          <ContactComponent v-if="isContactsVisible" :key="`contacts-${supplierId || 'new'}`" />
        </div>
        <div ref="commercialInformationRef" class="scroll-target">
          <CommercialInformationComponent
            v-if="isCommercialInformationVisible"
            :key="`commercial-${supplierId || 'new'}`"
          />
        </div>
        <div ref="servicesRef" class="scroll-target">
          <ServicesComponent v-if="isServicesVisible" :key="`services-${supplierId || 'new'}`" />
        </div>
        <div ref="policiesRef" class="scroll-target">
          <SupplierPoliciesComponent
            v-if="isPoliciesVisible"
            :key="`policies-${supplierId || 'new'}`"
          />
        </div>
        <!-- Espaciador para permitir scroll al tope en las últimas secciones -->
        <div class="scroll-spacer" :style="{ height: `${scrollSpacerHeight}px` }"></div>
      </div>
    </a-collapse-panel>
  </a-collapse>
</template>

<script setup lang="ts">
  import { ref, watch, nextTick, type Ref } from 'vue';
  import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
  import ClassificationComponent from './form/supplier-registration/sections/ClassificationComponent.vue';
  import GeneralInformationComponent from './form/supplier-registration/sections/GeneralInformationComponent.vue';
  import LocationComponent from './form/supplier-registration/sections/LocationComponent.vue';
  import ContactComponent from './form/supplier-registration/sections/ContactComponent.vue';
  import CommercialInformationComponent from './form/supplier-registration/sections/CommercialInformationComponent.vue';
  import ServicesComponent from './form/supplier-registration/sections/ServicesComponent.vue';
  import SupplierPoliciesComponent from './form/supplier-registration/sections/SupplierPoliciesComponent.vue';

  defineOptions({
    name: 'SupplierNegotiationsByItemComponent',
  });

  const {
    supplierId,
    activeCollapseKey,
    getProgressCountFormComponent,
    isClassificationVisible,
    isGeneralInformationVisible,
    isLocationVisible,
    isContactsVisible,
    isCommercialInformationVisible,
    isServicesVisible,
    isPoliciesVisible,
    sidebarScrollRequestKey,
    sidebarScrollRequestNonce,
  } = useSupplierGlobalComposable();

  // Refs para cada sección
  const classificationRef = ref<HTMLElement | null>(null);
  const generalInformationRef = ref<HTMLElement | null>(null);
  const locationRef = ref<HTMLElement | null>(null);
  const contactsRef = ref<HTMLElement | null>(null);
  const commercialInformationRef = ref<HTMLElement | null>(null);
  const servicesRef = ref<HTMLElement | null>(null);
  const policiesRef = ref<HTMLElement | null>(null);
  const scrollSpacerHeight = ref(0);

  // Mapa de itemKey a ref
  type SectionKey =
    | 'classification'
    | 'general_information'
    | 'location'
    | 'contacts'
    | 'commercial_information'
    | 'services'
    | 'policies';

  const sectionRefs: Record<SectionKey, Ref<HTMLElement | null>> = {
    classification: classificationRef,
    general_information: generalInformationRef,
    location: locationRef,
    contacts: contactsRef,
    commercial_information: commercialInformationRef,
    services: servicesRef,
    policies: policiesRef,
  };

  const SCROLL_CONTAINER_SELECTOR = '.supplier-container-form-component';
  const SCROLL_BOTTOM_BUFFER = 24;
  let latestScrollRequestId = 0;

  const waitForLayout = () =>
    new Promise<void>((resolve) => {
      requestAnimationFrame(() => resolve());
    });

  const getTargetScrollTop = (sectionElement: HTMLElement, scrollContainer: HTMLElement) => {
    const containerRect = scrollContainer.getBoundingClientRect();
    const sectionRect = sectionElement.getBoundingClientRect();
    return scrollContainer.scrollTop + (sectionRect.top - containerRect.top);
  };

  // Hace scroll a la sección dentro del contenedor, garantizando que quede al tope.
  // Resetea a 0 TODOS los scrolls ancestros (sin importar si son detectables como
  // scrollables) para que el contenedor principal siempre esté visible desde arriba.
  const scrollToSection = async (itemKey: string) => {
    latestScrollRequestId += 1;
    const requestId = latestScrollRequestId;

    await nextTick();
    await waitForLayout();

    if (requestId !== latestScrollRequestId) return;

    const sectionRef = sectionRefs[itemKey as SectionKey];
    if (!sectionRef?.value) return;

    const primaryContainer = sectionRef.value.closest(
      SCROLL_CONTAINER_SELECTOR
    ) as HTMLElement | null;

    if (!primaryContainer) {
      sectionRef.value.scrollIntoView({ behavior: 'instant' as ScrollBehavior, block: 'start' });
      return;
    }

    // Resetear a 0 todos los ancestros externos (scrollables o no — no-op si no scrollan).
    // Esto asegura que el contenedor principal esté visible desde el tope
    // sin importar en qué posición estén los scrolls exteriores.
    let node: HTMLElement | null = primaryContainer.parentElement;
    while (node && node !== document.body && node !== document.documentElement) {
      if (node.scrollTop > 0) {
        node.scrollTo({ top: 0, behavior: 'instant' as ScrollBehavior });
      }
      node = node.parentElement;
    }
    // También cubrir el scroll del window/documento
    if (window.scrollY > 0) {
      window.scrollTo({ top: 0, behavior: 'instant' as ScrollBehavior });
    }

    await waitForLayout();
    if (requestId !== latestScrollRequestId) return;

    // Calcular si necesitamos agregar espacio extra al final para secciones bajas
    const rawTop = getTargetScrollTop(sectionRef.value, primaryContainer);
    const maxScrollTop = primaryContainer.scrollHeight - primaryContainer.clientHeight;
    const extraNeeded = Math.max(0, rawTop - maxScrollTop);

    if (extraNeeded > 0) {
      scrollSpacerHeight.value = Math.ceil(extraNeeded + SCROLL_BOTTOM_BUFFER);
      await nextTick();
      await waitForLayout();
      if (requestId !== latestScrollRequestId) return;
    } else {
      scrollSpacerHeight.value = 0;
    }

    // Scroll instantáneo al tope de la sección dentro del contenedor principal
    const targetTop = Math.max(0, getTargetScrollTop(sectionRef.value, primaryContainer));
    primaryContainer.scrollTo({ top: targetTop, behavior: 'instant' as ScrollBehavior });
  };

  // Scroll solo por navegación explícita desde el sidebar
  watch(
    () => sidebarScrollRequestNonce.value,
    () => {
      if (sidebarScrollRequestKey.value) {
        scrollToSection(sidebarScrollRequestKey.value);
      }
    }
  );
</script>

<style lang="scss">
  .supplier-container-by-item {
    background: #ffffff;

    .ant-collapse-content-active {
      border-top: 1px solid transparent;
    }

    .ant-collapse-content-inactive {
      border-top: 1px solid transparent;
    }

    .ant-collapse-content-box {
      padding: 0 16px 16px 16px !important;
    }

    .title-header {
      color: #212121;
      font-weight: 600;
      font-size: 20px;
      line-height: 28px;
    }

    .progress-header {
      font-weight: 400;
      font-size: 16px;
      line-height: 24px;
      color: #7c7c7c;
    }

    // Espaciador permanente para que cualquier sección pueda anclarse al tope,
    // se expande solo cuando la sección seleccionada lo necesita.
    .scroll-spacer {
      min-height: 0;
    }
  }
</style>
