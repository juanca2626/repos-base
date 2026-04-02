import { computed } from 'vue';
import type { Component } from 'vue';
import CommercialInformationCruiseComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/cruises/commercial-information-cruise-component.vue';
import { useSupplierClassificationStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/supplier-classification-store-facade.composable';
import CommercialInformationComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/commercial-information-component.vue';
import SupplierTextareaFieldComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/supplier-textarea-field-component.vue';
import SupplierLanguageFieldComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/supplier-language-field-component.vue';
import CommercialScheduleComponent from '@/modules/negotiations/supplier-new/components/form/negotiations/form/supplier-registration/commercial-schedule-component.vue';

export function useSupplierNegotiationComponentResolver() {
  const { supplierClassificationId } = useSupplierClassificationStoreFacade();

  // mapeo de componentes por clasificacion
  const componentsByClassification: Record<number, Record<string, Component>> = {
    // ['RES']: {
    //   commercialInformation: InformationCommercialComponent,
    // },
    ['CRC']: {
      commercialInformation: CommercialInformationCruiseComponent,
    },
  };

  const getComponent = (classificationId: number | null): Record<string, Component> => {
    if (!classificationId) return {};
    return componentsByClassification[classificationId] || {};
  };

  const getComponentByType = (type: string): Component | null => {
    const components = getComponent(supplierClassificationId.value);
    const comp = components[type] || null;

    console.log('[Resolver] supplierClassificationId:', supplierClassificationId.value);
    console.log(`[Resolver] Componente para tipo "${type}":`, comp);

    return comp;
  };

  // mapeo de componente para informacion comercial por clasificacion
  const commercialInformationComponentMap: Record<number, Record<string, Component>> = {
    ['AER']: SupplierTextareaFieldComponent,
    ['TRP']: SupplierTextareaFieldComponent,
    ['TRN']: SupplierTextareaFieldComponent,
    ['ACU']: SupplierTextareaFieldComponent,
    ['OPE']: CommercialInformationCruiseComponent,
    ['CRC']: CommercialInformationCruiseComponent,
    ['LOD']: CommercialInformationCruiseComponent,
    ['RES']: CommercialInformationComponent,
    ['STA']: SupplierLanguageFieldComponent,
    ['ATT']: CommercialScheduleComponent,
    ['OTR']: SupplierTextareaFieldComponent,
  };

  const resolvedCommercialInformationComponent = computed(() => {
    if (!supplierClassificationId.value) return null;

    return commercialInformationComponentMap[supplierClassificationId.value] || null;
  });

  return {
    getComponentByType,
    resolvedCommercialInformationComponent,
  };
}
