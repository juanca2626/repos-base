import { trainServiceDetailsService } from '@/modules/negotiations/products/configuration/content/train/serviceDetails/services/trainServiceDetailsService';
import { useTrainConfigurationStore } from '@/modules/negotiations/products/configuration/stores/useTrainConfigurationStore';
import type { ServiceDetailsResponse } from '@/modules/negotiations/products/configuration/interfaces/service-details.interface';
import type { ServiceDetailsLoader } from '@/modules/negotiations/products/configuration/interfaces/loaders/serviceDetailsLoader.interface';

export class TrainServiceDetailsLoader implements ServiceDetailsLoader {
  private trainStore = useTrainConfigurationStore();

  async loadServiceDetails(productSupplierId: string): Promise<ServiceDetailsResponse[] | null> {
    const response = await trainServiceDetailsService.fetchTrainServiceDetails(productSupplierId);

    if (response.success && response.data) {
      // Para Train, mapear trainTypeCode a supplierCategoryCode para el store
      const mappedData: ServiceDetailsResponse[] = response.data.map((trainDetail) => ({
        id: trainDetail.id,
        groupingKeys: {
          operatingLocationKey: trainDetail.groupingKeys.operatingLocationKey,
          trainTypeCode: trainDetail.groupingKeys.trainTypeCode,
        },
        content: trainDetail.content as any, // El content de Train es diferente pero el store lo acepta
        completionStatus: trainDetail.completionStatus,
      }));

      // Guardar en el store de trains
      this.trainStore.setServiceDetails(mappedData);

      return mappedData;
    }

    return null;
  }
}
