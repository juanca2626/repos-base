import type {
  Inclusion,
  Requirement,
} from '@/modules/negotiations/products/configuration/content/shared/interfaces/content';
import type { TrainContentRequest } from '../interfaces/train-content-request.interface';

export const useTrainContentRequest = (
  textRemark: string,
  inclusions: Inclusion[],
  requirements: Requirement[]
) => {
  const buildRequest = (): TrainContentRequest => {
    const text = {
      html: textRemark || '',
      status: 'PENDING',
    };

    const mappedInclusions = inclusions
      .filter(
        (inclusion: Inclusion) =>
          inclusion.description !== null && inclusion.description !== undefined
      )
      .map((inclusion: Inclusion) => {
        return {
          inclusionCode: String(inclusion.description),
          included: inclusion.incluye,
          visibleToClient: inclusion.visibleCliente,
        };
      });

    const mappedRequirements = requirements
      .filter(
        (requirement: Requirement) =>
          requirement.description !== null && requirement.description !== undefined
      )
      .map((requirement: Requirement) => {
        return {
          requirementCode: String(requirement.description),
          visibleToClient: requirement.visibleCliente,
        };
      });

    return {
      text,
      inclusions: mappedInclusions,
      requirements: mappedRequirements,
    };
  };

  return {
    buildRequest,
  };
};
