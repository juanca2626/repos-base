import { ref, watch } from 'vue';
import { technicalSheetApi } from '@/modules/negotiations/api/negotiationsApi';
import type { PaginationInterface } from '@/modules/negotiations/interfaces/pagination.interface';
import type {
  OperationLocationData,
  TransportUnitGroup,
  TransportUnitGroupResponse,
} from '@/modules/negotiations/supplier/register/configuration-module/interfaces';
import { joinOperationLocationNames } from '@/modules/negotiations/supplier/register/helpers/operationLocationHelper';
import { useSupplierFormStoreFacade } from '@/modules/negotiations/supplier/register/composables/useSupplierFormStoreFacade';

export function useUnitList(selectedLocation: OperationLocationData) {
  const { subClassificationSupplierId } = useSupplierFormStoreFacade();

  const isLoading = ref<boolean>(false);

  const pagination = ref<PaginationInterface>({
    current: 1,
    pageSize: 10,
    total: 0,
  });

  const data = ref<TransportUnitGroup[]>([]);

  const columns = [
    {
      title: 'N°',
      dataIndex: 'type_unit_transport_id',
      key: 'type_unit_transport_id',
      align: 'center',
    },
    {
      title: 'Tipo de unidad',
      dataIndex: 'type_unit_transport_code',
      key: 'type_unit_transport_code',
      align: 'center',
    },
    {
      title: 'Descripción de unidad',
      dataIndex: 'type_unit_transport_name',
      key: 'type_unit_transport_name',
    },
    {
      title: 'Ciudad/Zona',
      dataIndex: 'location_name',
      key: 'location_name',
    },
    {
      title: 'Disponibilidad',
      dataIndex: 'availability',
      key: 'availability',
      align: 'center',
    },
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
      align: 'center',
    },
  ];

  const onChange = (page: number, perSize: number) => {
    fetchUnitListData(page, perSize);
  };

  const handleShowTechnicalSheet = () => {
    console.log('handleShowTechnicalSheet');
  };

  const fetchUnitListData = async (page: number = 1, pageSize: number = 10) => {
    isLoading.value = true;

    try {
      const response = await technicalSheetApi.get('supplier-transport-vehicles/list', {
        params: {
          perPage: pageSize,
          page,
          subClassificationSupplierId: subClassificationSupplierId.value,
          supplierBranchOfficeId: selectedLocation.supplier_branch_office_id,
        },
      });

      transformListData(response.data.data);

      pagination.value = {
        current: response.data.pagination.current_page,
        pageSize: response.data.pagination.per_page,
        total: response.data.pagination.total,
      };
    } catch (error) {
      console.error('Error fetching unit list data:', error);
    } finally {
      isLoading.value = false;
    }
  };

  const transformListData = (responseData: TransportUnitGroupResponse[]) => {
    data.value = responseData.map((row: TransportUnitGroupResponse) => {
      const supplierBranchOffice = row.supplier_branch_office;
      const locationName = joinOperationLocationNames(
        ', ',
        supplierBranchOffice.country.name,
        supplierBranchOffice.state.name,
        supplierBranchOffice.city?.name,
        supplierBranchOffice.zone?.name
      );

      return {
        type_unit_transport_id: row.type_unit_transport.id,
        type_unit_transport_code: row.type_unit_transport.code,
        type_unit_transport_name: row.type_unit_transport.name,
        location_name: locationName,
        availability: `${row.number_of_vehicles} ${row.number_of_vehicles === 1 ? 'unidad' : 'unidades'}`,
        status: true, // la lista muestra unidades activas
      };
    });
  };

  watch(
    () => selectedLocation,
    () => {
      fetchUnitListData();
    },
    { deep: true }
  );

  return {
    data,
    columns,
    pagination,
    isLoading,
    onChange,
    handleShowTechnicalSheet,
  };
}
