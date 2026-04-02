import { computed, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useSupplierAssignmentStoreFacade } from '@/modules/negotiations/products/general/composables/form/useSupplierAssignmentStoreFacade';
import { useSupplierAssignmentFormStoreFacade } from '@/modules/negotiations/products/general/composables/form/useSupplierAssignmentFormStoreFacade';
import { useSelectedServiceType } from '@/modules/negotiations/products/general/composables/useSelectedServiceType';
import { useConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useConfigurationStore';
import { resolveDrawerComponent } from '@/modules/negotiations/products/configuration/drawers/resolvers/drawerComponentResolver';

export function useSupplierAssignmentList() {
  const router = useRouter();
  const configurationStore = useConfigurationStore();

  const { setIsSupplierAssignmentForm } = useSupplierAssignmentStoreFacade();

  const { assignedSuppliers } = useSupplierAssignmentFormStoreFacade();

  const { productSupplierType } = useSelectedServiceType();

  const supplierOriginalId = ref<number | null>(null);
  const productSupplierId = ref<string | null>(null);

  const columns = [
    {
      title: 'Tipo de proveedor',
      dataIndex: 'classificationName',
      key: 'classificationName',
    },
    {
      title: 'Código del proveedor',
      dataIndex: 'code',
      key: 'code',
    },
    // {
    //   title: 'Avance',
    //   dataIndex: 'progress',
    //   key: 'progress',
    //   align: 'center',
    // },
    {
      title: 'Estado',
      dataIndex: 'status',
      key: 'status',
      align: 'center',
    },
    {
      title: 'Acciones',
      dataIndex: 'action',
      key: 'action',
      width: '10%',
      align: 'center',
    },
  ];

  const showDrawertConfigurationForm = ref<boolean>(false);

  const loadingConfigurationId = ref<string | null>(null);

  const handleAssignmentSupplier = () => {
    setIsSupplierAssignmentForm(true);
  };

  const checkExistingConfiguration = async (productSupplierIdValue: string): Promise<boolean> => {
    try {
      loadingConfigurationId.value = productSupplierIdValue;

      const configuration = await configurationStore.loadConfiguration(
        productSupplierIdValue,
        productSupplierType.value,
        false
      );

      if (configuration?.items?.length && configuration.items.length > 0) {
        router.push({
          name: 'serviceConfiguration',
          params: { id: productSupplierIdValue },
        });
        return true;
      }
      return false;
    } catch (error) {
      console.error('Error al verificar configuración:', error);
      return false;
    } finally {
      loadingConfigurationId.value = null;
    }
  };

  const handleConfiguration = async (supplierId: number, productSupplierIdValue: string | null) => {
    supplierOriginalId.value = supplierId;
    productSupplierId.value = productSupplierIdValue;

    if (productSupplierIdValue) {
      const hasConfiguration = await checkExistingConfiguration(productSupplierIdValue);
      if (hasConfiguration) {
        return;
      }
    }

    // Si no existe configuración, abrir el drawer como antes
    showDrawertConfigurationForm.value = true;
  };

  const configurationFormComponent = computed(() => {
    return resolveDrawerComponent(productSupplierType.value, 'CREATE');
  });

  return {
    columns,
    assignedSuppliers,
    showDrawertConfigurationForm,
    configurationFormComponent,
    supplierOriginalId,
    productSupplierId,
    loadingConfigurationId,
    handleAssignmentSupplier,
    handleConfiguration,
  };
}
