import { summaryService } from '../../infrastructure/configuration/summary.service';
import type { ProductSupplierSummaryData } from '../../infrastructure/configuration/dtos/summary.interface';

export async function fetchSummaryUseCase(
  productSupplierId: string
): Promise<ProductSupplierSummaryData> {
  const response = await summaryService(productSupplierId);

  return response?.data ?? [];
}
