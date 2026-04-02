import { storeToRefs } from 'pinia';
import { computed, onMounted } from 'vue';
import { useSupplierGlobalComposable } from '@/modules/negotiations/supplier-new/composables/form/supplier-global.composable';
import {
  handleCompleteResponse,
  handleError,
  handleErrorResponse,
} from '@/modules/negotiations/api/responseApi';
import { useSupplierService } from '@/modules/negotiations/supplier-new/service/supplier.service';
import { FormComponentEnum } from '@/modules/negotiations/supplier-new/enums/form-component.enum';
import { usePlaceOperationStore } from '@/modules/negotiations/supplier-new/store/negotiations/supplier-registration/place-operation.store';
import { v4 as uuidv4 } from 'uuid';
import slugify from 'slugify';
import { SupplierProgressModulesEnum } from '@/modules/negotiations/supplier-new/enums/supplier-progress-modules.enum';
import { useSupplierClassificationStoreFacade } from '@/modules/negotiations/supplier-new/composables/form/negotiations/supplier-registration/supplier-classification-store-facade.composable';

export function usePlaceOperationComposable() {
  const { supplierSubClassificationId, supplierSubClassifications } =
    useSupplierClassificationStoreFacade();

  const {
    supplierId,
    supplier,
    isEditMode,
    countryId,
    showFormComponent,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getDisabledComponent,
    getSavedFormComponent,
    handleShowFormSpecific,
    handleIsEditFormSpecific,
    handleLoadingFormSpecific,
    handleLoadingButtonSpecific,
    handleDisabledSpecific,
    handleSavedFormSpecific,
  } = useSupplierGlobalComposable();

  const { createSupplier, updateSupplier } = useSupplierService;

  const placeOperationStore = usePlaceOperationStore();

  const {
    formState,
    initialFormData,
    formRef,
    countryStateLocations,
    countryStateLocationsLoaded,
  } = storeToRefs(placeOperationStore);

  const { loadCountryStateLocations, loadZoneLocations } = placeOperationStore;

  const getListItem = computed(() => {
    return formState.value.locations
      .map((location: any) => {
        const fields = [
          {
            key: 'supplierSubClassificationId',
            label: 'Clasificación del proveedor:',
            format: (value: any) => {
              if (Array.isArray(value)) {
                return value
                  .map((id: any) => {
                    const classification = supplierSubClassifications.value.find(
                      (c: any) => c.id === id
                    );
                    return classification?.name || '';
                  })
                  .filter(Boolean)
                  .join(', ');
              } else {
                const classification = supplierSubClassifications.value.find(
                  (c: any) => c.id === value
                );
                return classification?.name || '';
              }
            },
          },
          {
            key: 'city',
            label: 'Departamento / Ciudad:',
            format: (value: string) => {
              const locationItem: any =
                countryStateLocations.value.find(
                  (loc: { id: string; name?: string }) => loc.id === value
                ) || {};
              return locationItem.name || '';
            },
          },
          {
            key: 'zone',
            label: 'Zona / Sede:',
            format: (value: string) => {
              const zone: any =
                (location.zonesLocations || []).find(
                  (loc: { id: string; name?: string }) => loc.id === value
                ) || {};
              return zone.name || '';
            },
          },
        ];

        return fields.map(({ key, label, format }) => ({
          title: label,
          value: format ? format(location[key]) : location[key],
        }));
      })
      .flat();
  });

  const getIsFormValid = computed(() => {
    return (
      Array.isArray(formState.value.locations) &&
      formState.value.locations.length > 0 &&
      formState.value.locations.every((loc: any) => !!loc.city && String(loc.city).trim() !== '')
    );
  });

  const getRequestData = () => {
    const data = (formState.value.locations || [])
      .map((loc: any) => {
        let countryId = null,
          stateId = null,
          cityId = null,
          zoneId = null;

        if (loc.city) {
          [countryId, stateId, cityId] = String(loc.city)
            .split('-')
            .map(Number)
            .map((v: any) => (v === 0 ? null : v));
        }

        if (loc.zone) {
          [, , , zoneId] = String(loc.zone)
            .split('-')
            .map(Number)
            .map((v: any) => (v === 0 ? null : v));
        }

        return {
          country_id: countryId ?? null,
          state_id: stateId ?? null,
          city_id: cityId ?? null,
          zone_id: zoneId ?? null,
          supplier_sub_classification_id: loc.supplierSubClassificationId ?? null,
          ...(typeof loc.id === 'number' && !isNaN(loc.id) ? { id: loc.id } : {}),
        };
      })
      .filter(
        (item: any) =>
          item.country_id !== null ||
          item.state_id !== null ||
          item.city_id !== null ||
          item.zone_id !== null ||
          item.sub_classification_supplier_id !== null
      );

    const supplierSubClassificationIds = (formState.value.locations || []).flatMap((loc: any) =>
      Array.isArray(loc.supplierSubClassificationId) ? loc.supplierSubClassificationId : []
    );

    const allExist = supplierSubClassificationId.value.every((id: any) =>
      supplierSubClassificationIds.includes(id)
    );

    if (!allExist) {
      handleErrorResponse(
        'Todas las clasificaciones del proveedor deben estar asociadas a un lugar de operación.'
      );
      return;
    }

    return data.length > 0
      ? {
          operations: data,
          classifications: supplierSubClassificationId.value,
        }
      : {
          operations: [],
          classifications: supplierSubClassificationId.value,
        };
  };

  const handleClose = () => {
    const isValid = getIsFormValid.value;
    const isSaved = getSavedFormComponent.value(FormComponentEnum.PLACE_OPERATION);

    formState.value = { ...initialFormData.value };

    if (isValid && isSaved) {
      handleIsEditFormSpecific(FormComponentEnum.PLACE_OPERATION, true);
      return;
    }

    handleShowFormSpecific(FormComponentEnum.PLACE_OPERATION, false);
  };

  const handleSaveForm = async () => {
    const request = getRequestData();

    if (!request) return;

    try {
      handleLoadingFormSpecific(FormComponentEnum.PLACE_OPERATION, true);
      handleLoadingButtonSpecific(FormComponentEnum.PLACE_OPERATION, true);

      const { data } = supplierId.value
        ? await updateSupplier(supplierId.value, request)
        : await createSupplier(request);

      if (!supplierId.value) {
        supplierId.value = data.id;
      }

      const { classifications } = data;
      const { operations } = request;

      const classificationById = new Map(
        classifications.map((classification: any) => [
          classification.supplier_sub_classification_id,
          classification,
        ])
      );

      const operationBySubClassificationId = new Map(
        operations
          .filter(
            (operation: any) =>
              Array.isArray(operation.supplier_sub_classification_id) ||
              operation.supplier_sub_classification_id != null
          )
          .flatMap((operation: any) =>
            Array.isArray(operation.supplier_sub_classification_id)
              ? operation.supplier_sub_classification_id.map((id: any) => [id, operation])
              : [[operation.supplier_sub_classification_id, operation]]
          )
      );

      formState.value.locations.forEach((location: any) => {
        const subClassIds = Array.isArray(location.supplierSubClassificationId)
          ? location.supplierSubClassificationId
          : [location.supplierSubClassificationId];

        subClassIds.forEach((subClassId: any) => {
          const selectedClassification: any = classificationById.get(subClassId);

          const selectedOperation: any = operationBySubClassificationId.get(
            selectedClassification?.supplier_sub_classification_id
          );

          if (selectedClassification && selectedOperation) {
            const matchingBranch = (selectedClassification.operations || []).find(
              (branch: any) =>
                branch.country_id === selectedOperation.country_id &&
                branch.state_id === selectedOperation.state_id &&
                branch.city_id === selectedOperation.city_id &&
                branch.zone_id === selectedOperation.zone_id
            );

            location.id = matchingBranch?.supplier_branch_office_id;
          }
        });
      });

      handleCompleteResponse(data);

      handleIsEditFormSpecific(FormComponentEnum.PLACE_OPERATION, true);
      handleSavedFormSpecific(FormComponentEnum.PLACE_OPERATION, true);
      handleDisabledSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
    } catch (error: any) {
      handleError(error);
      console.log('⛔ Error save: ', error.message);
    } finally {
      handleLoadingFormSpecific(FormComponentEnum.PLACE_OPERATION, false);
      handleLoadingButtonSpecific(FormComponentEnum.PLACE_OPERATION, false);
    }
  };

  const handleSave = async () => {
    try {
      await formRef.value?.validate();
      await handleSaveForm();
    } catch (error: any) {
      console.log('⛔ Validation error Place Operation: ', error.message);
    }
  };

  const handleShowForm = () => {
    handleIsEditFormSpecific(FormComponentEnum.PLACE_OPERATION, false);
  };

  const handleRemoveLocation = (item: any) => {
    const index = formState.value.locations.indexOf(item);
    if (index !== -1) {
      formState.value.locations.splice(index, 1);
    }
  };

  const handleAddLocations = () => {
    formState.value.locations.push({
      countryStateLocations: [],
      zonesLocations: [],
      zonesLocationsDisabled: true,
      zonesLocationsLoading: false,
      supplierSubClassificationId: undefined,
      city: undefined,
      zone: undefined,
      id: uuidv4().replace(/-/g, ''),
    });
  };

  const filterOption = (search: string, option: any) => {
    return slugify(option.name, { lower: true, replacement: '-', trim: true }).includes(
      slugify(search, { lower: true, replacement: '-', trim: true })
    );
  };

  const handleFilterCountryStateLocations = async (index: number, id: string) => {
    const [, , cityId] = id.split('-').map(Number);

    formState.value.locations[index].zonesLocationsLoading = true;
    formState.value.locations[index].zone = undefined;

    const { zonesLocations } = await loadZoneLocations(countryId.value, cityId);

    formState.value.locations[index] = {
      ...formState.value.locations[index],
      zonesLocationsDisabled: zonesLocations.length === 0,
      zonesLocationsLoading: false,
      zonesLocations,
    };
  };

  const initializeFormData = async () => {
    try {
      const { operations = [], supplier_progress_bar = [] } = supplier.value || {};

      const locationsData = await Promise.all(
        operations.map(async (o: any) => {
          const [countryId, , cityId] = o.city
            .split('-')
            .map(Number)
            .map((v: any) => (v === 0 ? null : v));

          const { countryStateLocations } = await loadCountryStateLocations(countryId);
          const { zonesLocations } = await loadZoneLocations(countryId, cityId);

          return {
            countryStateLocations,
            zonesLocations,
          };
        })
      );

      const data = operations.map((o: any, idx: number) => ({
        countryStateLocations: locationsData[idx].countryStateLocations,
        zonesLocations: locationsData[idx].zonesLocations,
        zonesLocationsDisabled: locationsData[idx].zonesLocations.length === 0,
        zonesLocationsLoading: false,
        supplierSubClassificationId: o.supplier_sub_classification_id,
        city: o.city,
        zone: o.zone,
        id: o.id,
      }));

      if (operations.length > 0) {
        initialFormData.value.locations = data;
        formState.value.locations = data;
      }

      const nextProgress = supplier_progress_bar.find(
        (spb: any) =>
          spb.supplier_progress_module_id === SupplierProgressModulesEnum.CONTACT_INFORMATION
      );

      handleDisabledSpecific(FormComponentEnum.CONTACT_INFORMATION, false);
      if (nextProgress?.percentage) {
        handleSavedFormSpecific(FormComponentEnum.CONTACT_INFORMATION, true);
        handleIsEditFormSpecific(FormComponentEnum.CONTACT_INFORMATION, true);
        handleShowFormSpecific(FormComponentEnum.CONTACT_INFORMATION, true);
      }
    } catch (error: any) {
      console.log('⛔ Error al inicializar datos del formulario: ', error.message);
    }
  };

  onMounted(async () => {
    if (!countryStateLocationsLoaded.value) {
      try {
        countryStateLocationsLoaded.value = true;
        const response = await loadCountryStateLocations(countryId.value);
        countryStateLocations.value = response.countryStateLocations;
      } catch {
        countryStateLocations.value = [];
      }
    }

    if (isEditMode.value) {
      await initializeFormData();
    }
  });

  return {
    formState,
    formRef,
    showFormComponent,
    getShowFormComponent,
    getIsEditFormComponent,
    getLoadingFormComponent,
    getLoadingButtonComponent,
    getListItem,
    getIsFormValid,
    getDisabledComponent,
    supplierSubClassifications,
    countryStateLocations,
    supplierSubClassificationId,
    handleClose,
    handleSave,
    handleShowForm,
    handleDisabledSpecific,
    handleRemoveLocation,
    handleAddLocations,
    handleFilterCountryStateLocations,
    filterOption,
  };
}
