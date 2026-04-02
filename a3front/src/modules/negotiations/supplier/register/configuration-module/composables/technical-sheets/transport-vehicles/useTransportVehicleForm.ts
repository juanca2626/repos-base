import { onMounted, reactive, ref } from 'vue';
import dayjs from 'dayjs';
import type { Rule } from 'ant-design-vue/es/form';
import type { FormInstance } from 'ant-design-vue';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import type {
  SupplierTransportVehicle,
  TransportVehicleForm,
  TransportVehicleResourceResponse,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import {
  filterOption,
  mapItemsToOptions,
} from '@/modules/negotiations/supplier/register//helpers/supplierFormHelper';
import type {
  DrawerEmitTypeInterface,
  SelectOption,
} from '@/modules/negotiations/supplier/interfaces/supplier-form.interface';
import type { OperationLocationData } from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';
import { handleError, handleSuccessResponse } from '@/modules/negotiations/api/responseApi';
import { emit as emitBus, on } from '@/modules/negotiations/api/eventBus';

export const useTransportVehicleForm = (
  emit: DrawerEmitTypeInterface,
  locationData: OperationLocationData[]
) => {
  const resource = 'supplier-transport-vehicles';
  const { subClassificationSupplierId } = useSupplierFormStoreFacade();

  const isLoading = ref<boolean>(false);
  const formRefTransportVehicle = ref<FormInstance | null>(null);

  const initFormData: TransportVehicleForm = {
    id: null,
    supplierBranchOfficeId: null,
    subClassificationSupplierId: null,
    autoBrandId: null,
    typeUnitTransportId: null,
    licensePlate: null,
    manufacturingYear: null,
    numberSeats: null,
    description: null,
    method: 'POST',
  };

  const formState = reactive<TransportVehicleForm>({ ...initFormData });
  const autoBrands = ref<SelectOption[]>([]);
  const operationLocations = ref<SelectOption[]>([]);
  const typeUnits = ref<SelectOption[]>([]);

  const getManufacturingYearsData = () => {
    const minManufacturingYear = 2000;
    const maxManufacturingYear = dayjs().year();
    const years = [];

    for (let year = minManufacturingYear; year <= maxManufacturingYear; year++) {
      years.push({ label: year.toString(), value: year });
    }

    return years;
  };

  const fetchTransportVehicleResources = async () => {
    isLoading.value = true;

    try {
      const { data } = await technicalSheetApi.get<TransportVehicleResourceResponse>(
        `${resource}/resources`,
        {
          params: {
            keys: ['auto_brands', 'type_units'],
          },
        }
      );

      typeUnits.value = mapTypeUnits(data);
      autoBrands.value = mapItemsToOptions(data.data.auto_brands);
    } catch (error) {
      console.error('Error fetching type unit transports data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const mapTypeUnits = (data: TransportVehicleResourceResponse) => {
    return data.data.type_units.map((item) => {
      return {
        value: item.id,
        label: `${item.code} - ${item.name}`,
      };
    });
  };

  const mapOperationLocations = async () => {
    operationLocations.value = locationData.map((item) => {
      return {
        value: item.supplier_branch_office_id ?? -1,
        label: item.display_name,
      };
    });
  };

  const initForm = (): void => {
    Object.assign(formState, { ...initFormData });
  };

  const resetForm = () => {
    formRefTransportVehicle.value?.resetFields();
  };

  const getRequestData = () => {
    return {
      supplier_branch_office_id: formState.supplierBranchOfficeId,
      sub_classification_supplier_id: subClassificationSupplierId.value,
      auto_brand_id: formState.autoBrandId,
      type_unit_transport_id: formState.typeUnitTransportId,
      license_plate: formState.licensePlate,
      manufacturing_year: formState.manufacturingYear,
      number_seats: formState.numberSeats,
      description: formState.description,
    };
  };

  const handleClose = (): void => {
    resetForm();
    initForm();
    emit('update:showDrawerForm', false);
  };

  on('editTransportVehicle', (item: SupplierTransportVehicle) => {
    setFormStateToUpdate(item);
  });

  const setFormStateToUpdate = async (item: SupplierTransportVehicle) => {
    formState.id = item.id;
    formState.supplierBranchOfficeId = item.supplierBranchOfficeId;
    formState.autoBrandId = item.autoBrand.id;
    formState.typeUnitTransportId = item.typeUnit.id;
    formState.licensePlate = item.licensePlate;
    formState.manufacturingYear = item.manufacturingYear;
    formState.numberSeats = item.numberSeats;
    formState.description = item.description;
    formState.method = 'PUT';
    emit('update:showDrawerForm', true);
  };

  const saveForm = async () => {
    const request = getRequestData();

    try {
      isLoading.value = true;

      if (formState.method === 'POST') {
        const { data } = await technicalSheetApi.post(`${resource}`, request);
        handleSuccessResponse(data);
      } else {
        const { data } = await technicalSheetApi.put(`${resource}/${formState.id}`, request);
        handleSuccessResponse(data);
      }

      emitBus('reloadTransportVehicleList');
      resetForm();
      handleClose();
    } catch (error: any) {
      handleError(error);
      console.error('Error save supplier transport vehicle:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const formRules: Record<string, Rule[]> = {
    supplierBranchOfficeId: [
      { required: true, message: 'Debe seleccionar la sede', trigger: 'change' },
    ],
    autoBrandId: [
      { required: true, message: 'Debe seleccionar la marca/modelo', trigger: 'change' },
    ],
    typeUnitTransportId: [
      { required: true, message: 'Debe seleccionar el tipo de unidad', trigger: 'change' },
    ],
    licensePlate: [
      { required: true, message: 'Debe ingresar la placa', trigger: 'change' },
      { pattern: /^[A-Z0-9]+$/, message: 'La placa debe tener letras mayúsculas y números' },
      { len: 6, message: 'La placa debe tener 6 caracteres', trigger: 'change' },
    ],
    manufacturingYear: [{ required: true, message: 'Debe ingresar el año', trigger: 'change' }],
    numberSeats: [
      { required: true, message: 'Debe ingresar la cantidad de asientos', trigger: 'change' },
    ],
  };

  const handleSubmit = async () => {
    try {
      await formRefTransportVehicle.value?.validate();
      await saveForm();
    } catch (error) {
      console.error('Validation failed:', error);
    }
  };

  onMounted(async () => {
    await Promise.all([fetchTransportVehicleResources()]);
    mapOperationLocations();
    initForm();
  });

  return {
    formRefTransportVehicle,
    formState,
    isLoading,
    formRules,
    operationLocations,
    autoBrands,
    typeUnits,
    filterOption,
    saveForm,
    handleClose,
    handleSubmit,
    getManufacturingYearsData,
  };
};
